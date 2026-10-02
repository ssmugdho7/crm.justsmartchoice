<?php

defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Handle Estimate request attachments if any
 * @param  mixed $estimateRequestId
 * @return boolean
 */
function handle_estimate_request_attachments($estimateRequestId, $index_name = 'file')
{
    $hookData = hooks()->apply_filters('before_handle_estimate_request_attachment', [
        'estimate_request_id' => $estimateRequestId,
        'index_name' => $index_name,
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    $totalUploaded = 0;
    if (
        (isset($_FILES[$index_name]['name']) && !empty($_FILES[$index_name]['name'])) ||
        (isset($_FILES[$index_name]) && is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)
    ) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            if (isset($_FILES[$index_name]) && empty($_FILES[$index_name]['name'][$i])) {
                continue;
            }

            if (isset($_FILES[$index_name][$i]) && _perfex_upload_error($_FILES[$index_name]['error'][$i])) {
                header('HTTP/1.0 400 Bad error');
                echo _perfex_upload_error($_FILES[$index_name]['error'][$i]);
                die;
            }

            $CI = & get_instance();
            if (isset($_FILES[$index_name]['name'][$i]) && $_FILES[$index_name]['name'][$i] != '') {
                hooks()->do_action('before_upload_estimate_request_attachment', $estimateRequestId);
                $path = get_upload_path_by_type('estimate_request') . $estimateRequestId . '/';
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath

                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (!_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                        continue;
                    }

                    _maybe_create_upload_path($path);

                    $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the company uploads dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $CI = & get_instance();
                        $CI->load->model('estimate_request_model');
                        $data   = [];
                        $data[] = [
                            'file_name' => $filename,
                            'filetype'  => $_FILES[$index_name]['type'][$i],
                            ];
                        $CI->estimate_request_model->add_attachment_to_database($estimateRequestId, $data, false);
                        $totalUploaded++;
                    }
                }
            }
        }
    }

    if ($totalUploaded > 0) {
        return true;
    }

    return false;
}

/**
 * Handles uploads error with translation texts
 * @param  mixed $error type of error
 * @return mixed
 */
function _perfex_upload_error($error)
{
    $uploadErrors = [
        0 => _l('file_uploaded_success'),
        1 => _l('file_exceeds_max_filesize'),
        2 => _l('file_exceeds_maxfile_size_in_form'),
        3 => _l('file_uploaded_partially'),
        4 => _l('file_not_uploaded'),
        6 => _l('file_missing_temporary_folder'),
        7 => _l('file_failed_to_write_to_disk'),
        8 => _l('file_php_extension_blocked'),
    ];

    if (isset($uploadErrors[$error]) && $error != 0) {
        return $uploadErrors[$error];
    }

    return false;
}
/**
 * Newsfeed post attachments
 * @param  mixed $postid Post ID to add attachments
 * @return void  - Result values
 */
