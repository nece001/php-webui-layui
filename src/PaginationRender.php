<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class PaginationRender extends Render
{
    private $id = '';

    public function render(): string
    {
        $this->id = $this->component->getId();
        return $this->renderJsVersion();
    }

    /**
     * 渲染分页组件的JS版本
     *
     * @author nece001@163.com
     * @create 2026-05-12 14:35:16
     *
     * @return string
     */
    private function renderJsVersion(): string
    {
        $div = $this->renderHtml('div', $this->component->getAttributes());
        $this->renderPaginationJs();
        return $div;
    }

    private function renderPaginationJs(): void
    {
        $var_name = $this->component->getConfig('var_name', 'page');
        $page = $this->component->getConfig('page');
        $total = $this->component->getConfig('total');
        $page_size = $this->component->getConfig('page_size');
        $page_size_options = $this->component->getConfig('page_size_options');
        $item_limit = $this->component->getConfig('item_limit');
        $first = $this->component->getConfig('first');
        $prev = $this->component->getConfig('prev');
        $next = $this->component->getConfig('next');
        $last = $this->component->getConfig('last');
        $layout = $this->component->getConfig('layout', ['first', 'prev', 'page', 'next', 'last', 'count', 'limit', 'refresh', 'skip']);
        $theme = $this->component->getConfig('theme');
        $jump = $this->component->getConfig('jump');

        $data = [
            'elem' => $this->id,
            'curr' => $page,
            'count' => $total,
            'limit' => $page_size,
            'limits' => $page_size_options,
            'groups' => $item_limit,
            'first' => $first,
            'prev' => $prev,
            'next' => $next,
            'last' => $last,
            'theme' => $theme,
            'layout' => $layout,
            'jump' => 'jump_callback',
        ];

        $params = [];
        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                $params[$key] = $value;
            }
        }

        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);

        if ($jump) {
            $params_json = str_replace('"jump_callback"', $jump, $params_json);
        } else {
            $params_json = $this->renderJumpCallback($params_json, $var_name);
        }

        $js = "layui.laypage.render($params_json);";
        PageRender::addJavaScriptCode($js);
    }

    private function renderJumpCallback(string $json, string $var_name): string
    {
        $callback = "function(obj, first){
            if(!first){
                var search = location.search.replace('?','');
                var parts = search.split('&');
                var params = {};
                for(var i = 0; i < parts.length; i++){
                    var part = parts[i].split('=');
                    params[part[0]] = part[1];
                }

                params['{$var_name}'] = obj.curr;
                var new_search = '';
                for(var key in params){
                    new_search += key + '=' + params[key] + '&';
                }
                new_search = new_search.slice(0, -1);

                location.href = '?' + new_search;
            }
        }";

        return str_replace('"jump_callback"', $callback, $json);
    }
}
