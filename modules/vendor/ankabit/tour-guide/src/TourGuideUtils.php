<?php

namespace TourGuide;

use HTMLPurifier;
use HTMLPurifier_Config;

class TourGuideUtils
{
    const FORM_FIELD_PREFIX = 'tour_guide';

    static function translate($key)
    {
        if (function_exists('tour_guide_translate')) {
            return tourGuideHelper()->translate($key);
        }

        return $key;
    }

    /**
     * Sanitize data using HTML Purifier.
     *
     * @param string|array $data The data to sanitize.
     * @return string|array The sanitized data.
     */
    public static function sanitizeData($data)
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = HTMLPurifier::instance($config);

        // Check if the input is an array
        if (is_array($data)) {
            // Recursively sanitize each element in the array
            return array_map([self::class, 'sanitizeData'], $data);
        }

        // Sanitize the string
        return $purifier->purify($data);
    }


    public static function isPostRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Get POST data with support for application/json in API endpoints.
     *
     * @param string|null $index The index of the data to retrieve.
     * @param bool $xssClean Whether to apply XSS cleaning.
     * @return mixed The POST data.
     */
    public static function getPostData(?string $index = null, bool $xssClean = false)
    {
        if (!self::isPostRequest()) {
            return null;
        }

        $data = $_POST;

        if (empty($data) && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);

            if ($xssClean) {
                $data = array_map([self::class, 'sanitizeData'], $data);
            }

            return $index ? ($data[$index] ?? null) : $data;
        }

        if ($xssClean)
            $data = array_map([self::class, 'sanitizeData'], $data);

        return ($index ? ($data[$index] ?? null) : $data);
    }

    public static function getFormFields()
    {

        $form_fields = [
            'title' => [
                'default' => '',
                'input_type' => 'text',
                'label' => tourGuideHelper()->translate('tour_guide_title'),
                'extra' => ['maxlength' => 150, 'class' => 'form-control', 'required' => 'requried']
            ],
            'description' => [
                'default' => '',
                'input_type' => 'textarea',
                'label' => tourGuideHelper()->translate('tour_guide_description'),
                'extra' => ['maxlength' => 500, 'class' => 'tinymce tinymce-manual form-control']
            ],
            'status' => [
                'default' => '',
                'input_type' => 'select',
                'label' => tourGuideHelper()->translate('tour_guide_status'),
                'options' => [
                    'active' => tourGuideHelper()->translate('tour_guide_active'),
                    'inactive' => tourGuideHelper()->translate('tour_guide_inactive')
                ],
                'extra' => ['class' => 'form-control']
            ]
        ];

        $buttonLabelHint = tourGuideHelper()->translate('tour_guide_button_label_hint');

        $settings_form_fields = [
            //'completeOnFinish' => ['default' => true, 'input_type' => 'checkbox'],
            'visitStepSource' => [
                'default' => "no",
                'input_type' => 'select',
                'options' => [
                    'yes' => tourGuideHelper()->translate('tour_guide_visit_step_source_yes'),
                    'maybe' => tourGuideHelper()->translate('tour_guide_visit_step_source_maybe'),
                    'no' => tourGuideHelper()->translate('tour_guide_visit_step_source_no'),
                ],
            ],
            'allowManualReplay' => ['default' => true, 'input_type' => 'checkbox'],
            'rememberStep' => ['default' => true, 'input_type' => 'checkbox'],
            'autoScroll' => ['default' => true, 'input_type' => 'checkbox'],
            'autoScrollSmooth' => ['default' => true, 'input_type' => 'checkbox'],
            'autoScrollOffset' => ['default' => 20, 'input_type' => 'number'],
            'backdropClass' => ['default' => '', 'input_type' => 'text'],
            'backdropColor' => ['default' => 'rgba(20,20,21,0.84)', 'input_type' => 'rgb_color'], // Special RGB case
            'targetPadding' => ['default' => 0, 'input_type' => 'number'],
            'backdropAnimate' => ['default' => true, 'input_type' => 'checkbox'],
            'dialogClass' => ['default' => '', 'input_type' => 'text'],
            'dialogZ' => ['default' => 999999999, 'input_type' => 'number'],
            'dialogWidth' => ['default' => 0, 'input_type' => 'number'],
            'dialogMaxWidth' => ['default' => 400, 'input_type' => 'number'],
            'dialogAnimate' => ['default' => true, 'input_type' => 'checkbox'],
            'showStepDots' => ['default' => true, 'input_type' => 'checkbox'],
            'stepDotsPlacement' => ['default' => 'footer', 'input_type' => 'select', 'options' => ['footer' => 'Footer', 'body' => 'Body']],
            'showButtons' => ['default' => true, 'input_type' => 'checkbox'],
            'showStepProgress' => ['default' => true, 'input_type' => 'checkbox'],
            'progressBar' => ['default' => '', 'input_type' => 'text'],
            'keyboardControls' => ['default' => true, 'input_type' => 'checkbox'],
            'exitOnEscape' => ['default' => false, 'input_type' => 'checkbox'],
            'exitOnClickOutside' => ['default' => false, 'input_type' => 'checkbox'],
            'debug' => ['default' => false, 'input_type' => 'checkbox'],
            'nextLabel' => ['default' => '', 'input_type' => 'text', 'label_hint' => $buttonLabelHint],
            'prevLabel' => ['default' => '', 'input_type' => 'text', 'label_hint' => $buttonLabelHint],
            'finishLabel' => ['default' => '', 'input_type' => 'text', 'label_hint' => $buttonLabelHint],
        ];

        $all_fields = [];
        $all_fields['default']  = $form_fields;
        $all_fields['settings'] = $settings_form_fields;

        return $all_fields;
    }

    public static function getFormData()
    {
        $data = self::getPostData(self::FORM_FIELD_PREFIX, true) ?? [];

        $settings = $data['settings'] ?? [];

        $form_fields = self::getFormFields();
        foreach ($form_fields['default'] as $name => $field) {
            // Handle checkboxes (unchecked checkboxes don't send data)
            if ($field['input_type'] === 'checkbox') {
                $data[$name] = isset($data[$name]) ? true : false;
            } else {
                // Handle others
                $data[$name] = $data[$name] ?? $field['default'];
            }
        }

        foreach ($form_fields['settings'] as $name => $field) {
            // Handle checkboxes (unchecked checkboxes don't send data)
            if ($field['input_type'] === 'checkbox') {
                $settings[$name] = isset($settings[$name]) ? true : false;
            } else {
                // Handle others
                $settings[$name] = $settings[$name] ?? $field['default'];
                if (is_numeric($settings[$name]))
                    $settings[$name] = stripos($settings[$name], '.') !== false ? (float)$settings[$name] : (int)$settings[$name];
            }
        }

        $data['settings'] = $settings;
        return $data;
    }



    /**
     * Merge extra attributes into the input element.
     *
     * @param array $extra Array of extra attributes.
     * @return string The HTML attributes string.
     */
    protected static function mergeExtraInputAttributes(array $extra): string
    {
        $attributes = '';
        foreach ($extra as $key => $value) {
            $attributes .= ' ' . self::escapeHtml($key) . '="' . self::escapeHtml($value) . '"';
        }
        return $attributes;
    }

    /**
     * Escape HTML entities.
     *
     * @param string $data The data to escape.
     * @return string The escaped data.
     */
    protected static function escapeHtml(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Copy directory and all contents
     * @since  Version 1.0.2
     * @param  string  $source      string
     * @param  string  $dest        destination
     * @param  integer $permissions folder permissions
     * @param  mixed $copier 
     * @return boolean
     */
    public static function xcopy($source, $dest, $permissions = 0755, $copier = null)
    {
        // Simple copy for a file
        if (is_file($source)) {

            if (is_callable($copier)) {
                return call_user_func($copier, $source, $dest);
            }
            return copy($source, $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Deep copy directories
            TourGuideUtils::xcopy("$source/$entry", "$dest/$entry", $permissions, $copier);
        }
        // Clean up
        $dir->close();

        return true;
    }

    /**
     * Publishes the tour guide assets to the specified destination directory.
     *
     * This method copies all assets from the current directory's `assets` folder 
     * to the provided destination directory, applying the specified permissions.
     *
     * @param string $destDir The destination directory where assets will be copied.
     * @param int $permissions The file permissions to set on the copied assets. Defaults to 0755.
     *
     * @return bool True on success, false on failure.
     *
     * @see TourGuideUtils::xcopy() for the method responsible for copying files.
     */
    public static function publishAssets($destDir, $permissions = 0755)
    {
        $copier = function ($source, $dest) {

            if (stripos($source, ".min.") === false) return false;

            return copy($source, $dest);
        };
        return TourGuideUtils::xcopy(TOUR_GUIDE_RESOURCES_DIR . '/assets', $destDir, $permissions, $copier);
    }

    /**
     * Renders a view file from the specified path with optional data.
     *
     * This function loads and includes a view file located in the specified
     * directory. It can also pass data to the view, which will be accessible as
     * variables inside the view file. The function either outputs the content
     * directly or returns it as a string, depending on the $returnHtml parameter.
     *
     * @param string $file The name of the view file to be rendered, relative to the views directory.
     * @param array $data An associative array of data to be extracted and used as variables within the view.
     * @param bool $returnHtml If true, the function returns the rendered content as a string. If false, it directly outputs the content.
     * 
     * @throws Exception If the view file does not exist, an exception is thrown.
     *
     * @return string|null If $returnHtml is true, the rendered content is returned as a string.
     *                     If $returnHtml is false, the content is output directly and nothing is returned.
     */
    static public function renderView($file, $data = [], $returnHtml = false)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (empty($ext))
            $file = $file . '.php';

        // Construct the full file path
        $filePath = TOUR_GUIDE_RESOURCES_DIR . '/views/' . $file;

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: " . $file);
        }

        // Extract the data array into variables to be accessible in the view
        if (!empty($data) && is_array($data)) {
            extract($data);
        }

        // Start output buffering if $returnHtml is true
        if ($returnHtml) {
            ob_start();
            require $filePath;
            return ob_get_clean(); // Get the output and clean the buffer
        } else {
            require $filePath; // Directly include the view
        }
    }
}