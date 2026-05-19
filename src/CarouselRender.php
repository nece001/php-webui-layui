<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class CarouselRender extends Render
{
    private $carousel_id = '';

    public function render(): string
    {
        $this->carousel_id = $this->component->getId();

        $children = $this->component->getChildren();

        $nodes = [];
        foreach ($children as $child) {
            $content = $this->getRender($child)->render();
            $nodes[] = $this->renderHtml('div', [], $content);
        }

        $this->renderJavaScript();

        $box = $this->renderHtml('div', ['carousel-item'], $nodes);
        return $this->renderHtml('div.layui-carousel', $this->component->getAttributes(), $box);
    }

    private function renderJavaScript(): void
    {
        $data = [
            'elem' => '#' . $this->carousel_id,
            'width' => $this->component->getConfig('width'),
            'height' => $this->component->getConfig('height'),
            'anim' => $this->component->getConfig('anim'),
            'full' => $this->component->getConfig('full'),
            'autoplay' => $this->component->getConfig('autoplay'),
            'interval' => $this->component->getConfig('interval'),
            'index' => $this->component->getConfig('index'),
            'arrow' => $this->component->getConfig('arrow'),
            'indicator' => $this->component->getConfig('indicator'),
        ];

        $params = [];
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $params[$key] = $value;
            }
        }

        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);

        $js = "layui.carousel.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
