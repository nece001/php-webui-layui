<?php

namespace Nece\WebUi\Layui;

class TreeDataGridRender extends DataGridRender
{
    protected $js_class = 'treeTable';

    protected function buildParamsJson(): string
    {
        $params_json = parent::buildParamsJson();

        $async = [
            'enable' => $this->component->getConfig('async'),
            'url' => $this->component->getConfig('async_url'),
            'autoParam' => $this->component->getConfig('async_params'),
        ];
        $async = $this->arrayFilter($async);

        $custom_name = [
            'children' => $this->component->getConfig('custom_children_field'),
            'isParent' => $this->component->getConfig('custom_is_parent_field'),
            'name' => $this->component->getConfig('custom_name_field'),
            'id' => $this->component->getConfig('custom_id_field'),
            'pid' => $this->component->getConfig('custom_pid_field'),
            'icon' => $this->component->getConfig('custom_icon_field'),
        ];
        $custom_name = $this->arrayFilter($custom_name);

        $tree = [
            'async' => $async ? $async : null,
            'customName' => $custom_name ? $custom_name : null,
        ];

        $tree = $this->arrayFilter($tree);

        // 去掉最后一个}
        if ($tree) {
            $params_json = substr(trim($params_json), 0, -1);
            $params_json .= ', tree:' . json_encode($tree) . '}';
        }
        return $params_json;
    }
}
