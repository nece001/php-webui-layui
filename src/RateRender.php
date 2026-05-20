<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class RateRender extends Render
{
    public function render(): string
    {
        $this->buildJavaScript();
        return $this->renderHtml('div', $this->component->getAttributes());
    }

    private function buildJavaScript(): void
    {
        $params = [
            'elem' => '#' . $this->component->getId(),
            'length' => $this->component->getConfig('length'),
            'value' => $this->component->getConfig('value'),
            'half' => $this->component->getConfig('half'),
            'theme' => $this->component->getConfig('theme'),
            'text' => $this->component->getConfig('show_text'),
            'readonly' => $this->component->getConfig('readonly'),
            'setText' => $this->component->getConfig('text_format'),
            'choose' => $this->component->getConfig('on_choose'),
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.rate.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
