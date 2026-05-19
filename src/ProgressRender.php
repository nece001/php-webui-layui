<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class ProgressRender extends Render
{
    public function render(): string
    {
        $current = floatval($this->component->getConfig('current', 0));
        $total = floatval($this->component->getConfig('total', 0));
        $show_percent = $this->component->getConfig('show_percent');
        $show_number = $this->component->getConfig('show_number');
        $color = $this->component->getConfig('color');
        $big = $this->component->getConfig('big');

        $progress = ' ' . intval($current / $total * 100) . '%';
        if ($show_number) {
            $progress = $current . ' / ' . $total;
        }

        $bar_attrs = [
            'lay-percent' => $progress,
        ];
        if ($color) {
            $bar_attrs['class'] = 'layui-bg-' . $color;
        }

        $attrs = [];
        if ($show_percent || $show_number) {
            $attrs['lay-showpercent'] = 'true';
        }
        if ($big) {
            $attrs['class'] = ' layui-progress-big';
        }
        if ($show_percent) {
            $attrs['lay-showpercent'] = 'true';
        }

        $bar = $this->renderHtml('div.layui-progress-bar', $bar_attrs);
        return $this->renderHtml('div.layui-progress', $attrs, $bar);
    }
}