function handle_newsfeed_post_attachments($postid)
{
    $hookData = hooks()->apply_filters('before_handle_newsfeed_post_attachments', [
        'newsfeed_post_id' => $postid,
        'index_name' => 'file',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        echo json_encode([
            'success' => $hookData['handled_externally_successfully'],
            'postid'  => $postid,
        ]);
        return;
    }

    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    $path = get_upload_path_by_type('newsfeed') . $postid . '/';
    $CI   = & get_instance();
    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_newsfeed_attachment', $postid);
        $uploaded_files = false;
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename = unique_filename($path, $_FILES['file']['name']);
            // In case client side validation is bypassed
            if (_upload_extension_allowed($filename)) {
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $file_uploaded = true;
                    $attachment    = [];
                    $attachment[]  = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'],
                    ];
                    $CI->misc_model->add_attachment_to_database($postid, 'newsfeed_post', $attachment);
                }
            }
        }
        if ($file_uploaded == true) {
            echo json_encode([
                'success' => true,
                'postid'  => $postid,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'postid'  => $postid,
            ]);
        }
    }
}
/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */
function handle_project_file_uploads($project_id)
{
    $hookData = hooks()->apply_filters('before_handle_project_file_uploads', [
        'project_id' => $project_id,
        'index_name' => 'file',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    $filesIDS = [];
    $errors   = [];

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
        hooks()->do_action('before_upload_project_attachment', $project_id);

        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        $path = get_upload_path_by_type('project') . $project_id . '/';

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);

                continue;
            }

            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $sourceFilename = sanitize_file_name($_FILES['file']['name'][$i]);
                $filename = unique_filename($path, $sourceFilename);
                $originalFilename = $sourceFilename;

                // In case client side validation is bypassed
                if (!_upload_extension_allowed($filename)) {
                    continue;
                }

                $newFilePath = $path . $filename;
                $CI = & get_instance();
                if (!is_dir($path)) {
                    _maybe_create_upload_path($path);
                }
                if (is_dir($path) && !is_writable($path)) {
                    @chmod($path, 0755);
                }

                // Prefer PHP's secure uploaded-file move. Some shared-hosting setups can
                // reject the rename operation even though the temporary upload is valid;
                // in that case use a local copy as a safe filesystem fallback.
                $moved = @move_uploaded_file($tmpFilePath, $newFilePath);
                if (!$moved && is_uploaded_file($tmpFilePath)) {
                    $moved = @copy($tmpFilePath, $newFilePath);
                    if ($moved) {
                        @unlink($tmpFilePath);
                    }
                }

                if ($moved) {
                    @chmod($newFilePath, 0644);
                    if (is_client_logged_in()) {
                        $contact_id = get_contact_user_id();
                        $staffid    = 0;
                    } else {
                        $staffid    = get_staff_user_id();
                        $contact_id = 0;
                    }
                    $data = [
                            'project_id' => $project_id,
                            'file_name'  => $filename,
                            'original_file_name'  => $originalFilename,
                            'filetype'   => $_FILES['file']['type'][$i],
                            'dateadded'  => date('Y-m-d H:i:s'),
                            'staffid'    => $staffid,
                            'contact_id' => $contact_id,
                            'subject'    => $originalFilename,
                        ];
                    if (is_client_logged_in()) {
                        $data['visible_to_customer'] = 1;
                    } else {
                        $data['visible_to_customer'] = ($CI->input->post('visible_to_customer') == 'true' ? 1 : 0);
                    }
                    $CI->db->insert(db_prefix() . 'project_files', $data);

                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        if (is_image($newFilePath)) {
                            create_img_thumb($path, $filename);
                        }
                        array_push($filesIDS, $insert_id);
                    } else {
                        unlink($newFilePath);

                        return false;
                    }
                } else {
                    log_message('error', 'Project upload filesystem move failed. Project=' . (int) $project_id . ' Path=' . $path . ' Writable=' . (is_writable($path) ? 'yes' : 'no'));
                    $errors[$originalFilename] = _l('problem_uploading_file');
                }
            }
        }
    }

    if (count($filesIDS) > 0) {
        try {
            $CI->load->model('projects_model');
            $lastFileID = end($filesIDS);
            if ($lastFileID) {
                $CI->projects_model->new_project_file_notification($lastFileID, $project_id);
            }
        } catch (Throwable $e) {
            // File upload must remain successful even when a notification provider fails.
            log_message('error', 'Project file notification failed for project ' . (int) $project_id . ': ' . $e->getMessage());
        }
    }

    return [
        'success' => count($filesIDS) > 0 && count($errors) === 0,
        'uploaded_ids' => $filesIDS,
        'uploaded_count' => count($filesIDS),
        'files' => count($filesIDS) ? $CI->db->where_in('id', $filesIDS)->get(db_prefix() . 'project_files')->result_array() : [],
        'errors' => $errors,
    ];
}
/**
 * Handle contract attachments if any
 * @param  mixed $contractid
 * @return boolean
 */
