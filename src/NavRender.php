<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Icon;
use Nece\WebUi\Nav;
use Nece\WebUi\Render;
use Nece\WebUi\Tag;

class NavRender extends Render
{
    public function render(): string
    {
        $children = $this->component->getChildren();

        return $this->renderChildList($children);
    }

    private function renderChildList(array $children, bool $is_sub = false): string
    {
        $list = [];
        foreach ($children as $child) {
            $list[] = $this->renderChildNav($child, $is_sub);
        }

        $attrs = [];
        $tag = 'ul';
        if ($is_sub) {
            $tag = 'dl';
            $attrs['class'] = 'layui-nav-child';
        } else {
            $tree = $this->component->getConfig('tree');
            $side = $this->component->getConfig('side');
            if ($tree) {
                $this->component->addClassName('layui-nav-tree');
            }
            
            if ($side) {
                $this->component->addClassName('layui-nav-side');
            }

            $this->component->addClassName('layui-nav');
            $attrs = $this->component->getAttributes();
        }

        return $this->renderHtml($tag, $attrs, $list);
    }

    private function renderChildNav(Nav $nav, bool $is_sub = false): string
    {
        $line = $nav->getConfig('line');
        if ($line) {
            return $this->renderHtml('hr', []);
        }

        $link = $nav->getConfig('link');
        $target = $nav->getConfig('target');
        $active = $nav->getConfig('active');
        $icon = $nav->getConfig('icon');
        $image = $nav->getConfig('image');
        $text = $nav->getConfig('text');
        $children = $nav->getChildren();

        $contents = [];
        if ($icon) {
            $contents[] = $this->getRender((new Icon($icon))->setClassName('layui-margin-2'))->render();
        }
        if ($image) {
            $contents[] = $this->renderHtml('img', ['src' => $image, 'class' => 'layui-nav-img']);
        }
        if ($text) {
            $contents[] = $text;
        }

        if ($active) {
            $nav->setClassName('layui-this');
        }

        if ($children) {
            $link = 'javascript:void(0);';
        }
        $a_attrs = ['href' => $link];

        if ($target) {
            $a_attrs['target'] = $target;
        }

        $nodes = [
            $this->renderHtml('a', $a_attrs, $contents)
        ];

        if ($children) {
            $nodes[] = $this->renderChildList($children, true);
        }

        $tag = 'li';
        if ($is_sub) {
            $tag = 'dd';
        } else {
            $nav->setClassName('layui-nav-item');
        }

        return $this->renderHtml($tag, $nav->getAttributes(), $nodes);
    }
}
