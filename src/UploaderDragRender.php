<?php

namespace Nece\WebUi\Layui;

class UploaderDragRender extends UploadRender
{
    protected $uploader_id = '';

    public function render(): string
    {
        $this->uploader_id = $this->component->getId();

        $this->renderUploadDoneFunction();
        $this->renderJavaScriptCode();

        return $this->renderDragArea();
    }

    private function renderDragArea(): string
    {
        $html = '<div class="layui-upload-drag" style="display: block;" id="' . $this->uploader_id . '">
  <i class="layui-icon layui-icon-upload"></i> 
  <div>点击上传，或将文件拖拽到此处</div>
  <div class="layui-hide" id="' . $this->uploader_id . '-upload-demo-preview">
    <hr> <img src="" alt="上传成功后渲染" style="max-width: 100%">
  </div>
</div>';

        return $html;
    }

    private function renderUploadDoneFunction(): void
    {
        $js = $this->component->getConfig('upload_done_function');

        if (!$js) {
            $js = "function(res){
                console.log(res)
                layer.msg('上传成功');
                layui.$('#{$this->uploader_id}-upload-demo-preview').removeClass('layui-hide')
                .find('img').attr('src', res.data.url);
            }";
        }

        $this->component->setDoneFunction($js);
    }
}
