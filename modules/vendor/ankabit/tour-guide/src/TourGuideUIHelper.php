<?php

namespace TourGuide;

/**
 * Class TourGuideUIHelper
 *
 * A helper class for managing the user interface components of the tour guide system.
 * This class extends the functionality of TourGuideUtils, providing additional methods 
 * for rendering UI elements and managing interactions within the tour guide experience.
 *
 * @package TourGuide
 *
 * @copyright Copyright (c) 2024, Ankabit Technologies. All rights reserved.
 * @license https://anakbit.com/legal/common-license
 *
 * @since 1.0
 */
class TourGuideUIHelper extends TourGuideUtils
{
    /**
     * Initializes the tour guide environment by loading necessary assets and rendering views.
     *
     * This method does the following:
     * - Loads the CSS styles for the tour guide.
     * - Retrieves all tour guides from the repository.
     * - Renders common scripts for the tour guide functionality.
     * - Renders the tour guide runner script.
     * - If a tour guide setup session exists, it renders the setup widget with the active tour guide ID and all tour guides.
     *
     * @return void
     *  
     * @see TourGuideUtils::renderView() for rendering views.
     * @see TourGuideRepository::getAll() for retrieving all tour guides.
     * @see tourGuideHelper()->session() for accessing the session data related to tour guides.
     */
    public static function init()
    {
        $tour_guides = [];
        $tour_guides_id_map = new \stdClass;

        try {
            $tour_guides = (new TourGuideRepository())->getAll();
            foreach ($tour_guides as $index => $tour_guide) {
                $tour_guide['steps'] = html_entity_decode($tour_guide['steps'] ?? ''); // Decode this as this might have be affected during xss cleaning when saving.
                $tour_guides_id_map->{$tour_guide['id']} =  $tour_guide;
                $tour_guides[$index] = $tour_guide;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        TourGuideUtils::renderView('scripts/common', ['tour_guides_json' => json_encode($tour_guides_id_map)]);
        TourGuideUtils::renderView('scripts/runner');

        if (!empty($tour_guide_id = tourGuideHelper()->getSetupSessionId())) {
            // Render the setup widget
            TourGuideUtils::renderView('setup', ['active_tour_guide_id' => $tour_guide_id, 'tour_guides' => $tour_guides]);
        }
    }

    /**
     * Renders a form content for the tour guide with specified fields and settings.
     *
     * This method generates a form content containing fields for tour guide settings and 
     * returns the HTML markup. The fields can be filtered through hooks, and 
     * hidden fields for triggers are included. Tooltips are added for hints where applicable.
     *
     * @param array $tourGuide An associative array of tour guide data, including settings.
     * @param string $groupSeparator HTML markup used to separate groups of fields. Defaults to '<hr/>'.
     * @param bool $includeScript Flag indicating whether to include the controlling scripts in the form. Defaults to true.
     *
     * @return string The generated HTML markup for the form.
     *
     *
     * @see self::getFormFields() for retrieving the form fields.
     * @see TourGuideHook::applyFilter() for applying filters to the form fields and the final form output.
     */
    public static function renderForm(array $tourGuide = [], string $groupSeparator = '<hr/>', $includeScript = true)
    {
        $fields = self::getFormFields();
        if (!empty($tourGuide['settings']) && is_string($tourGuide['settings']))
            $tourGuide['settings'] = json_decode($tourGuide['settings'], true);

        $fields = TourGuideHook::applyFilter('tour_guide_form_fields', $fields);

        $form = '<div class="tour-guide-form-main">';

        foreach ($fields['default'] as $name => $field) {
            $field['default'] = $tourGuide[$name] ?? $field['default'] ?? '';
            $form .= self::renderField(self::FORM_FIELD_PREFIX . '[' . $name . ']', $field);
        }

        $form .= '</div>';

        $form .= $groupSeparator;

        $form .= '<div id="tour-guide-widget-container"></div>';
        $triggers = self::escapeHtml($tourGuide['settings']['triggers'] ?? '');
        $form .= '<input value="' . ($triggers) . '" name="' . self::FORM_FIELD_PREFIX . '[settings][triggers]' . '" type="hidden" class="tour-guide-triggers-input" />';

        $form .= $groupSeparator;

        foreach ($fields['settings'] as $name => $field) {
            $field['label'] = tourGuideHelper()->translate('tour_guide_' . $name);
            $hint = $field['label_hint'] ?? tourGuideHelper()->translate('tour_guide_' . $name . '_hint');
            if (!empty($hint) && !str_starts_with($hint, 'tour_guide')) {
                $field['label'] .= ' <i class="tour-guide-icon tour-guide-icon-question-circle" data-toggle="tooltip" title="' . $hint . '"></i>';
                $field['extra']['title'] = $hint;
                $field['extra']['data-toggle'] = "tooltip";
            }
            if (!isset($field['extra']['class']))
                $field['extra']['class'] = 'form-control';
            $field['default'] = $tourGuide['settings'][$name] ?? $field['default'] ?? '';
            $form .= self::renderField(self::FORM_FIELD_PREFIX . '[settings][' . $name . ']', $field);
        }

        if ($includeScript) {
            $form .= TourGuideUtils::renderView('scripts/form', [], true);
        }

        $form = TourGuideHook::applyFilter('tour_guide_form', $form);

        return $form;
    }

    /**
     * Render form fields based on configuration.
     *
     * @param string $name The name of the field.
     * @param array $field The field configuration array.
     * @param string $wrapperClassname The wrapper class name.
     * @param string $labelClassname The label class name.
     * @return string The form input group string
     */
    public static function renderField(string $name, array $field, string $wrapperClassname = 'form-group', string $labelClassname = '')
    {
        $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $name));

        $view = '<div class="tour-guide-form-group ' . self::escapeHtml($wrapperClassname) . ' tour-guide-' . $field['input_type'] . '">';
        $view .= '<label class="' . self::escapeHtml($labelClassname) . '" for="' . self::escapeHtml($name) . '">' . $label . '</label>';

        switch ($field['input_type']) {
            case 'checkbox':
                $view .= self::renderCheckbox($name, $field['default'], $field['extra'] ?? []);
                break;

            case 'number':
                $view .= self::renderInput($name, 'number', $field['default'], $field['extra'] ?? []);
                break;

            case 'select':
                $view .= self::renderSelectInput($name, $field['options'], $field['default'], $field['extra'] ?? []);
                break;

            default:
                $view .= self::renderInput($name, 'text', $field['default'], $field['extra'] ?? []);
                break;
        }

        $view .= '</div>';
        return $view;
    }

