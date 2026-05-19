<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class FieldsetRender extends Render
{
    public function render(): string
    {
        $title = $this->component->getConfig('title');
        $line = $this->component->getConfig('line');
        $children = $this->component->getChildren();

        $legend = $this->renderHtml('legend', [], $title);
        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        if ($line) {
            $this->component->addClassName('layui-field-title');
            $fieldset = $this->renderHtml('fieldset.layui-elem-field', $this->component->getAttributes(), $legend);
            return $fieldset . implode('', $nodes);
        } else {
            $box = $this->renderHtml('div.layui-field-box', [], $nodes);
            return $this->renderHtml('fieldset.layui-elem-field', $this->component->getAttributes(), [$legend, $box]);
        }
    }
}
