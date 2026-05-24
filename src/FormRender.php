<?php

namespace Nece\WebUi\Layui;

use common\Ui\Control;
use Nece\WebUi\Render;

class FormRender extends Render
{
    private $hidden_controls = [];
    private $inline_layout = false;

    public function render(): string
    {
        $this->inline_layout = $this->component->getConfig('inline_layout', false);
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->renderItem($child);
        }

        $nodes = array_merge($this->hidden_controls, $nodes);
        $nodes[] = $this->renderButtons();

        if($this->inline_layout){
            $nodes = $this->renderHtml('div.layui-form-item', [], $nodes);
        }

        return $this->renderHtml('form.layui-form layui-form-pane', $this->component->getAttributes(), $nodes);
    }

    private function renderItem($control): string
    {
        if (is_array($control)) {
            $html = $this->renderInlineControls($control);
        } else {
            $html = $this->renderControl($control);
        }

        if ($html) {
            if ($this->inline_layout) {
                return $this->renderHtml('div.layui-inline', [], $html);
            } else {
                return $this->renderHtml('div.layui-form-item', [], $html);
            }
        }
        return '';
    }

    private function renderControl($control): string
    {
        $type = $control->getAttribute('type');
        if ($type ==  'hidden') {
            $this->hidden_controls[] = $this->renderHtml('input', $control->getAttributes());
            return '';
        }

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

            if ($this->inline_layout) {
                $nodes[] = $this->renderHtml('div.layui-input-inline', [], $ctl);
            } else {
                $nodes[] = $this->renderHtml('div.layui-input-block', [], $ctl);
            }
        } else {
            $nodes[] = $ctl;
        }

        return implode('', $nodes);
    }

    private function renderInlineControls(array $controls): string
    {
        $form_inline_id = 'form_inline_id_' . uniqid();
        $nodes = [];
        foreach ($controls as $control) {
            $type = $control->getAttribute('type');
            if ($type ==  'hidden') {
                $this->hidden_controls[] = $this->renderHtml('input', $control->getAttributes());
                continue;
            }

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

        if ($nodes) {
            if($this->inline_layout){
                return implode('', $nodes);
            }else{
                return $this->renderHtml('div.layui-inline', ['id' => $form_inline_id], $nodes);
            }
        }
        return '';
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
            $data_grid = $this->component->getConfig('data_grid');

            $nodes = [];
            foreach ($buttons as $button) {
                if ($data_grid) {
                    $type = $button->getAttribute('type');
                    if ($type == 'submit') {
                        $search_filter_key = $data_grid->getId() . '_search_grid';
                        $button->setFilter($search_filter_key);
                        $data_grid->setSearchButtonFilterKey($search_filter_key);
                    }
                }

                $nodes[] = $this->getRender($button)->render();
            }

            if ($this->inline_layout) {
                return $this->renderHtml('div.layui-inline > div.layui-input-inline', [], $nodes);
            }
            if ($button_align) {
                return $this->renderHtml('div.layui-btn-container', ['style' => 'text-align: ' . $button_align . ';'], $nodes);
            }
            return $this->renderHtml('div.layui-form-item > div.layui-input-block', $this->component->getAttributes(), $nodes);
        }
        return '';
    }
}
