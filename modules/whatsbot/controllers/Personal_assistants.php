<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for personal assistant functionality.
 */
class Personal_assistants extends AdminController {
    use modules\whatsbot\traits\OpenAiAssistantTraits;

    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
        $this->load->model('personal_assistant_model');
    }

    public function index() {
        if (!staff_can('view', 'wtc_pa')) {
            access_denied();
        }
        $data['title'] = _l('personal_assistant');
        $this->load->view("personal_assistant/manage", $data);
    }

    public function personal_assistant($id = '') {
        if (!staff_can('edit', 'wtc_pa') && !staff_can('create', 'wtc_pa')) {
            access_denied();
        }
        $data['title'] = _l('personal_assistant');

        if (!empty($id)) {
            $data['pa'] = $this->personal_assistant_model->get($id);
        }

        $this->load->view("personal_assistant/personal_assistant", $data);
    }

    public function get_table_data() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, '/tables/personal_assistant'));
    }

    public function save() {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $post_data = $this->input->post();
        $res = $this->personal_assistant_model->save($post_data);
        echo json_encode($res);
    }

    public function add_attachment($id) {
        if ((!staff_can('edit', 'wtc_pa') && !staff_can('create', 'wtc_pa')) || !$this->input->is_ajax_request()) {
            ajax_access_denied();
        }
        $path = get_upload_path_by_type('personal_assistant');
        _maybe_create_upload_path($path);
        $path = $path . $id;
        _maybe_create_upload_path($path);
        _maybe_create_upload_path($path . '/chunks/');
        $totalUploaded = 0;

        // Check if files are uploaded
        if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name']) || (is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {

            // Handle single and multiple file uploads by converting single file to array format
            if (!is_array($_FILES['file']['name'])) {
                $_FILES['file']['name'] = [$_FILES['file']['name']];
                $_FILES['file']['type'] = [$_FILES['file']['type']];
                $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
                $_FILES['file']['error'] = [$_FILES['file']['error']];
                $_FILES['file']['size'] = [$_FILES['file']['size']];
            }

            // Fix file attachments index
            _file_attachments_index_fix('file');

            $attachment = [];
            try {
                // Process each file in the upload
                foreach ($_FILES['file']['name'] as $index => $fileName) {

                    // Check for upload errors
                    if (_perfex_upload_error($_FILES['file']['error'][$index])) {
                        header('HTTP/1.0 400 Bad error');
                        echo _perfex_upload_error($_FILES['file']['error'][$index]);
                        die;
                    }

                    // Get temp file path
                    $tmpFilePath = $_FILES['file']['tmp_name'][$index];

                    // Continue if file path is not empty
                    if (!empty($tmpFilePath)) {

                        // Handle file upload error again (although already checked before)
                        if (_perfex_upload_error($_FILES['file']['error'][$index])) {
                            continue;
                        }

                        // Generate unique filename
                        $filename = strtolower(unique_filename($path, str_replace(' ', '_', $fileName)));
                        $newFilePath = $path . '/' . $filename;

                        // Move the file to the specified directory
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            $this->initializeOpenAI($path);
                            $chunk = $this->createFileChunk($filename);
                            if (!$chunk) {
                                continue;
                            }

                            // Prepare attachment data
                            $attachment[] = [
                                'pa_id' => $id,
                                'file_name' => $filename,
                                'filetype' => $_FILES['file']['type'][$index],
                            ];

                            // Increment total uploaded count
                            $totalUploaded++;
                        }
                    }
                }
            } catch (\Throwable $th) {
                $status = false;
                $attachment = [];
            }

            if (!empty($attachment)) {
                $status = true;
                // Add files to the database
                // TEMP: Need to fix for multiple file options
                $attach_files = $this->personal_assistant_model->get_pa_files($id);
                foreach ($attach_files as $attachment_file) {
                    $this->personal_assistant_model->delete_attachment($id, $attachment_file['id']);
                }
                $this->personal_assistant_model->add_pa_files($id, $attachment);
            }
        }
        echo json_encode([
            'status' => $status ?? false,
            'url' => admin_url('whatsbot/personal_assistants'),
        ]);
    }

    public function delete_attachment($pa_id, $attachment_id) {
        if (!empty($attachment_id)) {
            return $this->personal_assistant_model->delete_attachment($pa_id, $attachment_id);
        }
    }

    public function delete($id) {
        if (!staff_can('delete', 'wtc_pa')) {
            access_denied();
        }
        if (!empty($id)) {
            $this->load->model(['bots_model']);
            $flows = $this->bots_model->get_flow("", '"personal_assistants":"' . $id . '"');

            if (empty($flows)) {
                $res = $this->personal_assistant_model->delete($id);
                set_alert($res['type'], $res['message']);
            } else {
                set_alert('danger', _l('is_referenced', _l('personal_assistant')));
            }
        }
        redirect(admin_url('whatsbot/personal_assistants'));
    }
}
