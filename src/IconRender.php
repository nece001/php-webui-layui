<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class IconRender extends Render
{
    public function render(): string
    {
        $icon = $this->component->getConfig('icon', '');

        $this->component->addClassName('layui-icon-' . $icon);

        return $this->renderHtml('i.layui-icon', $this->component->getAttributes());
    }
}
