<?php defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_sales_links extends AdminController
{
    private $allowedTypes = ['proposal','estimate','invoice','credit_note','payment'];

    public function get($type, $id)
    {
        if (!is_staff_logged_in() || !in_array($type, $this->allowedTypes, true)) {
            show_404();
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'links'=>sc_sales_links_get($type,(int)$id)]));
    }

    public function save()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }
        $type = (string)$this->input->post('rel_type');
        $id = (int)$this->input->post('rel_id');
        if (!in_array($type, $this->allowedTypes, true) || $id < 1) {
            return $this->json(false, _l('sc_links_invalid_document'));
        }
        $urls = (array)$this->input->post('link_url');
        $titles = (array)$this->input->post('link_title');
        $rows=[];
        for($i=0;$i<4;$i++) {
            $url=trim((string)($urls[$i]??''));
            $title=trim((string)($titles[$i]??''));
            if($url==='') continue;
            if(!preg_match('#^https?://#i',$url) || filter_var($url,FILTER_VALIDATE_URL)===false) {
                return $this->json(false, _l('sc_links_invalid_url', $i+1));
            }
            $rows[]=['rel_type'=>$type,'rel_id'=>$id,'title'=>mb_substr($title,0,191),'url'=>$url,'sort_order'=>$i+1];
        }
        $table=db_prefix().'sc_sales_links';
        $this->db->trans_start();
        $this->db->where(['rel_type'=>$type,'rel_id'=>$id])->delete($table);
        foreach($rows as $row){$this->db->insert($table,$row);}
        $this->db->trans_complete();
        if(!$this->db->trans_status()) return $this->json(false,_l('sc_links_save_failed'));
        return $this->json(true,_l('sc_links_saved'));
    }

    private function json($success,$message)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>$success,'message'=>$message]));
    }
}
