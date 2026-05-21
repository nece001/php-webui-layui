<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ButtonRender extends Render
{
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

        $attributes = $this->component->getAttributes();
        if ($type == 'link') {
            $attributes['href'] = $url;
            return $this->renderHtml('a.layui-btn', $attributes, $nodes);
        }
        $action_data = $this->buildActionData();
        if ($action_data) {
            $attributes['do-action'] = 'action';
            $attributes['data-action'] = $action_data;
        }

        return $this->renderHtml('button.layui-btn', $attributes, $nodes);
    }

    private function buildActionData(): string
    {
        $action = $this->component->getConfig('action');
        if ($action) {
            $this->buildJavascript();
            $json = json_encode($action, JSON_UNESCAPED_UNICODE);
            return urlencode($json);
        }
        return '';
    }

    private function buildJavascript(): void
    {
        $js = "layui.util.on('do-action', {action:function(o, e){
            layui.stope(e);

            var data = o.data('action');
            if(data){
                data = decodeURIComponent(data);
                data = JSON.parse(data);
            }
            buttonDoAction(data);
        }});";
        PageRender::addJavaScriptCode($js);
        $this->buildDoActionJsFunction();
    }

    private function buildDoActionJsFunction(): void
    {
        $func = "function buttonDoAction(action){
            console.log(action);

            var type = action.type || '';
            if(type == 'form'){
                buttonDoActionForm(action);
            }else{
                buttonDoActionConfirm(action);
            }
        }

        function buttonDoActionForm(action){
            console.log(action);

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
                    console.log(index, layero);
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

            var data = action.data || '';
            if(action.is_json){
                data = JSON.stringify(data);
            }

            var params = {
                type: action.method || 'GET',
                url: action.url || '',
                data: data,
                success: function(res){
                    layer.close(loadIndex);
                    console.log(res);

                    layer.msg(res.message || '操作成功');
                },
                error: function(err){
                    console.log(err);
                    layer.msg(err.message || '操作失败');
                }
            };

            if(action.is_json){
                params.contentType = 'application/json';
            }

            layui.$.ajax(params);
        }";

        PageRender::addJavaScriptCode($func, 'buttonDoAction');
    }
}
