<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class InputRender extends Render
{
    public function render(): string
    {
        return $this->renderControl();
    }

    private function renderControl(): string
    {
        $type = $this->component->getAttribute('type', 'text');
        switch ($type) {
            case 'textarea':
                return $this->renderTextArea();
            default:
                return $this->renderInput();
        }
    }

    private function renderTextArea(): string
    {
        $value = $this->component->getAttribute('value', '');
        $this->component->removeAttribute('value');

        $validate = $this->component->getConfig('validate', []);
        $verify_type = $this->component->getConfig('validate_type', 'tips');
        $attributes = $this->component->getAttributes();
        if ($validate) {
            $attributes['lay-verify'] = implode('|', $validate);
        }
        if ($verify_type) {
            $attributes['lay-vertype'] = $verify_type;
        }

        return $this->renderHtml('textarea.layui-textarea', $attributes, $value);
    }

    private function renderInput(): string
    {
        $attributes = $this->component->getAttributes();

        $validate = $this->component->getConfig('validate', []);
        $verify_type = $this->component->getConfig('validate_type', 'tips');
        $affix = $this->component->getConfig('affix');
        if ($affix) {
            $attributes['lay-affix'] = $affix;
        }
        if ($validate) {
            $attributes['lay-verify'] = implode('|', $validate);
        }
        if ($verify_type) {
            $attributes['lay-vertype'] = $verify_type;
        }

        return $this->renderHtml('input.layui-input', $attributes, '');
    }
}
