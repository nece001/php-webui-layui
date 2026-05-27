<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\DataGridColumn;
use Nece\WebUi\Render;

class DataGridRender extends Render
{
    protected $grid_id = '';

    protected $html = [];
    protected $javascript = [];
    protected $toolbar = false;
    protected $operation = false;
    protected $primary_key = 'id';
    protected $js_class = 'table';

    public function render(): string
    {
        $this->toolbar = $this->component->getConfig('toolbar', false);
        $this->grid_id = $this->component->getId();
        $this->buildJavascript();

        $this->html[] = $this->renderHtml('table', ['id' => $this->grid_id, 'class' => 'layui-hide', 'lay-filter' => $this->grid_id]);

        return implode('', $this->html);
    }

    protected function buildJavascript(): void
    {
        $this->buildActionJavascriptFunction();
        $params = $this->buildParamsJson();

        $javascript = "layui.{$this->js_class}.render($params);";
        PageRender::addJavaScriptCode($javascript);

        foreach ($this->javascript as $javascript) {
            PageRender::addJavaScriptCode($javascript);
        }
    }

    protected function buildParamsJson(): string
    {
        $this->buildSearchFormJavascriptFunction();
        $this->buildDataParseFuncitonJavascript();
        $this->buildToolbarTemplate();
        $this->buildOperationTemplate();

        $cols = $this->buildColumnsJson();
        $exportToolJson = $this->buildExportToolJson();
        $refreshToolJson = $this->buildRefreshToolJson();

        $default_toolbar = ['refresh_toolbar_export_json', 'filter', 'print'];
        if ($exportToolJson) {
            $default_toolbar[] = 'default_toolbar_export_json';
        } else {
            $default_toolbar[] = 'exports';
        }

        $params = [
            'id' => $this->grid_id,
            'elem' => '#' . $this->grid_id,
            'cols' => 'cols_json_placeholder',
            'url' => $this->component->getConfig('data_url'),
            'data' => $this->component->getConfig('data'),
            'toolbar' => $this->toolbar ? '#' . $this->grid_id . '_toolbar_template' : null,
            'defaultToolbar' => $default_toolbar,
            'page' => $this->component->getConfig('pagination'),
            'even' => true,
            'limit' => $this->component->getConfig('page_size'),
            'request' => [
                'pageName' => $this->component->getConfig('page_var_name', 'page'),
                'limitName' => $this->component->getConfig('page_size_var_name', 'page_size'),
            ],
            'parseData' => 'parseData_function',
            'lineStyle' => $this->component->getConfig('line_style'),
        ];

        $params = $this->arrayFilter($params);
        return $this->arrayToJavaScriptObject($params, [
            'cols_json_placeholder' => $cols,
            'parseData_function' => 'data_grid_parse_data_function',
            'default_toolbar_export_json' => $exportToolJson,
            'refresh_toolbar_export_json' => $refreshToolJson,
        ]);
    }

    protected function buildColumnsJson(): string
    {
        $children = $this->component->getChildren();
        $checkbox = $this->component->getConfig('checkbox');

        $cols = [];
        foreach ($children as $key => $child) {
            $columns = $child->getChildren();

            $row = [];
            if ($key === 0 && $checkbox) {
                $row[] = '{"type":"checkbox", "fixed": "left"}';
            }

            foreach ($columns as $column) {
                if ($column->getConfig('primary_key')) {
                    $this->primary_key = $column->getConfig('field');
                }

                $template_id = $this->buildColumTemplate($column);

                $params = [
                    'field' => $column->getConfig('field'),
                    'title' => $column->getConfig('title'),
                    'width' => $column->getConfig('width'),
                    'fixed' => $column->getConfig('fixed'),
                    'templet' => $template_id,
                    'rowspan' => $column->getConfig('row_span'),
                    'colspan' => $column->getConfig('col_span'),
                    'align' => $column->getConfig('align'),
                ];

                $params = array_filter($params, function ($value) {
                    return !is_null($value);
                });

                $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);
                $row[] = $params_json;
            }

            if ($this->operation) {
                $operation = $child->getConfig('operation');
                $template_id = '#' . $this->grid_id . '_row_operation';
                if ($operation) {
                    $oper = [
                        'fixed' => 'right',
                        'templet' => $template_id,
                        'title' => $operation->getConfig('title'),
                        'width' => $operation->getConfig('width'),
                        'rowspan' => $operation->getConfig('row_span'),
                        'colspan' => $operation->getConfig('col_span'),
                        'align' => $operation->getConfig('align'),
                    ];
                } else {
                    $oper = [
                        'fixed' => 'right',
                        'templet' => $template_id,
                        'title' => 'Operation',
                        'align' => 'center',
                    ];
                }
                $oper = $this->arrayFilter($oper);
                $row[] = json_encode($oper, JSON_UNESCAPED_UNICODE);
            }

            $cols[] = '[' . implode(',', $row) . ']';
        }

