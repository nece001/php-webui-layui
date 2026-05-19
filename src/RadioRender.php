<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class RadioRender extends Render
{
    public function render(): string
    {
        return $this->renderRadio();
    }

    private function renderRadio(): string
    {
        $name = $this->component->getAttribute('name', '');
        $options = $this->component->getConfig('options', []);
        $checked = $this->component->getConfig('checked_value', []);

        $items = [];
        foreach ($options as $key => $option) {
            $label = $option['label'];
            $value = $option['value'];
            $disabled = $option['disabled'];
            $template = $option['template'];

            $attrs = ['type' => 'radio', 'name' => $name, 'value' => $value, 'title' => $label];
            if ($value == $checked) {
                $attrs['checked'] = 'checked';
            }
            if ($disabled) {
                $attrs['disabled'] = 'disabled';
            }
            $items[] = $this->renderHtml('input.layui-radio', $attrs, '');
            if ($template) {
                $items[] = $this->renderHtml('div', ['lay-radio' => ''], $template);
            }
        }
        return $this->renderHtml('div.layui-input', [], $items);
    }
}
