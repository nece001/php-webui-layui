<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class DropdownRender extends Render
{
    private $bind_id;

    public function render(): string
    {
        $this->bind_id = $this->component->getConfig('bind_id');
        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $this->bind_id = $child->getId();
            $nodes[] = $this->getRender($child)->render();
        }

        $this->buildJavaScript();
        return implode('', $nodes);
    }

    private function buildJavaScript(): void
    {
        $attributes = $this->component->getAttributes();
        $shade = $this->component->getConfig('shade');
        if ($shade) {
            if ($shade['color']) {
                $shade = [$shade['shade'], $shade['color']];
            } else {
                $shade = $shade['shade'];
            }
        }

        $params = [
            'elem' => '#' . $this->bind_id,
            'data' => $this->component->getConfig('data'),
            'trigger' => $this->component->getConfig('trigger'),
            'closeOnClick' => $this->component->getConfig('close_on_click'),
            'show' => $this->component->getConfig('show'),
            'align' => $this->component->getConfig('align'),
            'isAllowSpread' => $this->component->getConfig('allow_spread'),
            'isSpreadItem' => $this->component->getConfig('spread_item'),
            'accordion' => $this->component->getConfig('accordion'),
            'delay' => $this->component->getConfig('delay'),
            'className' => $attributes['class'] ?? null,
            'css' => $attributes['style'] ?? null,
            'shade' => $shade,
            'templet' => $this->component->getConfig('template'),
            'content' => $this->component->getConfig('content'),
            'clickScope' => $this->component->getConfig('click_scope'),
            'customName' => $this->component->getConfig('custom_name'),
            'ready' => $this->component->getConfig('ready'),
            'click' => $this->component->getConfig('click'),
            'close' => $this->component->getConfig('close'),
            'onClickOutside' => $this->component->getConfig('on_click_outside'),
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.dropdown.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
