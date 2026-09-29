<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single goal
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'goals')->row();
        }

        if ($exclude_notified == true) {
            $this->db->where('notified', 0);
        }

        if (staff_cant('view', 'goals') && staff_can('view_own', 'goals')) {
            $this->db->where('staff_id', get_staff_user_id());
        }
        return $this->db->get(db_prefix() . 'goals')->result_array();
    }

    public function get_all_goals($exclude_notified = true)
    {
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $goals = $this->db->get(db_prefix() . 'goals')->result_array();

        foreach ($goals as $key => $val) {
            $goal = get_goal_type($val['goal_type']);

            if (!$goal || $goal && isset($goal['dashboard']) && $goal['dashboard'] === false) {
                unset($goals[$key]);
                continue;
            }

            $goals[$key]['achievement']    = $this->calculate_goal_achievement($val['id']);
            $goals[$key]['goal_type_name'] = format_goal_type($val['goal_type']);
        }

        return array_values($goals);
    }

    public function get_staff_goals($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);

        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $goals = $this->db->get(db_prefix() . 'goals')->result_array();

        foreach ($goals as $key => $val) {
            $goals[$key]['achievement']    = $this->calculate_goal_achievement($val['id']);
            $goals[$key]['goal_type_name'] = format_goal_type($val['goal_type']);
        }

        return $goals;
    }

    /**
     * Add new goal
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date']    = to_sql_date($data['start_date']);
        $data['end_date']      = to_sql_date($data['end_date']);
        $data['priority'] = in_array($data['priority'] ?? 'medium', ['low','medium','high','critical'], true) ? $data['priority'] : 'medium';
        $data['goal_status'] = in_array($data['goal_status'] ?? 'active', ['active','paused','completed','cancelled'], true) ? $data['goal_status'] : 'active';
        $data['department_id'] = (int)($data['department_id'] ?? 0);
        $data['weight'] = (float)($data['weight'] ?? 100);
        $this->db->insert(db_prefix() . 'goals', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Goal Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update goal
     * @param  mixed $data All $_POST data
     * @param  mixed $id   goal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date']    = to_sql_date($data['start_date']);
        $data['end_date']      = to_sql_date($data['end_date']);
        $data['priority'] = in_array($data['priority'] ?? 'medium', ['low','medium','high','critical'], true) ? $data['priority'] : 'medium';
        $data['goal_status'] = in_array($data['goal_status'] ?? 'active', ['active','paused','completed','cancelled'], true) ? $data['goal_status'] : 'active';
        $data['department_id'] = (int)($data['department_id'] ?? 0);
        $data['weight'] = (float)($data['weight'] ?? 100);

        $goal = $this->get($id);

        if ($goal->notified == 1 && date('Y-m-d') < $data['end_date']) {
            // After goal finished, user changed/extended date? If yes, set this goal to be notified
            $data['notified'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goals', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete goal
     * @param  mixed $id goal id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'goals');
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Notify staff members about goal result
     * @param  mixed $id          goal id
     * @param  string $notify_type is success or failed
     * @param  mixed $achievement total achievent (Option)
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
                    'fromcompany'     => 1,
                    'touserid'        => $member['staffid'],
                    'description'     => $goal_desc,
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

    /**
     * Calculate goal achievement
     * @param  mixed $id goal id
     * @return array
     */
    public function calculate_goal_achievement($id)
    {
        $goal       = $this->get($id);
        $start_date = $goal->start_date;
        $end_date   = $goal->end_date;
        $type       = $goal->goal_type;
        $total      = 0;
        $percent    = 0;
        if ($type == 1) {
            $sql = 'SELECT SUM(amount) as total FROM ' . db_prefix() . 'invoicepaymentrecords';

            if ($goal->staff_id != 0) {
                $sql .= ' JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid';
            }

            $sql .= ' WHERE ' . db_prefix() . "invoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($goal->staff_id != 0) {
                $sql .= ' AND (sale_agent=' . $goal->staff_id . ')';
            }
        } elseif ($type == 8) {
            $sql = 'SELECT SUM(total) as total FROM ' . db_prefix() . 'invoices';

            $sql .= ' WHERE ' . db_prefix() . "invoices.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($goal->staff_id != 0) {
                $sql .= ' AND (sale_agent=' . $goal->staff_id . ')';
            }
        } elseif ($type == 2) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND status = 1 AND " . db_prefix() . 'leads.id IN (SELECT leadid FROM ' . db_prefix() . 'clients WHERE leadid=' . db_prefix() . 'leads.id)';
            if ($goal->staff_id != 0) {
                $sql .= ' AND CASE WHEN assigned=0 THEN addedfrom=' . $goal->staff_id . ' ELSE assigned=' . $goal->staff_id . ' END';
            }
        } elseif ($type == 3) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND leadid IS NULL";
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 4) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = 'dateadded';
            if ($type == 7) {
                $column = 'datestart';
            }
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . 'contracts WHERE ' . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND contract_type = " . $goal->contract_type . ' AND trash = 0';
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 6) {
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . "estimates WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND invoiceid IS NOT NULL AND invoiceid NOT IN (SELECT id FROM " . db_prefix() . 'invoices WHERE status=5)';
            if ($goal->staff_id != 0) {
                $sql .= ' AND (addedfrom=' . $goal->staff_id . ' OR sale_agent=' . $goal->staff_id . ')';
            }
        } else {
            $sql = hooks()->apply_filters('calculate_goal_achievement_sql', '', $goal);

            if ($sql === '') {
                return;
            }
        }

        $total = floatval($this->db->query($sql)->row()->total);

        if ($total >= floatval($goal->achievement)) {
            $percent = 100;
        } else {
            if ($total !== 0) {
                $percent = number_format(($total * 100) / $goal->achievement, 2);
            }
        }
        $progress_bar_percent = $percent / 100;

        return [
            'total'                => $total,
            'percent'              => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        ];
    }


    public function get_summary()
    {
        $table = db_prefix() . 'goals';
        $today = date('Y-m-d');
        $base = function () use ($table) {
            $this->db->from($table);
            if (staff_cant('view', 'goals') && staff_can('view_own', 'goals')) {
                $this->db->where('staff_id', get_staff_user_id());
            }
        };
        $base(); $total = $this->db->count_all_results();
        $base(); $this->db->where('goal_status', 'active'); $active = $this->db->count_all_results();
        $base(); $this->db->where('end_date <', $today)->where('goal_status !=', 'completed'); $overdue = $this->db->count_all_results();
        $base(); $this->db->where('goal_status', 'completed'); $completed = $this->db->count_all_results();
        return compact('total','active','overdue','completed');
    }

    public function mark_as_notified($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goals', [
            'notified' => 1,
        ]);
    }

    private function apply_visibility_scope()
    {
        if (staff_cant('view', 'goals') && staff_can('view_own', 'goals')) {
            $this->db->where(db_prefix() . 'goals.staff_id', get_staff_user_id());
        }
    }

    public function get_dashboard_metrics()
    {
        $summary = $this->get_summary();
        $summary['due_week'] = 0;
        $summary['completion_rate'] = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
        $this->db->from(db_prefix() . 'goals');
        $this->apply_visibility_scope();
        $this->db->where('end_date >=', date('Y-m-d'));
        $this->db->where('end_date <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->where('goal_status !=', 'completed');
        $summary['due_week'] = $this->db->count_all_results();
        return $summary;
    }

    public function get_goals_by_status()
    {
        $statuses = ['active','paused','completed','cancelled'];
        $result = [];
        foreach ($statuses as $status) {
            $this->db->from(db_prefix() . 'goals');
            $this->apply_visibility_scope();
            $this->db->where('goal_status', $status);
            $result[$status] = $this->db->count_all_results();
        }
        return $result;
    }

    public function get_overdue_goals($limit = 8)
    {
        $this->db->select('id,subject,end_date,achievement,staff_id');
        $this->db->from(db_prefix() . 'goals');
        $this->apply_visibility_scope();
        $this->db->where('end_date <', date('Y-m-d'));
        $this->db->where('goal_status !=', 'completed');
        $this->db->order_by('end_date', 'ASC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result_array();
    }

    public function get_goals_by_department()
    {
        $this->db->select(db_prefix().'departments.name, COUNT('.db_prefix().'goals.id) AS total');
        $this->db->from(db_prefix().'goals');
        $this->db->join(db_prefix().'departments', db_prefix().'departments.departmentid = '.db_prefix().'goals.department_id', 'left');
        $this->apply_visibility_scope();
        $this->db->group_by(db_prefix().'goals.department_id');
        $this->db->order_by('total', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_monthly_completion()
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime('-'.$i.' months'));
            $this->db->from(db_prefix().'goals');
            $this->apply_visibility_scope();
            $this->db->where('goal_status', 'completed');
            $this->db->where('DATE_FORMAT(end_date, "%Y-%m") =', $month);
            $result[] = ['month' => date('M Y', strtotime($month.'-01')), 'total' => $this->db->count_all_results()];
        }
        return $result;
    }

    public function get_staff_performance($limit = 8)
    {
        $this->db->select(db_prefix().'staff.staffid,'.db_prefix().'staff.firstname,'.db_prefix().'staff.lastname, COUNT('.db_prefix().'goals.id) AS total, SUM(CASE WHEN '.db_prefix().'goals.goal_status = "completed" THEN 1 ELSE 0 END) AS completed');
        $this->db->from(db_prefix().'goals');
        $this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'goals.staff_id', 'left');
        $this->apply_visibility_scope();
        $this->db->where(db_prefix().'goals.staff_id >', 0);
        $this->db->group_by(db_prefix().'goals.staff_id');
        $this->db->order_by('completed', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result_array();
    }

}