function handle_contract_attachment($id)
{
    $CI = &get_instance();
    $id = (int) $id;

    $hookData = hooks()->apply_filters('before_handle_contract_attachment', [
        'contract_id' => $id,
        'index_name' => 'file',
        'handled_externally' => false,
        'handled_externally_successfully' => false,
        'files' => $_FILES,
    ]);

    if ($hookData['handled_externally']) {
        return (bool) $hookData['handled_externally_successfully'];
    }

    if ($id < 1 || !isset($_FILES['file'])) {
        log_message('error', 'Contract attachment upload rejected: missing contract id or file.');
        return false;
    }

    $file = $_FILES['file'];
    if (_perfex_upload_error($file['error'])) {
        log_message('error', 'Contract attachment PHP upload error for contract ' . $id . ': ' . _perfex_upload_error($file['error']));
        return false;
    }

    if (empty($file['name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        log_message('error', 'Contract attachment upload rejected for contract ' . $id . ': invalid temporary upload.');
        return false;
    }

    if (!_upload_extension_allowed($file['name'])) {
        log_message('error', 'Contract attachment upload rejected for contract ' . $id . ': extension not allowed for ' . $file['name']);
        return false;
    }

    $maxBytes = function_exists('file_upload_max_size') ? (int) file_upload_max_size() : 0;
    if ($maxBytes > 0 && isset($file['size']) && (int) $file['size'] > $maxBytes) {
        log_message('error', 'Contract attachment upload rejected for contract ' . $id . ': file exceeds server limit.');
        return false;
    }

    hooks()->do_action('before_upload_contract_attachment', $id);
    $path = get_upload_path_by_type('contract') . $id . '/';
    _maybe_create_upload_path($path);

    if (!is_dir($path) || !is_writable($path)) {
        log_message('error', 'Contract attachment path is not writable: ' . $path);
        return false;
    }

    $filename = unique_filename($path, $file['name']);
    $newFilePath = $path . $filename;

    if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
        log_message('error', 'Contract attachment move failed for contract ' . $id . ' to ' . $newFilePath);
        return false;
    }

    @chmod($newFilePath, 0644);

    $attachment = [[
        'file_name' => $filename,
        'filetype'  => $file['type'] ?? mime_content_type($newFilePath),
    ]];

    try {
        $inserted = $CI->misc_model->add_attachment_to_database($id, 'contract', $attachment);
        if (!$inserted) {
            @unlink($newFilePath);
            log_message('error', 'Contract attachment database insert failed for contract ' . $id . '.');
            return false;
        }
    } catch (Throwable $e) {
        @unlink($newFilePath);
        log_message('error', 'Contract attachment database exception for contract ' . $id . ': ' . $e->getMessage());
        return false;
    }

    hooks()->do_action('contract_attachment_added', $id);
    return true;
}
/**
 * Handle lead attachments if any
 * @param  mixed $leadid
 * @return boolean
 */
function handle_lead_attachments($leadid, $index_name = 'file', $form_activity = false)
{
    $hookData = hooks()->apply_filters('before_handle_lead_attachment', [
        'lead_id' => $leadid,
        'index_name' => $index_name,
        'handled_externally' => false, // e.g. module upload to s3
        'form_activity' => $form_activity,
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    $path           = get_upload_path_by_type('lead') . $leadid . '/';
    $CI             = &get_instance();
    $CI->load->model('leads_model');

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != ''
                || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);

                $newFilePath = $path . $filename;

                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI->leads_model->add_attachment_to_database($leadid, [[
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]], false, $form_activity);
                }
            }
        }
    }

    return true;
}

/**
 * Task attachments upload array
 * Multiple task attachments can be upload if input type is array or dropzone plugin is used
 * @param  mixed $taskid     task id
 * @param  string $index_name attachments index, in different forms different index name is used
 * @return array|false
 */
function handle_task_attachments_array($taskid, $index_name = 'attachments')
{
    $hookData = hooks()->apply_filters('before_handle_task_attachments_array', [
        'task_id' => $taskid,
        'index_name' => $index_name,
        'uploaded_files' => [],
        'handled_externally' => false, // e.g. module upload to s3
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return count($hookData['uploaded_files']) > 0 ? $hookData['uploaded_files'] : false;
    }

    $uploaded_files = [];
    $path           = get_upload_path_by_type('task') . $taskid . '/';

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    array_push($uploaded_files, [
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]);

                    if (is_image($newFilePath)) {
                        create_img_thumb($path, $filename);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

/**
 * Invoice attachments
 * @param  mixed $invoiceid invoice ID to add attachments
 * @return void  - Result values
 */
function handle_sales_attachments($rel_id, $rel_type)
{
    $hookData = hooks()->apply_filters('before_handle_sales_attachments', [
        'rel_id' => $rel_id,
        'rel_type' => $rel_type,
        'index_name' => 'file',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        echo $hookData['handled_externally_successfully'];
        return;
    }

    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }

    $path = get_upload_path_by_type($rel_type) . $rel_id . '/';

    $CI = & get_instance();
    if (isset($_FILES['file']['name'])) {
        $uploaded_files = false;
        $file_uploaded  = false;
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $type = $_FILES['file']['type'];
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $file_uploaded = true;
                $attachment    = [];
                $attachment[]  = [
                    'file_name' => $filename,
                    'filetype'  => $type,
                    ];
                $insert_id = $CI->misc_model->add_attachment_to_database($rel_id, $rel_type, $attachment);
                $CI->db->where('id', $insert_id)->update(db_prefix() . 'files', ['visible_to_customer' => 1]);
                // Get the key so we can return to ajax request and show download link
                $CI->db->where('id', $insert_id);
                $_attachment = $CI->db->get(db_prefix() . 'files')->row();
                $key         = $_attachment->attachment_key;

                if ($rel_type == 'invoice') {
                    $CI->load->model('invoices_model');
                    $CI->invoices_model->log_invoice_activity($rel_id, 'invoice_activity_added_attachment');
                } elseif ($rel_type == 'estimate') {
                    $CI->load->model('estimates_model');
                    $CI->estimates_model->log_estimate_activity($rel_id, 'estimate_activity_added_attachment');
                }
            }
        }

        if ($file_uploaded == true) {
            echo json_encode([
                'success'       => true,
                'attachment_id' => $insert_id,
                'filetype'      => $type,
                'rel_id'        => $rel_id,
                'file_name'     => $filename,
                'key'           => $key,
            ]);
        } else {
            echo json_encode([
                'success'   => false,
                'rel_id'    => $rel_id,
                'file_name' => $filename,
            ]);
        }
    }
}
/**
 * Client attachments
 * @param  mixed $clientid Client ID to add attachments
 * @return array  - Result values
 */
function handle_client_attachments_upload($id, $customer_upload = false)
{
    $hookData = hooks()->apply_filters('before_handle_client_attachment', [
        'customer_id' => $id,
        'index_name' => 'file',
        'customer_upload' => $customer_upload,
        'total_uploaded' => 0,
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['total_uploaded'];
    }

    $path          = get_upload_path_by_type('customer') . $id . '/';
    $CI            = & get_instance();
    $totalUploaded = 0;

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        _file_attachments_index_fix('file');
        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            hooks()->do_action('before_upload_client_attachment', $id);
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES['file']['error'][$i])
                    || !_upload_extension_allowed($_FILES['file']['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES['file']['name'][$i]);
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $attachment   = [];
                    $attachment[] = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'][$i],
                    ];

                    if (is_image($newFilePath)) {
                        create_img_thumb($newFilePath, $filename);
                    }

                    if ($customer_upload == true) {
                        $attachment[0]['staffid']          = 0;
                        $attachment[0]['contact_id']       = get_contact_user_id();
                        $attachment['visible_to_customer'] = 1;
                    }

                    $CI->misc_model->add_attachment_to_database($id, 'customer', $attachment);
                    $totalUploaded++;
                }
            }
        }
    }

    return (bool) $totalUploaded;
}
/**
 * Handles upload for expenses receipt
 * @param  mixed $id expense id
 * @return void
 */
