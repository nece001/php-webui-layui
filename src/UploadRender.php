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
            'data' => $this->component->getConfig('data', $this->component->getConfig('data_functions')),
            'choose' => $this->component->getConfig('choose'),
            'before' => $this->component->getConfig('upload_before'),
            'progress' => $this->component->getConfig('progress'),
            'done' => $this->component->getConfig('done'),
            'allDone' => $this->component->getConfig('all_done'),
            'error' => $this->component->getConfig('upload_error'),
        ];

        $functions = $this->component->getJsFunctions();
        $params_json = $this->arrayToJavaScriptObject($params, $functions);

        $js = "layui.upload.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
