<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class HrRender extends Render
{
    public function render(): string
    {
        $color = $this->component->getConfig('color');

        if ($color) {
            $this->component->addClassName("layui-border-{$color}");
        }
        return $this->renderHtml('hr', $this->component->getAttributes(), '');
    }
}