function handle_expense_attachments($id)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }

    $hookData = hooks()->apply_filters('before_handle_expense_attachment', [
        'expense_id' => $id,
        'index_name' => 'file',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }


    $path = get_upload_path_by_type('expense') . $id . '/';
    $CI = &get_instance();
    if (empty($_FILES['file']['tmp_name'])) {
        return false;
    }
    hooks()->do_action('before_upload_expense_attachment', $id);
    _maybe_create_upload_path($path);
    $filename = unique_filename($path, sanitize_file_name($_FILES['file']['name']));
    if (!_upload_extension_allowed($filename) || !is_dir($path) || !is_writable($path)) {
        return false;
    }
    $newFilePath = $path . $filename;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $newFilePath)) {
        log_message('error', 'Unable to save expense attachment for expense ' . (int) $id);
        return false;
    }
    @chmod($newFilePath, 0644);
    $CI->misc_model->add_attachment_to_database($id, 'expense', [[
        'file_name' => $filename,
        'filetype' => $_FILES['file']['type'],
    ]]);
    return true;
}
/**
 * Check for ticket attachment after inserting ticket to database
 * @param  mixed $ticketid
 * @return mixed           false if no attachment || array uploaded attachments
 */
function handle_ticket_attachments($ticketid, $index_name = 'attachments')
{

    $hookData = hooks()->apply_filters('before_handle_ticket_attachment', [
        'ticket_id' => $ticketid,
        'index_name' => $index_name,
        'uploaded_files' => [],
        'handled_externally' => false, // e.g. module upload to s3
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return count($hookData['uploaded_files']) > 0 ? $hookData['uploaded_files'] : false;
    }

    $path           = get_upload_path_by_type('ticket') . $ticketid . '/';
    $uploaded_files = [];

    if (isset($_FILES[$index_name])) {
        _file_attachments_index_fix($index_name);

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            hooks()->do_action('before_upload_ticket_attachment', $ticketid);

            if ($i <= get_option('maximum_allowed_ticket_attachments')) {
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Getting file extension
                    $extension = strtolower(pathinfo($_FILES[$index_name]['name'][$i], PATHINFO_EXTENSION));

                    $allowed_extensions = explode(',', get_option('ticket_attachments_file_extensions'));
                    $allowed_extensions = array_map('trim', $allowed_extensions);
                    // Check for all cases if this extension is allowed
                    if (!in_array('.' . $extension, $allowed_extensions)) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        array_push($uploaded_files, [
                                'file_name' => $filename,
                                'filetype'  => $_FILES[$index_name]['type'][$i],
                                ]);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

/**
 * Check for company logo upload
 * @return boolean
 */
function handle_company_logo_upload()
{
    $CI = &get_instance();

    $logoIndex = [
        'logo'      => 'Light Logo',
        'logo_dark' => 'Dark Logo',
        'logo_contract' => 'Contract Logo',
    ];

    $result = [
        'attempted' => [],
        'uploaded'  => [],
        'failed'    => [],
    ];

    foreach ($logoIndex as $logo => $label) {
        $index = 'company_' . $logo;

        if (!isset($_FILES[$index]) || empty($_FILES[$index]['name'])) {
            continue;
        }

        $result['attempted'][] = $label;

        if (_perfex_upload_error($_FILES[$index]['error'])) {
            $result['failed'][$label] = _perfex_upload_error($_FILES[$index]['error']);
            continue;
        }

        $hookData = hooks()->apply_filters('before_handle_company_logo_upload', [
            'index_name' => $index,
            'handled_externally' => false,
            'handled_externally_successfully' => false,
            'files' => $_FILES,
        ]);

        if ($hookData['handled_externally']) {
            if ($hookData['handled_externally_successfully']) {
                $result['uploaded'][] = $label;
            } else {
                $result['failed'][$label] = 'External storage did not confirm the upload.';
            }
            continue;
        }

        $tmpFilePath = $_FILES[$index]['tmp_name'] ?? '';
        $originalName = $_FILES[$index]['name'] ?? '';
        $size = (int) ($_FILES[$index]['size'] ?? 0);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = array_unique(hooks()->apply_filters(
            'company_logo_upload_allowed_extensions',
            ['jpg', 'jpeg', 'png', 'gif', 'svg']
        ));

        if ($tmpFilePath === '' || !is_uploaded_file($tmpFilePath)) {
            $result['failed'][$label] = 'The temporary upload file is missing or invalid.';
            continue;
        }

        if ($size <= 0 || $size > 2097152) {
            $result['failed'][$label] = 'Maximum allowed file size is 2 MB.';
            continue;
        }

        if (!in_array($extension, $allowedExtensions, true)) {
            $result['failed'][$label] = 'Allowed file types: JPG, JPEG, PNG, GIF, and SVG.';
            continue;
        }

        // Raster images must fit the CRM logo envelope. SVG is validated by size/type only.
        if ($extension !== 'svg') {
            $imageInfo = @getimagesize($tmpFilePath);
            if (!$imageInfo) {
                $result['failed'][$label] = 'The uploaded file is not a valid image.';
                continue;
            }
            if ((int) $imageInfo[0] > 1600 || (int) $imageInfo[1] > 600) {
                $result['failed'][$label] = 'Maximum dimensions are 1600 × 600 pixels.';
                continue;
            }
        }

        hooks()->do_action('before_upload_company_logo_attachment');
        $path = get_upload_path_by_type('company');
        _maybe_create_upload_path($path);

        $filename = md5($logo . microtime(true) . $originalName) . '.' . $extension;
        $newFilePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;

        $moved = false;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        if (is_dir($path) && !is_writable($path)) {
            @chmod($path, 0755);
            clearstatcache(true, $path);
        }
        if (is_dir($path) && is_writable($path)) {
            $moved = @move_uploaded_file($tmpFilePath, $newFilePath);
            if (!$moved && is_file($tmpFilePath)) {
                $moved = @rename($tmpFilePath, $newFilePath);
            }
            if (!$moved && is_file($tmpFilePath)) {
                $moved = @copy($tmpFilePath, $newFilePath);
            }
            if (!$moved && is_file($tmpFilePath)) {
                $payload = @file_get_contents($tmpFilePath);
                if ($payload !== false) {
                    $written = @file_put_contents($newFilePath, $payload, LOCK_EX);
                    $moved = $written !== false && $written > 0;
                }
            }
            if ($moved && is_file($tmpFilePath)) {
                @unlink($tmpFilePath);
            }
        }

        if (!$moved || !is_file($newFilePath)) {
            $result['failed'][$label] = 'The CRM could not write the logo to ' . $path . '. Exists: ' . (is_dir($path) ? 'Yes' : 'No') . '; Writable: ' . (is_writable($path) ? 'Yes' : 'No') . '; Permissions: ' . (is_dir($path) ? substr(sprintf('%o', @fileperms($path)), -4) : 'N/A') . '.';
            continue;
        }

        @chmod($newFilePath, 0644);

        $oldFilename = get_option($index);

        // Save directly and verify against the database. get_option() is cached during
        // the current request, so reading it immediately after update_option() can
        // incorrectly report a failure even though the database update succeeded.
        $optionsTable = db_prefix() . 'options';
        // Use Perfex's own option API first so hooks/cache behavior stays consistent.
        // Verify directly against the database because get_option() is request-cached.
        $savedByApi = update_option($index, $filename);
        $existingOption = $CI->db->where('name', $index)->get($optionsTable)->row();
        if (!$existingOption) {
            $insert = ['name' => $index, 'value' => $filename];
            if ($CI->db->field_exists('autoload', $optionsTable)) {
                $insert['autoload'] = 1;
            }
            $CI->db->insert($optionsTable, $insert);
        } elseif ((string) $existingOption->value !== (string) $filename) {
            $CI->db->where('name', $index)->update($optionsTable, ['value' => $filename]);
        }

        $savedOption = $CI->db->select('value')->where('name', $index)->get($optionsTable)->row();
        if (!$savedOption || (string) $savedOption->value !== (string) $filename) {
            @unlink($newFilePath);
            $dbError = $CI->db->error();
            $result['failed'][$label] = 'The logo file was uploaded, but the CRM could not save the new logo setting.'
                . (!empty($dbError['message']) ? ' Database: ' . $dbError['message'] : '');
            continue;
        }

        // Remove stale application cache so the thumbnail and navigation logo update
        // on the very next request.
        $cachePath = APPPATH . 'cache';
        if (is_dir($cachePath)) {
            foreach ((array) glob($cachePath . DIRECTORY_SEPARATOR . '*') as $cacheFile) {
                if (is_file($cacheFile) && !in_array(basename($cacheFile), ['index.html', '.htaccess'], true)) {
                    @unlink($cacheFile);
                }
            }
        }

        if ($oldFilename && $oldFilename !== $filename) {
            $oldPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . basename($oldFilename);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $result['uploaded'][] = $label;
    }

    return $result;
}
/**
 * Check for company logo upload
 * @return boolean
 */
function handle_company_signature_upload()
{
    if (isset($_FILES['signature_image']) && _perfex_upload_error($_FILES['signature_image']['error'])) {
        set_alert('warning', _perfex_upload_error($_FILES['signature_image']['error']));

        return false;
    }

    $hookData = hooks()->apply_filters('before_handle_company_signature_upload', [
        'index_name' => 'signature_image',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    if (isset($_FILES['signature_image']['name']) && $_FILES['signature_image']['name'] != '') {
        hooks()->do_action('before_upload_signature_image_attachment');
        $path = get_upload_path_by_type('company');
        // Get the temp file path
        $tmpFilePath = $_FILES['signature_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $path_parts = pathinfo($_FILES['signature_image']['name']);
            $extension  = $path_parts['extension'];
            $extension  = strtolower($extension);

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];
            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', 'Image extension not allowed.');

                return false;
            }
            // Setup our new file path
            $filename    = 'signature' . '.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                update_option('signature_image', $filename);

                return true;
            }
        }
    }

    return false;
}
/**
 * Handle company favicon upload
 * @return boolean
 */
function handle_favicon_upload()
{
    $hookData = hooks()->apply_filters('before_handle_favicon_upload', [
        'index_name' => 'favicon',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    if (isset($_FILES['favicon']['name']) && $_FILES['favicon']['name'] != '') {
        hooks()->do_action('before_upload_favicon_attachment');
        $path = get_upload_path_by_type('company');
        // Get the temp file path
        $tmpFilePath = $_FILES['favicon']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            if ((int) $_FILES['favicon']['size'] > 524288) {
                set_alert('warning', 'Favicon file is too large. Maximum allowed size is 512 KB.');
                return false;
            }
            $imageInfo = @getimagesize($tmpFilePath);
            if ($imageInfo && ((int) $imageInfo[0] > 512 || (int) $imageInfo[1] > 512)) {
                set_alert('warning', 'Favicon dimensions are too large. Maximum allowed dimensions are 512 × 512 pixels.');
                return false;
            }
            // Getting file extension
            $path_parts = pathinfo($_FILES['favicon']['name']);
            $extension  = $path_parts['extension'];
            $extension  = strtolower($extension);
            // Setup our new file path
            $filename    = 'favicon' . '.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                update_option('favicon', $filename);

                return true;
            }
        }
    }

    return false;
}

/**
 * Maybe upload staff profile image
 * @param  string $staff_id staff_id or current logged in staff id will be used if not passed
 * @return boolean
 */
function handle_staff_profile_image_upload($staff_id = '')
{
    if (!is_numeric($staff_id)) {
        $staff_id = get_staff_user_id();
    }

    $hookData = hooks()->apply_filters('before_handle_staff_profile_image_upload', [
        'staff_id' => $staff_id,
        'index_name' => 'profile_image',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != '') {
        hooks()->do_action('before_upload_staff_profile_image');
        $path = get_upload_path_by_type('staff') . $staff_id . '/';
        // Get the temp file path
        $tmpFilePath = $_FILES['profile_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension          = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            $allowed_extensions = hooks()->apply_filters('staff_profile_image_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', _l('file_php_extension_blocked'));

                return false;
            }
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['profile_image']['name']);
            $newFilePath = $path . '/' . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('staff_profile_image_thumb_width', 320);
                $config['height']         = hooks()->apply_filters('staff_profile_image_thumb_height', 320);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('staff_profile_image_small_width', 96);
                $config['height']         = hooks()->apply_filters('staff_profile_image_small_height', 96);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->db->where('staffid', $staff_id);
                $CI->db->update(db_prefix() . 'staff', [
                    'profile_image' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }
        }
    }

    return false;
}

/**
 * Maybe upload contact profile image
 * @param  string $contact_id contact_id or current logged in contact id will be used if not passed
 * @return boolean
 */
function handle_contact_profile_image_upload($contact_id = '')
{
    $hookData = hooks()->apply_filters('before_handle_contact_profile_image_upload', [
        'contact_id' => $contact_id,
        'index_name' => 'profile_image',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $hookData['handled_externally_successfully'];
    }

    if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != '') {
        hooks()->do_action('before_upload_contact_profile_image');
        if ($contact_id == '') {
            $contact_id = get_contact_user_id();
        }
        $path = get_upload_path_by_type('contact_profile_images') . $contact_id . '/';
        // Get the temp file path
        $tmpFilePath = $_FILES['profile_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            $allowed_extensions = hooks()->apply_filters('contact_profile_image_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', _l('file_php_extension_blocked'));

                return false;
            }
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['profile_image']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_profile_image_thumb_width', 320);
                $config['height']         = hooks()->apply_filters('contact_profile_image_thumb_height', 320);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_profile_image_small_width', 32);
                $config['height']         = hooks()->apply_filters('contact_profile_image_small_height', 32);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();

                $CI->db->where('id', $contact_id);
                $CI->db->update(db_prefix() . 'contacts', [
                    'profile_image' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }
        }
    }

    return false;
}
/**
 * Handle upload for project discussions comment
 * Function for jquery-comment plugin
 * @param  mixed $discussion_id discussion id
 * @param  mixed $post_data     additional post data from the comment
 * @param  array $insert_data   insert data to be parsed if needed
 * @return array
 */
function handle_project_discussion_comment_attachments($discussion_id, $post_data, $insert_data)
{
    if (isset($_FILES['file']['name']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo json_encode(['message' => _perfex_upload_error($_FILES['file']['error'])]);
        die;
    }

    $hookData = hooks()->apply_filters('before_handle_project_discussion_comment_attachment', [
        'discussion_id' => $discussion_id,
        'post_data' => $post_data,
        'insert_data' => $insert_data,
        'index_name' => 'file',
        'handled_externally' => false, // e.g. module upload to s3
        'handled_externally_successfully' => false,
        'files' => $_FILES
    ]);

    if ($hookData['handled_externally']) {
        return $insert_data;
    }

    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_project_discussion_comment_attachment');
        $path = PROJECT_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id . '/';

        // Check for all cases if this extension is allowed
        if (!_upload_extension_allowed($_FILES['file']['name'])) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(['message' => _l('file_php_extension_blocked')]);
            die;
        }

        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $insert_data['file_name'] = $filename;

                if (isset($_FILES['file']['type'])) {
                    $insert_data['file_mime_type'] = $_FILES['file']['type'];
                } else {
                    $insert_data['file_mime_type'] = get_mime_by_extension($filename);
                }
            }
        }
    }

    return $insert_data;
}

/**
 * Create thumbnail from image
 * @param  string  $path     imat path
 * @param  string  $filename filename to store
 * @param  integer $width    width of thumb
 * @param  integer $height   height of thumb
 * @return null
 */
function create_img_thumb($path, $filename, $width = 300, $height = 300)
{
    $CI = &get_instance();

    $source_path  = rtrim($path, '/') . '/' . $filename;
    $target_path  = $path;
    $config_manip = [
        'image_library'  => 'gd2',
        'source_image'   => $source_path,
        'new_image'      => $target_path,
        'maintain_ratio' => true,
        'create_thumb'   => true,
        'thumb_marker'   => '_thumb',
        'width'          => $width,
        'height'         => $height,
    ];

    $CI->image_lib->initialize($config_manip);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
}

/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _upload_extension_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', get_option('allowed_files'));
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}

