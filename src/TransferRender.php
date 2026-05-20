<?php

namespace Nece\Webui\Layui;

use Nece\WebUi\Layui\PageRender;
use Nece\WebUi\Render;

class TransferRender extends Render
{
    public function render(): string
    {
        $this->buildJavascript();
        return $this->renderHtml('div', $this->component->getAttributes());
    }

    private function buildJavascript(): void
    {
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
            'elem' => '#' . $this->component->getId(),
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
}
