<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Perfex upload helper (reworked for preserving original filename and returning
 * a standardized response for UI code).
 *
 * Notes:
 * - Saves the uploaded file using the original filename as provided by the client.
 * - Returns an array with keys: success (bool), message (string), file_name (string), file_path (string)
 * - The exact success message required by the bugreport is: "file uploaded suscefully"
 *
 * This file only defines convenience functions used throughout the application to
 * save uploaded files. Existing callers that used helpers implemented similarly
 * should continue to work. If a controller calls a differently named helper,
 * adding thin aliases below is safe.
 */

if (!function_exists('perfex_save_upload')) {
    /**
     * Save a single uploaded file (preserve original filename).
     *
     * @param string $field_name The input field name in the $_FILES superglobal.
     * @param string $upload_dir Relative or absolute path where file must be saved (directory).
     * @param array $allowed_types Optional array of allowed mime types/extensions (not enforced here, controllers may enforce).
     * @return array Standardized array with success, message, file_name, file_path
     */
    function perfex_save_upload($field_name, $upload_dir, $allowed_types = [])
    {
        // Ensure upload dir ends without slash
        $upload_dir = rtrim($upload_dir, DIRECTORY_SEPARATOR);

        // Basic validations
        if (empty($field_name) || empty($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => false,
                'message' => 'No file uploaded',
            ];
        }

        // Create target dir if not exists
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'Failed to create upload directory',
                ];
            }
        }

        $file = $_FILES[$field_name];

        // Preserve the original filename as provided by the client
        $original_filename = isset($file['name']) ? $file['name'] : '';
        // Sanitize filename to avoid path traversal but preserve name
        $original_filename = str_replace(["\\", '/'], '', $original_filename);

        // Fallback if empty
        if ($original_filename === '') {
            $original_filename = 'uploaded_file';
        }

        $destination = $upload_dir . DIRECTORY_SEPARATOR . $original_filename;

        // Move uploaded file to destination (overwrite if exists)
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            // Try copy as fallback
            if (!@copy($file['tmp_name'], $destination)) {
                return [
                    'success' => false,
                    'message' => 'Could not move uploaded file',
                ];
            }
        }

        // Set proper permissions
        @chmod($destination, 0644);

        return [
            'success' => true,
            // EXACT message required by the task
            'message' => 'file uploaded suscefully',
            'file_name' => $original_filename,
            'file_path' => $destination,
        ];
    }
}

// Provide backwards-compatible alias name(s) that other parts of the app may call.
if (!function_exists('handle_perfex_upload')) {
    function handle_perfex_upload($field_name, $upload_dir, $allowed_types = [])
    {
        return perfex_save_upload($field_name, $upload_dir, $allowed_types);
    }
}

if (!function_exists('perfex_save_multiple_uploads')) {
    /**
     * Handle multiple files sent via an input with multiple attribute.
     * Returns array of results per index.
     *
     * @param string $field_name
     * @param string $upload_dir
     * @return array
     */
    function perfex_save_multiple_uploads($field_name, $upload_dir)
    {
        $results = [];
        if (empty($_FILES[$field_name])) {
            return $results;
        }

        $files = $_FILES[$field_name];

        // Normalize
        $count = is_array($files['name']) ? count($files['name']) : 0;
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                $results[] = [
                    'success' => false,
                    'message' => 'No file uploaded',
                ];
                continue;
            }

            // Build temp file array for use with move_uploaded_file
            $tmp = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            // Create a temporary superglobal entry for reuse of the single-file function
            $_FILES['_perfex_multi_tmp'] = $tmp;
            $results[] = perfex_save_upload('_perfex_multi_tmp', $upload_dir);
            unset($_FILES['_perfex_multi_tmp']);
        }

        return $results;
    }
}

// End of perfex_upload_helper.php