/**
 * Performs fixes when $_FILES is array and the index is messed up
 * Eq user click on + then remove the file and then added new file
 * In this case the indexes will be 0,2 - 1 is missing because it's removed but they should be 0,1
 * @param  string $index_name $_FILES index name
 * @return null
 */
function _file_attachments_index_fix($index_name)
{
    if (isset($_FILES[$index_name]['name']) && is_array($_FILES[$index_name]['name'])) {
        $_FILES[$index_name]['name'] = array_values($_FILES[$index_name]['name']);
    }

    if (isset($_FILES[$index_name]['type']) && is_array($_FILES[$index_name]['type'])) {
        $_FILES[$index_name]['type'] = array_values($_FILES[$index_name]['type']);
    }

    if (isset($_FILES[$index_name]['tmp_name']) && is_array($_FILES[$index_name]['tmp_name'])) {
        $_FILES[$index_name]['tmp_name'] = array_values($_FILES[$index_name]['tmp_name']);
    }

    if (isset($_FILES[$index_name]['error']) && is_array($_FILES[$index_name]['error'])) {
        $_FILES[$index_name]['error'] = array_values($_FILES[$index_name]['error']);
    }

    if (isset($_FILES[$index_name]['size']) && is_array($_FILES[$index_name]['size'])) {
        $_FILES[$index_name]['size'] = array_values($_FILES[$index_name]['size']);
    }
}

