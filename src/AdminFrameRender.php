<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Nav;
use Nece\WebUi\NavItem;
use Nece\WebUi\Render;

class AdminFrameRender extends Render
{

    public function render(): string
    {
        return $this->renderAdminFrame();
    }

    private function renderAdminFrame(): string
    {
        $logo_image = $this->component->getConfig('logo_image');
        $logo_text = $this->component->getConfig('logo_text');
        $nav_root = $this->component->getConfig('nav_root');
        $avatar_root = $this->component->getConfig('avatar_root');
        $menu_root = $this->component->getConfig('menu_root');
        $default_url = $this->component->getConfig('default_url');
        $footer_content = $this->component->getConfig('footer_content');
        $frame_name = $this->component->getConfig('frame_name', 'workspace');

        if ($logo_image) {
            $logo_content = '<img src="' . $logo_image . '" alt="' . $logo_text . '" class="layui-nav-img">';
        } else {
            $logo_content = $logo_text;
        }

        $nav = '';
        if ($nav_root) {
            $nav = $this->renderNav($nav_root, 'layui-layout-left');
        }

        $avatar = '';
        if ($avatar_root) {
            $avatar = $this->renderNav($avatar_root, 'layui-layout-right');
        }

        $menu = '';
        if ($menu_root) {
            $menu = $this->renderNav($menu_root, 'admin-menu', true);
        }

        $html = '
        <div class="layui-layout layui-layout-admin">

            <!-- 头部区域 -->
            <div class="layui-header">
                <!-- Logo -->
                <div class="layui-logo layui-hide-xs layui-bg-black">' . $logo_content . '</div>

                <!-- 水平导航 -->
                ' . $nav . '

                <!-- 头像菜单 -->
                ' . $avatar . '
            </div>

            <div class="layui-side layui-bg-black">
                <div class="layui-side-scroll">
                    ' . $menu . '
                </div>
            </div>

            <div class="layui-body">
                <iframe name="' . $frame_name . '" src="' . $default_url . '" frameborder="0" style="width: 100%; height: 100%;"></iframe>
            </div>

            <div class="layui-footer">' . $footer_content . '</div>

        </div>
        ';

        return $html;
    }

    private function renderNav(NavItem $nav_root, string $class_name = '', bool $is_tree = false): string
    {
        $children = $nav_root->getChildren();
        if ($children) {
            $nav = new Nav();
            foreach ($children as $child) {
                $nav->addChild($this->renderNavItem($child));
            }

            if ($is_tree) {
                $nav->setTree();
            }

            if ($class_name) {
                $nav->addClassName($class_name);
            }
            return $this->getRender($nav)->render();
        }
        return '';
    }

    private function renderNavItem(NavItem $item): Nav
    {
        $data = $item->toArray();
        $nav = new Nav();
        $nav->setText($data['text']);
        $nav->setLink($data['url']);
        $nav->setIcon($data['icon']);
        $nav->setDescription($data['description']);
        $nav->setTarget($data['target']);
        $nav->setImage($data['image']);

        $children = $item->getChildren();
        foreach ($children as $child) {
            $nav->addChild($this->renderNavItem($child));
        }

        return $nav;
    }
}
