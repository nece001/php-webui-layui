<?php

namespace Nece\WebUi\Layui;

class UploaderSingleRender extends UploadRender
{
    public function render(): string
    {
        $this->uploader_id = $this->component->getId();
        $preview = $this->component->getConfig('preview');
        $accept = $this->component->getConfig('accept');

        $nodes = [];
        if ($preview) {
            if ($accept == 'image') {
                $nodes[] = $this->renderImagePreview();
            } else {
                $nodes[] = $this->renderFilePreview();
            }
        }

        array_unshift($nodes, parent::render());

        return implode("\r\n", $nodes);
    }

    private function renderImagePreview(): string
    {
        $this->renderUploadBeforeFunction();
        $this->renderUploadDoneFunction();
        $this->renderUploadErrorFunction();
        $this->renderProgressFunction();
        $field_name = $this->component->getConfig('field_name');
        $value = $this->component->getConfig('value');

        $html = '
        <div style="width: 132px;">
            <div class="layui-upload-list">
                <img class="layui-upload-img" id="' . $this->uploader_id . '-upload-img" style="width: 100%; height: 92px;" src="' . $value . '">
                <div id="' . $this->uploader_id . '-upload-text"></div>
            </div>
            <div class="layui-progress layui-progress-big" lay-showPercent="yes" lay-filter="' . $this->uploader_id . '-filter">
                <div class="layui-progress-bar" lay-percent=""></div>
            </div>
            <input type="hidden" name="' . $field_name . '" id="' . $this->uploader_id . '-upload-input" value="' . $value . '">
        </div>';

        return $html;
    }

    private function renderUploadBeforeFunction(): void
    {
        $js = $this->component->getConfig('upload_before_function');
        if (!$js) {
            $js = "
                function(obj){
                    // 预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
                        layui.$('#{$this->uploader_id}-upload-img').attr('src', result); // 图片链接（base64）
                    });

                    layui.element.progress('{$this->uploader_id}-filter', '0%'); // 进度条复位
                }";

            $this->component->setUploadBeforeFunction($js);
        }
    }

    private function renderUploadDoneFunction(): void
    {
        $field_name = $this->component->getConfig('field_name');
        $js = $this->component->getConfig('done_function');
        if (!$js) {
            $js = "
                function(res){
                    console.log(res);

                    // 若上传失败
                    if(res.code > 0){
                        return layui.layer.msg('上传失败');
                    }
                    
                    layui.$('#{$this->uploader_id}-upload-input').val(res.data['$field_name']);
                    // 上传成功的一些操作
                    layui.$('#{$this->uploader_id}-upload-text').html(''); // 置空上传失败的状态
                }";

            $this->component->setDoneFunction($js);
        }
    }

    private function renderUploadErrorFunction(): void
    {
        $js = $this->component->getConfig('upload_error_function');
        if (!$js) {
            $js = "
                function(){
                    // 演示失败状态，并实现重传
                    var demoText = layui.$('#{$this->uploader_id}-upload-text');
                    demoText.html('<span style=\"color: #FF5722;\">上传失败</span>');
                }";

            $this->component->setUploadErrorFunction($js);
        }
    }

    private function renderProgressFunction(): void
    {
        $js = $this->component->getConfig('progress_function');
        if (!$js) {
            $js = "
                function(n, elem, e){
                    layui.element.progress('{$this->uploader_id}-filter', n + '%'); // 可配合 layui 进度条元素使用
                    if(n == 100){
                        layui.layer.msg('上传完毕', {icon: 1, time: 1000});
                    }
                }";

            $this->component->setProgressFunction($js);
        }
    }

    private function renderFilePreview(): string
    {
        $field_name = $this->component->getConfig('field_name');
        $value = $this->component->getConfig('value');

        $this->renderFileUploadDoneFunction();

        return '<input type="hidden" name="' . $field_name . '" id="' . $this->uploader_id . '-upload-input" value="' . $value . '">';
    }

    private function renderFileUploadDoneFunction(): void
    {
        $field_name = $this->component->getConfig('field_name');
        $js = $this->component->getConfig('done_function');
        if (!$js) {
            $js = "
                function(res){
                    console.log(res);

                    // 若上传失败
                    if(res.code > 0){
                        return layui.layer.msg('上传失败');
                    }

                    layui.$('#{$this->uploader_id}-upload-input').val(res.data['$field_name']);
                    layui.layer.msg('上传完毕', {icon: 1, time: 1000});
                }";

            $this->component->setDoneFunction($js);
        }
    }
}