    /**
     * Render generic input elements (text, number, etc.).
     *
     * @param string $name The name of the input field.
     * @param string $type The type of input field (text, number, etc.).
     * @param mixed $value The default value of the input.
     * @param array $extra Optional array of extra attributes.
     * @return string The HTML for the input element.
     */
    protected static function renderInput(string $name, string $type, $value, array $extra = []): string
    {
        if ($type === 'textarea') {
            return '<textarea name="' . self::escapeHtml($name) . '" id="' . self::escapeHtml($name) . '"' . self::mergeExtraInputAttributes($extra) . '>' . self::escapeHtml($value) . '</textarea>';
        }

        return '<input type="' . self::escapeHtml($type) . '" name="' . self::escapeHtml($name) . '" id="' . self::escapeHtml($name) . '" value="' . self::escapeHtml($value) . '"' . self::mergeExtraInputAttributes($extra) . '>';
    }

    /**
     * Render checkbox inputs.
     *
     * @param string $name The name of the checkbox field.
     * @param bool $checked Whether the checkbox is checked by default.
     * @param array $extra Optional array of extra attributes.
     * @return string The HTML for the checkbox input.
     */
    protected static function renderCheckbox(string $name, bool $checked, array $extra = []): string
    {
        $checkedAttr = $checked ? ' checked' : '';
        return '<input type="checkbox" name="' . self::escapeHtml($name) . '" id="' . self::escapeHtml($name) . '" value="1"' . $checkedAttr . self::mergeExtraInputAttributes($extra) . '>';
    }

