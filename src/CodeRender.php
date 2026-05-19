<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class CodeRender extends Render
{
    private $code_id = '';

    public function render(): string
    {
        $this->code_id = $this->component->getId();
        $code = $this->component->getConfig('code');
        $preview = $this->component->getConfig('preview');
        $attributes = $this->component->getAttributes();

        if ($preview && $preview != 'false') {
            $code = $this->renderHtml('textarea', [], $code);
        }

        $this->renderJavaScriptCode();
        return $this->renderHtml('pre.layui-code', $attributes, $code);
    }

    private function renderJavaScriptCode(): void
    {
        $tools_event_function = $this->component->getConfig('tools_event_function');
        $done_function = $this->component->getConfig('done_function');

        $functions = [
            'tools_event_function' => $tools_event_function,
            'done_function' => $done_function,
        ];

        $params = [
            'elem' => '#' . $this->code_id,
            'priview' => $this->component->getConfig('preview'),
            'layout' => $this->component->getConfig('layout'),
            'style' => $this->component->getConfig('style'),
            'code_style' => $this->component->getConfig('code_style'),
            'preview_style' => $this->component->getConfig('preview_style'),
            'tools' => $this->component->getConfig('tools'),
            'copy' => $this->component->getConfig('copy'),
            'text' => $this->component->getConfig('text'),
            'header' => $this->component->getConfig('header'),
            'ln' => $this->component->getConfig('ln'),
            'theme' => $this->component->getConfig('theme'),
            'encode' => $this->component->getConfig('encode'),
            'lang' => $this->component->getConfig('lang'),
            'lang_marker' => $this->component->getConfig('lang_marker'),
            'word_wrap' => $this->component->getConfig('word_wrap'),
            'highlighter' => $this->component->getConfig('highlighter'),
            'code_render' => $this->component->getConfig('code_render'),
            'tools_event' => $tools_event_function ? 'tools_event_function' : '',
            'done_function' => $done_function ? 'done_function' : '',
        ];

        $params = array_filter($params);
        $param_json = json_encode($params, JSON_UNESCAPED_UNICODE);

        $functions = array_filter($functions);
        foreach ($functions as $key => $func) {
            $param_json = str_replace($key, $func, $param_json);
        }

        $js = "layui.code({$param_json});";
        PageRender::addJavaScriptCode($js);
    }
}
