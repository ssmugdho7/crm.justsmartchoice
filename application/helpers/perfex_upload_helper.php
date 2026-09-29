<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Perfex upload helper replacement
// This helper ensures uploaded files are saved using the original filename when possible,
// ensures a consistent return structure (success / error and file meta), and preserves
// behavior across Perfex controllers that use upload helpers.

if (!function_exists('perfex_handle_file_upload')) {
    /**
     * Handle a single file upload (from $_FILES entry)
     *
     * @param array $file $_FILES['some_field'] or single-file array
     * @param string $relative_path Relative path under uploads/ (e.g. 'projects' or 'projects/' )
     * @param array $options Optional keys: 'allowed_types' (array or comma string), 'max_size' (KB)
     * @return array Array with either ['success'=>true, <file_meta...>] or ['success'=>false,'error'=>message]
     */
    function perfex_handle_file_upload($file, $relative_path = '', $options = [])
    {
        // Basic validations
        if (empty($file) || !isset($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded.'];
        }

        $ci = &get_instance();

        // Normalize relative path
        $relative_path = trim($relative_path, "\/ ");
        $upload_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . ($relative_path ? $relative_path . DIRECTORY_SEPARATOR : '');

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                return ['success' => false, 'error' => 'Could not create upload directory.'];
            }
        }

        // Extract original client filename
        $client_name = isset($file['name']) ? $file['name'] : null;
        // Some uploaders send array-style files (multiple) - support by taking first entry if arrays
        if (is_array($client_name)) {
            // If file input is multi, callers should pass single-file array; try to adapt
            $client_name = $client_name[0] ?? null;
            $file['tmp_name'] = is_array($file['tmp_name']) ? ($file['tmp_name'][0] ?? null) : $file['tmp_name'];
            $file['error'] = is_array($file['error']) ? ($file['error'][0] ?? 0) : $file['error'];
            $file['size'] = is_array($file['size']) ? ($file['size'][0] ?? 0) : $file['size'];
            $file['type'] = is_array($file['type']) ? ($file['type'][0] ?? '') : $file['type'];
        }

        if (empty($client_name)) {
            return ['success' => false, 'error' => 'Uploaded file has no name.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            // Sometimes AJAX uploaders place uploaded file as tmp; still attempt to move if file exists
            if (!file_exists($file['tmp_name'])) {
                return ['success' => false, 'error' => 'Temporary uploaded file missing.'];
            }
        }

        // Sanitize filename to avoid path injection and problematic chars
        $original_filename = $client_name;
        // Remove path information (some browsers may include full path)
        $original_filename = str_replace(['\\', '/'], '', $original_filename);
        // Normalize encoding and remove null bytes
        $original_filename = trim(preg_replace('/[\0]+/', '', $original_filename));

        // Keep extension and raw name
        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        $raw_name = pathinfo($original_filename, PATHINFO_FILENAME);

        // Create a safe filename (preserve readable name but remove unsafe chars)
        $safe_raw = preg_replace('/[^A-Za-z0-9 _\-\.\(\)\[\]]+/', '_', $raw_name);
        $safe_raw = trim(preg_replace('/_+/', '_', $safe_raw), '_');
        if ($safe_raw === '') {
            $safe_raw = 'file';
        }

        $safe_ext = $ext !== '' ? '.' . preg_replace('/[^A-Za-z0-9]/', '', $ext) : '';

        $target_name = $safe_raw . $safe_ext;

        $target_path = $upload_dir . $target_name;

        // If file exists, append incremental suffix to keep original filename visible
        $counter = 1;
        while (file_exists($target_path)) {
            $target_name = $safe_raw . '_' . $counter . $safe_ext;
            $target_path = $upload_dir . $target_name;
            $counter++;
            // Safety cap
            if ($counter > 1000) {
                return ['success' => false, 'error' => 'Could not find free filename for upload.'];
            }
        }

        // Move uploaded file to target
        $moved = false;
        // Try move_uploaded_file first (safer)
        if (is_uploaded_file($file['tmp_name'])) {
            $moved = @move_uploaded_file($file['tmp_name'], $target_path);
        }
        // Fallback to rename or copy
        if (!$moved) {
            // Try copy
            if (@copy($file['tmp_name'], $target_path)) {
                // Try to remove temp
                @unlink($file['tmp_name']);
                $moved = true;
            } else {
                // Last attempt: try rename
                $moved = @rename($file['tmp_name'], $target_path);
            }
        }

        if (!$moved) {
            return ['success' => false, 'error' => 'Failed to move uploaded file to destination. Check permissions.'];
        }

        // Set proper permissions
        @chmod($target_path, 0644);

        // Build response similar to CI Upload library + perfex expected fields
        $file_size_kb = isset($file['size']) ? round($file['size'] / 1024, 2) : round(filesize($target_path) / 1024, 2);

        $file_type = isset($file['type']) && $file['type'] ? $file['type'] : mime_content_type($target_path);

        $rel_storage_path = ($relative_path ? rtrim($relative_path, '/') . '/' : '') . $target_name;

        $result = [
            'success' => true,
            'file_name' => $target_name, // actual stored name on disk under uploads/<relative_path>
            'original_filename' => $original_filename, // original client name
            'client_name' => $original_filename,
            'raw_name' => $safe_raw,
            'file_ext' => $safe_ext,
            'file_type' => $file_type,
            'file_size' => $file_size_kb,
            'full_path' => $target_path,
            'file_path' => 'uploads/' . ($relative_path ? rtrim($relative_path, '/') . '/' : '') . $target_name,
            'is_image' => false,
        ];

        // If image, get dimensions
        $img_info = @getimagesize($target_path);
        if ($img_info !== false) {
            $result['is_image'] = true;
            $result['image_width'] = $img_info[0];
            $result['image_height'] = $img_info[1];
            $result['image_type'] = image_type_to_mime_type($img_info[2]);
        }

        return $result;
    }
}

// Backwards compatible wrapper expected by some older Perfex code
if (!function_exists('handle_project_file_upload')) {
    function handle_project_file_upload($file, $project_id = null)
    {
        // Determine folder for projects
        $path = 'projects';
        return perfex_handle_file_upload($file, $path, []);
    }
}

// Generic wrapper preserving older function name used across app code
if (!function_exists('handle_uploaded_file')) {
    function handle_uploaded_file($file, $relative_path = '')
    {
        return perfex_handle_file_upload($file, $relative_path, []);
    }
}

// End of helper
