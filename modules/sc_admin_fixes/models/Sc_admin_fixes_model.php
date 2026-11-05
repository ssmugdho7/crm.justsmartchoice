<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_admin_fixes_model extends App_Model {
    public function project_scope_convert($id) {
        $this->load->model('projects_model'); $this->load->model('tasks_model');
        $project=$this->projects_model->get($id);
        $sections=scaf_scope_tasks(scaf_project_scope($project),$project->start_date);
        if(!$sections)throw new InvalidArgumentException('No Day 1: or Step 1: headings found in the stored project scope.');
        $key='scaf_scope_'.(int)$id;
        $lock=$this->db->query('SELECT GET_LOCK(?, 5) AS acquired',[$key])->row_array();
        if((int)($lock['acquired']??0)!==1)throw new RuntimeException('Another scope conversion is running. Please retry.');
        $created=0;$skipped=0;
        try {
            $existing=$this->db->select('name,description')->where('rel_type','project')->where('rel_id',$id)->get(db_prefix().'tasks')->result_array();
            foreach($sections as $section) {
                $found=false;foreach($existing as $task) { if(strpos($task['description']??'',$section['marker'])!==false || trim($task['name'])===$section['name']) { $found=true;break; } }
                if($found){$skipped++;continue;}
                $data=['name'=>$section['name'],'description'=>html_escape($section['body'])."\n".$section['marker'],'startdate'=>_d($section['date']),'duedate'=>_d($section['date']),'rel_id'=>(int)$id,'rel_type'=>'project','priority'=>2,'status'=>1,'visible_to_client'=>0,'billable'=>0];
                if(!$this->tasks_model->add($data))throw new RuntimeException('Task creation failed. Retry safely to create only missing tasks.');
                $existing[]=$data;$created++;
            }
        } finally { $this->db->query('SELECT RELEASE_LOCK(?)',[$key]); }
        return ['created'=>$created,'skipped'=>$skipped];
    }
}
