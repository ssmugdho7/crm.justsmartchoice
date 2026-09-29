<?php

defined('BASEPATH') or exit('No direct script access allowed');

class feedbacks_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get feedback and all questions by id
     * @param  mixed $id feedback id
     * @return object
     */
	 
	public function get_client_feedback_questions($feedbackid){

       $this->db->where('feedbackid', $feedbackid);
       
        $feedback = $this->db->get(db_prefix().'feedbacks')->result_array();
		return $feedback; 

    }		
	 
	public function get_client_feedback_id($email){

        $this->db->where('email', $email);
       
        $feedback = $this->db->get(db_prefix().'feedbacksemailsendcron')->result_array();
		return $feedback; 
    }		
	
	public function get_client_email($client_id){

        
        $this->db->where('userid', $client_id);
       
        $questions = $this->db->get(db_prefix().'contacts')->result_array();
		return $questions; 
    }	
	
    public function get($id = '')
    {
        $this->db->where('feedbackid', $id);
        $feedback = $this->db->get(db_prefix().'feedbacks')->row();
        if (!$feedback) {
            return false;
        }
        $this->db->where('rel_id', $feedback->feedbackid);
        $this->db->where('rel_type', 'feedback');
        $this->db->order_by('question_order', 'asc');
        $questions = $this->db->get(db_prefix().'form_questions')->result_array();
        $i         = 0;
        foreach ($questions as $question) {
            $this->db->where('questionid', $question['questionid']);
            $box                      = $this->db->get(db_prefix().'form_question_box')->row();
            $questions[$i]['boxid']   = $box->boxid;
            $questions[$i]['boxtype'] = $box->boxtype;
            if ($box->boxtype == 'checkbox' || $box->boxtype == 'radio') {
                $this->db->order_by('questionboxdescriptionid', 'asc');
                $this->db->where('boxid', $box->boxid);
                $boxes_description = $this->db->get(db_prefix().'form_question_box_description')->result_array();
                if (count($boxes_description) > 0) {
                    $questions[$i]['box_descriptions'] = [];
                    foreach ($boxes_description as $box_description) {
                        $questions[$i]['box_descriptions'][] = $box_description;
                    }
                }
            }
            $i++;
        }
        $feedback->questions = $questions;

        return $feedback;
    }

    /**
     * Update feedback
     * @param  array $data     feedback $_POST data
     * @param  mixed $feedbackid feedback id
     * @return boolean
     */
    public function update($data, $feedbackid)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['onlyforloggedin'])) {
            $data['onlyforloggedin'] = 1;
        } else {
            $data['onlyforloggedin'] = 0;
        }
        if (isset($data['iprestrict'])) {
            $data['iprestrict'] = 1;
        } else {
            $data['iprestrict'] = 0;
        }
        $this->db->where('feedbackid', $feedbackid);
        $this->db->update(db_prefix().'feedbacks', [
            'subject'         => $data['subject'],
            'slug'            => slug_it($data['subject']),
            'description'     => $data['description'],
            'viewdescription' => $data['viewdescription'],
            'iprestrict'      => $data['iprestrict'],
            'active'          => $data['active'],
            'onlyforloggedin' => $data['onlyforloggedin'],
            'redirect_url'    => $data['redirect_url'],
            'fromname'        => $data['fromname'],
        ]);
        if ($this->db->affected_rows() > 0) {
            log_activity('feedback Updated [ID: ' . $feedbackid . ', Subject: ' . $data['subject'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Add new feedback
     * @param array $data feedback $_POST data
     * @return mixed
     */
    public function add($data)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['iprestrict'])) {
            $data['iprestrict'] = 1;
        } else {
            $data['iprestrict'] = 0;
        }
        if (isset($data['onlyforloggedin'])) {
            $data['onlyforloggedin'] = 1;
        } else {
            $data['onlyforloggedin'] = 0;
        }
        $datecreated = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'feedbacks', [
            'subject'         => $data['subject'],
            'slug'            => slug_it($data['subject']),
            'description'     => $data['description'],
            'viewdescription' => $data['viewdescription'],
            'datecreated'     => $datecreated,
            'active'          => $data['active'],
            'onlyforloggedin' => $data['onlyforloggedin'],
            'iprestrict'      => $data['iprestrict'],
            'redirect_url'    => $data['redirect_url'],
            'hash'            => md5($datecreated),
            'fromname'        => $data['fromname'],
        ]);
        $feedbackid = $this->db->insert_id();
        if (!$feedbackid) {
            // return false;
        }
        log_activity('New feedback Added [ID: ' . $feedbackid . ', Subject: ' . $data['subject'] . ']');

        return $feedbackid;
    }

    /**
     * Delete feedback and all connections
     * @param  mixed $feedbackid feedback id
     * @return boolean
     */
    public function delete($feedbackid)
    {
        $affectedRows = 0;
        $this->db->where('feedbackid', $feedbackid);
        $this->db->delete(db_prefix().'feedbacks');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            // get all questions from the feedback
            $this->db->where('rel_id', $feedbackid);
            $this->db->where('rel_type', 'feedback');
            $questions = $this->db->get(db_prefix().'form_questions')->result_array();
            // Delete the question boxes
            foreach ($questions as $question) {
                $this->db->where('questionid', $question['questionid']);
                $this->db->delete(db_prefix().'form_question_box');
                $this->db->where('questionid', $question['questionid']);
                $this->db->delete(db_prefix().'form_question_box_description');
            }
            $this->db->where('rel_id', $feedbackid);
            $this->db->where('rel_type', 'feedback');
            $this->db->delete(db_prefix().'form_questions');

            $this->db->where('rel_id', $feedbackid);
            $this->db->where('rel_type', 'feedback');
            $this->db->delete(db_prefix().'form_results');

            $this->db->where('feedbackid', $feedbackid);
            $this->db->delete(db_prefix().'feedbackresultsets');
        }
        if ($affectedRows > 0) {
            log_activity('feedback Deleted [ID: ' . $feedbackid . ']');

            return true;
        }

        return false;
    }

    /**
     * Get feedback send log
     * @param  mixed $feedbackid feedbackid
     * @return array
     */
    public function get_feedback_send_log($feedbackid)
    {
        $this->db->where('feedbackid', $feedbackid);

        return $this->db->get(db_prefix().'feedbacksendlog')->result_array();
    }

    /**
     * Add new feedback send log
     * @param mixed $feedbackid feedbackid
     * @param integer @iscronfinished always to 0
     * @param integer $lists array of lists which feedback has been send
     */
    public function init_feedback_send_log($feedbackid, $iscronfinished = 0, $lists = [])
    {
        $this->db->insert(db_prefix().'feedbacksendlog', [
            'date'               => date('Y-m-d H:i:s'),
            'feedbackid'           => $feedbackid,
            'total'              => 0,
            'iscronfinished'     => $iscronfinished,
            'send_to_mail_lists' => serialize($lists),
        ]);
        $log_id = $this->db->insert_id();
        log_activity('feedback Email Lists Send Setup [ID: ' . $feedbackid . ', Lists: ' . implode(' ', $lists) . ']');

        return $log_id;
    }

    public function remove_feedback_send($id)
    {
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'feedbacksendlog');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('log_id', $id);
        $this->db->delete(db_prefix().'feedbacksemailsendcron');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Add feedback result by user
     * @param mixed $id     feedbackid
     * @param mixed $result $_POST results/questions answers
     */
    public function add_feedback_result($id, $result)
    {
        $this->db->insert(db_prefix().'feedbackresultsets', [
            'date'      => date('Y-m-d H:i:s'),
            'feedbackid'  => $id,
            'ip'        => $this->input->ip_address(),
            'useragent' => substr($this->input->user_agent(), 0, 149),
        ]);
        $resultsetid = $this->db->insert_id();
        if ($resultsetid) {
            if (isset($result['selectable']) && sizeof($result['selectable']) > 0) {
                foreach ($result['selectable'] as $boxid => $question_answers) {
                    foreach ($question_answers as $questionid => $answer) {
                        $count = count($answer);
                        for ($i = 0; $i < $count; $i++) {
                            $this->db->insert(db_prefix().'form_results', [
                                'boxid'            => $boxid,
                                'boxdescriptionid' => $answer[$i],
                                'rel_id'           => $id,
                                'rel_type'         => 'feedback',
                                'questionid'       => $questionid,
                                'resultsetid'      => $resultsetid,
                            ]);
                        }
                    }
                }
            }
            unset($result['selectable']);

            if (isset($result['question'])) {
                foreach ($result['question'] as $questionid => $val) {
                    $boxid = $this->get_question_box_id($questionid);
                    $this->db->insert(db_prefix().'form_results', [
                        'boxid'       => $boxid,
                        'rel_id'      => $id,
                        'rel_type'    => 'feedback',
                        'questionid'  => $questionid,
                        'answer'      => $val[0],
                        'resultsetid' => $resultsetid,
                    ]);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Remove feedback question
     * @param  mixed $questionid questionid
     * @return boolean
     */
    public function remove_question($questionid)
    {
        $affectedRows = 0;
        $this->db->where('questionid', $questionid);
        $this->db->delete(db_prefix().'form_question_box_description');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('questionid', $questionid);
        $this->db->delete(db_prefix().'form_question_box');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('questionid', $questionid);
        $this->db->delete(db_prefix().'form_questions');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            log_activity('feedback Question Deleted [' . $questionid . ']');

            return true;
        }

        return false;
    }

    /**
     * Remove feedback question box description / radio/checkbox
     * @param  mixed $questionboxdescriptionid question box description id
     * @return boolean
     */
    public function remove_box_description($questionboxdescriptionid)
    {
        $this->db->where('questionboxdescriptionid', $questionboxdescriptionid);
        $this->db->delete(db_prefix().'form_question_box_description');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Add feedback box description radio/checkbox
     * @param mixed $questionid  question id
     * @param mixed $boxid       main box id
     * @param string $description box question
     */
    public function add_box_description($questionid, $boxid, $description = '')
    {
        $this->db->insert(db_prefix().'form_question_box_description', [
            'questionid'  => $questionid,
            'boxid'       => $boxid,
            'description' => $description,
        ]);

        return $this->db->insert_id();
    }

    /**
     * Private functino for insert question
     * @param  mixed $feedbackid feedback id
     * @param  string $question question
     * @return mixed
     */
    private function insert_feedback_question($feedbackid, $question = '')
    {
        $this->db->insert(db_prefix().'form_questions', [
            'rel_id'   => $feedbackid,
            'rel_type' => 'feedback',
            'question' => $question,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New feedback Question Added [feedbackID: ' . $feedbackid . ']');
        }

        return $insert_id;
    }

    /**
     * Add new question type
     * @param  string $type       checkbox/textarea/radio/input
     * @param  mixed $questionid question id
     * @return mixed
     */
    private function insert_question_type($type, $questionid)
    {
        $this->db->insert(db_prefix().'form_question_box', [
            'boxtype'    => $type,
            'questionid' => $questionid,
        ]);

        return $this->db->insert_id();
    }

    /**
     * Add new question ti feedback / ajax
     * @param array $data $_POST question data
     */
    public function add_feedback_question($data)
    {
        $questionid = $this->insert_feedback_question($data['feedbackid']);
        if ($questionid) {
            $boxid    = $this->insert_question_type($data['type'], $questionid);
            $response = [
                'questionid' => $questionid,
                'boxid'      => $boxid,
            ];
            if ($data['type'] == 'checkbox' or $data['type'] == 'radio') {
                $questionboxdescriptionid = $this->add_box_description($questionid, $boxid);
                array_push($response, [
                    'questionboxdescriptionid' => $questionboxdescriptionid,
                ]);
            }

            return $response;
        }

        return false;
    }

    /**
     * Update question / ajax
     * @param  array $data $_POST question data
     * @return boolean
     */
    public function update_question($data)
    {
        $_required = 1;
        if ($data['question']['required'] == 'false') {
            $_required = 0;
        }
        $affectedRows = 0;
        $this->db->where('questionid', $data['questionid']);
        $this->db->update(db_prefix().'form_questions', [
            'question' => $data['question']['value'],
            'required' => $_required,
        ]);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if (isset($data['boxes_description'])) {
            foreach ($data['boxes_description'] as $box_description) {
                $this->db->where('questionboxdescriptionid', $box_description[0]);
                $this->db->update(db_prefix().'form_question_box_description', [
                    'description' => $box_description[1],
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }
        if ($affectedRows > 0) {
            log_activity('feedback Question Updated [QuestionID: ' . $data['questionid'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Reorder feedback quesions / ajax
     * @param  mixed $data feedbacks order and question id
     */
    public function update_feedback_questions_orders($data)
    {
        foreach ($data['data'] as $question) {
            $this->db->where('questionid', $question[0]);
            $this->db->update(db_prefix().'form_questions', [
                'question_order' => $question[1],
            ]);
        }
    }

    /**
     * Get quesion box id
     * @param  mixed $questionid questionid
     * @return integer
     */
    private function get_question_box_id($questionid)
    {
        $this->db->select('boxid');
        $this->db->from(db_prefix().'form_question_box');
        $this->db->where('questionid', $questionid);
        $box = $this->db->get()->row();

        return $box->boxid;
    }

    /**
     * Change feedback status / active / inactive
     * @param  mixed $id     feedbackid
     * @param  integer $status active or inactive
     */
    public function change_feedback_status($id, $status)
    {
        $this->db->where('feedbackid', $id);
        $this->db->update(db_prefix().'feedbacks', [
            'active' => $status,
        ]);
        log_activity('feedback Status Changed [feedbackID: ' . $id . ' - Active: ' . $status . ']');
    }

    // MAIL LISTS

    /**
     * Get mail list/s
     * @param  mixed $id Optional
     * @return mixed     object if id is passed else array
     */
    public function get_mail_lists($id = '')
    {
        $this->db->select();
        $this->db->from(db_prefix().'emaillists');
        if (is_numeric($id)) {
            $this->db->where('listid', $id);

            return $this->db->get()->row();
        }
        $lists = $this->db->get()->result_array();

        return $lists;
    }

    /**
     * Add new mail list
     * @param array $data mail list data
     */
    public function add_mail_list($data)
    {
        $data['creator']     = get_staff_full_name(get_staff_user_id());
        $data['datecreated'] = date('Y-m-d H:i:s');
        if (isset($data['list_custom_fields_add'])) {
            $custom_fields = $data['list_custom_fields_add'];
            unset($data['list_custom_fields_add']);
        }
        $this->db->insert(db_prefix().'emaillists', $data);
        $listid = $this->db->insert_id();
        if (isset($custom_fields)) {
            foreach ($custom_fields as $field) {
                if (!empty($field)) {
                    $this->db->insert(db_prefix().'maillistscustomfields', [
                        'listid'    => $listid,
                        'fieldname' => $field,
                        'fieldslug' => slug_it($data['name'] . '-' . $field),
                    ]);
                }
            }
        }
        log_activity('New Email List Added [ID: ' . $listid . ', ' . $data['name'] . ']');

        return $listid;
    }

    /**
     * Update mail list
     * @param  mixed $data mail list data
     * @param  mixed $id   list id
     * @return boolean
     */
    public function update_mail_list($data, $id)
    {
        if (isset($data['list_custom_fields_add'])) {
            foreach ($data['list_custom_fields_add'] as $field) {
                if (!empty($field)) {
                    $this->db->insert(db_prefix().'maillistscustomfields', [
                        'listid'    => $id,
                        'fieldname' => $field,
                        'fieldslug' => slug_it($field),
                    ]);
                }
            }
            unset($data['list_custom_fields_add']);
        }
        if (isset($data['list_custom_fields_update'])) {
            foreach ($data['list_custom_fields_update'] as $key => $update_field) {
                $this->db->where('customfieldid', $key);
                $this->db->update(db_prefix().'maillistscustomfields', [
                    'fieldname' => $update_field,
                    'fieldslug' => slug_it($data['name'] . '-' . $update_field),
                ]);
            }
            unset($data['list_custom_fields_update']);
        }
        $this->db->where('listid', $id);
        $this->db->update(db_prefix().'emaillists', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Mail List Updated [ID: ' . $id . ', ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete mail list and all connections
     * @param  mixed $id list id
     * @return boolean
     */
    public function delete_mail_list($id)
    {
        $affectedRows = 0;
        $this->db->where('listid', $id);
        $this->db->delete(db_prefix().'maillistscustomfieldvalues');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('listid', $id);
        $this->db->delete(db_prefix().'maillistscustomfields');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('listid', $id);
        $this->db->delete(db_prefix().'listemails');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('listid', $id);
        $this->db->delete(db_prefix().'emaillists');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            log_activity('Mail List Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get all emails from mail list
     * @param  mixed $id list id
     * @return array
     */
    public function get_mail_list_emails($id)
    {
        $this->db->select('email,emailid')->from(db_prefix().'listemails')->where('listid', $id);

        return $this->db->get()->result_array();
    }

    /**
     * List data used in view
     * @param  mixed $id list id
     * @return mixed object
     */
    public function get_data_for_view_list($id)
    {
        $list         = $this->get_mail_lists($id);
        $list_emails  = $this->db->select('email,dateadded,emailid')->from(db_prefix().'listemails')->where('listid', $id)->get()->result_array();
        $list->emails = $list_emails;

        return $list;
    }

    /**
     * Get list custom fields added by staff
     * @param  mixed $listid list id
     * @return array
     */
    public function get_list_custom_fields($id)
    {
        $this->db->where('listid', $id);

        return $this->db->get(db_prefix().'maillistscustomfields')->result_array();
    }

    /**
     * Get custom field values
     * @param  mixed $emailid       email id from db
     * @param  mixed $listid        lis id
     * @param  mixed $customfieldid custom field id from db
     * @return mixed
     */
    public function get_email_custom_field_value($emailid, $listid, $customfieldid)
    {
        $this->db->where('emailid', $emailid);
        $this->db->where('listid', $listid);
        $this->db->where('customfieldid', $customfieldid);
        $row = $this->db->get(db_prefix().'maillistscustomfieldvalues')->row();
        if ($row) {
            return $row->value;
        }

        return '';
    }

    /**
     * Add new email to mail list
     * @param array $data
     * @return mixed
     */
    public function add_email_to_list($data)
    {
        $exists = total_rows(db_prefix().'listemails', [
            'email'  => $data['email'],
            'listid' => $data['listid'],
        ]);
        if ($exists > 0) {
            return [
                'success'       => false,
                'duplicate'     => true,
                'error_message' => _l('email_is_duplicate_mail_list'),
            ];
        }
        $dateadded = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'listemails', [
            'listid'    => $data['listid'],
            'email'     => $data['email'],
            'dateadded' => $dateadded,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if (isset($data['customfields'])) {
                foreach ($data['customfields'] as $key => $val) {
                    $this->db->insert(db_prefix().'maillistscustomfieldvalues', [
                        'listid'        => $data['listid'],
                        'customfieldid' => $key,
                        'emailid'       => $insert_id,
                        'value'         => $val,
                    ]);
                }
            }
            log_activity('Email Added To Mail List [ID:' . $data['listid'] . ' - Email:' . $data['email'] . ']');

            return [
                'success'   => true,
                'dateadded' => $dateadded,
                'email'     => $data['email'],
                'emailid'   => $insert_id,
                'message'   => _l('email_added_to_mail_list_successfully'),
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Remove email from mail list
     * @param  mixed $emailid email id (is unique)
     * @return mixed          array
     */
    public function remove_email_from_mail_list($emailid)
    {
        $this->db->where('emailid', $emailid);
        $this->db->delete(db_prefix().'listemails');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('emailid', $emailid);
            $this->db->delete(db_prefix().'maillistscustomfieldvalues');

            return [
                'success' => true,
                'message' => _l('email_removed_from_list'),
            ];
        }

        return [
            'success' => false,
            'message' => _l('email_remove_fail'),
        ];
    }

    /**
     * Remove mail list custom field and all connections
     * @param  mixed $fieldid custom field id from db
     * @return mixed          array
     */
    public function remove_list_custom_field($fieldid)
    {
        $this->db->where('customfieldid', $fieldid);
        $this->db->delete(db_prefix().'maillistscustomfields');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('customfieldid', $fieldid);
            $this->db->delete(db_prefix().'maillistscustomfieldvalues');

            return [
                'success' => true,
                'message' => _l('custom_field_deleted_success'),
            ];
        }

        return [
            'success' => false,
            'message' => _l('custom_field_deleted_fail'),
        ];
    }
}
