<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class PanelRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }
        return $this->renderHtml('div.layui-panel', $this->component->getAttributes(), $nodes);
    }
}
