<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class DataGridRender extends Render
{
    protected $grid_id = '';

    private $html = [];
    private $javascript = [];
    private $toolbar = false;
    private $action = false;
    private $primary_key = 'id';

    public function render(): string
    {
        $this->toolbar = $this->component->getConfig('toolbar', false);
        $this->grid_id = $this->component->getId();
        $this->buildJavascript();

        $this->html[] = $this->renderHtml('table', ['id' => $this->grid_id, 'class' => 'layui-hide', 'lay-filter' => $this->grid_id]);

        return implode('', $this->html);
    }

    private function buildJavascript(): void
    {
        $this->buildActionJavascriptFunction();
        $params = $this->buildParamsJson();

        $javascript = "layui.table.render($params);";
        PageRender::addJavaScriptCode($javascript);

        foreach ($this->javascript as $javascript) {
            PageRender::addJavaScriptCode($javascript);
        }
    }

    private function buildParamsJson(): string
    {
        $this->buildSearchFormJavascriptFunction();
        $this->buildDataParseFuncitonJavascript();
        $this->buildToolbarTemplate();
        $this->buildRowActionTemplate();

        $cols = $this->buildColumnsJson();
        $exportToolJson = $this->buildExportToolJson();

        $default_toolbar = ['filter', 'print'];
        if ($exportToolJson) {
            $default_toolbar[] = 'default_toolbar_export_json';
        }

        $params = [
            'id' => $this->grid_id,
            'elem' => '#' . $this->grid_id,
            'cols' => 'cols_json_placeholder',
            'url' => $this->component->getConfig('data_url'),
            'toolbar' => $this->toolbar ? '#' . $this->grid_id . '_toolbar_template' : null,
            'defaultToolbar' => $default_toolbar,
            'page' => true,
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
        ]);
    }

    private function buildColumnsJson(): string
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

                $template_id = $this->buildColumTemplate($column->getConfig('field'), $column->getConfig('template', ''));

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

            if ($this->action) {
                $operation = $child->getConfig('operation');
                if ($operation) {
                    $oper = [
                        'fixed' => 'right',
                        'templet' => '#' . $this->grid_id . '_row_action',
                        'title' => $operation->getConfig('title'),
                        'width' => $operation->getConfig('width'),
                        'rowspan' => $operation->getConfig('row_span'),
                        'colspan' => $operation->getConfig('col_span'),
                        'align' => $operation->getConfig('align'),
                    ];

                    $oper = $this->arrayFilter($oper);
                    $row[] = json_encode($oper, JSON_UNESCAPED_UNICODE);
                }
            }

            $cols[] = '[' . implode(',', $row) . ']';
        }

        return '[' . implode(',', $cols) . ']';
    }

    private function buildToolbarTemplate(): void
    {
        $tools = $this->component->getConfig('tools', []);
        if ($tools) {
            $this->toolbar = true;

            $buttons = [];
            foreach ($tools as $event => $tool) {
                if ($this->inPermission($tool['url'])) {
                    $text = $tool['text'];
                    $icon = $tool['icon'];
                    $buttons[] = '<button class="layui-btn layui-btn-xs" lay-event="' . $event . '" title="' . $text . '"><i class="layui-icon layui-icon-' . $icon . '"></i></button>';
                }
            }

            $html = '<script type="text/html" id="' . $this->grid_id . '_toolbar_template">
                <div class="layui-btn-group">
                    ' . implode('', $buttons) . '
                </div>
            </script>';
            $tools_json = json_encode($tools, JSON_UNESCAPED_UNICODE);

            $javascript = "layui.table.on('toolbar($this->grid_id)', function(obj){
                console.log(obj);

                var tools = {$tools_json};
                var checkdata = layui.table.checkStatus(obj.config.id);
                var values = '';
                if(checkdata.data.length > 0){
                    values = checkdata.data.map(function(item){return item['{$this->primary_key}'];}).join(',');
                }

                try{
                    if(tools[obj.event]){
                        var type = tools[obj.event]['type'];
                        if(type === 'form'){
                            var url = tools[obj.event]['url'].replace('{value}', values);
                            var save_url = tools[obj.event]['save_url'].replace('{value}', values);
                            var text = tools[obj.event]['text'];
                            admin_grid_open_form_function('{$this->grid_id}', text, url, save_url);
                        }else{
                            var url = tools[obj.event]['url'].replace('{value}', values);
                            var message = tools[obj.event]['message'];
                            admin_grid_do_request_function('{$this->grid_id}', url, message);
                        }
                    }
                }catch(e){console.log(e);}
            })";

            $this->html[] = $html;
            $this->javascript[] = $javascript;
        }
    }

    private function buildRowActionTemplate()
    {
        $actions = $this->component->getConfig('actions', []);
        if ($actions) {
            $this->action = true;

            $buttons = [];
            foreach ($actions as $event => $action) {
                if ($this->inPermission($action['url'])) {
                    $text = $action['text'];
                    $buttons[] = '<button class="layui-btn layui-btn-xs" lay-event="' . $event . '" title="' . $text . '">' . $text . '</button>';
                }
            }
            $actions_json = json_encode($actions, JSON_UNESCAPED_UNICODE);

            $html = '<script type="text/html" id="' . $this->grid_id . '_row_action"><div class="layui-clear-space">' . implode('', $buttons) . '</div></div></script>';
            $javascript = "layui.table.on('tool({$this->grid_id})', function(obj){
                console.log(obj);

                var actions = {$actions_json};
                try{
                    if(actions[obj.event]){
                        var type = actions[obj.event]['type'];
                        if(type === 'form'){
                            var url = actions[obj.event]['url'].replace('{value}', obj.data.id);
                            var save_url = actions[obj.event]['save_url'].replace('{value}', obj.data.id);
                            var text = actions[obj.event]['text'];
                            admin_grid_open_form_function('{$this->grid_id}', text, url, save_url);
                        }else{
                            var url = actions[obj.event]['url'].replace('{value}', obj.data.id);
                            var message = actions[obj.event]['message'];
                            admin_grid_do_request_function('{$this->grid_id}', url, message);
                        }
                    }
                }catch(e){}
            })";

            $this->html[] = $html;
            $this->javascript[] = $javascript;
        }
    }

    private function buildDataParseFuncitonJavascript()
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

    private function buildExportToolJson(): string
    {
        $export_url = $this->component->getConfig('export_url');
        if (!$export_url) {
            return '';
        }

        $json = "{name: 'exports', onClick:function(obj) {
            var checkdata = layui.table.checkStatus(obj.config.id);
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

    private function buildColumTemplate(string $field, string $template)
    {
        if ($template) {
            $id = $this->grid_id . '_column_template_' . $field;

            $pattern = '/({([^{}]+)})/';
            if (preg_match_all($pattern, $template, $matches)) {
                foreach ($matches[2] as $i => $name) {
                    $hold = $matches[0][$i];
                    $template = str_replace($hold, '{{=d.' . $name . '}}', $template);
                }
            }

            $this->html[] = "<script id='{$id}' type='text/html'>{$template}</script>";
            return '#' . $id;
        }
        return null;
    }

    private function buildSearchFormJavascriptFunction()
    {

        $filter_key = $this->component->getConfig('search_button_filter_key');

        $javascript = "layui.form.on('submit({$filter_key})', function(data){
            layui.table.reload('{$this->grid_id}', {
                page: {
                    curr: 1
                },
                where: data.field
            });
        })";

        PageRender::addJavaScriptCode($javascript);
    }

    private function buildActionJavascriptFunction(): void
    {
        $this->javascript[] = $this->buildOpenFormActionJavascriptFunction();
        $this->javascript[] = $this->buildDoRequestActionJavascriptFunction();
    }

    private function buildOpenFormActionJavascriptFunction(): string
    {
        $function = "function admin_grid_open_form_function(reload_id, title, url, save_url){

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

                    if(layui.form.validate(form)){

                    var items = form.serializeArray();

                    var data = {};
                    items.forEach(function(item){
                        data[item.name] = item.value;
                    });
                    // console.log(data);

                    layer.close(index);
                        layui.$.ajax({
                            url: save_url,
                            type: 'post',
                            contentType: 'application/json',
                            data: JSON.stringify(data),
                            success: function(res){
                                if(res.code === 0){
                                    layer.msg(res.message, {icon: 1});
                                    if(reload_id){
                                        layui.table.reload(reload_id);
                                    }
                                }else{
                                    layer.msg(res.message, {icon: 3});
                                }
                            }
                        });
                    }

                }
            });
        }";

        return $function;
    }

    private function buildDoRequestActionJavascriptFunction(): string
    {
        $function = "function admin_grid_do_request_function(reload_id, url, message){

            if(message){
                layer.confirm(message, {
                    btn: ['确定', '关闭'] //按钮
                }, function(){
                    doRequest();
                }, function(){
                });
            }else{
                doRequest();
            }

            function doRequest(){
                layui.$.ajax({
                    url: url,
                    type: 'get',
                    success: function(res){
                        if(res.code === 0){
                            layer.msg(res.message, {icon: 1});
                            if(reload_id){
                                layui.table.reload(reload_id);
                            }
                        }else{
                            layer.msg(res.message, {icon: 2});
                        }
                    }
                });
            }
        }";
        return $function;
    }

    private function inPermission(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        $permission = $this->component->getConfig('permission', []);

        return in_array($path, $permission);
    }
}
