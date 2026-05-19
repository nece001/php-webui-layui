<?php

namespace Nece\WebUi\Layui;

class UploaderMultipleRender extends UploadRender
{
    public function render(): string
    {
        $this->uploader_id = $this->component->getId();
        $this->component->setMultiple(true);
        $preview = $this->component->getConfig('preview');
        $accept = $this->component->getConfig('accept');

        $this->renderUploadDoneFunction();
        $nodes = [];
        if ($preview) {
            if ($accept == 'image') {
                $nodes[] = $this->renderImagePreview();
            }
        }

        $button = parent::render();
        array_unshift($nodes, $button);
        return implode("\r\n", $nodes);
    }

    private function renderImagePreview(): string
    {
        $this->renderImageUploadBeforeFunction();
        $this->renderUploadDoneFunction();

        $html = '<blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 11px;">
    预览图：
    <div class="layui-upload-list" id="' . $this->uploader_id . '-upload-image-preview"></div>
 </blockquote>';

        return $html;
    }

    private function renderImageUploadBeforeFunction(): void
    {
        $js = $this->component->getConfig('upload_before_function');

        if (!$js) {
            $js = "function(obj){
                // 预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    layui.$('#{$this->uploader_id}-upload-image-preview').append('<img src=\"'+ result +'\" alt=\"'+ file.name +'\" style=\"width: 90px; height: 90px;\">')
                });
            }";
        }

        $this->component->setUploadBeforeFunction($js);
    }

    private function renderUploadDoneFunction(): void
    {
        $js = $this->component->getConfig('upload_done_function');

        if (!$js) {
            $js = "function(obj){
                // 上传成功后的回调
                console.log('上传成功后的回调');
                layui.layer.msg('上传成功', {icon: 1, time: 1000});
            }";
        }

        $this->component->setDoneFunction($js);
    }
}