        return '[' . implode(',', $cols) . ']';
    }

    protected function  buildToolbarTemplate(): void
    {
        $permissions = $this->component->getConfig('permissions', []);
        $tools = $this->component->getConfig('tools', []);
        if ($tools) {
            $this->toolbar = true;

            $tools_data = [];
            $buttons = [];
            foreach ($tools as $tool) {
                $url = $tool->getConfig('url');
                $action = $tool->getConfig('action');
                if ($action) {
                    $event = $action->getConfig('event_name');
                    $url_path = $action->getConfig('url');
                    $tools_data[$event] = $action->toArray();
                }

                $url_path = parse_url($url, PHP_URL_PATH);
                if (in_array($url_path, $permissions)) {
                    $buttons[] = $this->getRender($tool)->render();
                }
            }

            $html = '<script type="text/html" id="' . $this->grid_id . '_toolbar_template">
                <div class="layui-btn-group">
                    ' . implode('', $buttons) . '
                </div>
            </script>';
            $tools_json = json_encode($tools_data, JSON_UNESCAPED_UNICODE);

            $javascript = "layui.{$this->js_class}.on('toolbar($this->grid_id)', function(obj){
                console.log('toolbar data:', obj);

                var tools = {$tools_json};
                var checkdata = layui.{$this->js_class}.checkStatus(obj.config.id);
                var values = '';
                if(checkdata.data.length > 0){
                    values = checkdata.data.map(function(item){return item['{$this->primary_key}'];}).join(',');
                }

                try{
                    if(tools[obj.event]){
                        var tool = tools[obj.event];
                        var type = tool['type'];
                        var title = tool['title'];
                        var data = tool['data'] || {};

                        for(var key in data){
                            data[key] = data[key].replace('{value}', values);
                        }

                        var request = {
                            url: tool['url'].replace('{value}', values),
                            method: tool['method'] || 'get',
                            is_json: tool['is_json'] || false,
                            data: data
                        };

                        if(type === 'form'){
                            var action = tool['submit_action'];
                            var submit_data = action['data'] || {};

                            for(var key in submit_data){
                                submit_data[key] = submit_data[key].replace('{value}', values);
                            }

                            var submit = {
                                url: (action['url'] || '').replace('{value}', values),
                                method: action['method'] || 'post',
                                is_json: action['is_json'] || false,
                                data: submit_data
                            };

                            {$this->js_class}_data_grid_open_form_function('{$this->grid_id}', title, request, submit);
                        }else{
                            {$this->js_class}_data_grid_do_request_function('{$this->grid_id}', title, request);
                        }
                    }
                }catch(e){console.log(e);}
            })";

            $this->html[] = $html;
            $this->javascript[] = $javascript;
        }
    }

    protected function  buildOperationTemplate()
    {
        $permissions = $this->component->getConfig('permissions', []);
        $operations = $this->component->getConfig('operations', []);

        if ($operations) {
            $this->operation = true;

            $operations_data = [];
            $buttons = [];
            foreach ($operations as $row) {
                $button = $row['button'];
                $condition = $row['condition'];

                $html = $this->templateReplaceOutput($this->getRender($button)->render());
                if ($condition) {
                    $condition = $this->templateReplaceVarName($condition);
                    $tmp = [
                        '{{# if (' . $condition . ') { }}',
                        $html,
                        '{{# } }}',
                    ];

                    $html = implode('', $tmp);
                }

                $action = $button->getConfig('action');
                $url = $button->getConfig('url');
                if ($action) {
                    $event = $action->getConfig('event_name');
                    $url = $action->getConfig('url');
                    $operations_data[$event] = $action->toArray();
                }

                $url_path = parse_url($url, PHP_URL_PATH);
                if (in_array($url_path, $permissions)) {
                    $buttons[] = $html;
                }
            }
            $operations_json = json_encode($operations_data, JSON_UNESCAPED_UNICODE);

            $html = '<script type="text/html" id="' . $this->grid_id . '_row_operation"><div class="layui-clear-space">' . implode('', $buttons) . '</div></div></script>';
            $javascript = "layui.{$this->js_class}.on('tool({$this->grid_id})', function(obj){
                console.log('operation data:', obj);

                var operations = {$operations_json};
                try{
                    if(operations[obj.event]){
                        var operation = operations[obj.event];
                        var type = operation['type'];
                        var title = operation['title'];
                        var data = operation['data'] || {};

                        for(var key in data){
                            data[key] = data[key].replace('{value}', obj.data.id);
                        }

                        var request = {
                            url: operation['url'].replace('{value}', obj.data.id),
                            method: operation['method'] || 'get',
                            is_json: operation['is_json'] || false,
                            data: data
                        };

                        if(type === 'form'){
                            var action = operation['submit_action'];
                            var param_name = action['param_name'] || 'id';
                            var save_data = action['data'] || {};
                            save_data['{$this->primary_key}'] = obj.data.id;
                            
                            for(var key in save_data){
                                if(key == param_name){
                                    save_data[key] = obj.data.id;
                                }
                            }

                            var submit = {
                                url: (action['url'] || '').replace('{value}', obj.data.id),
                                method: action['method'] || 'post',
                                is_json: action['is_json'] || false,
                                data: save_data
                            };

                            {$this->js_class}_data_grid_open_form_function('{$this->grid_id}', title, request, submit);
                        }else{
                            {$this->js_class}_data_grid_do_request_function('{$this->grid_id}', title, request);
                        }
                    }
                }catch(e){}
            })";

            $this->html[] = $html;
            $this->javascript[] = $javascript;
        }
    }

    protected function  buildDataParseFuncitonJavascript()
    {
        $javascript = "function data_grid_parse_data_function(res){
            return {
                code: res.code,
                msg: res.message,
                count: res.data.total,
                data: res.data.items
            };
        }";
        PageRender::addJavaScriptCode($javascript, 'data_grid_parseData_function');
    }

    protected function  buildExportToolJson(): string
    {
        $export_url = $this->component->getConfig('export_url');
        if (!$export_url) {
            return '';
        }

        $json = "{name: 'exports', onClick:function(obj) {
            var checkdata = layui.{$this->js_class}.checkStatus(obj.config.id);
            var values = '';

            if(checkdata.data.length > 0){
                var values = checkdata.data.map(function(item){
                        return item['{$this->primary_key}'];
                    }).join(',');
            }

            var url = '{$export_url}'.replace('{value}', values);
            if(!values){
                layer.confirm('确定全部导出？', {icon: 3}, function(){
                    layer.msg('开始导出全部', {icon: 1});
                    window.location.href = url;
                }, function(){
                    return;
                });
            }else{
                window.location.href = url;
            }

        }}";
        return $json;
    }

    protected function  buildRefreshToolJson(): string
    {
        $json = "{name: 'refresh', title:'刷新', icon: 'layui-icon-refresh-3', layEvent: 'LAYTABLE_REFRESH',onClick:function(obj) {
            layui.{$this->js_class}.reload('{$this->grid_id}', {
                page: {
                    curr: 1
                }
            });
        }}";
        return $json;
    }

    protected function  buildColumTemplate(DataGridColumn $column)
    {
        $field = $column->getConfig('field');
        $template = $column->getConfig('template', '');
        if ($template) {
            $id = $this->grid_id . '_column_template_' . $field;

            if ($template == 'switch') {
                $template = $this->buildSwitchTemplate($column);
            } else {
                $template = $this->templateReplaceOutput($template);
            }

            $this->html[] = "<script id='{$id}' type='text/html'>{$template}</script>";
            return '#' . $id;
        }
        return null;
    }

    protected function  buildSearchFormJavascriptFunction()
    {
        $filter_key = $this->component->getConfig('search_button_filter_key');

        if ($filter_key) {
            $javascript = "layui.form.on('submit({$filter_key})', function(data){
                layui.{$this->js_class}.reload('{$this->grid_id}', {
                    page: {
                        curr: 1
                    },
                    where: data.field
                });
            })";

            PageRender::addJavaScriptCode($javascript);
        }
    }

    protected function  buildActionJavascriptFunction(): void
    {
        $this->javascript[] = $this->buildOpenFormActionJavascriptFunction();
        $this->javascript[] = $this->buildDoRequestActionJavascriptFunction();
    }

    protected function  buildOpenFormActionJavascriptFunction(): string
    {
        $function = "function {$this->js_class}_data_grid_open_form_function(reload_id, title, request, submit){

            var url = request.url || '';
            var method = request.method || 'get';
            var is_json = request.is_json || false;
            var data = request.data || {};

            var save_url = submit.url || '';
            var save_method = submit.method || 'post';
            var save_data = submit.data || {};
            var save_is_json = submit.is_json || false;

            if(location.search){
                if(url.indexOf('?') === -1){
                    url += '?' + location.search.substring(1);
                }else{
                    url += '&' + location.search.substring(1);
                }

                if(save_url.indexOf('?') === -1){
                    save_url += '?' + location.search.substring(1); 
                }else{
                    save_url += '&' + location.search.substring(1);
                }
            }

            layer.open({
                type: 2,
                area: ['80%', '90%'],
                content: url,
                title: title,
                maxmin: true,
                shade: 0.6, // 遮罩透明度
                shadeClose: true, // 点击遮罩区域，关闭弹层
                btn: ['保存', '取消'],
                btnAlign: 'c',
                yes: function(index, layero){
                    // 获取 iframe 的窗口对象
                    var iframeWin =  window[layero.find('iframe')[0]['name']];
                    var form = iframeWin.layui.$('form');

                    var is_valid = false;
                    for(var i = 0; i < form.length; i++){
                        if(layui.form.validate(form[i])){
                            form = form.eq(i);
                            is_valid = true;
                            break;
                        }
                    }

                    if(is_valid){
                        var items = form.serializeArray();

                        var data = save_data;
                        items.forEach(function(item){
                            data[item.name] = item.value;
                        });
                        // console.log(data);

                        layer.close(index);

                        var params = {
                            url: save_url,
                            type: save_method,
                            success: function(res){
                                if(res.code === 0){
                                    layer.msg(res.message, {icon: 1});
                                    if(reload_id){
                                        layui.{$this->js_class}.reload(reload_id);
                                    }
                                }else{
                                    layer.msg(res.message, {icon: 3});
                                }
                            }
                        }

                        if(save_method.toLowerCase() === 'get'){
                            var query =[];
                            for(var key in data){
                                query.push(key + '=' + data[key]);
                            }
                            if(query.length > 0){
                                if(save_url.indexOf('?') === -1){
                                    save_url += '?' + query.join('&');
                                }else{
                                    save_url += '&' + query.join('&');
                                }
                            }
                        }

                        if(save_method.toLowerCase() === 'post'){
                            if(save_is_json){
                                params.contentType = 'application/json';
                                params.data = JSON.stringify(data);
                            }else{
                                params.data = data;
                            }
                        }

                        layui.$.ajax(params);
                    }
                }
            });
        }";

        return $function;
    }

    protected function  buildDoRequestActionJavascriptFunction(): string
    {
        $function = "function {$this->js_class}_data_grid_do_request_function(reload_id, title, request){

            url = request.url || '';
            data = request.data || {};
            method = request.method || 'get';
            is_json = request.is_json || false;

            if(title){
                layer.confirm(title, {
                    btn: ['确定', '关闭'] //按钮
                }, function(){
                    doRequest();
                }, function(){
                });
            }else{
                doRequest();
            }

            function doRequest(){
                var params = {
                    url: url,
                    type: method,
                    success: function(res){
                        if(res.code === 0){
                            layer.msg(res.message || '操作成功', {icon: 1});
                            if(reload_id){
                                layui.{$this->js_class}.reload(reload_id);
                            }
                        }else{
                            layer.msg(res.message || '操作失败', {icon: 2});
                        }
                    }
                };

                if(method.toLowerCase() === 'get'){
                    if(data){
                        var query = [];
                        for(var key in data){
                            query.push(key + '=' + data[key]);
                        }

                        if(query.length > 0){
                            if(params.url.indexOf('?') === -1){
                                params.url += '?' + query.join('&');
                            }else{
                                params.url += '&' + query.join('&');
                            }
                        }
                    }
                }

                if(method.toLowerCase() === 'post'){
                    if(data){
                        if(is_json){
                            params.contentType = 'application/json';
                            params.data = JSON.stringify(data);
                        }else{
                            params.data = data;
                        }
                    }
                }

                layui.$.ajax(params);
            }
        }";
        return $function;
    }

    protected function  inPermission(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        $permission = $this->component->getConfig('permission', []);

        return in_array($path, $permission);
    }

    protected function buildSwitchTemplate(DataGridColumn $column)
    {
        $filter = $column->getId() . '_switch';
        $field = $column->getConfig('field');
        $switch = $column->getConfig('switch', '');
        $action = $column->getConfig('switch_action');
        $html = '<input type="checkbox" name="' . $field . '" value="{{= d.' . $this->primary_key . ' }}" title="' . $switch . '" lay-skin="switch" lay-filter="' . $filter . '" {{= d.' . $field . ' ? "checked" : "" }}>';

        if ($action) {
            $param_name = $action->getConfig('param_name', 'id');
            $js = "layui.form.on('switch(" . $filter . ")', function(obj){
                var url = '{$action->getConfig('url')}';
                var method = '{$action->getConfig('method')}';
                var is_json = '{$action->getConfig('is_json')}';

                var data = {
                    $param_name : obj.elem.value,
                    field:obj.elem.name,
                    value:obj.elem.checked
                };

                var params = {
                    url: url,
                    method: method,
                    data: data
                };

                if(is_json){
                    params.contentType = 'application/json';
                    params.data = JSON.stringify(data);
                }

                layui.$.ajax(params);
                
            });";

            PageRender::addJavaScriptCode($js);
        }

        return $html;
    }
}
