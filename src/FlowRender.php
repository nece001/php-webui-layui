<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class FlowRender extends Render
{
    private $flow_id = '';

    public function render(): string
    {
        $this->flow_id = $this->component->getId();

        $this->renderjavascript();
        return $this->renderHtml('div', $this->component->getAttributes());
    }

    private function renderjavascript(): void
    {
        $data = [
            'elem' => '#' . $this->flow_id,
            'scrollElem' => $this->component->getConfig('scroll_elem_id'),
            'isAuto' => $this->component->getConfig('auto'),
            'moreText' => $this->component->getConfig('more_text'),
            'end' => $this->component->getConfig('end'),
            'isLazyimg' => $this->component->getConfig('lazyimg'),
            'mb' => $this->component->getConfig('mb'),
            'direction' => $this->component->getConfig('direction'),
            'done' => 'done_js_function',
        ];

        $done_js_function = $this->renderDoneJsFunctionCode();
        if (!$done_js_function) {
            unset($data['done']);
        }

        $params = array_filter($data);
        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);
        if ($done_js_function) {
            $params_json = str_replace('"done_js_function"', $done_js_function, $params_json);
        }

        $js = "layui.flow.load($params_json);";
        PageRender::addJavaScriptCode($js);
        if (isset($params['isLazyimg'])) {
            PageRender::addJavaScriptCode($this->renderLazyimgJsCode());
        }
    }

    private function renderDoneJsFunctionCode(): string
    {
        $done_js_function = $this->component->getConfig('done_js_function');
        if (is_null($done_js_function)) {
            $data_url = $this->component->getConfig('data_url');
            if ($data_url) {
                $data_template = $this->component->getConfig('data_template');
                $lazyimg = $this->component->getConfig('lazyimg');
                // 将模板中的src替换为：lay-src
                if ($lazyimg) {
                    $data_template = str_replace('src=', 'lay-src=', $data_template);
                }

                $data_template = json_encode(['template' => $data_template], JSON_UNESCAPED_UNICODE);

                $done_js_function = "function(page, next){
                    const url = '$data_url[url]'.replace('{page}', page);
                    const method = '$data_url[method]';
                    const data_template = $data_template;
                    const template = data_template.template;

                    fetch(url, {
                        method: method,
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);

                        var result = [];
                        data.data.items.forEach(item => {
                            var content = template;
                            for(let key in item){
                                content = content.replace('{' + key + '}', item[key]);
                            }

                            result.push(content);
                        });

                        next(result.join(''), data.data.items.length>0);
                    });
                }";
            }
        }

        return $done_js_function ? $done_js_function : '';
    }

    private function renderLazyimgJsCode(): string
    {
        $params = ['elem' => '#' . $this->flow_id, 'scrollElem' => $this->component->getConfig('scroll_elem_id')];
        $params = array_filter($params);
        $params_json = json_encode($params, JSON_UNESCAPED_UNICODE);
        return "layui.flow.lazyimg($params_json);";
    }
}
