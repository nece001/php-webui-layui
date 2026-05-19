<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class BadgeRender extends Render
{
    public function render(): string
    {
        $text = $this->component->getConfig('text');
        $dot = $this->component->getConfig('dot');
        $color = $this->component->getConfig('color');
        $border = $this->component->getConfig('border');

        if ($dot) {
            $text = '';
            $this->component->addClassName('layui-badge-dot');
        } else {
            $this->component->addClassName('layui-badge');
        }

        if($color) {
            $this->component->addClassName('layui-bg-' . $color);
        }

        if($border) {
            $this->component->setClassName('layui-badge-rim');
        }

        return $this->renderHtml('span', $this->component->getAttributes(), $text);
    }
}