/**
 * Check if path exists if not exists will create one
 * This is used when uploading files
 * @param  string $path path to check
 * @return null
 */
function _maybe_create_upload_path($path)
{
    if (!is_dir($path)) {
        // Some attachment types have a nested base directory that may not exist
        // yet (for example uploads/projects/{project_id}).
        if (!@mkdir($path, 0755, true) && !is_dir($path)) {
            log_message('error', 'Unable to create upload directory: ' . $path);

            return;
        }
    }

    $indexFile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . 'index.html';
    if (!file_exists($indexFile)) {
        @touch($indexFile);
    }
}

/**
 * Function that return full path for upload based on passed type
 * @param  string $type
 * @return string
 */
function get_upload_path_by_type($type)
{
    $path = '';
    switch ($type) {
        case 'lead':
            $path = LEAD_ATTACHMENTS_FOLDER;

        break;
        case 'expense':
            $path = EXPENSE_ATTACHMENTS_FOLDER;

        break;
        case 'project':
            $path = PROJECT_ATTACHMENTS_FOLDER;

        break;
        case 'proposal':
            $path = PROPOSAL_ATTACHMENTS_FOLDER;

        break;
        case 'estimate':
            $path = ESTIMATE_ATTACHMENTS_FOLDER;

        break;
        case 'invoice':
            $path = INVOICE_ATTACHMENTS_FOLDER;

        break;
        case 'credit_note':
            $path = CREDIT_NOTES_ATTACHMENTS_FOLDER;

        break;
        case 'task':
            $path = TASKS_ATTACHMENTS_FOLDER;

        break;
        case 'contract':
            $path = CONTRACTS_UPLOADS_FOLDER;

        break;
        case 'customer':
            $path = CLIENT_ATTACHMENTS_FOLDER;

        break;
        case 'staff':
        $path = STAFF_PROFILE_IMAGES_FOLDER;

        break;
        case 'company':
        $path = COMPANY_FILES_FOLDER;

        break;
        case 'ticket':
        $path = TICKET_ATTACHMENTS_FOLDER;

        break;
        case 'contact_profile_images':
        $path = CONTACT_PROFILE_IMAGES_FOLDER;

        break;
        case 'newsfeed':
        $path = NEWSFEED_FOLDER;

        break;
        case 'estimate_request':
        $path = NEWSFEED_FOLDER;

        break;
    }

    return hooks()->apply_filters('get_upload_path_by_type', $path, $type);
}


