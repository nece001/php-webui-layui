<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class BlockquoteRender extends Render
{
    public function render(): string
    {
        $border = $this->component->getConfig('border');
        $children = $this->component->getChildren();
        if ($border) {
            $this->component->addClassName('layui-quote-nm');
        }

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        return $this->renderHtml('blockquote.layui-elem-quote', $this->component->getAttributes(), $nodes);
    }
}
