<?php

namespace Nece\WebUi\Layui;

use Nece\WebUi\Render;

class InputRender extends Render
{
    public function render(): string
    {
        return $this->renderControl();
    }

    private function renderControl(): string
    {
        $type = $this->component->getAttribute('type', 'text');
        switch ($type) {
            case 'textarea':
                return $this->renderTextArea();
            case 'year':
            case 'month':
            case 'date':
            case 'time':
            case 'datetime':
                return $this->renderDatetime();
            default:
                return $this->renderInput();
        }
    }

    private function renderTextArea(): string
    {
        $value = $this->component->getAttribute('value', '');
        $this->component->removeAttribute('value');

        $validate = $this->component->getConfig('validate', []);
        $verify_type = $this->component->getConfig('validate_type', 'tips');
        $attributes = $this->component->getAttributes();
        if ($validate) {
            $attributes['lay-verify'] = implode('|', $validate);
        }
        if ($verify_type) {
            $attributes['lay-vertype'] = $verify_type;
        }

        return $this->renderHtml('textarea.layui-textarea', $attributes, $value);
    }

    private function renderInput(): string
    {
        $attributes = $this->component->getAttributes();

        $validate = $this->component->getConfig('validate', []);
        $verify_type = $this->component->getConfig('validate_type', 'tips');
        $affix = $this->component->getConfig('affix');
        if ($affix) {
            $attributes['lay-affix'] = $affix;
        }
        if ($validate) {
            $attributes['lay-verify'] = implode('|', $validate);
        }
        if ($verify_type) {
            $attributes['lay-vertype'] = $verify_type;
        }

        return $this->renderHtml('input.layui-input', $attributes, '');
    }

    private function renderDatetime(): string
    {
        $this->buildDatetimeJavascript();

        $attributes = $this->component->getAttributes();
        $type = $this->component->getAttribute('type', 'datetime');
        $attributes['type'] = 'text';
        $attributes['autocomplete'] = 'off';
        return $this->renderHtml('input.layui-input', $attributes, '');
    }

    private static $datetime_js_binded_ids = [];

    private function buildDatetimeJavascript(): void
    {
        $id = $this->component->getId();
        if (!in_array($id, self::$datetime_js_binded_ids)) {

            $type = $this->component->getAttribute('type', 'datetime');
            $ancestor_id = $this->component->getConfig('ancestor_id');
            $separator = $this->component->getConfig('separator');
            $range = $this->component->getConfig('datetime_range');
            $range_separator = $this->component->getConfig('datetime_range_separator');
            $associated_id = $this->component->getConfig('datetime_range_associated_id');
            $range_linked = $this->component->getConfig('datetime_range_linked');
            $shade = $this->component->getConfig('datetime_panel_shade');
            $theme = $this->component->getConfig('datetime_theme');

            if ($range_separator) {
                $range = $range_separator;
            }

            if ($associated_id) {
                $range = ['#' . $id, '#' . $associated_id];
                self::$datetime_js_binded_ids[] = $id;
                self::$datetime_js_binded_ids[] = $associated_id;
                if ($ancestor_id) {
                    $id = $ancestor_id;
                }
            }

            if ($shade) {
                if (!$shade['color']) {
                    $shade = [$shade['shade'], $shade['color']];
                } else {
                    $shade = $shade['shade'];
                }
            }

            if ($theme) {
                if ($theme['color']) {
                    $theme = [$theme['theme'], $theme['color']];
                } else {
                    $theme = $theme['theme'];
                }
            }

            $params = [
                'elem' => '#' . $id,
                'type' => $type,
                'range' => $range,
                'rangeLinked' => $this->component->getConfig('datetime_range_linked'),
                'fullPanel' => $this->component->getConfig('datetime_full_panel'),
                'format' => $this->component->getConfig('datetime_format'),
                'value' => $this->component->getAttribute('value'),
                'weekDayStart' => $this->component->getConfig('datetime_week_day_start'),
                'min' => $this->component->getAttribute('min'),
                'max' => $this->component->getAttribute('max'),
                'formatToDisplay' => $this->component->getConfig('datetime_format_to_display'),
                'shortcuts' => $this->component->getConfig('datetime_shortcuts'),
                'disabledDate' => $this->component->getConfig('datetime_disabled_date'),
                'show' => $this->component->getConfig('datetime_panel_show'),
                'position' => $this->component->getConfig('datetime_panel_position'),
                'zIndex' => $this->component->getConfig('datetime_panel_z_index'),
                'lang' => $this->component->getConfig('datetime_lang'),
                'calendar' => $this->component->getConfig('datetime_show_festival'),
                'shade' => $shade,
                'theme' => $theme,
                'calendar' => $this->component->getConfig('datetime_show_festival'),
                'mark' => $this->component->getConfig('datetime_marks'),
                'holidays' => $this->component->getConfig('datetime_holidays'),
                'cellRender' => $this->component->getConfig('datetime_cell_render'),
                'ready' => $this->component->getConfig('datetime_ready'),
                'change' => $this->component->getConfig('datetime_change'),
                'done' => $this->component->getConfig('datetime_done'),
                'onConfirm' => $this->component->getConfig('datetime_on_confirm'),
                'onNow' => $this->component->getConfig('datetime_on_now'),
                'onClear' => $this->component->getConfig('datetime_on_clear'),
                'close' => $this->component->getConfig('datetime_panel_close'),

                'trigger' => 'click',
                'isInitValue' => true,
                'isPreview' => true,
                'showBottom' => true,
                'autoConfirm' => true,
                'btns' => ['clear', 'now', 'confirm'],
            ];

            $params = array_filter($params);
            $params_json = json_encode($params);

            $js_functions = $this->component->getJsFunctions();
            if ($js_functions) {
                foreach ($js_functions as $key => $func) {
                    $hold = '"' . $key . '"';
                    $params_json = str_replace($hold, $func, $params_json);
                }
            }

            $js = "layui.laydate.render({$params_json});";
            PageRender::addJavaScriptCode($js);
        }
    }
}
