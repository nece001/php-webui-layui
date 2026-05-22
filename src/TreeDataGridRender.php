<?php

namespace Nece\WebUi\Layui;

class TreeDataGridRender extends DataGridRender
{
    public function render(): string
    {
        $this->buildJavascript();
        return $this->renderHtml('table.layui-hide', $this->component->getAttributes());
    }

    protected function  buildJavascript(): void
    {
        $cols = $this->buildColumnsJson();
        $this->buildDataParseFuncitonJavascript();

        $async_url = $this->component->getConfig('async_url');
        $async_params = $this->component->getConfig('async_params');
        $async = null;
        if ($async_url) {
            $async['enable'] = false;
            $async['url'] = $async_url;
        }
        if ($async_params) {
            $async['enable'] = false;
            $async['autoParam'] = $async_params;
        }

        $params = [
            'elem' => '#' . $this->component->getId(),
            'cols' => 'cols_json_placeholder',
            'url' => $this->component->getConfig('data_url'),
            'async' => $async,
            'page' => $this->component->getConfig('pagination'),
            'parseData' => 'parseData_function',
        ];

        $params_json = $this->arrayToJavaScriptObject($params, [
            'cols_json_placeholder' => $cols,
            'parseData_function' => 'data_grid_parse_data_function',
        ]);

        $js = "layui.treeTable.render({$params_json});";
        PageRender::addJavaScriptCode($js);
    }
}
