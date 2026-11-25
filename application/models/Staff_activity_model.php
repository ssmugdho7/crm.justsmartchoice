<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Staff_activity_model extends App_Model
{
    public function heartbeat($active)
    {
        $table=db_prefix().'sc_staff_active_time';
        if(!$this->db->table_exists($table)) return false;
        $now=date('Y-m-d H:i:s');
        // One row per employee/day: atomic updates prevent multiple tabs counting twice.
        return $this->db->query("INSERT INTO {$table} (staff_id,tracked_date,seconds,last_seen,is_active) VALUES (?,?,0,?,?) ON DUPLICATE KEY UPDATE seconds=seconds+IF(is_active=1 AND VALUES(is_active)=1 AND TIMESTAMPDIFF(SECOND,last_seen,VALUES(last_seen)) BETWEEN 1 AND 30,TIMESTAMPDIFF(SECOND,last_seen,VALUES(last_seen)),0),last_seen=VALUES(last_seen),is_active=VALUES(is_active)",[(int)get_staff_user_id(),date('Y-m-d'),$now,$active?1:0]);
    }
    public function summary($staffId)
    {
        $result=array_fill_keys(['total','this_month','last_month','this_week','last_week'],0);
        $table=db_prefix().'sc_staff_active_time';
        if(!$this->db->table_exists($table))return $result;
        $month=date('Y-m-01');$previous=date('Y-m-01',strtotime('first day of last month'));
        $week=date('Y-m-d',strtotime('monday this week'));$lastWeek=date('Y-m-d',strtotime('monday last week'));
        $rows=$this->db->query("SELECT tracked_date,seconds FROM {$table} WHERE staff_id=?",[(int)$staffId])->result_array();
        foreach($rows as $row){$day=$row['tracked_date'];$seconds=max(0,(int)$row['seconds']);$result['total']+=$seconds;
            if($day>=$month)$result['this_month']+=$seconds;
            if($day>=$previous&&$day<$month)$result['last_month']+=$seconds;
            if($day>=$week)$result['this_week']+=$seconds;
            if($day>=$lastWeek&&$day<$week)$result['last_week']+=$seconds;
        }
        return $result;
    }
}
