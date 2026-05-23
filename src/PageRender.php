<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render\PageRender as RenderPageRender;

class PageRender extends RenderPageRender
{
    public function render(): string
    {
        $this->addAjaxBeforeIntercept();
        return parent::render();
    }

    private function addAjaxBeforeIntercept(): void
    {
        $func = $this->component->getConfig('ajax_before_intercept_js_function');
        var_dump($func);
        if ($func) {
            $js = "layui.define(['jquery'], function(exports){
                var $ = layui.jquery;

                // Layui全局请求拦截 表格/普通ajax全部生效
                $.ajaxPrefilter(function(options) {
                    options.headers = options.headers || {};

                    var headers = $func(options.data);
                    for (var key in headers) {
                        options.headers[key] = headers[key];
                    }
                });
            });";

            PageRender::addJavaScriptCode($js);
        }
    }
}
