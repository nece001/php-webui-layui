<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class TreeRender extends Render
{
    private $tree_id = '';
    private $tree_instance_id = '';

    public function render(): string
    {
        $name = $this->component->getConfig('name', '');

        $this->tree_id = $this->component->getId();
        $this->tree_instance_id = $this->tree_id . '_' . uniqid();
        $this->renderJavaScript();

        $hidden = $this->renderHtml('input', ['type' => 'hidden', 'name' => $name, 'id' => $this->tree_instance_id]); // 数据没有变化时value==''
        return $this->renderHtml('div', ['id' => $this->tree_id]) . $hidden;
    }

    public function renderJavaScript(): void
    {
        $show_line = $this->component->getConfig('show_line');
        $only_icon_control = $this->component->getConfig('only_icon_control');
        $accordion = $this->component->getConfig('accordion');
        $show_checkbox = $this->component->getConfig('show_checkbox');
        $data = $this->component->getConfig('data');

        $params = [
            'id' => $this->tree_instance_id,
            'elem' => '#' . $this->tree_id,
            'data' => $data,
            'oncheck' => 'oncheck_function'
        ];

        if (!is_null($show_line)) {
            $params['showLine'] = $show_line;
        }
        if (!is_null($only_icon_control)) {
            $params['onlyIconControl'] = $only_icon_control;
        }
        if (!is_null($accordion)) {
            $params['accordion'] = $accordion;
        }
        if (!is_null($show_checkbox)) {
            $params['showCheckbox'] = $show_checkbox;
        }

        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);
        $params_json = $this->renderOncheckFunction($params_json);

        $js = "layui.tree.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }

    private function renderOncheckFunction(string $json): string
    {
        $function = "function(obj){
            var checkedData = layui.tree.getChecked('$this->tree_instance_id');
            var json = JSON.stringify(checkedData);
            document.getElementById('$this->tree_instance_id').value = json;
        }";

        return str_replace('"oncheck_function"', $function, $json);
    }
}
