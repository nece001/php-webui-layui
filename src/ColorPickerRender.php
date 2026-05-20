<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ColorPickerRender extends Render
{
    private $bind_id = '';

    public function render(): string
    {
        $this->bind_id = $this->component->getConfig('bind_id');

        $this->buildJavaScript();
        return $this->renderHtml('div', $this->component->getAttributes());
    }

    private function buildJavaScript(): void
    {
        $this->buildDefaultDoneFunction();

        $params = [
            'elem' => '#' . $this->component->getId(),
            'color' => $this->component->getConfig('color'),
            'format' => $this->component->getConfig('format'),
            'alpha' => $this->component->getConfig('alpha'),
            'predefine' => $this->component->getConfig('predefine'),
            'colors' => $this->component->getConfig('colors'),
            'size' => $this->component->getConfig('size'),
            'change' => $this->component->getConfig('on_change'),
            'done' => $this->component->getConfig('done'),
            'cancel' => $this->component->getConfig('cancel'),
            'close' => $this->component->getConfig('close'),
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.colorpicker.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }

    private function buildDefaultDoneFunction(): void
    {
        $done = $this->component->getConfig('done');
        if (!$done && $this->bind_id) {
            $done = "function(color){layui.$('#{$this->bind_id}').val(color);}";
            $this->component->setDoneJsFunction($done);
        }
    }
}
