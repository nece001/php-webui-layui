<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;
use Nece\WebUi\TabItem;

class TabRender extends Render
{
    private $tab_id = '';

    public function render(): string
    {
        $card = $this->component->getConfig('card');
        $inline = $this->component->getConfig('inline');
        $panel = $this->component->getConfig('panel');
        $children = $this->component->getChildren();

        if ($card) {
            $this->component->addClassName('layui-tabs-card');
        }

        if ($panel) {
            $this->component->addClassName('layui-panel');
        }

        if ($inline) {
            $this->component->addClassName('layui-inline');
        }

        $headers = [];
        $bodys = [];
        foreach ($children as $child) {
            $this->tab_id = 'tab_id_' . uniqid();
            $headers[] = $this->renderHeader($child);
            $bodys[] = $this->renderBody($child);
        }

        $header = $this->renderHtml('ul.layui-tabs-header', [], $headers);
        $body = $this->renderHtml('div.layui-tabs-body', [], $bodys);
        return $this->renderHtml('div.layui-tabs', $this->component->getAttributes(), [$header, $body]);
    }

    private function renderHeader(TabItem $item): string
    {
        $label = $item->getConfig('label');
        $badge = $item->getConfig('badge');
        $nodes = [$label];

        if($badge) {
            $nodes[] = $this->getRender($badge)->render();
        }

        return $this->renderHtml('li', ['lay-id' => $this->tab_id], $nodes);
    }

    private function renderBody(TabItem $item): string
    {
        $children = $item->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }

        $item->setAttribute('lay-id', $this->tab_id);

        return $this->renderHtml('div.layui-tabs-item', $item->getAttributes(), $nodes);
    }
}
