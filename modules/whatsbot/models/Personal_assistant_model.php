<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Personal_assistant_model extends App_Model {
    public function __construct() {
        parent::__construct();
    }

    public function save($post_data) {
        $insert = $update = $id = false;
        if (!empty($post_data['id'])) {
            $update = $this->db->update(db_prefix() . 'wtc_personal_assistants', $post_data, ['id' => $post_data['id']]);
            $id = $post_data['id'];
        } else {
            $insert = $this->db->insert(db_prefix() . 'wtc_personal_assistants', $post_data);
            $id = $this->db->insert_id();
        }
        return [
            'id' => $id,
            'type' => $update || $insert ? 'success' : 'danger',
            'message' => $update ? _l('updated_successfully', _l('personal_assistant')) : ($insert ? _l('added_successfully', _l('personal_assistant')) : _l('something_went_wrong')),
            'url' => admin_url('whatsbot/personal_assistants')
        ];
    }

    public function get($id = '') {
        if (!empty($id)) {
            $data = $this->db->get_where(db_prefix() . 'wtc_personal_assistants', ['id' => $id])->row_array();
            $data['files'] = $this->get_pa_files($id);
            return $data;
        }
        return $this->db->get(db_prefix() . 'wtc_personal_assistants')->result_array();
    }

    public function delete($id) {
        $this->load->model(['bots_model']);
        $flows = $this->bots_model->get_flow("", '"personal_assistants":"'.$id.'"');
        if (!empty($flows)) {
            return [
                'type' => 'danger',
                'message' => _l('is_referenced', _l('personal_assistant')),
            ];
        }

        $delete = $this->db->delete(db_prefix() . 'wtc_personal_assistants', ['id' => $id]);
        if ($delete) {
            $files = $this->get_pa_files($id);
            foreach ($files as $attachment_file) {
                $this->personal_assistant_model->delete_attachment($id, $attachment_file['id']);
                $filepath = get_upload_path_by_type('personal_assistant') . $id;
                delete_dir($filepath);
            }
        }
        return [
            'type' => 'danger',
            'message' => $delete ? _l('deleted', _l('personal_assistant')) : _l('something_went_wrong'),
        ];
    }

    public function get_pa_files($pa_id = '', $attachment_id = '') {
        if (!empty($pa_id)) {
            return $this->db->get_where(db_prefix() . 'wtc_pa_files', ['pa_id' => $pa_id])->result_array();
        }
        if (!empty($attachment_id)) {
            return $this->db->get_where(db_prefix() . 'wtc_pa_files', ['id' => $attachment_id])->row_array();
        }
        return $this->db->get(db_prefix() . 'wtc_pa_files')->result_array();
    }

    public function add_pa_files($pa_id, $attachments) {
        if (!empty($pa_id)) {
            return $this->db->insert_batch(db_prefix() . 'wtc_pa_files', $attachments);
        }
        return false;
    }

    public function delete_attachment($pa_id, $attachment_id) {
        $file = $this->get_pa_files('', $attachment_id);
        $delete = $this->db->delete(db_prefix() . 'wtc_pa_files', ['id' => $attachment_id, 'pa_id' => $pa_id]);
        if ($delete) {
            $filepath = get_upload_path_by_type('personal_assistant') . $pa_id . '/' . $file['file_name'];
            unlink($filepath);
            $chunkpath = get_upload_path_by_type('personal_assistant') . $pa_id . '/chunks/' . pathinfo($file['file_name'], PATHINFO_FILENAME) . '.json';
            unlink($chunkpath);
        }
        return [
            'type' => 'danger',
            'message' => $delete ? _l('deleted', _l('file')) : _l('something_went_wrong')
        ];
    }
}
