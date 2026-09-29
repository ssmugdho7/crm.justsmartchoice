<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * For more readable code created this function to render only yes or not values for settings
 *
 * @param string $option_value     option from database to compare
 * @param string $label            input label
 * @param string $tooltip          tooltip
 * @param mixed  $replace_yes_text
 * @param mixed  $replace_no_text
 * @param mixed  $replace_1
 * @param mixed  $replace_0
 */
function render_yes_no_option($option_value, $label, $tooltip = '', $replace_yes_text = '', $replace_no_text = '', $replace_1 = '', $replace_0 = '')
{
    ob_start(); ?>
<div class="form-group">
    <label for="<?= $option_value; ?>"
        class="control-label clearfix">
        <?= ($tooltip != '' ? '<i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="' . _l($tooltip, '', false) . '"></i> ' : '') . _l($label, '', false); ?>
    </label>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_1_<?= $label; ?>"
            name="settings[<?= $option_value; ?>]"
            value="<?= $replace_1 == '' ? 1 : $replace_1; ?>"
            <?php if (get_option($option_value) == ($replace_1 == '' ? '1' : $replace_1)) {
                echo 'checked';
            } ?>>
        <label for="y_opt_1_<?= $label; ?>">
            <?= $replace_yes_text == '' ? 'Yes' : $replace_yes_text; ?>
        </label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_2_<?= $label; ?>"
            name="settings[<?= $option_value; ?>]"
            value="<?= $replace_0 == '' ? 0 : $replace_0; ?>"
            <?php if (get_option($option_value) == ($replace_0 == '' ? '0' : $replace_0)) {
                echo 'checked';
            } ?>>
        <label for="y_opt_2_<?= $label; ?>">
            <?= $replace_no_text == '' ? 'No' : $replace_no_text; ?>
        </label>
    </div>
</div>
<?php
    $settings = ob_get_contents();
    ob_end_clean();
    echo $settings;
}

