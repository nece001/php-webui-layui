<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Icon;
use Nece\WebUi\Render;
use Nece\WebUi\TimelineItem;

class TimelineRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();
        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->renderItem($child);
        }
        return $this->renderHtml('div.layui-timeline', [], $nodes);
    }

    private function renderItem(TimelineItem $item): string
    {
        $icon = $item->getConfig('icon');
        $time = $item->getConfig('time');
        $children = $item->getChildren();

        $contents = [];
        if ($icon) {
            $contents[] = $this->getRender((new Icon($icon))->addClassName('layui-timeline-axis'))->render();
        }

        $nodes = [];
        if ($time) {
            $nodes[] = $this->renderHtml('h3.layui-timeline-title', [], $time);
        }

        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        $contents[] = $this->renderHtml('div.layui-timeline-content layui-text', [], $nodes);

        return $this->renderHtml('div.layui-timeline-item', [], $contents);
    }
}
