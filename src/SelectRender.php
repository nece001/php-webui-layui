<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class SelectRender extends Render
{
    public function render(): string
    {
        return $this->renderSelect();
    }

    private function renderSelect(): string
    {
        $options = $this->component->getConfig('options', []);
        $checked = $this->component->getConfig('selected_values', []);

        $opts = [];
        foreach ($options as $key => $data) {
            if (is_array($data) && isset($data['items'])) {
                $items = [];
                foreach ($data['items'] as $ir) {
                    $items[] = $this->renderSelectOption($ir, $checked);
                }
                $opts[] = $this->renderHtml('optgroup', ['label' => $key], $items);
            } else {
                $opts[] = $this->renderSelectOption($data, $checked);
            }
        }
        return $this->renderHtml('select', $this->component->getAttributes(), $opts);
    }

    private function renderSelectOption(array $option, array $checked): string
    {
        $label = $option['label'] ?? '';
        $value = $option['value'] ?? $label;
        $disabled = $option['disabled'] ?? false;
        $attrs = ['value' => $value];
        if ($disabled) {
            $attrs['disabled'] = 'disabled';
        }
        if (in_array($value, $checked)) {
            $attrs['selected'] = 'selected';
        }
        return $this->renderHtml('option', $attrs, $label);
    }
}
