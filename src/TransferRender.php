<?php

namespace Nece\Webui\Layui;

use Nece\WebUi\Layui\PageRender;
use Nece\WebUi\Render;

class TransferRender extends Render
{
    private $transfer_id = '';
    private $hidden_id = '';

    public function render(): string
    {
        $this->transfer_id = $this->component->getId();
        $this->hidden_id = $this->transfer_id . '_hidden';
        $checked_values = $this->component->getAttribute('checked_values', []);
        $init_value = implode(',', $checked_values);
        $field_name = $this->component->getConfig('field_name', '');

        $this->buildJavascript();
        $hidden = $this->renderHtml('input', ['type' => 'hidden', 'name' => $field_name, 'id' => $this->hidden_id, 'value' => $init_value]);
        $box = $this->renderHtml('div', $this->component->getAttributes());

        return $hidden . $box;
    }

    private function buildJavascript(): void
    {
        $this->buildOnChangeFunction();

        $none_text = $this->component->getConfig('no_data_text');
        $search_none_text = $this->component->getConfig('search_no_data_text');
        $text = null;
        if ($none_text) {
            $text['none'] = $none_text;
        }
        if ($search_none_text) {
            $text['searchNone'] = $search_none_text;
        }

        $params = [
            'id' => $this->transfer_id,
            'elem' => '#' . $this->transfer_id,
            'title' => $this->component->getConfig('title'),
            'data' => $this->component->getConfig('data'),
            'value' => $this->component->getConfig('checked_values'),
            'showSearch' => $this->component->getConfig('show_search'),
            'width' => $this->component->getConfig('width'),
            'height' => $this->component->getConfig('height'),
            'text' => $text,
            'onchange' => $this->component->getConfig('on_change'),
            'dblclick' => $this->component->getConfig('on_dblclick'),
            'parseData' => $this->component->getConfig('parse_data'),
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.transfer.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }

    private function buildOnChangeFunction(): void
    {
        $id = $this->transfer_id;
        $function = $this->component->getConfig('on_change');
        if (!$function) {
            $js = "function(obj, index){
                var data = layui.transfer.getData('{$id}');
                console.log(data);

                if(data){
                    var valus = [];
                    for(var i=0; i<data.length; i++){
                        valus.push(data[i].value);
                    }
                    layui.$('#{$this->hidden_id}').val(valus.join(','));
                }else{
                    layui.$('#{$this->hidden_id}').val('');
                }
            }";

            $this->component->setOnChangeJsFunction($js);
        }
    }
}
