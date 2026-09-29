<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Engineering_projects_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function clean_data($data)
  {
    $allowed = ['project_id', 'customer_id', 'name', 'start_date', 'end_date', 'site_survey_schedule', 'site_survey_schedule_date', 'contractor_id', 'install_schedule', 'install_schedule_date', 'drawings_ids', 'final_inspection', 'final_inspection_date', 'document_ids', 'shared_file_ids', 'folder_path', 'tpo_status', 'created_by', 'extra_meta', 'datecreated'];
    return array_intersect_key((array)$data, array_flip($allowed));
  }

  /**
     * @param  integer (optional)
     * @return object
     * Get single engineering_project
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('engg_proj_id', $id);

            return $this->db->get(db_prefix() . 'eng_engineering_projects')->row();
        }

        return $this->db->get(db_prefix() . 'eng_engineering_projects')->result_array();
    }

    public function get_all_engineering_projects($exclude_notified = true)
    {
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $engineering_projects = $this->db->get(db_prefix() . 'eng_engineering_projects')->result_array();

        foreach ($engineering_projects as $key => $val) {
            $engineering_project = get_engineering_project_type($val['engineering_project_type']);

            if (!$engineering_project || $engineering_project && isset($engineering_project['dashboard']) && $engineering_project['dashboard'] === false) {
                unset($engineering_projects[$key]);
            }

            $engineering_projects[$key]['achievement']    = $this->calculate_engineering_project_achievement($val['id']);
            $engineering_projects[$key]['engineering_project_type_name'] = format_engineering_project_type($val['engineering_project_type']);
        }

        return array_values($engineering_projects);
    }

    public function get_staff_engineering_projects($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);

        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $engineering_projects = $this->db->get(db_prefix() . 'eng_engineering_projects')->result_array();

        foreach ($engineering_projects as $key => $val) {
            $engineering_projects[$key]['achievement']    = $this->calculate_engineering_project_achievement($val['id']);
            $engineering_projects[$key]['engineering_project_type_name'] = format_engineering_project_type($val['engineering_project_type']);
        }

        return $engineering_projects;
    }

    /**
     * Add new engineering_project
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data = $this->clean_data($data);
    $this->db->insert(db_prefix() . 'eng_engineering_projects', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Engineering Project Added [ID:' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update engineering_project
     * @param  mixed $data All $_POST data
     * @param  mixed $id   engineering_project id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['site_survey_schedule'] = $data['site_survey_schedule'];

        $engineering_project = $this->get($id);

        $this->db->where('engg_proj_id', $id);
        $data = $this->clean_data($data);
    $this->db->update(db_prefix() . 'eng_engineering_projects', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Engineering Project Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete engineering_project
     * @param  mixed $id engineering_project id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('engg_proj_id', $id);
        $this->db->delete(db_prefix() . 'eng_engineering_projects');
        if ($this->db->affected_rows() > 0) {
            log_activity('Engineering Project Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Calculate engineering_project achievement
     * @param  mixed $id engineering_project id
     * @return array
     */
    public function calculate_engineering_project_achievement($id)
    {
        $engineering_project       = $this->get($id);
        $start_date = $engineering_project->start_date;
        $end_date   = $engineering_project->end_date;
        $type       = $engineering_project->engineering_project_type;
        $total      = 0;
        $percent    = 0;
        if ($type == 1) {
            $sql = 'SELECT SUM(amount) as total FROM ' . db_prefix() . 'invoicepaymentrecords';

            if ($engineering_project->staff_id != 0) {
                $sql .= ' JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid';
            }

            $sql .= ' WHERE ' . db_prefix() . "invoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND (' . db_prefix() . 'invoices.addedfrom=' . $engineering_project->staff_id . ' OR sale_agent=' . $engineering_project->staff_id . ')';
            }
        } elseif ($type == 8) {
            $sql = 'SELECT SUM(total) as total FROM ' . db_prefix() . 'invoices';

            $sql .= ' WHERE ' . db_prefix() . "invoices.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND (' . db_prefix() . 'invoices.addedfrom=' . $engineering_project->staff_id . ' OR sale_agent=' . $engineering_project->staff_id . ')';
            }
        } elseif ($type == 2) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND status = 1 AND " . db_prefix() . 'leads.id IN (SELECT leadid FROM ' . db_prefix() . 'clients WHERE leadid=' . db_prefix() . 'leads.id)';
            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND CASE WHEN assigned=0 THEN addedfrom=' . $engineering_project->staff_id . ' ELSE assigned=' . $engineering_project->staff_id . ' END';
            }
        } elseif ($type == 3) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND leadid IS NULL";
            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $engineering_project->staff_id;
            }
        } elseif ($type == 4) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $engineering_project->staff_id;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = 'dateadded';
            if ($type == 7) {
                $column = 'datestart';
            }
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . 'contracts WHERE ' . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND contract_type = " . $engineering_project->contract_type . ' AND trash = 0';
            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $engineering_project->staff_id;
            }
        } elseif ($type == 6) {
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . "estimates WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND invoiceid IS NOT NULL AND invoiceid NOT IN (SELECT id FROM " . db_prefix() . 'invoices WHERE status=5)';
            if ($engineering_project->staff_id != 0) {
                $sql .= ' AND (addedfrom=' . $engineering_project->staff_id . ' OR sale_agent=' . $engineering_project->staff_id . ')';
            }
        } else {
            $sql = hooks()->apply_filters('calculate_engineering_project_achievement_sql', '', $engineering_project);

            if ($sql === '') {
                return;
            }
        }

        $total = floatval($this->db->query($sql)->row()->total);

        if ($total >= floatval($engineering_project->achievement)) {
            $percent = 100;
        } else {
            if ($total !== 0) {
                $percent = number_format(($total * 100) / $engineering_project->achievement, 2);
            }
        }
        $progress_bar_percent = $percent / 100;

        return [
            'total'                => $total,
            'percent'              => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        ];
    }

    public function mark_as_notified($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'eng_engineering_projects', [
            'notified' => 1,
        ]);
    }
}
