<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quickshare_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param integer (optional)
     * @return object
     */
    public function getFileBasedOnKey($fileKey = '')
    {
        $this->db->where('file_key', $fileKey);
        return $this->db->get(db_prefix() . 'quick_share_uploads')->row();
    }

    public function getFileBasedOnId($fileId = '')
    {
        $this->db->where('id', $fileId);
        return $this->db->get(db_prefix() . 'quick_share_uploads')->row();
    }

    /**
     * Add new file
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . 'quick_share_uploads', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * Delete file
     * @param mixed $file_id file id
     * @return boolean
     */
    public function delete($file_id)
    {
        $this->db->where('id', $file_id);
        $this->db->delete(db_prefix() . 'quick_share_uploads');

        $this->db->where('download_id', $file_id);
        $this->db->delete(db_prefix() . 'quick_share_downloads');

        $directory = FCPATH . 'modules/quick_share/uploads/' . $file_id . '/';

        if (is_dir($directory)) {
            delete_dir($directory);
        }

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function update_file_content($fileId, $data)
    {
        $this->db->where('id', $fileId);
        $this->db->update(db_prefix() . 'quick_share_uploads', $data);

        $updated = false;

        if ($this->db->affected_rows() > 0) {
            $updated = true;
        }
        return $updated;
    }

    public function add_download($data)
    {
        $this->db->insert(db_prefix() . 'quick_share_downloads', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function check_downloads_of_file($fileId)
    {
        $sql = 'SELECT COUNT(*) as total FROM '. db_prefix() .'quick_share_downloads WHERE download_id='.$fileId;
        return $this->db->query($sql)->row()->total;
    }

    /**
     * Notify staff members about goal result
     * @param mixed $id goal id
     * @param string $notify_type is success or failed
     * @param mixed $achievement total achievent (Option)
     * @return boolean
     */
    public function notify_staff_members($id, $notify_type, $achievement = '')
    {
        $goal = $this->get($id);
        if ($achievement == '') {
            $achievement = $this->calculate_goal_achievement($id);
        }
        if ($notify_type == 'success') {
            $goal_desc = 'not_goal_message_success';
        } else {
            $goal_desc = 'not_goal_message_failed';
        }

        if ($goal->staff_id == 0) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', ['active' => 1]);
        } else {
            $this->db->where('active', 1)
                ->where('staffid', $goal->staff_id);
            $staff = $this->db->get(db_prefix() . 'staff')->result_array();
        }

        $notifiedUsers = [];
        foreach ($staff as $member) {
            if (is_staff_member($member['staffid'])) {
                $notified = add_notification([
                    'fromcompany' => 1,
                    'touserid' => $member['staffid'],
                    'description' => $goal_desc,
                    'additional_data' => serialize([
                        format_goal_type($goal->goal_type),
                        $goal->achievement,
                        $achievement['total'],
                        _d($goal->start_date),
                        _d($goal->end_date),
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
        $this->mark_as_notified($goal->id);

        if (count($staff) > 0 && $this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function mark_as_notified($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goals', [
            'notified' => 1,
        ]);
    }
}
