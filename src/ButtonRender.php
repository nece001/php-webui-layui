<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ButtonRender extends Render
{
    public function render(): string
    {
        $type = $this->component->getAttribute('type');
        $size = $this->component->getConfig('size');
        $border_color = $this->component->getConfig('border_color');
        $bg_color = $this->component->getConfig('bg_color');
        $font_color = $this->component->getConfig('font_color');
        $radius = $this->component->getConfig('radius');
        $fluid = $this->component->getConfig('fluid');
        $url = $this->component->getConfig('url');
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        if ($size) {
            $this->component->addClassName('layui-btn-' . $size);
        }
        if ($border_color) {
            $this->component->addClassName('layui-btn-primary layui-border-' . $border_color);
        } else if ($bg_color) {
            $this->component->addClassName('layui-bg-' . $bg_color);
        }

        if ($font_color) {
            $this->component->addClassName('layui-font-' . $font_color);
        }

        if ($radius) {
            $this->component->addClassName('layui-btn-radius');
        }
        if ($fluid) {
            $this->component->addClassName('layui-btn-fluid');
        }

        if ($type == 'submit') {
            $this->component->setAttribute('lay-submit', '');
        }

        $attributes = $this->component->getAttributes();
        if ($type == 'link') {
            $attributes['href'] = $url;
            return $this->renderHtml('a.layui-btn', $attributes, $nodes);
        }

        return $this->renderHtml('button.layui-btn', $attributes, $nodes);
    }
}
