<?php

namespace Nece\WebUi\Layui;

class TreeDataGridRender extends DataGridRender
{
    protected $js_class = 'table';
    
    protected function buildParamsJson(): string
    {
        $this->buildSearchFormJavascriptFunction();
        $this->buildDataParseFuncitonJavascript();
        $this->buildToolbarTemplate();
        $this->buildOperationTemplate();

        $cols = $this->buildColumnsJson();
        $exportToolJson = $this->buildExportToolJson();
        $refreshToolJson = $this->buildRefreshToolJson();

        $default_toolbar = ['refresh_toolbar_export_json', 'filter', 'print'];
        if ($exportToolJson) {
            $default_toolbar[] = 'default_toolbar_export_json';
        } else {
            $default_toolbar[] = 'exports';
        }

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
            'id' => $this->grid_id,
            'elem' => '#' . $this->grid_id,
            'cols' => 'cols_json_placeholder',
            'url' => $this->component->getConfig('data_url'),
            'data' => $this->component->getConfig('data'),
            'toolbar' => $this->toolbar ? '#' . $this->grid_id . '_toolbar_template' : null,
            'defaultToolbar' => $default_toolbar,
            'page' => $this->component->getConfig('pagination'),
            'even' => true,
            'limit' => $this->component->getConfig('page_size'),
            'request' => [
                'pageName' => $this->component->getConfig('page_var_name', 'page'),
                'limitName' => $this->component->getConfig('page_size_var_name', 'page_size'),
            ],
            'parseData' => 'parseData_function',
            'lineStyle' => $this->component->getConfig('line_style'),
            'async' => $async,
        ];

        $params = $this->arrayFilter($params);
        return $this->arrayToJavaScriptObject($params, [
            'cols_json_placeholder' => $cols,
            'parseData_function' => 'data_grid_parse_data_function',
            'default_toolbar_export_json' => $exportToolJson,
            'refresh_toolbar_export_json' => $refreshToolJson,
        ]);
    }
}
