<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\GridColumn;
use Nece\WebUi\Render;

class GridRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();
        $space = $this->component->getConfig('space');

        $nodes = [];
        foreach ($children as $column) {
            $nodes[] = $this->renderColumn($column);
        }

        $attributes = [];
        if($space > 0) {
            $attributes['class'] = "layui-col-space{$space}";
        }

        return $this->renderHtml('div.layui-row', $attributes, $nodes);
    }

    private function renderColumn(GridColumn $column): string
    {
        $children = $column->getChildren();
        $sizes = [
            'xs' => $column->getConfig('xs'),
            'sm' => $column->getConfig('sm'),
            'md' => $column->getConfig('md'),
            'lg' => $column->getConfig('lg'),
            'xl' => $column->getConfig('xl'),
        ];

        $classes = [];
        foreach($sizes as $size => $value) {
            if($value > 0) {
                $classes[] = "layui-col-{$size}{$value}";
            }
        }
        $class = implode(' ', $classes);

        $nodes = [];
        foreach ($children as $child) {
            $nodes[] = $this->getRender($child)->render();
        }
        return $this->renderHtml('div', ['class' => $class], $nodes);
    }
}
