<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Icon;
use Nece\WebUi\Render;

class UploadRender extends Render
{
    protected $uploader_id = '';

    public function render(): string
    {
        $this->uploader_id = $this->component->getId();
        $button_text = $this->component->getConfig('button_text', '上传');
        $button_icon = $this->component->getConfig('button_icon', '');

        $nodes = [];
        if ($button_icon) {
            $nodes[] = $this->getRender(new Icon($button_icon))->render();
        }
        $nodes[] = $button_text;

        $this->component->setAttribute('type', 'button');

        $this->renderJavaScriptCode();
        return $this->renderHtml('button.layui-btn', $this->component->getAttributes(), $nodes);
    }

    protected function renderJavaScriptCode(): void
    {
        $accept_mime = $this->component->getConfig('accept_mime');
        $accept_exts = $this->component->getConfig('accept_exts');

        if ($accept_mime) {
            $accept_mime = implode(',', $accept_mime);
        }
        if ($accept_exts) {
            $accept_exts = implode('|', $accept_exts);
        }

        $text = [
            'data-format-error' => $this->component->getConfig('format_error'),
            'check-error' => $this->component->getConfig('check_error'),
            'error' => $this->component->getConfig('upload_error'),
            'limit-number' => $this->component->getConfig('limit_number_message'),
            'limit-size' => $this->component->getConfig('limit_size_message'),
            'cross-domain' => $this->component->getConfig('cross_domain_message'),
        ];

        $params = [
            'elem' => '#' . $this->uploader_id,
            'url' => $this->component->getConfig('url'),
            'field' => $this->component->getConfig('field_name'),
            'data' => $this->component->getConfig('data'),
            'headers' => $this->component->getConfig('headers'),
            'dataType' => $this->component->getConfig('data_type'),
            'accept' => $this->component->getConfig('accept'),
            'acceptMime' => $accept_mime,
            'exts' => $accept_exts,
            'auto' => $this->component->getConfig('auto'),
            'bindAction' => $this->component->getConfig('bind_action'),
            'force' => $this->component->getConfig('force'),
            'size' => $this->component->getConfig('size'),
            'multiple' => $this->component->getConfig('multiple'),
            'unified' => $this->component->getConfig('unified'),
            'number' => $this->component->getConfig('limit_number'),
            'drag' => $this->component->getConfig('drag'),
            'text' => array_filter($text),
        ];

        $functions = [
            'data_function' => $this->component->getConfig('functions.data_function'),
            'choose_function' => $this->component->getConfig('functions.choose_function'),
            'before_function' => $this->component->getConfig('functions.upload_before_function'),
            'progress_function' => $this->component->getConfig('functions.progress_function'),
            'done_function' => $this->component->getConfig('functions.done_function'),
            'allDone_function' => $this->component->getConfig('functions.all_done_function'),
            'error_function' => $this->component->getConfig('functions.upload_error_function'),
        ];

        $params = array_filter($params, function ($value) {
            return !is_null($value);
        });
        $functions = array_filter($functions);

        foreach ($functions as $key => $function) {
            $name = str_replace('_function', '', $key);
            $params[$name] = $key;
        }

        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);
        foreach ($functions as $key => $function) {
            $name = '"' . $key . '"';
            $params_json = str_replace($name, $function, $params_json);
        }

        $js = "layui.upload.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
