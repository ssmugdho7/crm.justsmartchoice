<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Training_manual_books_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    private function handle_cover_upload($old_file = '')
    {
        if (!isset($_FILES['cover_image_file']) || empty($_FILES['cover_image_file']['name'])) return $old_file;
        $file = $_FILES['cover_image_file']; if (!empty($file['error'])) return $old_file;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) return $old_file;
        if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) return $old_file;
        if (@getimagesize($file['tmp_name']) === false) return $old_file;
        $path = FCPATH . 'uploads/training_manual/';
        if (function_exists('_maybe_create_upload_path')) _maybe_create_upload_path($path); elseif (!is_dir($path)) @mkdir($path,0755,true);
        $name = 'manual_' . (int)get_staff_user_id() . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $path.$name)) { if ($old_file && file_exists($path.$old_file)) @unlink($path.$old_file); return $name; }
        return $old_file;
    }

    public function add($data)
    {
        $visible = isset($data['customer_visible']) ? 1 : 0;
        $dataDB = [
            'name' => trim((string)($data['name'] ?? '')),
            'short_description' => (string)($data['short_description'] ?? ''),
            'customer_visible' => $visible,
            'area_type' => $visible ? 'customer' : 'crm',
            'area_label' => trim((string)($data['area_label'] ?? ($visible ? 'Customer Area' : 'CRM Center'))),
            'cover_image' => $this->handle_cover_upload(''),
        ];
        $assignType = (string)($data['assign_type'] ?? 'specific_staff');
        $dataDB['assign_type'] = $assignType;
        if ($assignType === 'roles') $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_roles'] ?? [], 'role_');
        elseif ($assignType === 'specific_staff') $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_staff'] ?? [], 'staff_');
        else $dataDB['assign_ids'] = '';
        $dataDB['author_id'] = (int)get_staff_user_id();
        $this->db->insert(db_prefix().'wiki_books',$dataDB);
        $id=$this->db->insert_id(); if($id){log_activity('New Book Added [ID:'.$id.']'); return $id;} return false;
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'wiki_books')->row();
        }

        return $this->db->get(db_prefix() . 'wiki_books')->result_array();
    }

    public function getOwnBooks()
    {
        $user = get_staff($this->session->userdata('tfa_staffid'));
        $this->db->where('author_id', $user->staffid);
        return $this->db->get(db_prefix() . 'wiki_books')->result_array();
    }

    public function update($data, $id)
    {
        $current=$this->get($id); if(!$current) return false;
        $visible=isset($data['customer_visible'])?1:0;
        $dataDB=[
            'name'=>trim((string)($data['name']??'')),
            'short_description'=>(string)($data['short_description']??''),
            'customer_visible'=>$visible,
            'area_type'=>$visible?'customer':'crm',
            'area_label'=>trim((string)($data['area_label']??($visible?'Customer Area':'CRM Center'))),
            'cover_image'=>$this->handle_cover_upload($current->cover_image??''),
            'author_id'=>(int)get_staff_user_id(),
        ];
        $assignType=(string)($data['assign_type']??'specific_staff'); $dataDB['assign_type']=$assignType;
        if($assignType==='roles') $dataDB['assign_ids']=training_manual_serialize($data['assign_ids_roles']??[],'role_');
        elseif($assignType==='specific_staff') $dataDB['assign_ids']=training_manual_serialize($data['assign_ids_staff']??[],'staff_');
        else $dataDB['assign_ids']='';
        $this->db->set('updated_at','NOW()',false)->where('id',(int)$id)->update(db_prefix().'wiki_books',$dataDB);
        log_activity('Book Updated [ID:'.(int)$id.']'); return true;
    }

    public function delete($id)
    {
        $book=$this->get($id); if($book && !empty($book->cover_image)){ $path=FCPATH . 'uploads/training_manual/' . $book->cover_image; if(file_exists($path)) @unlink($path); }
        if(!isset($this->training_manual_articles_model)){
            $this->load->model('training_manual_articles_model');
        }
        $this->training_manual_articles_model->delete_by_book($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wiki_books');
        if ($this->db->affected_rows() > 0) {
            log_activity('Book Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_all_books($query = ""){
        $tblBooks = db_prefix() . 'wiki_books';
        $tblArticles = db_prefix() . 'wiki_articles';
        
        $sqlFilterName = " ";
        if($query != ""){
            $sqlFilterName = " and ( TBLBooks.name LIKE '%" . $query . "%' OR TBLBooks.short_description LIKE '%" . $query . "%' ) ";
        }

        $sqlPermissionBook = '1 = 1';
        // haven't permssion view global
        if (!has_permission('training_manual_articles', '', 'view')) {
            $sqlPermissionBook = $this->getPermissionClause('TBLBooks');
        }

        $sql = "
            SELECT 
                TBLBooks.*,
                IFNULL(TWKCounters.total, 0) AS articles_total
            FROM " . $tblBooks . " TBLBooks
                LEFT JOIN (
                    SELECT TBLArticles.book_id AS book_id, COUNT(*) as total
                    FROM " . $tblArticles . " TBLArticles
                    GROUP BY TBLArticles.book_id
                ) TWKCounters ON TBLBooks.id = TWKCounters.book_id
            WHERE 1 = 1 " . $sqlFilterName . ' and ' . $sqlPermissionBook . " 
            ORDER BY TBLBooks.updated_at DESC
        ";

        $rs = $this->db->query($sql);
        $data = $rs->result_array();
        return array_values($data);
    }

    public function get_customer_books($query = '', $published_only = true)
    {
        $this->db->select('B.*, COUNT(A.id) AS articles_total', false);
        $this->db->from(db_prefix() . 'wiki_books B');
        $this->db->join(db_prefix() . 'wiki_articles A', 'A.book_id = B.id' . ($published_only ? ' AND A.is_publish = 1' : ''), 'left');
        $this->db->where('B.customer_visible', 1);
        if ($query !== '') {
            $this->db->group_start();
            $this->db->like('B.name', $query);
            $this->db->or_like('B.short_description', $query);
            $this->db->group_end();
        }
        $this->db->group_by('B.id');
        if ($published_only) {
            $this->db->having('articles_total >', 0);
        }
        $this->db->order_by('B.updated_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_customer_book_articles($book_id)
    {
        return $this->db->select('A.*, CONCAT(IFNULL(S.firstname, ""), " ", IFNULL(S.lastname, "")) AS creator_name', false)
            ->from(db_prefix() . 'wiki_articles A')
            ->join(db_prefix() . 'staff S', 'S.staffid = A.author_id', 'left')
            ->where('A.book_id', (int) $book_id)
            ->where('A.is_publish', 1)
            ->order_by('A.updated_at', 'DESC')
            ->get()->result_array();
    }

    public function getPermissionClause($tableName, $user = null){
        if(!isset($user)){
            $user = get_staff($this->session->userdata('tfa_staffid'));
        }

        $user_id = $user->staffid;
        $role_pattern = 'role_' . $user->role;
        $staff_pattern = 'staff_' . $user->staffid;

        $sqlFilterPermission = " (" . $tableName . ".author_id = ".$user_id." OR " . $tableName . ".assign_ids LIKE '%".$role_pattern."%' OR " . $tableName . ".assign_ids LIKE '%".$staff_pattern."%') ";
        
        return $sqlFilterPermission;
    }

}