function render_input($name, $label = '', $value = '', $type = 'text', $input_attrs = [], $form_group_attr = [], $form_group_class = '', $input_class = '')
{
    $input            = '';
    $_form_group_attr = '';
    $_input_attrs     = '';

    foreach ($input_attrs as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '="' . $val . '" ';
    }

    $_input_attrs = rtrim($_input_attrs);
    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '="' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (! empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    if (! empty($input_class)) {
        $input_class = ' ' . $input_class;
    }

    $input .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $input .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $input .= '<input type="' . $type . '" id="' . ($input_attrs['id'] ?? $name) . '" name="' . $name . '" class="form-control' . $input_class . '" ' . $_input_attrs . ' value="' . set_value($name, $value) . '">';
    $input .= '</div>';

    return $input;
}

function render_color_picker($name, $label = '', $value = '', $input_attrs = [])
{
    $_input_attrs = '';

    foreach ($input_attrs as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '="' . $val . '"';
    }

    $picker = '';
    $picker .= '<div class="form-group" app-field-wrapper="' . $name . '">';
    $picker .= '<label for="' . $name . '" class="control-label">' . $label . '</label>';
    $picker .= '<div class="input-group mbot15 colorpicker-input">
    <input type="text" value="' . set_value($name, $value) . '" name="' . $name . '" id="' . ($input_attrs['id'] ?? $name) . '" class="form-control" ' . $_input_attrs . ' />
    <span class="input-group-addon"><i></i></span>
</div>';
    $picker .= '</div>';

    return $picker;
}

function render_date_input($name, $label = '', $value = '', $input_attrs = [], $form_group_attr = [], $form_group_class = '', $input_class = '')
{
    $input            = '';
    $_form_group_attr = '';
    $_input_attrs     = '';

    foreach ($input_attrs as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '="' . $val . '" ';
    }

    $_input_attrs = rtrim($_input_attrs);
    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '="' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (! empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    if (! empty($input_class)) {
        $input_class = ' ' . $input_class;
    }

    $input .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $input .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $input .= '<div class="input-group date">';
    $input .= '<input type="text" id="' . $name . '" name="' . ($input_attrs['id'] ?? $name) . '" class="form-control datepicker' . $input_class . '" ' . $_input_attrs . ' value="' . set_value($name, $value) . '" autocomplete="off">';
    $input .= '<div class="input-group-addon">
    <i class="fa-regular fa-calendar calendar-icon"></i>
</div>';
    $input .= '</div>';
    $input .= '</div>';

    return $input;
}

function render_datetime_input($name, $label = '', $value = '', $input_attrs = [], $form_group_attr = [], $form_group_class = '', $input_class = '')
{
    $html = render_date_input($name, $label, $value, $input_attrs, $form_group_attr, $form_group_class, $input_class);

    return str_replace('datepicker', 'datetimepicker', $html);
}

function render_textarea($name, $label = '', $value = '', $textarea_attrs = [], $form_group_attr = [], $form_group_class = '', $textarea_class = '')
{
    $textarea         = '';
    $_form_group_attr = '';
    $_textarea_attrs  = '';

    if (! isset($textarea_attrs['rows'])) {
        $textarea_attrs['rows'] = 4;
    }

    if (isset($textarea_attrs['class'])) {
        $textarea_class .= ' ' . $textarea_attrs['class'];
        unset($textarea_attrs['class']);
    }

    foreach ($textarea_attrs as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_textarea_attrs .= $key . '="' . $val . '" ';
    }

    $_textarea_attrs = rtrim($_textarea_attrs);
    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '="' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (! empty($textarea_class)) {
        $textarea_class = trim($textarea_class);
        $textarea_class = ' ' . $textarea_class;
    }
    if (! empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }

    $textarea .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $textarea .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }

    $v = clear_textarea_breaks($value);
    if (strpos($textarea_class, 'tinymce') !== false) {
        $v = html_purify($value);
    }

    $textarea .= '<textarea id="' . ($textarea_attrs['id'] ?? $name) . '" name="' . $name . '" class="form-control' . $textarea_class . '" ' . $_textarea_attrs . '>' . set_value($name, $v) . '</textarea>';
    $textarea .= '</div>';

    return $textarea;
}

function render_select($name, $options, $option_attrs = [], $label = '', $selected = '', $select_attrs = [], $form_group_attr = [], $form_group_class = '', $select_class = '', $include_blank = true)
{
    $callback_translate = '';

    if (isset($options['callback_translate'])) {
        $callback_translate = $options['callback_translate'];
        unset($options['callback_translate']);
    }

    $select           = '';
    $_form_group_attr = '';
    $_select_attrs    = '';

    if (! isset($select_attrs['data-width'])) {
        $select_attrs['data-width'] = '100%';
    }

    if (! isset($select_attrs['data-none-selected-text'])) {
        $select_attrs['data-none-selected-text'] = _l('dropdown_non_selected_tex');
    }

    foreach ($select_attrs as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_select_attrs .= $key . '="' . $val . '" ';
    }

    $_select_attrs = rtrim($_select_attrs);
    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '="' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (! empty($select_class)) {
        $select_class = ' ' . $select_class;
    }

    if (! empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }

    $select .= '<div class="select-placeholder form-group' . $form_group_class . '" ' . $_form_group_attr . '>';

    if ($label != '') {
        $select .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }

    $select .= '<select id="' . $name . '" name="' . ($select_attrs['id'] ?? $name) . '" class="selectpicker' . $select_class . '" ' . $_select_attrs . ' data-live-search="true">';

    if ($include_blank == true) {
        $select .= '<option value=""></option>';
    }

    foreach ($options ?? [] as $option) {
        $val       = '';
        $_selected = '';
        $key       = '';

        if (isset($option[$option_attrs[0]]) && ! empty($option[$option_attrs[0]])) {
            $key = $option[$option_attrs[0]];
        }

        if (! is_array($option_attrs[1])) {
            $val = isset($option[$option_attrs[1]]) && $option[$option_attrs[1]] !== null ? $option[$option_attrs[1]] : '';
        } else {
            foreach ($option_attrs[1] as $_val) {
                $val .= (isset($option[$_val]) && $option[$_val] !== null ? $option[$_val] : '') . ' ';
            }
        }

        $val = trim((string) $val);

        if ($callback_translate != '') {
            if (function_exists($callback_translate) && is_callable($callback_translate)) {
                $val = call_user_func($callback_translate, $key);
            }
        }

        $data_sub_text = '';

        if (! is_array($selected)) {
            if ($selected != '') {
                if ($selected == $key) {
                    $_selected = ' selected';
                }
            }
        } else {
            foreach ($selected as $id) {
                if ($key == $id) {
                    $_selected = ' selected';
                }
            }
        }

        if (isset($option_attrs[2])) {
            if (strpos($option_attrs[2], ',') !== false) {
                $sub_text = '';
                $_temp    = explode(',', $option_attrs[2]);

                foreach ($_temp as $t) {
                    if (isset($option[$t])) {
                        $sub_text .= $option[$t] . ' ';
                    }
                }
            } else {
                if (isset($option[$option_attrs[2]])) {
                    $sub_text = $option[$option_attrs[2]];
                } else {
                    $sub_text = $option_attrs[2];
                }
            }

            $data_sub_text = ' data-subtext="' . $sub_text . '"';
        }

        $data_content = '';

        if (isset($option['option_attributes'])) {
            foreach ($option['option_attributes'] as $_opt_attr_key => $_opt_attr_val) {
                $data_content .= $_opt_attr_key . '="' . $_opt_attr_val . '"';
            }

            if ($data_content != '') {
                $data_content = ' ' . $data_content;
            }
        }

        $select .= '<option value="' . $key . '"' . $_selected . $data_content . $data_sub_text . '>' . $val . '</option>';
    }

    $select .= '</select>';
    $select .= '</div>';

    return $select;
}

function render_select_with_input_group($name, $options, $option_attrs = [], $label = '', $selected = '', $input_group_contents = '', $select_attrs = [], $form_group_attr = [], $form_group_class = '', $select_class = '', $include_blank = true)
{
    $select_class .= ' _select_input_group';
    $select = render_select($name, $options, $option_attrs, $label, $selected, $select_attrs, $form_group_attr, $form_group_class, $select_class, $include_blank);
    $select = str_replace('form-group', 'input-group input-group-select select-' . $name, $select);
    $select = str_replace('select-placeholder ', '', $select);
    $select = str_replace('</select>', '</select>' . $input_group_contents, $select);

    $re = '/<label.*<\/label>/i';
    preg_match($re, $select, $label);

    if (count($label) > 0) {
        $select = preg_replace($re, '', $select);
        $select = '<div class="select-placeholder form-group form-group-select-input-' . $name . ' input-group-select">' . $label[0] . $select . '</div>';
    }

    return $select;
}

if (! function_exists('render_form_builder_field')) {
    function render_form_builder_field($field)
    {
        if (is_array($field)) {
            $field = json_decode(json_encode($field));
        }

        if (! is_object($field)) {
            return;
        }

        if (! isset($field->type) && isset($field->fields) && is_array($field->fields)) {
            foreach ($field->fields as $nestedField) {
                render_form_builder_field($nestedField);
            }

            return;
        }

        if (! isset($field->type) || $field->type === '') {
            return;
        }

        if (! isset($field->name) || $field->name === '') {
            $field->name = 'field_' . uniqid();
        }

        if (isset($field->values) && is_array($field->values)) {
            foreach ($field->values as $valueKey => $valueItem) {
                if (is_array($valueItem)) {
                    $field->values[$valueKey] = json_decode(json_encode($valueItem));
                }
            }
        }

        $type         = $field->type;
        $classNameCol = 'col-md-12';

        if (isset($field->className)) {
            if (strpos($field->className, 'form-col') !== false) {
                $classNames = explode(' ', $field->className);
                if (is_array($classNames)) {
                    $classNameColArray = array_filter($classNames, function ($class) {
                        return startsWith($class, 'form-col');
                    });

                    $classNameCol = implode(' ', $classNameColArray);
                    $classNameCol = trim($classNameCol);
                    $classNameCol = str_replace('form-col-xs', 'col-xs', $classNameCol);
                    $classNameCol = str_replace('form-col-sm', 'col-sm', $classNameCol);
                    $classNameCol = str_replace('form-col-md', 'col-md', $classNameCol);
                    $classNameCol = str_replace('form-col-lg', 'col-lg', $classNameCol);
                    $classNameCol = str_replace('form-col', 'col-md', $classNameCol);
                }
            }
        }

        echo '<div class="' . $classNameCol . '">';

        if ($type == 'header' || $type == 'paragraph') {
            $subtype = $field->subtype ?? ($type == 'header' ? 'h4' : 'p');
            $label   = $field->label ?? '';

            echo '<' . $subtype . ' class="' . ($field->className ?? '') . '"';

            if ($type === 'paragraph') {
                echo ' style="line-height:24px;"';
            } else {
                echo ' style="margin-top:0; font-size:22px;"';
            }

            echo ' >' . check_for_links(nl2br($label)) . '</' . $subtype . '>';
        } else {
            echo '<div class="form-group" data-type="' . $type . '" data-name="' . $field->name . '" data-required="' . (isset($field->required) ? true : 'false') . '">';

            $label = ($field->label ?? '') ?: '';

            if ($label && ! empty(trim($label)) && $label != 'undefined') {
                echo '<label class="control-label" style="display:block;" for="' . $field->name . '">' . (isset($field->required) ? ' <span class="text-danger">* </span> ' : '') . $label . '' . (isset($field->description) ? ' <i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="' . $field->description . '" data-placement="top"></i>' : '') . '</label>';
            }

            if (isset($field->subtype) && $field->subtype == 'color') {
                echo '<div class="input-group colorpicker-input">
         <input' . (isset($field->required) ? ' required="true"' : '') . ' placeholder="' . ($field->placeholder ?? '') . '" type="text"' . (isset($field->value) ? ' value="' . $field->value . '"' : '') . ' name="' . $field->name . '" id="' . $field->name . '" class="' . ($field->className ?? '') . '" />
             <span class="input-group-addon"><i></i></span>
         </div>';
            } elseif ($type == 'text' || $type == 'number') {
                $ftype = $field->subtype ?? $type;

                if ($field->name === 'email' && is_client_logged_in()) {
                    $field->value = $GLOBALS['contact']->email;
                }

                echo '<input' . (isset($field->required) ? ' required="true"' : '') . (isset($field->placeholder) ? ' placeholder="' . $field->placeholder . '"' : '') . ' type="' . $ftype . '" name="' . $field->name . '" id="' . $field->name . '" class="' . ($field->className ?? '') . '" value="' . ($field->value ?? '') . '"' . ($field->type == 'file' ? ' accept="' . get_form_accepted_mimes() . '" filesize="' . file_upload_max_size() . '"' : '') . (isset($field->step) ? 'step="' . $field->step . '"' : '') . (isset($field->min) ? 'min="' . $field->min . '"' : '') . (isset($field->max) ? 'max="' . $field->max . '"' : '') . (isset($field->maxlength) ? 'maxlength="' . $field->maxlength . '"' : '') . '>';
            } elseif ($type == 'file') {
                $ftype = $field->subtype ?? $type;

                echo '<input' . (isset($field->required) ? ' required="true"' : '') . (isset($field->placeholder) ? ' placeholder="' . $field->placeholder . '"' : '') . ' type="' . $ftype . '" name="' . (isset($field->multiple) ? $field->name . '[]' : $field->name) . '" id="' . $field->name . '" class="' . ($field->className ?? '') . '" value="' . ($field->value ?? '') . '"' . ($field->type == 'file' ? ' accept="' . get_form_accepted_mimes() . '" filesize="' . file_upload_max_size() . '"' : '') . (isset($field->step) ? 'step="' . $field->step . '"' : '') . (isset($field->multiple) ? 'multiple="' . $field->multiple . '"' : '') . '>';
            } elseif ($type == 'textarea') {
                echo '<textarea' . (isset($field->required) ? ' required="true"' : '') . ' id="' . $field->name . '" name="' . $field->name . '" rows="' . ($field->rows ?? '4') . '" class="' . ($field->className ?? '') . '" placeholder="' . ($field->placeholder ?? '') . '"' . (isset($field->maxlength) ? 'maxlength="' . $field->maxlength . '"' : '') . '>' . ($field->value ?? '') . '</textarea>';
            } elseif ($type == 'date') {
                echo '<input' . (isset($field->required) ? ' required="true"' : '') . ' placeholder="' . ($field->placeholder ?? '') . '" type="text" class="' . ($field->className ?? '') . ' datepicker" name="' . $field->name . '" id="' . $field->name . '" value="' . (isset($field->value) ? _d($field->value) : '') . '">';
            } elseif ($type == 'datetime-local') {
                echo '<input' . (isset($field->required) ? ' required="true"' : '') . ' placeholder="' . ($field->placeholder ?? '') . '" type="text" class="' . ($field->className ?? '') . ' datetimepicker" name="' . $field->name . '" id="' . $field->name . '" value="' . (isset($field->value) ? _dt($field->value) : '') . '">';
            } elseif ($type == 'select') {
                $fieldValues = (isset($field->values) && is_array($field->values)) ? $field->values : [];

                echo '<select' . (isset($field->required) ? ' required="true"' : '') . '' . (isset($field->multiple) ? ' multiple="true"' : '') . ' class="' . ($field->className ?? '') . '" name="' . $field->name . (isset($field->multiple) ? '[]' : '') . '" id="' . $field->name . '"' . (count($fieldValues) > 10 ? 'data-live-search="true"' : '') . 'data-none-selected-text="' . ($field->placeholder ?? '-') . '">';

                foreach ($fieldValues as $option) {
                    if (is_array($option)) {
                        $option = (object) $option;
                    }

                    echo '<option value="' . ($option->value ?? '') . '" ' . (isset($option->selected) ? ' selected' : '') . '>' . ($option->label ?? '') . '</option>';
                }

                echo '</select>';
            } elseif ($type == 'checkbox-group') {
                $fieldValues = (isset($field->values) && is_array($field->values)) ? $field->values : [];

                if (count($fieldValues) > 0) {
                    $i = 0;
                    echo '<div class="chk">';

                    foreach ($fieldValues as $checkbox) {
                        if (is_array($checkbox)) {
                            $checkbox = (object) $checkbox;
                        }

                        echo '<div class="checkbox' . ((isset($field->inline) && $field->inline == 'true') || (isset($field->className) && strpos($field->className, 'form-inline-checkbox') !== false) ? ' checkbox-inline' : '') . '">';
                        echo '<input' . (isset($field->required) ? ' required="true"' : '') . ' class="' . ($field->className ?? '') . '" type="checkbox" id="chk_' . $field->name . '_' . $i . '" value="' . ($checkbox->value ?? '') . '" name="' . $field->name . '[]"' . (isset($checkbox->selected) ? ' checked' : '') . '>';
                        echo '<label for="chk_' . $field->name . '_' . $i . '">';
                        echo $checkbox->label ?? '';
                        echo '</label>';
                        echo '</div>';
                        $i++;
                    }

                    echo '</div>';
                }
            } elseif ($type == 'radio-group') {
                $fieldValues = (isset($field->values) && is_array($field->values)) ? $field->values : [];

                if (count($fieldValues) > 0) {
                    $i = 0;

                    foreach ($fieldValues as $radio) {
                        if (is_array($radio)) {
                            $radio = (object) $radio;
                        }

                        echo '<div class="radio ' . ((isset($field->inline) && $field->inline == true) || (isset($field->className) && strpos($field->className, 'form-inline-radio') !== false) ? ' radio-inline' : '') . '">';
                        echo '  <input ' . (isset($field->required) ? ' required="true"' : '') . ' class="' . ($field->className ?? '') . '" type="radio"';
                        echo 'name="' . $field->name . '" id="radio_' . $field->name . '_' . $i . '"';
                        echo 'value="' . ($radio->value ?? '') . '"' . (isset($radio->selected) ? ' checked' : '') . '>';
                        echo '<label for="radio_' . $field->name . '_' . $i . '">';
                        echo $radio->label ?? '';
                        echo '</label>';
                        echo '</div>';
                        $i++;
                    }
                }
            }

            echo '</div>';
        }

        echo '</div>';
    }
}

function format_external_form_custom_fields($custom_fields)
{
    $cfields = [];

    foreach ($custom_fields as $f) {
        $_field_object = new stdClass();
        $type          = $f['type'];
        $subtype       = '';
        $className     = 'form-control';

        if ($f['type'] == 'colorpicker') {
            $type    = 'text';
            $subtype = 'color';
        } elseif ($f['type'] == 'date_picker') {
            $type = 'date';
        } elseif ($f['type'] == 'date_picker_time') {
            $type = 'datetime-local';
        } elseif ($f['type'] == 'checkbox') {
            $type      = 'checkbox-group';
            $className = '';
            if ($f['display_inline'] == 1) {
                $className .= 'form-inline-checkbox';
            }
        } elseif ($f['type'] == 'input') {
            $type = 'text';
        } elseif ($f['type'] == 'multiselect') {
            $type = 'select';
        }

        $field_array = [
            'subtype'   => $subtype,
            'type'      => $type,
            'label'     => $f['name'],
            'className' => $className,
            'name'      => 'form-cf-' . $f['id'],
        ];

        if ($f['type'] == 'multiselect') {
            $field_array['multiple'] = true;
        }

        if ($f['required'] == 1) {
            $field_array['required'] = true;
        }

        if ($f['type'] == 'checkbox' || $f['type'] == 'select' || $f['type'] == 'multiselect') {
            $field_array['values'] = [];
            $options               = explode(',', $f['options']);

            if ($f['type'] == 'select') {
                array_push($field_array['values'], [
                    'label' => '',
                    'value' => '',
                ]);
            }

            foreach ($options as $option) {
                $option = trim($option);
                if ($option != '') {
                    array_push($field_array['values'], [
                        'label' => $option,
                        'value' => $option,
                    ]);
                }
            }
        }

        $_field_object->label    = $f['name'];
        $_field_object->name     = 'form-cf-' . $f['id'];
        $_field_object->fields   = [];
        $_field_object->fields[] = $field_array;
        $cfields[]               = $_field_object;
    }

    return $cfields;
}

function render_estimate_request_status_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [])
{
    if ($selected == '') {
        foreach ($statuses as $key => $status) {
            if ($status['flag'] == 'processing') {
                $selected = $status['id'];

                break;
            }
        }
    }

    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}
?>