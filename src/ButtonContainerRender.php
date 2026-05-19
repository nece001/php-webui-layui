<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ButtonContainerRender extends Render
{
    public function render(): string
    {
        $align = $this->component->getConfig('align');
        $children = $this->component->getChildren();
        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        if ($align) {
            $this->component->addCss("text-align:{$align}");
        }

        $attributes = $this->component->getAttributes();

        return $this->renderHtml('div.layui-btn-container', $attributes, $nodes);
    }
}
