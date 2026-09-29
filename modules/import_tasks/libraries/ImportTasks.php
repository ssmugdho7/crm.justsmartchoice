<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class ImportTasks extends App_import
{
    private static $STATUS_FIELD_NAME = 'status';
    private static $STATUS_SLUG_PREFIX = 'task_status_';
    private static $SEARCH_STATUS_IDS = 1000;
    private static $SEARCH_PRIORITY_IDS = 50;
    private static $MEDIUM_PRIORITY_FILED_VALUE = 2;

    protected $notImportableFields = [];
    protected $requiredFields = [];
    private $importedFields;
    private $statusFieldIndex;

    public function __construct()
    {
        $this->notImportableFields = hooks()->apply_filters(
            'not_importable_clients_fields',
            [
                'id'
            ]
        );
        parent::__construct();
        $this->setImportGuidelines();
    }

    public function perform()
    {
        if (empty($this->temporaryFileFromFormLocation)) {
            set_alert('warning', _l('import_upload_failed'));
            redirect($this->failureRedirectURL());
        }
        $this->tmpFileStoragePath = $this->temporaryFileFromFormLocation;
        $this->readFileRows();
        $this->importedFields = $this->getImportableDatabaseFields();
        $this->setStatusFieldIndex();
        $totalDatabaseFields = count($this->importedFields);
        foreach ($this->getRows() as $row) {
            $insert = [];
            for ($index = 0; $index < $totalDatabaseFields; $index++) {
                if (!isset($row[$index])) {
                    continue;
                }
                $row[$index] = $this->checkNullString($row[$index]);
                if (!is_numeric($row[$index])) {
                    $row[$index] = $this->getValueForNonNumericFieldValue($row, $index);
                }
                $insert[$this->importedFields[$index]] = $row[$index];
            }
            $insert = $this->trimInsertValues($insert);
            if (count($insert) > 0) {
                $this->incrementImported();
                if (!$this->isSimulation()) {
                    $this->storeTask($insert);
                }
            }
        }
    }

    private function getValueForNonNumericFieldValue($row, $index)
    {
        if ($index == $this->statusFieldIndex) {
            $row[$index] = $this->getStatusIdByName($row[$this->statusFieldIndex]);
        } else if ($this->importedFields[$index] == 'priority') {
            $row[$index] = $this->getPriorityIdByName($row[$index]);
        } else if ($this->importedFields[$index] == 'addedfrom') {
            if ($this->importedFields[$index + 1] == 'is_added_from_contact' && $row[$index + 1] == 0) {
                $row[$index] = $this->getStaffIdByEmail($row[$index]);
            } else {
                $row[$index] = $this->getContactIdByEmail($row[$index]);
            }
        }
        return $row[$index];
    }

    private function getPriorityIdByName($value)
    {
        for ($i = 1; $i <= self::$SEARCH_PRIORITY_IDS; $i++) {
            if (strtolower($value) == strtolower(_l('ticket_priority_db_' . $i))) {
                return $i;
            }
        }
        return self::$MEDIUM_PRIORITY_FILED_VALUE;
    }

    private function getStaffIdByEmail($value)
    {
        if ($value == 'default') {
            return get_staff_user_id();
        }
        $CI = &get_instance();
        $staff = $CI->db->select('staffid')->where('email', $value)->get(db_prefix() . 'staff')->row();
        if ($staff) {
            return $staff->staffid;
        }
        return 0;
    }

    private function getContactIdByEmail($value)
    {
        if ($value == 'default') {
            return get_contact_user_id();
        }
        $CI = &get_instance();
        $contact = $CI->db->select('id')->where('email', $value)->get(db_prefix() . 'contacts')->row();
        if ($contact) {
            return $contact->id;
        }
        return 0;
    }

    private function getStatusIdByName($statusName)
    {
        for ($id = 0; $id < self::$SEARCH_STATUS_IDS; $id++) {
            $statusSlug = self::$STATUS_SLUG_PREFIX . $id;
            if (_l($statusSlug) === $statusName) {
                return $id;
            }
        }
        return get_option('default_task_status');
    }

    private function setStatusFieldIndex()
    {
        foreach ($this->importedFields as $key => $databaseField) {
            if ($databaseField === self::$STATUS_FIELD_NAME) {
                $this->statusFieldIndex = $key;
            }
        }
    }

    public function storeTask($data, $clientRequest = false)
    {
        $fromTicketId = null;
        if (isset($data['ticket_to_task'])) {
            $fromTicketId = $data['ticket_to_task'];
            unset($data['ticket_to_task']);
        }
        $data['startdate'] = to_sql_date($data['startdate']);
        $data['duedate'] = to_sql_date($data['duedate']);
        $data['dateadded'] = $data['dateadded'] ? to_sql_date($data['dateadded'], true) : date('Y-m-d H:i:s');
        $data['addedfrom'] = isset($data['addedfrom']) ? $data['addedfrom'] : get_staff_user_id();
        $data['is_added_from_contact'] = isset($data['is_added_from_contact']) ? $data['is_added_from_contact'] : 0;
        $checklistItems = [];
        if (isset($data['checklist_items'])) {
            $checklistItems = explode(',', $data['checklist_items']);
            unset($data['checklist_items']);
        }
        if ($data['status'] == 'auto') {
            if (date('Y-m-d') >= $data['startdate']) {
                $data['status'] = 4;
            } else {
                $data['status'] = 1;
            }
        }
        if (isset($data['is_public'])) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }
        if (isset($data['repeat_every']) && $data['repeat_every'] != '') {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every'] = $data['repeat_every_custom'];
                $data['recurring_type'] = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                $_temp = explode('-', $data['repeat_every']);
                $data['recurring_type'] = $_temp[1];
                $data['repeat_every'] = $_temp[0];
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring'] = 0;
            $data['repeat_every'] = null;
        }
        if (isset($data['repeat_type_custom']) && isset($data['repeat_every_custom'])) {
            unset($data['repeat_type_custom']);
            unset($data['repeat_every_custom']);
        }
        if (is_client_logged_in() || $clientRequest) {
            $data['visible_to_client'] = 1;
        } else {
            if (isset($data['visible_to_client'])) {
                $data['visible_to_client'] = 1;
            } else {
                $data['visible_to_client'] = 0;
            }
        }
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }
        if ((!isset($data['milestone']) || $data['milestone'] == '') || (isset($data['milestone']) && $data['milestone'] == '')) {
            $data['milestone'] = 0;
        } else {
            if ($data['rel_type'] != 'project') {
                $data['milestone'] = 0;
            }
        }
        if (empty($data['rel_type'])) {
            unset($data['rel_type']);
            unset($data['rel_id']);
        } else {
            if (empty($data['rel_id'])) {
                unset($data['rel_type']);
                unset($data['rel_id']);
            }
        }
        $data = hooks()->apply_filters('before_add_task', $data);
        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        $attachments = [];
        if (isset($data['attachments'])) {
            $attachments = explode(',', $data['attachments']);
            unset($data['attachments']);
        }
        if (isset($data['assignees'])) {
            $assignees = $data['assignees'];
            unset($data['assignees']);
        }
        if (isset($data['followers'])) {
            $followers = $data['followers'];
            unset($data['followers']);
        }
        $customFields = [];
        foreach ($data as $columnName => $columnValue) {
            if (strpos($columnName, 'cf_') !== false) {
                $customField = strafter($columnName, 'cf_');
                if (is_numeric($customField)) {
                    $customFieldId = $customField;
                } else {
                    $customFieldId = $this->getCustomFieldIdByName($customField, 'tasks');
                }
                $customFields[$customFieldId] = $columnValue;
                unset($data[$columnName]);
            }
        }
        $this->ci->db->insert(db_prefix() . 'tasks', $data);
        $insertId = $this->ci->db->insert_id();
        if ($insertId) {
            foreach ($checklistItems as $index => $description) {
                $this->ci->db->insert(db_prefix() . 'task_checklist_items', [
                    'description' => $description,
                    'taskid'      => $insertId,
                    'dateadded'   => date('Y-m-d H:i:s'),
                    'addedfrom'   => get_staff_user_id(),
                    'list_order'  => $index,
                ]);
            }
            handle_tags_save($tags, $insertId, 'task');
            $this->handleAttachmentsSave($insertId, $attachments);
            if (!empty($customFields)) {
                handle_custom_fields_post($insertId, ['tasks' => $customFields]);
            }
            if (isset($data['rel_type']) && $data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['rel_id'], 'not_activity_new_task_created', false, serialize([
                    '<a href="' . admin_url('tasks/view/' . $insertId) . '" onclick="init_task_modal(' . $insertId . ');return false;">' . $data['name'] . '</a>',
                ]));
            }
            if ($clientRequest == false) {
                if (isset($assignees)) {
                    foreach ($assignees as $staff_id) {
                        $this->ci->tasks_model->add_task_assignees([
                            'taskid'   => $insertId,
                            'assignee' => $staff_id,
                        ]);
                    }
                }
                if (isset($followers)) {
                    foreach ($followers as $staff_id) {
                        $this->ci->tasks_model->add_task_followers([
                            'taskid'   => $insertId,
                            'follower' => $staff_id,
                        ]);
                    }
                }
                if ($fromTicketId !== null) {
                    $ticket_attachments = $this->ci->db->query(
                        'SELECT * FROM ' . db_prefix()
                            . 'ticket_attachments WHERE ticketid=' . $this->ci->db->escape_str($fromTicketId)
                            . ' OR (ticketid=' . $this->ci->db->escape_str($fromTicketId)
                            . ' AND replyid IN (SELECT id FROM ' . db_prefix()
                            . 'ticket_replies WHERE ticketid=' . $this->ci->db->escape_str($fromTicketId) . '))'
                    )->result_array();
                    if (count($ticket_attachments) > 0) {
                        $task_path = get_upload_path_by_type('task') . $insertId . '/';
                        _maybe_create_upload_path($task_path);
                        foreach ($ticket_attachments as $ticket_attachment) {
                            $path = get_upload_path_by_type('ticket') . $fromTicketId . '/' . $ticket_attachment['file_name'];
                            if (file_exists($path)) {
                                $f = fopen($path, FOPEN_READ);
                                if ($f) {
                                    $filename = unique_filename($task_path, $ticket_attachment['file_name']);
                                    $fpt      = fopen($task_path . $filename, 'w');
                                    if ($fpt && fwrite($fpt, stream_get_contents($f))) {
                                        $this->ci->db->insert(db_prefix() . 'files', [
                                            'rel_id'         => $insertId,
                                            'rel_type'       => 'task',
                                            'file_name'      => $filename,
                                            'filetype'       => $ticket_attachment['filetype'],
                                            'staffid'        => get_staff_user_id(),
                                            'dateadded'      => date('Y-m-d H:i:s'),
                                            'attachment_key' => app_generate_hash(),
                                        ]);
                                    }
                                    if ($fpt) {
                                        fclose($fpt);
                                    }
                                    fclose($f);
                                }
                            }
                        }
                    }
                }
            }
            log_activity('New Task Added [ID:' . $insertId . ', Name: ' . $data['name'] . ']');
            hooks()->do_action('after_add_task', $insertId);
            return $insertId;
        }
        return false;
    }

    private function getCustomFieldIdByName($name)
    {
        $this->ci->db->where('name', $name);
        $this->ci->db->limit(1);
        $field = $this->ci->db->get(db_prefix() . 'customfields')->row();
        if ($field) {
            return $field->id;
        }
        return false;
    }

    private function handleAttachmentsSave($insertId, $attachments)
    {
        foreach ($attachments as $attachmentURL) {
            $attachment = [0 => [
                'name' => basename($attachmentURL),
                'link' => $attachmentURL,
                'file_name' => basename($attachmentURL),
            ]];
            $this->ci->misc_model->add_attachment_to_database($insertId, 'task', $attachment, true);
        }
    }

    private function setImportGuidelines()
    {
        $this->importGuidelines = [];
        $dataFormat = 'Y-m-d';
        $this->addImportGuidelinesInfo(_l('The "Is public" column - Whether the task should be visible to all employees or only to assignees, creator and administrators.<br>
        The "Billable" column - determines whether the task can be factored.<br>
        The "Billed" column - specifies whether an invoice for the task has been issued.<br>
        The "Invoice id" column - indicates the invoice ID, if it was issued.<br>
        The "Hourly rate" column - stores the individual hourly rate per task.<br>
        The "Milestone" column - milestone identifier.<br>
        The "Kanban order" column - location on the kanabana.<br>
        The "Milestone order" column - location on milestones.<br>
        The "Visible to client" column - determines whether the task should be visible to the client.<br>
        The "Deadline notified" column - indicates whether a notification about the end of the task deadline has been sent.<br>
        The "Attachments" column - Indicates comma separated URLs with attachments to the task.<br>
        The "Tags" column - contains tags separated by commas.<br>
        The "Checklist items" column - contains points from the checklist separated by commas.<br>
        The "Cf ..." columns - all columns beginning with "Cf" contain values ​​for Custom Fields.<br>
        After the tag "Cf" you should put the id or the name of a custom field.'));
        $this->addImportGuidelinesInfo(_l('The "Rel id" column indicates the related customer or other model selected in the Rel type field.<br>
        The "Rel type" column indicates the related model (possible values ​​"project", "invoice", "customer", "estimate", "contract", "ticket", "expense", "lead", "proposal").'));
        $this->addImportGuidelinesInfo(_l('The "Recurring type" column - this column can take one of the values ​​(day, week, month, year).<br>
        The "Repeat every" column contains an integer (the number of units from the "Recurring type" column to repeat the job).<br>
        The "Recurring" column with a value of 0 means repetition is off or 1 is on.<br>
        The "Is recurring from" column - integer means repeating from the selected unit.<br>
        The "Cycles" column - means the number of repetitions (cycles).<br>
        The "Total cycles" column means the number of cycles performed - 0 means an infinite number of cycles.<br>
        The "Custom recurring" column is marked 1 if the task is to be repeated at non-standard intervals.<br>
        The "Last recurring date" column holds the last recurring date of the job.<br>'));
        $this->addImportGuidelinesInfo(_l('The "Name" column is required and must contain the name of the task.<br>'
            . 'The "Description" column is required and contains the description of the task. Can be empty or HTML code.<br>'
            . 'The "Priority" column can be name of priority or id.<br>'
            . 'The "Dateadded" column contain date and time of task addition.<br>'
            . 'The "Startdate" column contain date of task start.<br>'
            . 'The "Duedate" column contain due date of task.<br>'
            . 'The "Datefinished" column contain date and time of finish.<br>'
            . 'In the "Addedfrom" column, put the employee\'s id or email address, and when in the "Is added from contact" '
            . 'column the value is 1, then in the "Addedfrom" column, put contact\'s (customer\'s) id or email.<br>'
            . 'The "Status" column can be name of status or id.<br>'));
        $this->addImportGuidelinesInfo(_l('If the column <b>you are trying to import is date make sure that is formatted in format')
            . " $dataFormat (" . date($dataFormat) . ').</b>');
        $this->addImportGuidelinesInfo(_l('Your CSV data should be in the format below. The first line of your CSV file should be the '
            . 'column headers as in the table example. Also make sure that your file encoding is <b>UTF-8</b> to avoid unnecessary <b>encoding problems</b>.'));
    }

    private function checkNullString($value)
    {
        if ($value === 'NULL' || $value === 'null') {
            $value = null;
        }
        return $value;
    }

    protected function name_formatSampleData()
    {
        return _l('Add nested comments.');
    }

    protected function description_formatSampleData()
    {
        return _l('Add nested comments functionality to perfex.');
    }

    protected function priority_formatSampleData()
    {
        return 2;
    }

    protected function dateadded_formatSampleData()
    {
        return '2022-06-02 10:01:57';
    }

    protected function startdate_formatSampleData()
    {
        return '2022-06-06';
    }

    protected function duedate_formatSampleData()
    {
        return '2022-06-10';
    }

    protected function datefinished_formatSampleData()
    {
        return 'NULL';
    }

    protected function addedfrom_formatSampleData()
    {
        return 'default';
    }

    protected function is_added_from_contact_formatSampleData()
    {
        return 0;
    }

    protected function status_formatSampleData()
    {
        return 'Not Started';
    }

    protected function recurring_type_formatSampleData()
    {
        return 'NULL';
    }

    protected function repeat_every_formatSampleData()
    {
        return 'NULL';
    }

    protected function recurring_formatSampleData()
    {
        return 0;
    }

    protected function is_recurring_from_formatSampleData()
    {
        return 'NULL';
    }

    protected function cycles_formatSampleData()
    {
        return 0;
    }

    protected function total_cycles_formatSampleData()
    {
        return 0;
    }

    protected function custom_recurring_formatSampleData()
    {
        return 0;
    }

    protected function last_recurring_date_formatSampleData()
    {
        return 'NULL';
    }

    protected function rel_id_formatSampleData()
    {
        return 36;
    }

    protected function rel_type_formatSampleData()
    {
        return 'customer';
    }

    protected function is_public_formatSampleData()
    {
        return 0;
    }

    protected function billable_formatSampleData()
    {
        return 0;
    }

    protected function billed_formatSampleData()
    {
        return 0;
    }

    protected function invoice_id_formatSampleData()
    {
        return 0;
    }

    protected function hourly_rate_formatSampleData()
    {
        return 0.0;
    }

    protected function milestone_formatSampleData()
    {
        return 0;
    }

    protected function kanban_order_formatSampleData()
    {
        return 0;
    }

    protected function milestone_order_formatSampleData()
    {
        return 0;
    }

    protected function visible_to_client_formatSampleData()
    {
        return 0;
    }

    protected function deadline_notified_formatSampleData()
    {
        return 1;
    }

    protected function attachments_formatSampleData()
    {
        return base_url('uploads/company/logo.png') . ',' . base_url('uploads/company/favicon.png');
    }

    protected function tags_formatSampleData()
    {
        return 'imported,addon';
    }

    protected function checklist_items_formatSampleData()
    {
        return 'Checklist item 1,Checklist item 2';
    }

    protected function cf_estymacja_formatSampleData()
    {
        return 10;
    }

    protected function failureRedirectURL()
    {
        return admin_url('import_tasks/import');
    }
}
