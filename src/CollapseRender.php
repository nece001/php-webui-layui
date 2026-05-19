<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\CollapseItem;
use Nece\WebUi\Render;

class CollapseRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();
        $accordion = $this->component->getConfig('accordion');

        $items = [];
        foreach ($children as $child) {
            $items[] = $this->renderItem($child);
        }

        $attributes = [];
        if ($accordion) {
            $attributes['lay-accordion'] = '';
        }

        return $this->renderHtml('div.layui-collapse', $attributes, $items);
    }

    private function renderItem(CollapseItem $item): string
    {
        $title = $item->getConfig('title');
        $show = $item->getConfig('show');
        $children = $item->getChildren();
        $nodes = [];
        
        if ($title) {
            $nodes[] = $this->renderHtml('div.layui-colla-title', [], $title);
        }

        if ($children) {
            $bodys = [];
            foreach ($children as $child) {
                $bodys[] = $this->getRender($child)->render();
            }

            $attributes = [];
            if ($show) {
                $attributes['class'] = 'layui-show';
            }
            $nodes[] = $this->renderHtml('div.layui-colla-content', $attributes, $bodys);
        }
        return $this->renderHtml('div.layui-colla-item', [], $nodes);
    }
}
