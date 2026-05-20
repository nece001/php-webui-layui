<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class FixbarRender extends Render
{
    public function render(): string
    {
        $this->buildJavaScript();
        return '';
    }

    private function buildJavaScript(): void
    {

        $attributes = $this->component->getAttributes();
        $target_id = $this->component->getConfig('target_id');
        $scroll_id = $this->component->getConfig('scroll_id');
        $events = $this->component->getConfig('events');
        if ($target_id) {
            $target_id = "layui.$('#{$target_id}')";
        }
        if ($scroll_id) {
            $scroll_id = "layui.$('#{$scroll_id}')";
        }

        $params = [
            'bars' => $this->component->getConfig('bars'),
            'default' => $this->component->getConfig('default'),
            'bgcolor' => $this->component->getConfig('bgcolor'),
            'css' => $attributes['css'] ?? null,
            'target' => $target_id,
            'scroll' => $scroll_id,
            'margin' => $this->component->getConfig('margin'),
            'duration' => $this->component->getConfig('duration'),
            'on' => $events,
        ];

        $js_functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $js_functions);

        $js = "layui.util.fixbar({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
