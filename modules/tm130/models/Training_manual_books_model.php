<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Training_manual_books_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {
        $dataDB = [];

        $dataDB['name'] = isset($data['name']) ? $data['name'] : '';
        $dataDB['short_description'] = isset($data['short_description']) ? $data['short_description'] : '';
        $dataDB['customer_visible'] = isset($data['customer_visible']) ? 1 : 0;

        if(isset($data['assign_type'])){
            switch ($data['assign_type']) {
                case 'specific_staff':
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_staff'], 'staff_');
                    break;

                case 'roles':
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_roles'], 'role_');
                    break;
                
                default:
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize([], 'default_');
                    break;
            }
        } else {
            $dataDB['assign_type'] = $data['assign_type'];
            $dataDB['assign_ids'] = training_manual_serialize([], 'default_');
        }
        
        $user = get_staff($this->session->userdata('tfa_staffid'));
        $dataDB['author_id'] = $user->staffid;
        
        $this->db->insert(db_prefix() . 'wiki_books', $dataDB);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Book Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
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
        $dataDB = [];

        $dataDB['name'] = isset($data['name']) ? $data['name'] : '';
        $dataDB['short_description'] = isset($data['short_description']) ? $data['short_description'] : '';
        $dataDB['customer_visible'] = isset($data['customer_visible']) ? 1 : 0;

        if(isset($data['assign_type'])){
            switch ($data['assign_type']) {
                case 'specific_staff':
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_staff'], 'staff_');
                    break;

                case 'roles':
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize($data['assign_ids_roles'], 'role_');
                    break;
                
                default:
                    $dataDB['assign_type'] = $data['assign_type'];
                    $dataDB['assign_ids'] = training_manual_serialize([], 'default_');
                    break;
            }
        } else {
            $dataDB['assign_type'] = $data['assign_type'];
            $dataDB['assign_ids'] = training_manual_serialize([], 'default_');
        }
        
        $user = get_staff($this->session->userdata('tfa_staffid'));
        $dataDB['author_id'] = $user->staffid;

        $this->db->set('updated_at', 'NOW()', FALSE);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wiki_books', $dataDB);
        if ($this->db->affected_rows() > 0) {
            log_activity('Book Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
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
