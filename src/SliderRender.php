<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class SliderRender extends Render
{
    public function render(): string
    {
        $this->buildJavaScript();
        return $this->renderHtml('div', $this->component->getAttributes());
    }

    private function buildJavaScript(): void
    {
        $type = $this->component->getConfig('vertical');    // 是否垂直方向
        $value = $this->component->getConfig('value');
        $range_value = $this->component->getConfig('range_value');
        if ($type) {
            $type = 'vertical';
        }

        if ($range_value) {
            $value = $range_value;
        }

        $params = [
            'elem' => '#' . $this->component->getId(),
            'type' => $type,
            'value' => $this->component->getConfig('value'),
            'range' => $this->component->getConfig('range'),
            'min' => $this->component->getConfig('min'),
            'max' => $this->component->getConfig('max'),
            'step' => $this->component->getConfig('step'),
            'showstep' => $this->component->getConfig('show_step'),
            'tips' => $this->component->getConfig('tips'),
            'tipsAlways' => $this->component->getConfig('tips_always'),
            'input' => $this->component->getConfig('show_input'),
            'height' => $this->component->getConfig('height'),
            'theme' => $this->component->getConfig('theme'),
            'disabled' => $this->component->getConfig('disabled'),
            'setTips' => $this->component->getConfig('tips_format'),
            'change' => $this->component->getConfig('on_change'),
            'done' => $this->component->getConfig('done'),
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.slider.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
