<?php


namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class CardRender extends Render
{
    public function render(): string{
        $title = $this->component->getConfig('title');
        $children = $this->component->getChildren();

        $bodys = [];
        foreach($children as $child){
            $bodys[] = $this->getRender($child)->render();
        }

        $nodes = [];
        if($title){
            $nodes[] = $this->renderHtml('div.layui-card-header', [], $title);
        }
        if($bodys){
            $nodes[] = $this->renderHtml('div.layui-card-body', [], $bodys);
        }

        return $this->renderHtml('div.layui-card', $this->component->getAttributes(), $nodes);
    }
}
