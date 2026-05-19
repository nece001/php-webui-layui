<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ContainerRender extends Render
{
    public function render(): string
    {
        $fluid = $this->component->getConfig('fluid');
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        if ($fluid) {
            return $this->renderHtml('div.layui-fluid', $this->component->getAttributes(), $nodes);
        } else {
            return $this->renderHtml('div.layui-container', $this->component->getAttributes(), $nodes);
        }
    }
}