/**
 * Upload one or more files attached to a sales document form.
 * Uses Perfex file storage and the native tblfiles relationship model.
 */
function handle_smart_choice_sales_attachments($relType, $relId, $indexName = 'sales_attachments')
{
    $allowed = ['invoice', 'estimate', 'proposal', 'contract'];
    if (!in_array($relType, $allowed, true) || !$relId || empty($_FILES[$indexName]['name'])) {
        return true;
    }

    $CI = &get_instance();
    $CI->load->model('misc_model');
    $files = $_FILES[$indexName];
    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $types = is_array($files['type']) ? $files['type'] : [$files['type']];
    $tmp   = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $errs  = is_array($files['error']) ? $files['error'] : [$files['error']];
    $path  = get_upload_path_by_type($relType) . (int)$relId . '/';
    _maybe_create_upload_path($path);

    foreach ($names as $i => $originalName) {
        if ($originalName === '' || (int)$errs[$i] === UPLOAD_ERR_NO_FILE) { continue; }
        if ((int)$errs[$i] !== UPLOAD_ERR_OK) {
            log_activity('Sales attachment upload failed ['.$relType.':'.$relId.', error:'.(int)$errs[$i].']');
            return false;
        }
        $safeName = unique_filename($path, $originalName);
        if (!move_uploaded_file($tmp[$i], $path . $safeName)) {
            log_activity('Sales attachment move failed ['.$relType.':'.$relId.', file:'.$originalName.']');
            return false;
        }
        $CI->misc_model->add_attachment_to_database($relId, $relType, [[
            'file_name' => $safeName,
            'filetype' => $types[$i] ?: get_mime_by_extension($safeName),
        ]]);
        $CI->db->where('rel_id', (int)$relId)->where('rel_type', $relType)->where('file_name', $safeName)
            ->update(db_prefix().'files', ['visible_to_customer' => 1]);
    }
    return true;
}
