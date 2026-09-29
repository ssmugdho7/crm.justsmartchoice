<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Dmn_model extends App_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	public function add($data)
	{
		$file_name = rand(100,100000);
		$file_name = $file_name.'.dmn';
		$check =  __dir__ ;
		$str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
		if(isset($_POST['dmn_content'])){
			file_put_contents($str.'/dmndiagram/diagram'.$file_name, $_POST['dmn_content']);
			$data['dmn_content'] = $file_name;
		}else{
			$data['dmn_content'] ='';
		}
		$data['hash'] = app_generate_hash();
		$data['staffid']      = $data['staffid'] == '' ? 0 : $data['staffid'];
		$data['dateadded'] = date('Y-m-d H:i:s');
		$this->db->insert(db_prefix() . 'dmn', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id) {
			log_activity('New dmn Added [ID:' . $insert_id . ']');
			return $insert_id;
		}
		return false;
	}
	public function update($data, $id)
	{   
		$this->db->where('id', $id);
		$currentdata = $this->db->get(db_prefix() . 'dmn')->row();
		$old_file_name = $currentdata->dmn_content;
		$file_name = rand(111111111,999999999);
		$file_name = $file_name.'.dmn';
		$check =  __dir__ ;
		$str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
		if(is_file($str.'/dmndiagram/diagram'.$old_file_name)){
			unlink($str.'/dmndiagram/diagram'.$old_file_name);
		}
		$str = $str.'/dmndiagram/diagram'.$file_name;
		file_put_contents($str, $_POST['dmnxml']);
		$data['dmn_content'] = $file_name;
		$data['staffid']      = $data['staffid'] == '' ? 0 : $data['staffid'];
		$data['dateaupdated'] = date('Y-m-d H:i:s');
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'dmn', $data);
		if ($this->db->affected_rows() > 0) {
			log_activity('dmn Updated [ID:' . $id . ']');
			return true;
		}
		return false;
	}
	public function get($id = '')
	{
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'dmn')->row();
		}
		return $this->db->get(db_prefix() . 'dmn')->result_array();
	}
	public function get_projects()
	{
		return $this->db->get(db_prefix() . 'projects')->result_array();
	}
	public function get_canvas_vote_count($post)
	{
		$return_data['like'] = 0;
		$return_data['dislike'] = 0;
		$result = $this->db->query("SELECT (select count(thumb) from ".db_prefix()."dmn_votes WHERE thumb = 'like' AND dmn_id = ".$post['dmn_id'].") as liked,(select count(thumb) from ".db_prefix()."dmn_votes WHERE thumb = 'dislike' AND dmn_id = ".$post['dmn_id'].") as disliked  FROM ".db_prefix()."dmn_votes WHERE dmn_id = ".$post['dmn_id']." GROUP BY dmn_id")->row();
		if($result){
			$return_data['like'] = $result->liked;
			$return_data['dislike'] = $result->disliked;
		}
		return json_encode($return_data);
	}
	public function get_canvas_vote($post)
	{
		$this->db->where(['user_id'=>$post['user_id'], 'dmn_id'=>$post['dmn_id'], 'user_type'=>$post['user_type']]);
		$result_row = $this->db->get(db_prefix().'dmn_votes')->row();
		if($result_row){
			return $result_row;
		}
	}
	public function add_canvas_vote($post)
	{
		$this->db->where(['user_id'=>$post['user_id'], 'dmn_id'=>$post['dmn_id'],'user_type'=>$post['user_type']]);
		$result_row = $this->db->get(db_prefix().'dmn_votes')->row();
		if($result_row){
			$newPost = $post;
			unset($newPost['dmn_id']);
			unset($newPost['user_id']);
			unset($newPost['user_type']);
			$this->db->where(['user_id'=>$post['user_id'], 'dmn_id'=>$post['dmn_id'],'user_type'=>$post['user_type']]);
			$this->db->update(db_prefix() . 'dmn_votes',$newPost);
			if($this->db->affected_rows()>0){
				return true;
			}
		}else{
			$this->db->insert(db_prefix() . 'dmn_votes', $post);
			return true;
		}
	}
	public function delete($id)
	{
		$this->db->where('id', $id);
		$currentdata = $this->db->get(db_prefix() . 'dmn')->row();
		if (isset($currentdata) && !empty($currentdata)) {
			$old_file_name = $currentdata->dmn_content;
			if(is_file(FCPATH.'modules/dmn/dmndiagram/diagram'.$old_file_name)){
				unlink(FCPATH.'modules/dmn/dmndiagram/diagram'.$old_file_name); 
			}
		}
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'dmn');
		if ($this->db->affected_rows() > 0) {
			log_activity('dmn Deleted [ID:' . $id . ']');
			return true;
		}
		return false;
	}
	public function dmn_update_details($data, $id){
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'dmn', $data);		
		if ($this->db->affected_rows() > 0) {
			log_activity('dmn Updated [ID:' . $id . ']');
			return true;
		}
		return false;
	}
	public function get_dmn_by_project_id($id)
    {
        $this->db->order_by(db_prefix() . 'dmn.id', "DESC");
        $this->db->where(db_prefix() . 'dmn.project_id',$id);
        $result = $this->db->get(db_prefix() . 'dmn')->result();
        return $result;
    }
}