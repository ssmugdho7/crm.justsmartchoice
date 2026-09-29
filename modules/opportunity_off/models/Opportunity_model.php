<?php

use app\services\AbstractKanban;

defined('BASEPATH') or exit('No direct script access allowed');

class opportunity_model extends App_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;
    protected $_primary_filter = 'intval';


public function opportunityInfo($id)
{
    $opportunity = opportunity_join_data(
        db_prefix() . 'opportunity',
        db_prefix() . 'opportunity.*,CONCAT(firstname, " ", lastname) as full_name,' . db_prefix() . 'opportunity_source.source_name,' . db_prefix() . 'opportunity_pipelines.pipeline_name,' . db_prefix() . 'opportunity_stages.stage_name',
        array('id' => $id),
        array(
            db_prefix() . 'staff' => db_prefix() . 'staff.staffid = ' . db_prefix() . 'opportunity.default_opportunity_owner',
            db_prefix() . 'opportunity_stages' => db_prefix() . 'opportunity_stages.stage_id = ' . db_prefix() . 'opportunity.stage_id',
            db_prefix() . 'opportunity_pipelines' => db_prefix() . 'opportunity.pipeline_id = ' . db_prefix() . 'opportunity_pipelines.pipeline_id',
            db_prefix() . 'opportunity_source' => db_prefix() . 'opportunity.source_id = ' . db_prefix() . 'opportunity_source.source_id'
        )
    );

    if ($opportunity) {
        $opportunity->comments = $this->get_opportunity_comments($id) ?? null;
        $opportunity->assignees = $this->get_opportunity_assignees($opportunity) ?? null;
        $opportunity->customers = $this->get_opportunity_customers($opportunity ?? null);
        $opportunity->attachments = $this->get_opportunity_attachments($id) ?? null;
    } else {
        // Handle the case where no opportunity is found for the given ID
        // You can return an empty object, null, or throw an exception based on your requirements
        return null; // or throw new Exception("Opportunity not found for ID: $id");
    }

    return $opportunity;
}


    public function get_opportunity_customers($opportunity)
    {

        // if $opportunity is numeric, then it's an id, otherwise it's an object
        if (is_numeric($opportunity)) {
            $opportunity = $this->get($opportunity);
        }

        if (!empty($opportunity->rel_id) && !empty($opportunity->rel_type)) {
            $task_rel_data = get_relation_data($opportunity->rel_type, $opportunity->rel_id);
            $task_rel_value = get_relation_values($task_rel_data, $opportunity->rel_type);
            return $task_rel_value;
        }
    }


    /**
     * Get all task attachments
     * @param mixed $opportunity_id opportunity_id
     * @return array
     */

    public function get_opportunity_attachments($opportunity_id, $where = [])
    {
        $this->db->select(implode(', ', prefixed_table_fields_array(db_prefix() . 'files')) . ', ' . db_prefix() . 'opportunity_comments.id as comment_file_id');
        $this->db->where(db_prefix() . 'files.rel_id', $opportunity_id);
        $this->db->where(db_prefix() . 'files.rel_type', 'opportunity');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        $this->db->join(db_prefix() . 'opportunity_comments', db_prefix() . 'opportunity_comments.file_id = ' . db_prefix() . 'files.id', 'left');
        $this->db->join(db_prefix() . 'opportunity', db_prefix() . 'opportunity.id = ' . db_prefix() . 'files.rel_id');
        $this->db->order_by(db_prefix() . 'files.dateadded', 'desc');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }


    public function get_opportunity_assignees($opportunity)
    {
        if (empty($opportunity->user_id)) {
            return [];
        }
        $user_id = json_decode($opportunity->user_id);
        $this->db->select('staffid,firstname,lastname,CONCAT(firstname, " ", lastname) as full_name');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where_in('staffid', $user_id);
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_opportunity_comments($id)
    {
        $task_comments_order = hooks()->apply_filters('task_comments_order', 'DESC');
        $this->db->select('id,dateadded,content,' . db_prefix() . 'staff.firstname,' . db_prefix() . 'staff.lastname,' . db_prefix() . 'opportunity_comments.staffid,' . db_prefix() . 'opportunity_comments.contact_id as contact_id,file_id,CONCAT(firstname, " ", lastname) as staff_full_name');
        $this->db->from(db_prefix() . 'opportunity_comments');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'opportunity_comments.staffid', 'left');
        $this->db->where('opportunity_id', $id);
        $this->db->order_by('dateadded', $task_comments_order);

        $comments = $this->db->get()->result_array();

        $ids = [];
        foreach ($comments as $key => $comment) {
            array_push($ids, $comment['id']);
            $comments[$key]['attachments'] = [];
        }

        if (count($ids) > 0) {
            $allAttachments = $this->get_opportunity_attachments($id, 'opportunity_comment_id IN (' . implode(',', $ids) . ')');
            foreach ($comments as $key => $comment) {
                foreach ($allAttachments as $attachment) {
                    if ($comment['id'] == $attachment['opportunity_comment_id']) {
                        $comments[$key]['attachments'][] = $attachment;
                    }
                }
            }
        }

        return $comments;
    }


    public function add_attachment_to_database($rel_id, $rel_type, $attachment, $external = false)
    {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['rel_id'] = $rel_id;
        if (!isset($attachment[0]['staffid'])) {
            $data['staffid'] = get_staff_user_id();
        } else {
            $data['staffid'] = $attachment[0]['staffid'];
        }

        if (isset($attachment[0]['opportunity_comment_id'])) {
            $data['opportunity_comment_id'] = $attachment[0]['opportunity_comment_id'];
        }

        $data['rel_type'] = $rel_type;

        if (isset($attachment[0]['contact_id'])) {
            $data['contact_id'] = $attachment[0]['contact_id'];
            $data['visible_to_customer'] = 1;
            if (isset($data['staffid'])) {
                unset($data['staffid']);
            }
        }

        $data['attachment_key'] = app_generate_hash();

        if ($external == false) {
            $data['file_name'] = $attachment[0]['file_name'];
            $data['filetype'] = $attachment[0]['filetype'];
        } else {
            $path_parts = pathinfo($attachment[0]['name']);
            $data['file_name'] = $attachment[0]['name'];
            $data['external_link'] = $attachment[0]['link'];
            $data['filetype'] = !isset($attachment[0]['mime']) ? get_mime_by_extension('.' . $path_parts['extension']) : $attachment[0]['mime'];
            $data['external'] = $external;
            if (isset($attachment[0]['thumbnailLink'])) {
                $data['thumbnail_link'] = $attachment[0]['thumbnailLink'];
            }
        }

        $this->db->insert(db_prefix() . 'files', $data);
        $insert_id = $this->db->insert_id();

        if ($data['rel_type'] == 'customer' && isset($data['contact_id'])) {
            if (get_option('only_own_files_contacts') == 1) {
                $this->db->insert(db_prefix() . 'shared_customer_files', [
                    'file_id' => $insert_id,
                    'contact_id' => $data['contact_id'],
                ]);
            } else {
                $this->db->select('id');
                $this->db->where('userid', $data['rel_id']);
                $contacts = $this->db->get(db_prefix() . 'contacts')->result_array();
                foreach ($contacts as $contact) {
                    $this->db->insert(db_prefix() . 'shared_customer_files', [
                        'file_id' => $insert_id,
                        'contact_id' => $contact['id'],
                    ]);
                }
            }
        }

        return $insert_id;
    }

    public function edit_comment($data)
    {
        // Check if user really creator
        $this->db->where('id', $data['id']);
        $comment = $this->db->get(db_prefix() . 'opportunity_comments')->row();
        if ($comment->staffid == get_staff_user_id() || has_permission('opportunity', '', 'edit') || $comment->contact_id == get_contact_user_id()) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour = strtotime('-1 hours');
            if (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()) {
                if (total_rows(db_prefix() . 'files', ['opportunity_comment_id' => $comment->id]) > 0) {
                    $data['content'] .= '[opportunity_attachment]';
                }

                $this->db->where('id', $data['id']);
                $this->db->update(db_prefix() . 'opportunity_comments', [
                    'content' => $data['content'],
                ]);
                if ($this->db->affected_rows() > 0) {
                    return true;
                }
            } else {
                return false;
            }

            return false;
        }
    }

    /**
     * Remove task comment from database
     * @param mixed $id task id
     * @return boolean
     */
    public function remove_comment($id, $force = false)
    {
        // Check if user really creator
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'opportunity_comments')->row();

        if (!$comment) {
            return true;
        }

        if ($comment->staffid == get_staff_user_id() || has_permission('opportunity', '', 'delete') || $comment->contact_id == get_contact_user_id() || $force === true) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour = strtotime('-1 hours');
            if (
                get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour)
                || (is_admin() || $force === true)
            ) {
                $this->db->where('id', $id);
                $this->db->delete(db_prefix() . 'opportunity_comments');

                if ($this->db->affected_rows() > 0) {
                    if ($comment->file_id != 0) {
                        $this->remove_opportunity_attachment($comment->file_id);
                    }

                    $commentAttachments = $this->get_opportunity_attachments($comment->opportunity_id, 'opportunity_comment_id=' . $id);
                    foreach ($commentAttachments as $attachment) {
                        $this->remove_opportunity_attachment($attachment['id']);
                    }

                    return true;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Remove task attachment from server and database
     * @param mixed $id attachmentid
     * @return boolean
     */
    public function remove_opportunity_attachment($id)
    {
        $comment_removed = false;
        $deleted = false;
        // Get the attachment
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'files')->row();

        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath = get_upload_path_for_opportunity() . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('opportunity Attachment Deleted [opportunityID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_for_opportunity() . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_for_opportunity() . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_for_opportunity() . $attachment->rel_id);
                }
            }
        }

        if ($deleted) {
            if ($attachment->opportunity_comment_id != 0) {
                $total_comment_files = total_rows(db_prefix() . 'files', ['opportunity_comment_id' => $attachment->opportunity_comment_id]);
                if ($total_comment_files == 0) {
                    $this->db->where('id', $attachment->opportunity_comment_id);
                    $comment = $this->db->get(db_prefix() . 'opportunity_comments')->row();

                    if ($comment) {
                        // Comment is empty and uploaded only with attachments
                        // Now all attachments are deleted, we need to delete the comment too
                        if (empty($comment->content) || $comment->content === '[opportunity_attachment]') {
                            $this->db->where('id', $attachment->opportunity_comment_id);
                            $this->db->delete(db_prefix() . 'opportunity_comments');
                            $comment_removed = $comment->id;
                        } else {
                            $this->db->query("UPDATE '.db_prefix() . 'opportunity_comments
                            SET content = REPLACE(content, '[opportunity_attachment]', '')
                            WHERE id = " . $attachment->opportunity_comment_id);
                        }
                    }
                }
            }

            $this->db->where('file_id', $id);
            $comment_attachment = $this->db->get(db_prefix() . 'opportunity_comments')->row();

            if ($comment_attachment) {
                $this->remove_comment($comment_attachment->id);
            }
        }

        return ['success' => $deleted, 'comment_removed' => $comment_removed];
    }

    function get_opportunity_cost($opportunity_id)
    {
        $this->db->select_sum('total_cost');
        $this->db->where('opportunity_id', $opportunity_id);
        $this->db->from(db_prefix() . 'opportunity_items');
        $query_result = $this->db->get();
        $cost = $query_result->row();
        if (!empty($cost->total_cost)) {
            $result = $cost->total_cost;
        } else {
            $result = '0';
        }
        return $result;
    }

    public function staff_can_access_opportunity($id, $staff_id = '')
    {
        $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;

        if (has_permission('opportunity', $staff_id, 'view')) {
            return true;
        }

        $CI = &get_instance();

        if (total_rows(db_prefix() . 'opportunity', 'id="' . $CI->db->escape_str($id) . '" AND (assigned=' . $CI->db->escape_str($staff_id) . ' OR is_public=1 OR addedfrom=' . $CI->db->escape_str($staff_id) . ')') > 0) {
            return true;
        }

        return false;
    }

    public function get_comment_details($module_id, $module = null, $relpy_id = null)
    {
        // get all data from tbltask_comments and '.db_prefix() . 'users and assign the data to array and order by comment_datetime
        $this->commentsJoinStart();
        if (empty($module)) {
            if ($relpy_id) {
                $this->db->where(db_prefix() . 'task_comments.id', $module_id);
            } else {
                $this->db->where(db_prefix() . 'task_comments.contact_id', $module_id);
            }
        } else {
            // $this->db->where(db_prefix() . 'task_comments.module', $module);
            // $this->db->where(db_prefix() . 'task_comments.module_field_id', $module_id);
            // $this->db->where(db_prefix() . 'task_comments.id', '0');
            // $this->db->where(db_prefix() . 'task_comments.attachments_id', '0');
            // $this->db->where(db_prefix() . 'task_comments.file_id', '0');
        }
        $result = $this->commentsJoinEnd();
        return $result;
    }

    private function commentsJoinEnd()
    {
        $this->db->order_by(db_prefix() . 'task_comments.id', 'desc');
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    private function commentsJoinStart()
    {
        $this->db->select(db_prefix() . 'task_comments.*', FALSE);
        $this->db->select(db_prefix() . 'staff.firstname,'.db_prefix() .'staff.lastname,'.db_prefix() .'staff.profile_image', FALSE);
        $this->db->from(db_prefix() . 'task_comments');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = '.db_prefix() .'task_comments.opportunity_id', 'left');
    }

    public
    function check_opportunity_update($table, $where, $id = Null)
    {
        $this->db->select('*', FALSE);
        $this->db->from($table);
        if ($id != null) {
            $this->db->where($id);
        }
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public
    function opportunity_array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field, true);
        }
        return $data;
    }

    public
    function save_opportunity($data, $id = NULL)
    {
        // Insert
        if ($id === NULL) {
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] = NULL;
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $id = $this->db->insert_id();
        } // Update
        else {
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name);
        }
        return $id;
    }

    function check_by_opportunity($where, $tbl_name)
    {

        $this->db->select('*');
        $this->db->from($tbl_name);
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->row();
        return $result;
    }

    public function staff_query($table)
    {
        $role = $this->session->userdata('user_type');
        $userid = $this->input->post('user_id', true);
        if ($role == 3 || !empty($userid)) {
            if (empty($userid)) {
                $userid = opportunity_my_id();
            }
            if (!empty($this->db->field_exists('permission', $table))) {
                $this->db->group_start();
                if ($this->db->version() >= 8) {
                    $sq = $this->db->escape('\\b' . ($userid) . '\\b');
                } else {
                    $sq = $this->db->escape('[[:<:]]' . ($userid) . '[[:>:]]');
                }
                $this->db->where($table . '.permission REGEXP', $sq, false);
                $this->db->or_where(array($table . '.permission' => 'all'));
                $this->db->or_where(array($table . '.permission' => NULL));
                $this->db->group_end(); //close bracket
            }
        }
    }

    public function check_opportunity_by($where, $tbl_name)
    {

        $this->db->select('*');
        $this->db->from($tbl_name);
        $this->db->where($where);
        $query_result = $this->db->get();
        $result = $query_result->row();
        return $result;
    }

    public function delete_opportunity($id)
    {
        $filter = $this->_primary_filter;
        $id = $filter($id);
        if (!$id) {
            return FALSE;
        }
        $this->db->where($this->_primary_key, $id);
        $this->db->limit(1);
        $this->db->delete($this->_table_name);
    }

    public function getItemsInfo($term = null, $warehouse_id = null, $limit = 10)
    {
        $for_purcahse = $this->input->get('for', true);

        $table = db_prefix() . 'items';
        $this->db->select('*');
        if (!empty($term)) {
            $this->db->where("(description LIKE '%" . $term . "%' OR long_description LIKE '%" . $term . "%' OR  concat(description, ' (', long_description, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get($table);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function get_attach_file($id, $module = null, $files_id = null)
    {

        // get all data from attachments and attachments_files and assign the data to array
        // $this->db->select(db_prefix() . 'attachments.*', FALSE);
        $this->db->select(db_prefix() . 'files.*', FALSE);
        $this->db->select(db_prefix() . 'staff.firstname,'.db_prefix() .'staff.lastname', FALSE);
        $this->db->from(db_prefix() . 'files');
        // $this->db->join(db_prefix() . 'attachments_files', db_prefix() . 'attachments_files.attachments_id = '.db_prefix() . 'attachments.attachments_id', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = '.db_prefix() .'files.rel_id', 'left');
        if (!empty($module) && empty($files_id)) {

            if ($module == 'g') {
                $this->db->where(db_prefix() . 'files.attachments_id', $id);
            } else {
                $this->db->where(db_prefix() . 'files.rel_type', $module);
                $this->db->where(db_prefix() . 'files.rel_id', $id);
            }
            $query_result = $this->db->get();
            $result = $query_result->result();
            // assign the data to array using attachments_id as key
            if ($module != 'g') {
                $data = array();
                foreach ($result as $row) {
                    $data[$row->attachments_id][] = $row;
                }
            } else {
                $data = $result;
            }
        } else {
            $this->db->where(db_prefix() . 'files.rel_id', $id);
            // if (!empty($files_id)) {
            //     $this->db->where(db_prefix() . 'attachments_files.uploaded_files_id', $files_id);
            // }
            $query_result = $this->db->get();
            if (!empty($module) && $module == 'r') {
                $data[] = $query_result->row();
            } else {
                $data = $query_result->result();
            }
        }

        return $data;
    }

    public function count_rows($table, $where = null)
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }


    public function delete_opportunity_attachment($id)
    {
        $attachment = $this->get_opportunity_attachments('', $id);
        $deleted = false;

        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_for_opportunity() . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('opportunity Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_for_opportunity() . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_for_opportunity() . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_for_opportunity() . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function get($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'opportunity.id', $id);
            $opportunity = $this->db->get(db_prefix() . 'opportunity')->row();
            if ($opportunity) {
                $opportunity->attachments = $this->get_opportunity_attachments($id);
            }

            return $opportunity;
        }

        return $this->db->get(db_prefix() . 'opportunity')->result_array();
    }



    public function add_attachment_opportunity_database($opportunity_id, $attachment, $external = false, $form_activity = false)
    {

        $this->misc_model->add_attachment_to_database($opportunity_id, 'opportunity', $attachment, $external);

        if ($form_activity == false) {
            $this->log_opportunity_activity($opportunity_id, 'not_opportunity_activity_added_attachment');
        } else {
            $this->log_opportunity_activity($opportunity_id, 'not_opportunity_activity_log_attachment', true, serialize([
                $form_activity,
            ]));
        }

        // No notification when attachment is imported from web to opportunity form
        if ($form_activity == false) {
            $opportunity = $this->get($opportunity_id);
            $not_user_ids = [];
            foreach (json_decode($opportunity->user_id) as $userId) {

                if ($userId != get_staff_user_id()) {
                    array_push($not_user_ids, $userId);
                }
                if ($userId != get_staff_user_id() && $userId != 0) {
                    array_push($not_user_ids, $userId);
                }
            }
            $notifiedUsers = [];
            foreach ($not_user_ids as $uid) {
                $notified = add_notification([
                    'description' => 'not_opportunity_added_attachment',
                    'touserid' => $uid,
                    'link' => '#opportunityid=' . $opportunity_id,
                    'additional_data' => serialize([
                        $opportunity->title,
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $uid);
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }
    }

    /**
     * Add new task comment
     * @param array $data comment $_POST data
     * @return boolean
     */
    public function add_opportunity_comment($data)
    {
        if (is_client_logged_in()) {
            $data['staffid'] = 0;
            $data['contact_id'] = get_contact_user_id();
        } else {
            $data['staffid'] = get_staff_user_id();
            $data['contact_id'] = 0;
        }

        $this->db->insert(db_prefix() . 'opportunity_comments', [
            'opportunity_id' => $data['opportunity_id'],
            'content' => is_client_logged_in() ? _strip_tags($data['content']) : $data['content'],
            'staffid' => $data['staffid'],
            'contact_id' => $data['contact_id'],
            'dateadded' => date('Y-m-d H:i:s'),
        ]);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {


            return $insert_id;
        }

        return false;
    }

    private function _send_opportunity_mentioned_users_notification($description, $opportunity_id, $staff, $email_template, $notification_data, $comment_id)
    {
        $staff = array_unique($staff, SORT_NUMERIC);

        $this->load->model('staff_model');
        $notifiedUsers = [];

        foreach ($staff as $staffId) {
            if (!is_client_logged_in()) {
                if ($staffId == get_staff_user_id()) {
                    continue;
                }
            }

            $member = $this->staff_model->get($staffId);

            $link = '#opportunity_id=' . $opportunity_id;

            if ($comment_id) {
                $link .= '#comment_' . $comment_id;
            }

            $notified = add_notification([
                'description' => $description,
                'touserid' => $member->staffid,
                'link' => $link,
                'additional_data' => $notification_data,
            ]);

            if ($notified) {
                array_push($notifiedUsers, $member->staffid);
            }

            if ($email_template != '') {
                send_mail_template($email_template, $member->email, $member->staffid, $opportunity_id);
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function log_opportunity_activity($id, $description, $integration = false, $additional_data = '')
    {
        $log = [
            'date' => date('Y-m-d H:i:s'),
            'description' => $description,
            'opportunity_id' => $id,
            'staffid' => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name' => get_staff_full_name(get_staff_user_id()),
        ];
        if ($integration == true) {
            $log['staffid'] = 0;
            $log['full_name'] = '[CRON]';
        }

        $this->db->insert(db_prefix() . 'opportunity_activity_log', $log);

        return $this->db->insert_id();
    }

    public function get_lead_activity_log($id)
    {
        $sorting = hooks()->apply_filters('opportunity_activity_log_default_sort', 'DESC');

        $this->db->where('opportunity_id', $id);
        $this->db->order_by('date', $sorting);

        return $this->db->get(db_prefix() . 'opportunity_activity_log')->result_array();
    }

    public function array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field, true);
        }
        return $data;
    }

    public function send_email($params)
    {
        $template = mail_template('opportunity_send_email', 'opportunity', array_to_object($params));
        $template->send();
    }

    public function update_opportunity_satges($data)
    {
        $this->db->select('stage_id');
        $this->db->where('id', $data['leadid']);
        $_old = $this->db->get(db_prefix() . 'opportunity')->row();
        $old_status = '';

        if ($_old) {
            $old_status = get_opportunity_row(db_prefix() . 'opportunity_stages', ['stage_id' => $_old->stage_id]);
            if ($old_status) {
                $old_status = $old_status->stage_name;
            }
        }

        $affectedRows = 0;
        $current_status = get_opportunity_row(db_prefix() . 'opportunity_stages', ['stage_id' => $data['status']])->stage_name;

        $this->db->where('id', $data['leadid']);
        $this->db->update(db_prefix() . 'opportunity', [
            'stage_id' => $data['status'],
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message = 'not_opportunity_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);
            }
            $this->db->where('id', $data['leadid']);
            $this->db->update(db_prefix() . 'leads', [
                'last_status_change' => date('Y-m-d H:i:s'),
            ]);
        }

        if (isset($data['order'])) {
            AbstractKanban::updateOrder($data['order'], 'opportunityorder', '_opportunity', $data['status']);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }

            $this->log_opportunity_activity($data['leadid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }

    public function update_stage_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('stage_id', $status[0]);
            $this->db->update(db_prefix() . 'opportunity_stages', [
                'stage_order' => $status[1],
            ]);
        }
    }

}
