<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class CheckBoxRender extends Render
{
    public function render(): string
    {
        return $this->renderCheckbox();
    }

    private function renderCheckbox(): string
    {
        $name = $this->component->getAttribute('name', '');
        $switch = $this->component->getConfig('switch', false);

        $options = $this->component->getConfig('options', []);
        $checked = $this->component->getConfig('checked_values', []);
        if (!$switch) {
            $name .= '[]';
            $this->component->setAttribute('name', $name);
        }

        $items = [];
        foreach ($options as $key => $option) {

            $label = $option['label'];
            $value = $option['value'];
            $disabled = $option['disabled'];
            $skin = $option['skin'];
            $template = $option['template'];

            $attrs = ['type' => 'checkbox', 'name' => $name, 'value' => $value, 'title' => $label];
            if (in_array($value, $checked)) {
                $attrs['checked'] = 'checked';
            }
            if ($disabled) {
                $attrs['disabled'] = 'disabled';
            }
            if ($skin) {
                $attrs['lay-skin'] = $skin;
            }

            $items[] = $this->renderHtml('input.layui-checkbox', $attrs, '');
            if ($template) {
                $items[] = $this->renderHtml('div', ['lay-checkbox' => ''], $template);
            }
        }

        if ($switch) {
            return array_shift($items);
        }
        return $this->renderHtml('div.layui-input', [], $items);
    }
}
