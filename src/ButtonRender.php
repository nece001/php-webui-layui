<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ButtonRender extends Render
{
    private static $js_action_data = [];

    public static function addJsActionData(string $key, array $data): void
    {
        self::$js_action_data[$key] = $data;
    }

    public static function getJsActionData(): array
    {
        return self::$js_action_data;
    }

    public static function clearJsActionData(): void
    {
        self::$js_action_data = [];
    }

    public function render(): string
    {
        $type = $this->component->getAttribute('type');
        $size = $this->component->getConfig('size');
        $border_color = $this->component->getConfig('border_color');
        $bg_color = $this->component->getConfig('bg_color');
        $font_color = $this->component->getConfig('font_color');
        $radius = $this->component->getConfig('radius');
        $fluid = $this->component->getConfig('fluid');
        $url = $this->component->getConfig('url');
        $filter = $this->component->getConfig('filter');
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        if ($size) {
            $this->component->addClassName('layui-btn-' . $size);
        }
        if ($border_color) {
            $this->component->addClassName('layui-btn-primary layui-border-' . $border_color);
        } else if ($bg_color) {
            $this->component->addClassName('layui-bg-' . $bg_color);
        }

        if ($font_color) {
            $this->component->addClassName('layui-font-' . $font_color);
        }

        if ($radius) {
            $this->component->addClassName('layui-btn-radius');
        }
        if ($fluid) {
            $this->component->addClassName('layui-btn-fluid');
        }

        if ($type == 'submit') {
            $this->component->setAttribute('lay-submit', '');
        }

        if ($filter) {
            $this->component->setAttribute('lay-filter', $filter);
        }

        $attributes = $this->component->getAttributes();
        if ($type == 'link') {
            $attributes['href'] = $url;
            return $this->renderHtml('a.layui-btn', $attributes, $nodes);
        }

        $action = $this->component->getConfig('action');
        if ($action) {
            $attributes['lay-event'] = $action->getConfig('event_name', 'action');
            if ($attributes['lay-event'] == 'action') {
                $this->buildActionData();
            }
        }

        return $this->renderHtml('button.layui-btn', $attributes, $nodes);
    }

    private function buildActionData(): void
    {
        $action = $this->component->getConfig('action');
        if ($action) {

            self::addJsActionData($this->component->getId(), $action->toArray());
            PageRender::addDataClearFunction('buttonDoAction', function () {
                ButtonRender::clearJsActionData();
            });

            $this->buildJavascript();
        }
    }

    private function buildJavascript(): void
    {
        $countdown = $this->component->getConfig('countdown', []);
        $countdown_sec = $countdown['countdown'] ?? 0;
        $countdown_text = $countdown['text'] ?? '秒后重新发送';

        $js_action_data = self::getJsActionData();
        $js_action_var_json = '{}';
        if ($js_action_data) {
            $js_action_var_json = json_encode($js_action_data, JSON_UNESCAPED_UNICODE);
        }

        $js = "layui.util.on('lay-event', {action:function(o, e){
            layui.stope(e);

            var js_action_data = {$js_action_var_json};

            var data = js_action_data[o.attr('id')];
            if(data){
                data['data'] = data['data'] || {};

                var bindId = data.bind_id || '';
                if(bindId){
                    var isValid = layui.form.validate('#'+bindId);
                    if(!isValid){
                        return;
                    }

                    var bind = layui.$('#' + bindId);
                    if(bind){
                        data['data'][bind.attr('name')] = bind.val();
                    }
                }
            }

            var countdown_sec = {$countdown_sec};
            var countdown_text = '{$countdown_text}';
            if(countdown_sec > 0){
                var text = o.val() ? o.val() : o.text();
                o.disabled = true;
                o.addClass('layui-btn-disabled');

                var timer = setInterval(function(){
                    countdown_sec--;
                    o.text(countdown_sec + countdown_text);
                    if(countdown_sec <= 0){
                        clearInterval(timer);
                        o.text(text);
                        o.val(text);
                        o.removeClass('layui-btn-disabled');
                        o.disabled = false;
                    }
                }, 1000);
            }

            buttonDoAction(data);
        }});";
        PageRender::addJavaScriptCode($js, 'buttonDoAction');
        $this->buildDoActionJsFunction();
    }

    private function buildDoActionJsFunction(): void
    {
        $func = "function buttonDoAction(action){

            var type = action.type || '';
            if(type == 'form'){
                buttonDoActionForm(action);
            }else if(type == 'confirm'){
                buttonDoActionConfirm(action);
            }else{
                buttonDoActionAjax(action);
            }
        }

        function buttonDoActionForm(action){
            var i = layer.open({
                type: 2,
                area: ['80%', '80%'],
                title: action.title || '',
                content: action.url || '',
                maxmin: true,
                shadeClose: true,
                btn: ['保存', '取消'],
                btnAlign: 'c',
                yes: function(index, layero){
                    var loadIndex = layer.msg('操作中...', {icon: 16,shade: 0.01});
                    layer.close(i);

                    var submitAction = action.submit_action || {};
                    if(submitAction.url || ''){
                        var iframeWin =  window[layero.find('iframe')[0]['name']];
                        var form = iframeWin.layui.$('form');

                        if(layui.form.validate(form)){

                            var items = form.serializeArray();
                            var data = {};
                            items.forEach(function(item){
                                data[item.name] = item.value;
                            });
                    
                            submitAction.data = data;
                            buttonDoActionAjax(submitAction, loadIndex);
                        }
                    }
                }
            });
        }

        function buttonDoActionConfirm(action){
            layer.confirm(action.title || '', {icon: 3, title:'提示', shade: [0.3, '#000']}, function(index){
                    layer.close(index);

                    if(action.url || ''){
                        var loadIndex = layer.msg('操作中...', {icon: 16,shade: 0.01});
                        buttonDoActionAjax(action, loadIndex);
                    }
                });
        }
        
        function buttonDoActionAjax(action, loadIndex){

            var url = action.url || '';
            var method = action.method || 'GET';
            var data = action.data || [];
            if(method.toUpperCase() == 'GET'){
                var query = [];
                for(var key in data){
                    query.push(key + '=' + data[key]);
                }

                if(url.indexOf('?') == -1){
                    url += '?' + query.join('&');
                }else{
                    url += '&' + query.join('&');
                }
            }

            var params = {
                type: method,
                url: url,
                success: function(res){
                    if(loadIndex){
                        layer.close(loadIndex);
                    }

                    layer.msg(res.message || '操作成功');
                },
                error: function(err){
                    console.log(err);
                    layer.msg(err.message || '操作失败');
                }
            };

            if(method.toUpperCase() == 'POST'){
                if(action.is_json){
                    data = JSON.stringify(data);
                    params.contentType = 'application/json';
                }
                params.data = data;
            }

            layui.$.ajax(params);
        }";

        PageRender::addJavaScriptCode($func, 'buttonDoActionFunction');
    }
}
