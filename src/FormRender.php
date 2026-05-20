<?php

namespace Nece\WebUi\Layui;

use common\Ui\Control;
use Nece\WebUi\Render;

class FormRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->renderItem($child);
        }

        $nodes[] = $this->renderButtons();
        return $this->renderHtml('form.layui-form layui-form-pane', $this->component->getAttributes(), $nodes);
    }

    private function renderItem($control): string
    {
        if (is_array($control)) {
            $html = $this->renderInlineControls($control);
        } else {
            $html = $this->renderControl($control);
        }

        return $this->renderHtml('div.layui-form-item', [], $html);
    }

    private function renderControl($control): string
    {
        $ctl = $this->getRender($control)->render();
        $ctl = $this->renderControlPend($control, $ctl);

        $description = $control->getConfig('description', '');
        if ($description) {
            $ctl .= $this->renderHtml('p.layui-font-12 layui-font-gray layui-margin-1', [], $description);
        }

        $nodes = [];
        $label = $control->getConfig('label', '');
        if ($label) {
            $nodes[] = $this->renderHtml('label.layui-form-label', ['for' => $control->getId()], $label);
        }

        $nodes[] = $this->renderHtml('div.layui-input-block', [], $ctl);

        return implode('', $nodes);
    }

    private function renderInlineControls(array $controls): string
    {
        $form_inline_id = 'form_inline_id_' . uniqid();
        $nodes = [];
        foreach ($controls as $control) {
            $control->setAncestorId($form_inline_id);
            $ctl = $this->getRender($control)->render();
            $ctl = $this->renderControlPend($control, $ctl);

            $description = $control->getConfig('description', '');
            if ($description) {
                $ctl .= $this->renderHtml('p.layui-font-12 layui-font-gray layui-margin-1', [], $description);
            }

            $ctl = $this->renderHtml('div.layui-input-inline', [], $ctl);

            $label = $control->getConfig('label', '');
            if ($label) {
                $nodes[] = $this->renderHtml('label.layui-form-label', ['for' => $control->getId()], $label);
            }

            $nodes[] = $ctl;

            $separator = $control->getConfig('separator', '');
            if ($separator) {
                $nodes[] = $this->renderHtml('div.layui-form-mid', [], $separator);
            }
        }

        return $this->renderHtml('div.layui-inline', ['id' => $form_inline_id], $nodes);
    }

    private function renderControlPend($control, string $ctl): string
    {
        $prefix = $control->getConfig('prefix');
        $suffix = $control->getConfig('suffix');
        $prepend = $control->getConfig('prepend');
        $append = $control->getConfig('append');

        if ($prefix || $suffix) {
            $nodes = [];

            if ($prefix) {
                $r = $this->getRender($prefix)->render();
                $nodes[] = $this->renderHtml('div.layui-input-prefix', [], $r);
            }

            $nodes[] = $ctl;

            if ($suffix) {
                $r = $this->getRender($suffix)->render();
                $nodes[] = $this->renderHtml('div.layui-input-suffix', [], $r);
            }

            $ctl = $this->renderHtml('div.layui-input-wrap', [], $nodes);
        }

        if ($prepend || $append) {
            $nodes = [];
            if ($prepend) {
                $r = $this->getRender($prepend)->render();
                $nodes[] = $this->renderHtml('div.layui-input-split layui-input-prefix', [], $r);
            }

            $nodes[] = $ctl;

            if ($append) {
                $r = $this->getRender($append)->render();
                $nodes[] = $this->renderHtml('div.layui-input-split layui-input-suffix', [], $r);
            }
            $ctl = $this->renderHtml('div.layui-input-group', [], $nodes);
        }

        return $ctl;
    }

    private function renderButtons(): string
    {
        $buttons = $this->component->getConfig('buttons', []);
        if ($buttons) {
            $button_align = $this->component->getConfig('button_align');

            $nodes = [];
            foreach ($buttons as $button) {
                $nodes[] = $this->getRender($button)->render();
            }

            if ($button_align) {
                return $this->renderHtml('div.layui-btn-container', ['style' => 'text-align: ' . $button_align . ';'], $nodes);
            }
            return $this->renderHtml('div.layui-form-item > div.layui-input-block', $this->component->getAttributes(), $nodes);
        }
        return '';
    }
}