    /**
     * Render select dropdown inputs.
     *
     * @param string $name The name of the select field.
     * @param array $options The options for the select dropdown.
     * @param string $selectedValue The default selected value.
     * @param array $extra Optional array of extra attributes.
     * @return string The HTML for the select input.
     */
    protected static function renderSelectInput(string $name, array $options, string $selectedValue, array $extra = []): string
    {
        $html = '<select name="' . self::escapeHtml($name) . '" id="' . self::escapeHtml($name) . '"' . self::mergeExtraInputAttributes($extra) . '>';
        foreach ($options as $value => $label) {
            $selected = ($value === $selectedValue) ? ' selected' : '';
            $html .= '<option value="' . self::escapeHtml($value) . '"' . $selected . '>' . self::escapeHtml($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }


    /** Tour guides table */
    /**
     * Renders the tour guide table with customizable columns and row data.
     *
     * @param array $tourGuides Array of tour guide data.
     * @param string $orderCol Column index for default ordering.
     * @param string $orderType Type of ordering (asc/desc).
     * @param bool $includeBlankRow Wether to show empty table data message or not.
     * 
     * The function allows dynamic customization of the table's classes, columns, and rows
     * through filter callbacks, enabling extensions and modifications.
     */
    public static function renderTable(array $tourGuides = [], string $orderCol = '4', string $orderType = 'desc', bool $includeBlankRow = false): void
    {
        // Apply filter to modify table classes if needed.
        $tableClass = TourGuideHook::applyFilter('tour_guide_table_class', 'table dt-table');

?>
<table class="<?php echo $tableClass; ?>" data-order-type="<?php echo $orderType; ?>"
    data-order-col="<?php echo $orderCol; ?>">
    <thead>
        <tr>
            <?php
                    // Render the default columns.
                    $columns = [
                        'priority' => tourGuideHelper()->translate('tour_guide_priority'),
                        'title' => tourGuideHelper()->translate('tour_guide_title'),
                        'description' => tourGuideHelper()->translate('tour_guide_description'),
                        'status' => tourGuideHelper()->translate('tour_guide_status'),
                        'created_at' => tourGuideHelper()->translate('tour_guide_created_at'),
                        'options' => tourGuideHelper()->translate('tour_guide_options'),
                    ];

                    // Allow columns to be filtered/modified.
                    $columns = TourGuideHook::applyFilter('tour_guide_table_columns', $columns);

                    // Display each column.
                    foreach ($columns as $key => $label) : ?>
            <th><?php echo $label; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tourGuides)) : ?>
        <?php foreach ($tourGuides as $tourGuide) : ?>
        <tr>
            <?php
                            // Prepare the row data, allowing filtering/modification.
                            $rowData = [
                                'priority' => $tourGuide['priority'],
                                'title' => htmlspecialchars($tourGuide['title'], ENT_QUOTES, 'UTF-8'),
                                'description' => htmlspecialchars($tourGuide['description'], ENT_QUOTES, 'UTF-8'),
                                'status' => sprintf(
                                    '<span class="badge bg-%s">%s</span>',
                                    $tourGuide['status'] === 'active' ? 'success' : 'danger',
                                    tourGuideHelper()->translate($tourGuide['status'])
                                ),
                                'created_at' => htmlspecialchars($tourGuide['created_at'], ENT_QUOTES, 'UTF-8'),
                                'options' => self::renderTableRowActions($tourGuide),
                            ];

                            // Allow row data to be modified by filters.
                            $rowData = TourGuideHook::applyFilter('tour_guide_table_row', $rowData, $tourGuide);

                            // Display each column's data.
                            foreach ($columns as $key => $label) : ?>
            <td><?php echo $rowData[$key] ?? ''; ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <?php elseif ($includeBlankRow) : ?>
        <tr>
            <td valign="top" colspan="<?php echo count($columns); ?>" class="text-center">
                <?php echo tourGuideHelper()->translate('tour_guide_not_found'); ?>
            </td>
            <?php for ($i = 0; $i < count($columns) - 1; $i++) {
                            echo '<td class="hidden" style="display:none"></td>';
                        } ?>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
    }


    /**
     * Generates the action buttons for each row in the tour guide table.
     *
     * @param array $tourGuide The tour guide.
     * 
     * The function provides a set of default actions (translate, edit, setup, duplicate, delete),
     * and it allows the modification or extension of these actions via filter callbacks.
     *
     * @return string The HTML string containing the action buttons.
     */
    public static function renderTableRowActions(array $tourGuide): string
    {
        $id = $tourGuide['id'];

        ob_start();
        $actions = [
            'edit' => sprintf(
                '<a href="%s" class="btn-icon" title="%s" data-toggle="tooltip"><i class="tour-guide-icon  tour-guide-icon-pencil"></i></a>',
                tourGuideHelper()->adminUrl('form/' . $id),
                tourGuideHelper()->translate('tour_guide_edit')
            ),
            'setup' => sprintf(
                '<a href="%s" title="%s" data-toggle="tooltip" class="btn-icon"><i class="tour-guide-icon  tour-guide-icon-cogs"></i></a>',
                tourGuideHelper()->adminUrl('setup/' . $id),
                tourGuideHelper()->translate('tour_guide_setup')
            ),
            'duplicate' => sprintf(
                '<a href="%s" title="%s" data-toggle="tooltip" class="btn-icon"><i class="tour-guide-icon  tour-guide-icon-duplicate"></i></a>',
                tourGuideHelper()->adminUrl('clone/' . $id),
                tourGuideHelper()->translate('tour_guide_duplicate')
            ),
            'translate' => sprintf(
                '<a href="%s" class="btn-icon" title="%s" data-toggle="tooltip"><i class="tour-guide-icon  tour-guide-icon-language"></i></a>',
                tourGuideHelper()->adminUrl('translate/' . $id),
                tourGuideHelper()->translate('tour_guide_translate')
            ),
            'play' => empty($tourGuide['steps']) ? '' : sprintf(
                '<a href="javascript:;" class="btn-icon tour-guide-player" data-tour-id="%s" title="%s" data-toggle="tooltip"><i class="tour-guide-icon  tour-guide-icon-play-circle"></i></a>',
                $id,
                tourGuideHelper()->translate('tour_guide_play')
            ),
            'delete' => sprintf(
                '<a href="%s" class="btn-icon text-danger _delete delete" title="%s" data-toggle="tooltip"><i class="tour-guide-icon  tour-guide-icon-trash-can"></i></a>',
                tourGuideHelper()->adminUrl('delete/' . $id),
                tourGuideHelper()->translate('tour_guide_delete')
            )
        ];

        // Allow actions to be modified by filters.
        $actions = TourGuideHook::applyFilter('tour_guide_table_actions', $actions, $id);

        echo '<div class="tour-guide-table-options">';
        foreach ($actions as $action) {
            echo $action;
        }
        echo '</div>';

        return ob_get_clean();
    }
}