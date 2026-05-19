<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class BreadCrumbRender extends Render
{
    public function render(): string
    {
        $links = $this->component->getConfig('links', []);

        $nodes = [];
        foreach ($links as $row) {
            $nodes[] = $this->renderHtml('a', ['href' => $row['url'], 'class' => 'layui-font-blue layui-font-14 layui-padding-2'], $row['title']);
        }

        $html = '/' . implode('/', $nodes);

        return $this->renderHtml('div.layui-panel layui-font-blue layui-padding-2 layui-font-14', $this->component->getAttributes(), $html);
    }
}
