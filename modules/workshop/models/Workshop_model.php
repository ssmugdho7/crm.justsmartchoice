<?php
defined('BASEPATH') or exit('No direct script access allowed');
use app\services\utilities\Arr;

/**
 * Workshop model
 */
#[\AllowDynamicProperties]
class Workshop_model extends App_Model
{
    /**
     * construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * update prefix number
     * @param  [type] $data 
     * @return [type]       
     */
    public function update_prefix_number($data)
    {
        $affected_rows=0;
        foreach ($data as $key => $value) {

            if (update_option($key, $value)){
                $affected_rows++;
            }
        }

        if($affected_rows > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * get holiday
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_holiday($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_holidays')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_holidays')->result_array();
        }
    }

    /**
     * add holiday
     * @param [type] $data 
     */
    public function add_holiday($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        $data['days_off'] = to_sql_date($data['days_off']);

        $this->db->insert(db_prefix().'wshop_holidays',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update holiday
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_holiday($data, $id)
    {
        $affected_rows=0;
        $data['days_off'] = to_sql_date($data['days_off']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_holidays', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete holiday
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_holiday($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_holidays');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change holiday status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_holiday_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_holidays', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get manufacturer
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_manufacturer($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_manufacturers')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_manufacturers')->result_array();
        }
    }

    /**
     * add manufacturer
     * @param [type] $data 
     */
    public function add_manufacturer($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_manufacturers',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            handle_manufacturer_image($insert_id);
            return $insert_id;
        }
        return false;
    }

    /**
     * update manufacturer
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_manufacturer($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_manufacturers', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        handle_manufacturer_image($id);

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete manufacturer
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_manufacturer($id)
    {
        if (is_dir(MANUFACTURER_IMAGES_FOLDER. $id)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(MANUFACTURER_IMAGES_FOLDER. $id);
                // okey only index.html so we can delete the folder also
            delete_dir(MANUFACTURER_IMAGES_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_manufacturers');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change manufacturer status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_manufacturer_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_manufacturers', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get category
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_category($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_categories')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');
            return $this->db->get(db_prefix() . 'wshop_categories')->result_array();
        }
    }

    /**
     * add category
     * @param [type] $data 
     */
    public function add_category($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_categories',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update category
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_category($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_categories', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete category
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_category($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_categories');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change category status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_category_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_categories', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get delivery method
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_delivery_method($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_delivery_methods')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_delivery_methods')->result_array();
        }
    }

    /**
     * add delivery_method
     * @param [type] $data 
     */
    public function add_delivery_method($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_delivery_methods',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update delivery_method
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_delivery_method($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_delivery_methods', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete delivery_method
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_delivery_method($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_delivery_methods');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change delivery_method status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_delivery_method_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_delivery_methods', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get interval
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_interval($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_intervals')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_intervals')->result_array();
        }
    }

    /**
     * add interval
     * @param [type] $data 
     */
    public function add_interval($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_intervals',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update interval
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_interval($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_intervals', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete interval
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_interval($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_intervals');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change interval status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_interval_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_intervals', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }


    /**
     * get model
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_model($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_models')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_models')->result_array();
        }
    }

    /**
     * add model
     * @param [type] $data 
     */
    public function add_model($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_models',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update model
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_model($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_models', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete model
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_model($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_models');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change model status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_model_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_models', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get appointment_type
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_appointment_type($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_appointment_types')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_appointment_types')->result_array();
        }
    }

    /**
     * add appointment_type
     * @param [type] $data 
     */
    public function add_appointment_type($data)
    {
        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        if(isset($data['plate_renewal'])){
            $data['plate_renewal'] = 1;
        }else{
            $data['plate_renewal'] = 0;
        }
        if(isset($data['warrant_of_fitness'])){
            $data['warrant_of_fitness'] = 1;
        }else{
            $data['warrant_of_fitness'] = 0;
        }
        if(isset($data['next_service'])){
            $data['next_service'] = 1;
        }else{
            $data['next_service'] = 0;
        }

        if (isset($data['item_id'])) {
            $item_ids = $data['item_id'];
            unset($data['item_id']);
        }

        $this->db->insert(db_prefix().'wshop_appointment_types',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (isset($item_ids)) {
                foreach ($item_ids as $item_id) {
                    $this->db->insert(db_prefix() . 'wshop_appointment_products', [
                        'appointment_type_id'      => $insert_id,
                        'item_id' => $item_id,
                    ]);
                }
            }

            return $insert_id;
        }
        return false;
    }

    /**
     * update appointment_type
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_appointment_type($data, $id)
    {
        $affectedRows=0;
        if(isset($data['plate_renewal'])){
            $data['plate_renewal'] = 1;
        }else{
            $data['plate_renewal'] = 0;
        }
        if(isset($data['warrant_of_fitness'])){
            $data['warrant_of_fitness'] = 1;
        }else{
            $data['warrant_of_fitness'] = 0;
        }
        if(isset($data['next_service'])){
            $data['next_service'] = 1;
        }else{
            $data['next_service'] = 0;
        }
        if (isset($data['item_id'])) {
            $item_ids = $data['item_id'];
            unset($data['item_id']);
        }

        $appointment_type_products = $this->get_appointment_type_products($id);
        if (sizeof($appointment_type_products) > 0) {
            if (!isset($data['item_id'])) {
                $this->db->where('appointment_type_id', $id);
                $this->db->delete(db_prefix() . 'wshop_appointment_products');
            } else {
                foreach ($appointment_type_products as $appointment_type_product) {
                    if (isset($item_ids)) {
                        if (!in_array($appointment_type_product['item_id'], $data['item_id'])) {
                            $this->db->where('appointment_type_id', $id);
                            $this->db->where('item_id', $appointment_type_product['item_id']);
                            $this->db->delete(db_prefix() . 'wshop_appointment_products');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }
            if (isset($item_ids)) {
                foreach ($item_ids as $itemid) {
                    $this->db->where('appointment_type_id', $id);
                    $this->db->where('item_id', $itemid);
                    $_exists = $this->db->get(db_prefix() . 'wshop_appointment_products')->row();
                    if (!$_exists) {
                        $this->db->insert(db_prefix() . 'wshop_appointment_products', [
                            'appointment_type_id'      => $id,
                            'item_id' => $itemid,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($item_ids)) {
                foreach ($item_ids as $itemid) {
                    $this->db->insert(db_prefix() . 'wshop_appointment_products', [
                        'appointment_type_id'      => $id,
                        'item_id' => $itemid,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_appointment_types', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete appointment_type
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_appointment_type($id)
    {
        $affectedrows = 0;
        $this->db->where('appointment_type_id', $id);
        $this->db->delete(db_prefix() . 'wshop_appointment_products');
        if ($this->db->affected_rows() > 0) {
            $affectedrows++;
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_appointment_types');
        if ($this->db->affected_rows() > 0) {
            $affectedrows++;
        }

        if($affectedrows > 0){
            return true;
        }
        return false;
    }

    /**
     * change appointment_type status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_appointment_type_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_appointment_types', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get appointment type products
     * @param  boolean $onlyids 
     * @return [type]           
     */
    public function get_appointment_type_products($_appointment_type = false, $onlyids = false)
    {
        if ($onlyids == false) {
            $this->db->select();
        } else {
            $this->db->select(db_prefix() . 'wshop_appointment_products.item_id');
        }
        $this->db->from(db_prefix() . 'wshop_appointment_products');
        $this->db->join(db_prefix() . 'wshop_appointment_types', db_prefix() . 'wshop_appointment_products.item_id = ' . db_prefix() . 'wshop_appointment_types.id', 'left');
        if($_appointment_type){
            $this->db->where('appointment_type_id', $_appointment_type);
        }
        $wshop_appointment_types = $this->db->get()->result_array();
        if ($onlyids == true) {
            $wshop_appointment_typesid = [];
            foreach ($wshop_appointment_types as $appointment_type) {
                array_push($wshop_appointment_typesid, $appointment_type['item_id']);
            }

            return $wshop_appointment_typesid;
        }

        return $wshop_appointment_types;
    }

    /**
     * get fieldset
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_fieldset($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_fieldsets')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_fieldsets')->result_array();
        }
    }

    /**
     * add fieldset
     * @param [type] $data 
     */
    public function add_fieldset($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_fieldsets',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update fieldset
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_fieldset($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_fieldsets', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete fieldset
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_fieldset($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_fieldsets');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change fieldset status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_fieldset_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_fieldsets', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single custom field
     */
    public function get_custom_field($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix().'wshop_customfields')->row();
        }

        return $this->db->get(db_prefix().'wshop_customfields')->result_array();
    }

    /**
     * Add new custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function add_custom_field($data)
    {
        if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
            $data['options'] = is_array($data['options']) ? json_encode($data['options']) : null;
        }else{
            $data['options'] =  NULL;
        }

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }

        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }

        $data['slug'] = slug_it($data['fieldto'] . '_' . $data['name'], [
            'separator' => '_',
        ]);
        $slugs_total = total_rows(db_prefix().'wshop_customfields', ['slug' => $data['slug']]);

        if ($slugs_total > 0) {
            $data['slug'] .= '_' . ($slugs_total + 1);
        }

        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        } elseif ($data['fieldto'] == 'items') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        }

        $this->db->insert(db_prefix().'wshop_customfields', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Custom Field Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function update_custom_field($data, $id)
    {
        $original_field = $this->get_custom_field($id);

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }

        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }

        if (!isset($data['display_inline'])) {
            $data['display_inline'] = 0;
        }
        if (!isset($data['show_on_ticket_form'])) {
            $data['show_on_ticket_form'] = 0;
        }

        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        } elseif ($data['fieldto'] == 'items') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        }
        $options = $data['options'];
        $data['options'] = is_array($data['options']) ? json_encode($data['options']) : null;

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_customfields', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Custom Field Updated [' . $data['name'] . ']');

            if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
                if (trim($data['options']) != trim($original_field->options)) {
                    $options_now = $options;
                    foreach ($options_now as $key => $val) {
                        $options_now[$key] = trim($val);
                    }
                    $options_before = explode(',', $original_field->options);

                    foreach ($options_before as $key => $val) {
                        $options_before[$key] = trim($val);
                    }
                    $removed_options_in_use = [];
                    foreach ($options_before as $option) {
                        if (!in_array($option, $options_now) && total_rows(db_prefix().'wshop_customfieldsvalues', [
                            'fieldid' => $id,
                            'value' => $option,
                        ])) {
                            array_push($removed_options_in_use, $option);
                        }
                    }
                    if (count($removed_options_in_use) > 0) {
                        $this->db->where('id', $id);
                        $this->db->update(db_prefix().'wshop_customfields', [
                            'options' => implode(',', $options_now) . ',' . implode(',', $removed_options_in_use),
                        ]);

                        return [
                            'cant_change_option_custom_field' => true,
                        ];
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Custom fields
     * All values for this custom field will be deleted from database
     */
    public function delete_custom_field($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'wshop_customfields');
        if ($this->db->affected_rows() > 0) {
            // Delete the values
            $this->db->where('fieldid', $id);
            $this->db->delete(db_prefix().'wshop_customfieldsvalues');
            log_activity('Custom Field Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_custom_field_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_customfields', [
            'active' => $status,
        ]);
        log_activity('Custom Field Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }

    /**
     * get fieldset for custom field
     * @return [type] 
     */
    public function get_fieldset_for_custom_field()
    {
        $this->db->select('CONCAT("fieldset_", id) as fieldid, name');
        $this->db->order_by('name', 'ASC');

        return $this->db->get(db_prefix() . 'wshop_fieldsets')->result_array();
    }

    /**
     * count custom field by field_set
     * @param  [type] $fieldset_id 
     * @return [type]              
     */
    public function count_custom_field_by_field_set($fieldset_id)
    {
        $count = 0;
        $this->db->where('fieldset_id', $fieldset_id);
        $count_row = $this->db->get(db_prefix().'wshop_customfields')->num_rows();
        if(is_numeric($count_row)){
            $count = $count_row;
        }
        return $count;
    }

    /**
     * get inspection_template
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_inspection_template($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_inspection_templates')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_inspection_templates')->result_array();
        }
    }

    /**
     * add inspection_template
     * @param [type] $data 
     */
    public function add_inspection_template($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_inspection_templates',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update inspection_template
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_inspection_template($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspection_templates', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete inspection_template
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_inspection_template($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_inspection_templates');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change inspection_template status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_inspection_template_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspection_templates', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get inspection template form
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_inspection_template_form($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_inspection_template_forms')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('form_order', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_inspection_template_forms')->result_array();
        }
    }

    /**
     * add inspection_template_form
     * @param [type] $data 
     */
    public function add_inspection_template_form($data)
    {
        $form_order = 1;
        $sql_where = 'SELECT MAX(form_order)+1 as form_order FROM `'.db_prefix().'wshop_inspection_template_forms`
        WHERE inspection_template_id = '.$data['inspection_template_id'];
        $get_form_order = $this->db->query($sql_where)->row();
        if($get_form_order){
            $form_order = $get_form_order->form_order;
        }

        $data['form_order'] = $form_order;
        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_inspection_template_forms',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update inspection_template_form
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_inspection_template_form($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspection_template_forms', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;  
    }

    /**
     * delete inspection_template_form
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_inspection_template_form($id)
    {
        $affected_rows = 0;
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_inspection_template_forms');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        $this->db->where('inspection_template_form_id', $id);
        $this->db->delete(db_prefix() . 'wshop_inspection_template_form_details');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single custom field
     */
    public function get_inspection_template_form_detail($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_inspection_template_form_details')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('active', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('field_order', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_inspection_template_form_details')->result_array();
        }
    }

    /**
     * Add new custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function add_inspection_template_form_detail($data)
    {
        if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
            $data['options'] = is_array($data['options']) ? json_encode($data['options']) : null;
        }else{
            $data['options'] =  NULL;
        }

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
       
        $field_order = 1;
        $sql_where = 'SELECT MAX(field_order)+1 as field_order FROM `'.db_prefix().'wshop_inspection_template_form_details`
        WHERE inspection_template_form_id = '.$data['inspection_template_form_id'];
        $get_field_order = $this->db->query($sql_where)->row();
        if($get_field_order){
            $field_order = $get_field_order->field_order;
        }

        $data['field_order'] = $field_order;

        $data['slug'] = slug_it($data['fieldto'] . '_' . $data['name'], [
            'separator' => '_',
        ]);
        $slugs_total = total_rows(db_prefix().'wshop_inspection_template_form_details', ['slug' => $data['slug']]);

        if ($slugs_total > 0) {
            $data['slug'] .= '_' . ($slugs_total + 1);
        }

        $this->db->insert(db_prefix().'wshop_inspection_template_form_details', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Inspection Template Detail Field Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function update_inspection_template_form_detail($data, $id)
    {
        $original_field = $this->get_inspection_template_form_detail($id);

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }

        if (!isset($data['display_inline'])) {
            $data['display_inline'] = 0;
        }
   
        $options = $data['options'];
        $data['options'] = is_array($data['options']) ? json_encode($data['options']) : null;

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_inspection_template_form_details', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Inspection Template Detail Field Updated [' . $data['name'] . ']');

            if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
                if (trim($data['options']) != trim($original_field->options ?? '')) {
                    $options_now = $options;
                    foreach ($options_now as $key => $val) {
                        $options_now[$key] = trim($val);
                    }
                    $options_before = new_strlen($original_field->options) ? json_decode($original_field->options) : [];
                    foreach ($options_before as $key => $val) {
                        $options_before[$key] = trim($val);
                    }
                    $removed_options_in_use = [];
                    foreach ($options_before as $option) {
                        if (!in_array($option, $options_now) && total_rows(db_prefix().'wshop_inspection_template_values', [
                            'inspection_template_form_detail_id' => $id,
                            'value' => $option,
                        ])) {
                            array_push($removed_options_in_use, $option);
                        }
                    }
                    if (count($removed_options_in_use) > 0) {
                        $this->db->where('id', $id);
                        $this->db->update(db_prefix().'wshop_inspection_template_form_details', [
                            'options' => implode(',', $options_now) . ',' . implode(',', $removed_options_in_use),
                        ]);

                        return [
                            'cant_change_option_custom_field' => true,
                        ];
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Custom fields
     * All values for this custom field will be deleted from database
     */
    public function delete_inspection_template_form_detail($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'wshop_inspection_template_form_details');
        if ($this->db->affected_rows() > 0) {
            // Delete the values
            if(1==2){
                $this->db->where('inspection_template_form_detail_id', $id);
                $this->db->delete(db_prefix().'wshop_inspection_template_values');
            }
            log_activity('Inspection Template Detail Field Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_inspection_template_form_detail_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_inspection_template_form_details', [
            'active' => $status,
        ]);
        log_activity('Inspection Template Detail Field Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }

    /**
     * mechanic role exists
     * @return [type] 
     */
    public function mechanic_role_exists ()
    {
        $this->db->where('name', 'Mechanic');
        $role = $this->db->get(db_prefix() . 'roles')->row();
        if(!$role){
            $sql = "INSERT INTO ".db_prefix()."roles (name, permissions)
            VALUES ('Mechanic', NULL)";
            $mechanic_role = $this->db->query($sql);
            $roleid = $this->db->insert_id();
            return $roleid;
        }else{
            return $role->roleid;
        }
        return true;
    }

    /**
        * get staff in deparment
        * @param  integer $department_id 
        * @return integer                
        */
    public function get_staff_in_deparment($department_id)
    {
        $data = [];
        $sql = 'select 
        departmentid 
        from    (select * from '.db_prefix().'departments
        order by '.db_prefix().'departments.parent_id, '.db_prefix().'departments.departmentid) departments_sorted,
        (select @pv := '.$department_id.') initialisation
        where   find_in_set(parent_id, @pv)
        and     length(@pv := concat(@pv, ",", departmentid)) OR departmentid = '.$department_id.'';
        $result_arr = $this->db->query($sql)->result_array();
        foreach ($result_arr as $key => $value) {
            $data[$key] = $value['departmentid'];
        }
        return $data;
    }

    /**
     * get attachment file
     * @param  [type] $rel_id   
     * @param  [type] $rel_type 
     * @return [type]           
     */
    public function get_attachment_file($rel_id, $rel_type){
        $this->db->order_by('dateadded', 'desc');
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    /**
     * get branch
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_branch($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_branches')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('active', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_branches')->result_array();
        }
    }

    /**
     * add branch
     * @param [type] $data 
     */
    public function add_branch($data)
    {

        $data['active'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_branches',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update branch
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_branch($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_branches', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete branch
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_branch($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_branches');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change branch status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_branch_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_branches', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * send mail to branch
     * @param  [type] $data 
     * @return [type]       
     */
    public function send_mail_to_branch($data) {
        $staff_id = get_staff_user_id();

        $inbox = array();
        $inbox['to'] = $data['branch_email'];
        $inbox['sender_name'] = get_staff_full_name($staff_id);
        $inbox['subject'] = _strip_tags($data['email_subject']);
        $inbox['body'] = ($data['email_content']);
        $inbox['body'] = nl2br_save_html($inbox['body']);
        $inbox['date_received'] = date('Y-m-d H:i:s');
        $inbox['from_email'] = get_option('smtp_email');

        if (new_strlen(get_option('smtp_host')) > 0 && new_strlen(get_option('smtp_password')) > 0 && new_strlen(get_option('smtp_username')) > 0) {

            $this->wshop_send_simple_email($inbox['to'], $inbox['subject'], $inbox['body']);
        }
    }

    /**
     * wshop send simple email
     * @param  [type] $email    
     * @param  [type] $subject  
     * @param  [type] $message  
     * @param  string $fromname 
     * @return [type]           
     */
    public function wshop_send_simple_email($email, $subject, $message, $fromname = '')
    {
        $cnf = [
            'from_email' => get_option('smtp_email'),
            'from_name'  => $fromname != '' ? $fromname : get_option('companyname'),
            'email'      => $email,
            'subject'    => $subject,
            'message'    => $message,
        ];

        // Simulate fake template to be parsed
        $template           = new StdClass();
        $template->message  = get_option('email_header') . $cnf['message'] . get_option('email_footer');
        $template->fromname = $cnf['from_name'];
        $template->subject  = $cnf['subject'];

        $template = parse_email_template($template);

        $cnf['message']   = $template->message;
        $cnf['from_name'] = $template->fromname;
        $cnf['subject']   = $template->subject;

        $cnf['message'] = check_for_links($cnf['message']);

        $cnf = hooks()->apply_filters('before_send_simple_email', $cnf);

        if (isset($cnf['prevent_sending']) && $cnf['prevent_sending'] == true) {
            $this->clear_attachments();

            return false;
        }
        $this->load->config('email');
        $this->email->clear(true);
        $this->email->set_newline(config_item('newline'));
        $this->email->from($cnf['from_email'], $cnf['from_name']);
        $this->email->to($cnf['email']);

        $bcc = '';
        // Used for action hooks
        if (isset($cnf['bcc'])) {
            $bcc = $cnf['bcc'];
            if (is_array($bcc)) {
                $bcc = implode(', ', $bcc);
            }
        }

        $systemBCC = get_option('bcc_emails');
        if ($systemBCC != '') {
            if ($bcc != '') {
                $bcc .= ', ' . $systemBCC;
            } else {
                $bcc .= $systemBCC;
            }
        }
        if ($bcc != '') {
            $this->email->bcc($bcc);
        }

        if (isset($cnf['cc'])) {
            $this->email->cc($cnf['cc']);
        }

        if (isset($cnf['reply_to'])) {
            $this->email->reply_to($cnf['reply_to']);
        }

        $this->email->subject($cnf['subject']);
        $this->email->message($cnf['message']);

        $this->email->set_alt_message(strip_html_tags($cnf['message'], '<br/>, <br>, <br />'));

        if (isset($this->attachment) && count($this->attachment) > 0) {
            foreach ($this->attachment as $attach) {
                if (!isset($attach['read'])) {
                    $this->email->attach($attach['attachment'], 'attachment', $attach['filename'], $attach['type']);
                } else {
                    if (!isset($attach['filename']) || (isset($attach['filename']) && empty($attach['filename']))) {
                        $attach['filename'] = basename($attach['attachment']);
                    }
                    $this->email->attach($attach['attachment'], '', $attach['filename']);
                }
            }
        }

        $this->clear_attachments();
        if ($this->email->send()) {
            log_activity('Email sent to: ' . $cnf['email'] . ' Subject: ' . $cnf['subject']);

            return true;
        }

        return false;
    }

    /**
     * clear attachments
     * @return [type] 
     */
    private function clear_attachments()
    {
        $this->attachment = [];
    }


    /**
     * get device
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_device($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_devices')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_devices')->result_array();
        }
    }

    /**
     * add device
     * @param [type] $data 
     */
    public function add_device($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        if($data['purchase_date'] != ''){
            $data['purchase_date'] = to_sql_date($data['purchase_date']);
        }else{
            $data['purchase_date'] = null;
        }
        if($data['warranty_start_date'] != ''){
            $data['warranty_start_date'] = to_sql_date($data['warranty_start_date']);
        }else{
            $data['warranty_start_date'] = null;
        }
        if($data['warranty_expiry_date'] != ''){
            $data['warranty_expiry_date'] = to_sql_date($data['warranty_expiry_date']);
        }else{
            $data['warranty_expiry_date'] = null;
        }
        if($data['prod_date'] != ''){
            $data['prod_date'] = to_sql_date($data['prod_date']);
        }else{
            $data['prod_date'] = null;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            foreach ($custom_fields as $key => $value) {
                if(preg_match('/^fieldset/', $key)){
                    $fieldset = []; 
                    $fieldset[$key] = $value; 
                }elseif(preg_match('/^wshop_device/', $key)){
                    $wshop_device = [];
                    $wshop_device[$key] = $value;
                }
            }
            unset($data['custom_fields']);
        }

        $this->db->insert(db_prefix().'wshop_devices',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            handle_device_image($insert_id);

            if (isset($fieldset)) {
                wshop_handle_custom_fields_post($insert_id, $fieldset);
            }

            if (isset($wshop_device)) {
                handle_custom_fields_post($insert_id, $wshop_device);
            }

            return $insert_id;
        }
        return false;
    }

    /**
     * update device
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_device($data, $id)
    {
        $affected_rows=0;

        if($data['purchase_date'] != ''){
            $data['purchase_date'] = to_sql_date($data['purchase_date']);
        }else{
            $data['purchase_date'] = null;
        }
        if($data['warranty_start_date'] != ''){
            $data['warranty_start_date'] = to_sql_date($data['warranty_start_date']);
        }else{
            $data['warranty_start_date'] = null;
        }
        if($data['warranty_expiry_date'] != ''){
            $data['warranty_expiry_date'] = to_sql_date($data['warranty_expiry_date']);
        }else{
            $data['warranty_expiry_date'] = null;
        }
        if($data['prod_date'] != ''){
            $data['prod_date'] = to_sql_date($data['prod_date']);
        }else{
            $data['prod_date'] = null;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            foreach ($custom_fields as $key => $value) {
                if(preg_match('/^fieldset/', $key)){
                    $fieldset = []; 
                    $fieldset[$key] = $value; 
                }elseif(preg_match('/^wshop_device/', $key)){
                    $wshop_device = [];
                    $wshop_device[$key] = $value;
                }
            }
            unset($data['custom_fields']);
        }

        if (isset($fieldset)) {
            if (wshop_handle_custom_fields_post($id, $fieldset)) {
                $affected_rows++;
            }
        }

        if (isset($wshop_device)) {
            if(handle_custom_fields_post($id, $wshop_device)) {
                $affected_rows++;
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_devices', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        handle_device_image($id);

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete device
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_device($id)
    {
        $affected_rows = 0;
        if (is_dir(MAIN_IMAGE_DEVICES_IMAGES_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(MAIN_IMAGE_DEVICES_IMAGES_FOLDER. $id);
        }
        if (is_dir(DEVICES_IMAGES_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(DEVICES_IMAGES_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_devices');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        // Delete the custom field values
        $this->db->where('relid', $id);
        $this->db->where('fieldto', 'wshop_device');
        $this->db->delete(db_prefix() . 'customfieldsvalues');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        // Delete the field set field values
        $this->db->where('relid', $id);
        $this->db->where('fieldto', 'fieldset_'.$id);
        $this->db->delete(db_prefix() . 'wshop_customfieldsvalues');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * change device status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_device_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_devices', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * delete workshop file
     * @param  [type] $attachment_id 
     * @param  [type] $folder_name   
     * @return [type]                
     */
    public function delete_workshop_file($attachment_id, $folder_name)
    {
        $deleted    = false;
        $attachment = $this->misc_model->get_file($attachment_id);
        if ($attachment) {
            if (empty($attachment->external)) {
                if (file_exists($folder_name .$attachment->rel_id.'/'.$attachment->file_name)) {
                    unlink($folder_name .$attachment->rel_id.'/'.$attachment->file_name);
                }
                if (file_exists($folder_name .$attachment->rel_id.'/small_'.$attachment->file_name)) {
                    unlink($folder_name .$attachment->rel_id.'/small_'.$attachment->file_name);
                }
                if (file_exists($folder_name .$attachment->rel_id.'/thumb_'.$attachment->file_name)) {
                    unlink($folder_name .$attachment->rel_id.'/thumb_'.$attachment->file_name);
                }
            }
            
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('workshop Attachment Deleted [ID: ' . $attachment->rel_id . '] folder name: '.$folder_name);
            }

            if (is_dir($folder_name .$attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files($folder_name .$attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir($folder_name .$attachment->rel_id);
                }
            }
        }
        return $deleted;
    }

    /**
     * get device asset
     * @param  [type] $id 
     * @return [type]     
     */
    public function get_device_images($id)
    {
        $assets = [];
        $primary_profile_image = '';
        $device = $this->get_device($id);
        if($device){
            $primary_profile_image = $device->primary_profile_image;
        }

        $device_images = $this->get_attachment_file($id, 'wshop_device');

        if ($primary_profile_image != '' && file_exists(MAIN_IMAGE_DEVICES_IMAGES_FOLDER.$id.'/'.$primary_profile_image)) {
            $main_image =  site_url('modules/workshop/uploads/main_image_devices/'.$id.'/'.$primary_profile_image);

            $assets[] = [
                'type' => 'main_image',
                'site_url' => $main_image,
            ];
        }

        foreach ($device_images as $key => $value) {
            $value['type'] = 'image';
            $assets[] = $value;
        }

        return $assets;
    }

    /**
     * log workshop activity
     * @param  [type]  $id              
     * @param  string  $description     
     * @param  boolean $client          
     * @param  string  $additional_data 
     * @param  string  $rel_type        
     * @return [type]                   
     */
    public function log_workshop_activity($id, $description = '', $client = false, $additional_data = '', $rel_type = 'device')
    {
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif (defined('STRIPE_SUBSCRIPTION_INVOICE')) {
            $staffid   = null;
            $full_name = '[Stripe]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        } else {
            $staffid   = get_staff_user_id();
            $full_name = get_staff_full_name(get_staff_user_id());
        }
        $this->db->insert(db_prefix() . 'wshop_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => $rel_type,
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * get labour_product
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_labour_product($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $labour_product = $this->db->get(db_prefix() . 'wshop_labour_products')->row();

            $this->db->where('labour_product_id', $id);
            $parts = $this->db->get(db_prefix().'wshop_labour_product_materials')->result_array();
            if($parts){
                $labour_product->parts = $parts;
            }
            return $labour_product;
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_labour_products')->result_array();
        }
    }

    /**
     * add labour_product
     * @param [type] $data 
     */
    public function add_labour_product($data)
    {

        $data['status'] = 1;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_labour_products',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update labour_product
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_labour_product($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_labour_products', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete labour_product
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_labour_product($id)
    {
        $affected_rows = 0;
        

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_labour_products');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * change labour_product status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_labour_product_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_labour_products', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get material
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_material($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_labour_product_materials')->row();
        }
        if ($id == false) {
            
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_labour_product_materials')->result_array();
        }
    }

    /**
     * add material
     * @param [type] $data 
     */
    public function add_material($data)
    {

        $this->db->insert(db_prefix().'wshop_labour_product_materials',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * update material
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_material($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_labour_product_materials', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete material
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_material($id)
    {
        $affected_rows = 0;
        

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_labour_product_materials');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * get repair_job
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_repair_job($id = false, $active = false, $where = [], $all_labour_part = false) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $repair_job = $this->db->get(db_prefix() . 'wshop_repair_jobs')->row();

            if($all_labour_part){
                $this->db->where('repair_job_id', $id);
                $repair_job_labour_products = $this->db->get(db_prefix().'wshop_repair_job_labour_products')->result_array();
                if($repair_job_labour_products){
                    $repair_job->repair_job_labour_products = $repair_job_labour_products;
                }

                $this->db->where('repair_job_id', $id);
                $repair_job_labour_materials = $this->db->get(db_prefix().'wshop_repair_job_labour_materials')->result_array();
                if($repair_job_labour_materials){
                    $repair_job->repair_job_labour_materials = $repair_job_labour_materials;
                }

            }else{
                $this->db->where('repair_job_id', $id);
                $this->db->where('inspection_id', 0);
                $repair_job_labour_products = $this->db->get(db_prefix().'wshop_repair_job_labour_products')->result_array();
                if($repair_job_labour_products){
                    $repair_job->repair_job_labour_products = $repair_job_labour_products;
                }

                $this->db->where('repair_job_id', $id);
                $this->db->where('inspection_id', 0);
                $repair_job_labour_materials = $this->db->get(db_prefix().'wshop_repair_job_labour_materials')->result_array();
                if($repair_job_labour_materials){
                    $repair_job->repair_job_labour_materials = $repair_job_labour_materials;
                }
            }

            return $repair_job;
        }
        if ($id == false) {

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('id', 'DESC');

            return $this->db->get(db_prefix() . 'wshop_repair_jobs')->result_array();
        }
    }

    /**
     * add repair_job
     * @param [type] $data 
     */
    public function add_repair_job($data)
    {

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        $data['appointment_date'] = to_sql_date($data['appointment_date'], true);
        $data['estimated_completion_date'] = to_sql_date($data['estimated_completion_date'], true);
        $data['hash'] = app_generate_hash();
        $data['prefix'] = get_option('wshop_repair_job_prefix');
        $data['number_format'] = get_option('wshop_repair_job_number_format');
        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['DataTables_Table_0_length'])) {
            unset($data['DataTables_Table_0_length']);
        }
        if (isset($data['DataTables_Table_1_length'])) {
            unset($data['DataTables_Table_1_length']);
        }
        if(isset($data['include_shipping'])){
            unset($data['include_shipping']);
        }
        if(isset($data['show_shipping_on_repair_job'])){
            unset($data['show_shipping_on_repair_job']);
        }
        if(isset($data['standard_time'])){
            unset($data['standard_time']);
        }
        if(isset($data['quantity'])){
            unset($data['quantity']);
        }
        
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }
        if (isset($data['save_and_send_later'])) {
            $save_and_send_later = $data['save_and_send_later'];
            unset($data['save_and_send_later']);
        }
        if (isset($data['save_and_send'])) {
            $save_and_send = $data['save_and_send'];
            unset($data['save_and_send']);
        }

        $newlabouritems = [];
        if (isset($data['newlabouritems'])) {
            $newlabouritems = $data['newlabouritems'];
            unset($data['newlabouritems']);
        }
        $newpartitems = [];
        if (isset($data['newpartitems'])) {
            $newpartitems = $data['newpartitems'];
            unset($data['newpartitems']);
        }

        $this->db->insert(db_prefix().'wshop_repair_jobs',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->getBarcode($data['job_tracking_number']);
            // Update next repair job number in settings
            $this->db->where('name', 'wshop_repair_job_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            foreach ($newlabouritems as $labouritem) {
                $labouritem['repair_job_id'] = $insert_id;
                $labouritem['item_order'] = $labouritem['order'];
                $tax_id = null;
                $tax_rate = null;
                $tax_name = null;

                if(isset($labouritem['tax_select'])){
                    $tax_rate_data = $this->wshop_get_tax_rate($labouritem['tax_select']);
                    $tax_id = $tax_rate_data['tax_id_str'];
                    $tax_rate = $tax_rate_data['tax_rate_str'];
                    $tax_name = $tax_rate_data['tax_name_str'];
                }
                $labouritem['tax_id'] = $tax_id;
                $labouritem['tax_rate'] = $tax_rate;
                $labouritem['tax_name'] = $tax_name;

                unset($labouritem['order']);
                unset($labouritem['id']);
                unset($labouritem['tax_select']);
                $this->db->insert(db_prefix() . 'wshop_repair_job_labour_products', $labouritem);
            }

            foreach ($newpartitems as $partitem) {
                $partitem['repair_job_id'] = $insert_id;
                $partitem['item_order'] = $partitem['order'];
                $tax_id = null;
                $tax_rate = null;
                $tax_name = null;

                if(isset($partitem['tax_select'])){
                    $tax_rate_data = $this->wshop_get_tax_rate($partitem['tax_select']);
                    $tax_id = $tax_rate_data['tax_id_str'];
                    $tax_rate = $tax_rate_data['tax_rate_str'];
                    $tax_name = $tax_rate_data['tax_name_str'];
                }
                $partitem['tax_id'] = $tax_id;
                $partitem['tax_rate'] = $tax_rate;
                $partitem['tax_name'] = $tax_name;

                unset($partitem['order']);
                unset($partitem['id']);
                unset($partitem['tax_select']);
                $this->db->insert(db_prefix() . 'wshop_repair_job_labour_materials', $partitem);
            }
            $this->log_workshop_activity($insert_id, 'wshop_repair_job_activity_created', false, '', 'repair_job');

            return $insert_id;
        }
        return false;
    }

    /**
     * update repair_job
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_repair_job($data, $id)
    {
        $affected_rows=0;
        $newlabouritems = [];
        $update_labouritems = [];
        $remove_labouritems = [];
        $newpartitems = [];
        $update_partitems = [];
        $remove_partitems = [];
        if(isset($data['isedit'])){
            unset($data['isedit']);
        }

        if (isset($data['newlabouritems'])) {
            $newlabouritems = $data['newlabouritems'];
            unset($data['newlabouritems']);
        }
        if (isset($data['labouritems'])) {
            $update_labouritems = $data['labouritems'];
            unset($data['labouritems']);
        }
        if (isset($data['removed_labour_product_items'])) {
            $remove_labouritems = $data['removed_labour_product_items'];
            unset($data['removed_labour_product_items']);
        }

        if (isset($data['newpartitems'])) {
            $newpartitems = $data['newpartitems'];
            unset($data['newpartitems']);
        }
        if (isset($data['partitems'])) {
            $update_partitems = $data['partitems'];
            unset($data['partitems']);
        }
        if (isset($data['removed_part_items'])) {
            $remove_partitems = $data['removed_part_items'];
            unset($data['removed_part_items']);
        }
        if (isset($data['save_and_send_later'])) {
            $save_and_send_later = $data['save_and_send_later'];
            unset($data['save_and_send_later']);
        }
        if (isset($data['save_and_send'])) {
            $save_and_send = $data['save_and_send'];
            unset($data['save_and_send']);
        }

        $data['appointment_date'] = to_sql_date($data['appointment_date'], true);
        $data['estimated_completion_date'] = to_sql_date($data['estimated_completion_date'], true);
        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);
        if (isset($data['DataTables_Table_0_length'])) {
            unset($data['DataTables_Table_0_length']);
        }
        if (isset($data['DataTables_Table_1_length'])) {
            unset($data['DataTables_Table_1_length']);
        }
        if(isset($data['include_shipping'])){
            unset($data['include_shipping']);
        }
        if(isset($data['show_shipping_on_repair_job'])){
            unset($data['show_shipping_on_repair_job']);
        }
        if(isset($data['standard_time'])){
            unset($data['standard_time']);
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_repair_jobs', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        foreach ($update_labouritems as $labouritem) {
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;
            if(isset($labouritem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($labouritem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $labouritem['tax_id'] = $tax_id;
            $labouritem['tax_rate'] = $tax_rate;
            $labouritem['tax_name'] = $tax_name;

            unset($labouritem['order']);
            unset($labouritem['tax_select']);

            $this->db->where('id', $labouritem['id']);
            if ($this->db->update(db_prefix() . 'wshop_repair_job_labour_products', $labouritem)) {
                $affected_rows++;
            }
        }

       // delete labour product
        foreach ($remove_labouritems as $labouritem) {
            $this->db->where('id', $labouritem);
            if ($this->db->delete(db_prefix() . 'wshop_repair_job_labour_products')) {
                $affected_rows++;
            }
        }

        // add labour product
        foreach ($newlabouritems as $labouritem) {
            $labouritem['repair_job_id'] = $id;
            $labouritem['item_order'] = $labouritem['order'];
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;

            if(isset($labouritem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($labouritem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $labouritem['tax_id'] = $tax_id;
            $labouritem['tax_rate'] = $tax_rate;
            $labouritem['tax_name'] = $tax_name;

            unset($labouritem['order']);
            unset($labouritem['id']);
            unset($labouritem['tax_select']);
            $this->db->insert(db_prefix() . 'wshop_repair_job_labour_products', $labouritem);
            if($this->db->insert_id()){
                $affected_rows++;
            }
        }


        foreach ($update_partitems as $partitem) {
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;
            if(isset($partitem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($partitem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $partitem['tax_id'] = $tax_id;
            $partitem['tax_rate'] = $tax_rate;
            $partitem['tax_name'] = $tax_name;

            unset($partitem['order']);
            unset($partitem['tax_select']);

            $this->db->where('id', $partitem['id']);
            if ($this->db->update(db_prefix() . 'wshop_repair_job_labour_materials', $partitem)) {
                $affected_rows++;
            }
        }

       // delete labour product
        foreach ($remove_partitems as $partitem) {
            $this->db->where('id', $partitem);
            if ($this->db->delete(db_prefix() . 'wshop_repair_job_labour_materials')) {
                $affected_rows++;
            }
        }

        foreach ($newpartitems as $partitem) {
            $partitem['repair_job_id'] = $id;
            $partitem['item_order'] = $partitem['order'];
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;

            if(isset($partitem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($partitem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $partitem['tax_id'] = $tax_id;
            $partitem['tax_rate'] = $tax_rate;
            $partitem['tax_name'] = $tax_name;

            unset($partitem['order']);
            unset($partitem['id']);
            unset($partitem['tax_select']);
            $this->db->insert(db_prefix() . 'wshop_repair_job_labour_materials', $partitem);
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete repair_job
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_repair_job($id)
    {
        $affected_rows = 0;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_repair_jobs');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        // Delete the custom field values
        $this->db->where('repair_job_id', $id);
        $this->db->delete(db_prefix() . 'wshop_repair_job_labour_products');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        // Delete the field set field values
        $this->db->where('repair_job_id', $id);
        $this->db->delete(db_prefix() . 'wshop_repair_job_labour_materials');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * Generate a unique job tracking number with 12 characters
     * @return string
     */
    public function generate_job_tracking_number()
    {
        $characters = '0123456789';
        $tracking_number = '';
        do {
            $tracking_number = '';
            for ($i = 0; $i < 12; $i++) {
                $tracking_number .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while ($this->tracking_number_exists($tracking_number));
        $tracking_number = $this->ean13_check_digit($tracking_number);
        
        return $tracking_number;
    }

    /**
     * ean13 check digit
     * @param  [type] $digits 
     * @return [type]         
     */
    public function ean13_check_digit($digits){
        //first change digits to a string so that we can access individual numbers
        $digits =(string)$digits;
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
        return $digits . $check_digit;
    }

    /**
     * Check if a tracking number exists in the wshop_repair_jobs table
     * @param string $tracking_number
     * @return bool
     */
    private function tracking_number_exists($tracking_number)
    {
        $this->db->where('job_tracking_number', $tracking_number);
        $query = $this->db->get('wshop_repair_jobs');
        return $query->num_rows() > 0;
    }   

    /**
     * Gets the tax name.
     *
     * @param        $tax    The tax
     *
     * @return     string  The tax name.
     */
    public function get_tax_name($tax){
        $this->db->where('id', $tax);
        $tax_if = $this->db->get(db_prefix().'taxes')->row();
        if($tax_if){
            return $tax_if->name;
        }
        return '';
    }

    /**
     * { tax rate by id }
     *
     * @param        $tax_id  The tax identifier
     */
    public function tax_rate_by_id($tax_id){
        $this->db->where('id', $tax_id);
        $tax = $this->db->get(db_prefix().'taxes')->row();
        if($tax){
            return $tax->taxrate;
        }
        return 0;
    }

    public function wshop_get_tax_rate($taxname)
    {   
        $tax_rate = 0;
        $tax_rate_str = '';
        $tax_id_str = '';
        $tax_name_str = '';
        if(is_array($taxname)){
            foreach ($taxname as $key => $value) {
                $_tax = new_explode("|", $value);
                if(isset($_tax[1])){
                    $tax_rate += (float)$_tax[1];
                    if(new_strlen($tax_rate_str) > 0){
                        $tax_rate_str .= '|'.$_tax[1];
                    }else{
                        $tax_rate_str .= $_tax[1];
                    }

                    $this->db->where('name', $_tax[0]);
                    $taxes = $this->db->get(db_prefix().'taxes')->row();
                    if($taxes){
                        if(new_strlen($tax_id_str) > 0){
                            $tax_id_str .= '|'.$taxes->id;
                        }else{
                            $tax_id_str .= $taxes->id;
                        }
                    }

                    if(new_strlen($tax_name_str) > 0){
                        $tax_name_str .= '|'.$_tax[0];
                    }else{
                        $tax_name_str .= $_tax[0];
                    }
                }
            }
        }
        return ['tax_rate' => $tax_rate, 'tax_rate_str' => $tax_rate_str, 'tax_id_str' => $tax_id_str, 'tax_name_str' => $tax_name_str];
    }

    /**
     * get taxes dropdown template
     * @param  [type]  $name     
     * @param  [type]  $taxname  
     * @param  string  $type     
     * @param  string  $item_key 
     * @param  boolean $is_edit  
     * @param  boolean $manual   
     * @return [type]            
     */
    public function get_taxes_dropdown_template($name, $taxname, $type = '', $item_key = '', $is_edit = false, $manual = false)
    {

        // if passed manually - like in proposal convert items or project
        if($taxname != '' && !is_array($taxname)){
            $taxname = new_explode(',', $taxname);
        }

        if ($manual == true) {
            // + is no longer used and is here for backward compatibilities
            if (is_array($taxname) || strpos($taxname, '+') !== false) {
                if (!is_array($taxname)) {
                    $__tax = new_explode('+', $taxname);
                } else {
                    $__tax = $taxname;
                }
                // Multiple taxes found // possible option from default settings when invoicing project
                $taxname = [];
                foreach ($__tax as $t) {
                    $tax_array = new_explode('|', $t);
                    if (isset($tax_array[0]) && isset($tax_array[1])) {
                        array_push($taxname, $tax_array[0] . '|' . $tax_array[1]);
                    }
                }
            } else {
                $tax_array = new_explode('|', $taxname);
                // isset tax rate
                if (isset($tax_array[0]) && isset($tax_array[1])) {
                    $tax = get_tax_by_name($tax_array[0]);
                    if ($tax) {
                        $taxname = $tax->name . '|' . $tax->taxrate;
                    }
                }
            }
        }
        // First get all system taxes
        $this->load->model('taxes_model');
        $taxes = $this->taxes_model->get();
        $i     = 0;
        foreach ($taxes as $tax) {
            unset($taxes[$i]['id']);
            $taxes[$i]['name'] = $tax['name'] . '|' . $tax['taxrate'];
            $i++;
        }
        if ($is_edit == true) {

            // Lets check the items taxes in case of changes.
            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_' . $type . '_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item_key);
            }

            foreach ($item_taxes as $item_tax) {
                $new_tax            = [];
                $new_tax['name']    = $item_tax['taxname'];
                $new_tax['taxrate'] = $item_tax['taxrate'];
                $taxes[]            = $new_tax;
            }
        }

        // In case tax is changed and the old tax is still linked to estimate/proposal when converting
        // This will allow the tax that don't exists to be shown on the dropdowns too.
        if (is_array($taxname)) {
            foreach ($taxname as $tax) {
                // Check if tax empty
                if ((!is_array($tax) && $tax == '') || is_array($tax) && $tax['taxname'] == '') {
                    continue;
                };
                // Check if really the taxname NAME|RATE don't exists in all taxes
                if (!value_exists_in_array_by_key($taxes, 'name', $tax)) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = new_explode('|', $tax);
                    } else {
                        $tax_array   = new_explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ($tmp_taxname == '') {
                            continue;
                        }
                    }
                    $taxes[] = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1]];
                }
            }
        }

        // Clear the duplicates
        $taxes = $this->uniqueByKey($taxes, 'name');

        $select = '<select class="selectpicker display-block taxes" data-width="100%" name="' . $name . '" multiple data-none-selected-text="' . _l('no_tax') . '">';

        foreach ($taxes as $tax) {
            $selected = '';
            if (is_array($taxname)) {
                foreach ($taxname as $_tax) {
                    if (is_array($_tax)) {
                        if ($_tax['taxname'] == $tax['name']) {
                            $selected = 'selected';
                        }
                    } else {
                        if ($_tax == $tax['name']) {
                            $selected = 'selected';
                        }
                    }
                }
            } else {
                if ($taxname == $tax['name']) {
                    $selected = 'selected';
                }
            }

            $select .= '<option value="' . $tax['name'] . '" ' . $selected . ' data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /**
     * uniqueByKey
     * @param  [type] $array 
     * @param  [type] $key   
     * @return [type]        
     */
    public function uniqueByKey($array, $key)
    {
        $temp_array = [];
        $i          = 0;
        $key_array  = [];

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]  = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }

        return $temp_array;
    }

    /**
     * create labour product row template
     * @param  string  $name              
     * @param  string  $labour_product_id 
     * @param  string  $product_name      
     * @param  string  $description       
     * @param  string  $labour_type       
     * @param  string  $estimated_hours   
     * @param  string  $unit_price        
     * @param  string  $qty               
     * @param  string  $tax_id            
     * @param  string  $tax_rate          
     * @param  string  $tax_name          
     * @param  string  $discount          
     * @param  string  $subtotal          
     * @param  string  $item_id           
     * @param  boolean $is_edit           
     * @return [type]                     
     */
    public function create_labour_product_row_template($name = '', $labour_product_id = '', $product_name = '', $description = '', $labour_type = '', $estimated_hours = '', $unit_price = '', $qty = '', $tax_id = '', $tax_rate = '', $tax_name = '', $discount = '', $subtotal = '', $item_id = '', $is_edit = false ) {
        
        $row = '';

        $name_labour_product_id = 'labour_product_id';
        $name_product_name = 'name';
        $name_description = 'description';
        $name_labour_type = 'labour_type';
        $name_estimated_hours = 'estimated_hours';
        $name_unit_price = 'unit_price';
        $name_qty = 'qty';
        $name_tax_id = 'tax_id';
        $name_tax_rate = 'tax_rate';
        $name_tax_name = 'tax_name';
        $name_discount = 'discount';
        $name_subtotal = 'subtotal';
        $name_tax_id_select = 'tax_select';

        $array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_estimated_hours_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_discount_attr = [ 'min' => '0.0', 'step' => 'any'];

        if ($name == '') {
            $row .= '<tr class="main hide">
                  <td></td>';
            $manual             = true;
            $invoice_item_taxes = '';

        } else {
            $row .= '<tr class="sortable item">
            <td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_id . '"></td>';

            $name_labour_product_id = $name . '[labour_product_id]';
            $name_product_name = $name . '[name]';
            $name_description = $name . '[description]';
            $name_labour_type = $name . '[labour_type]';
            $name_estimated_hours = $name . '[estimated_hours]';
            $name_unit_price = $name . '[unit_price]';
            $name_qty = $name . '[qty]';
            $name_tax_id = $name . '[tax_id]';
            $name_tax_rate = $name . '[tax_rate]';
            $name_tax_name = $name . '[tax_name]';
            $name_discount = $name . '[discount]';
            $name_subtotal = $name . '[subtotal]';
            $name_tax_id_select = $name . '[tax_select][]';

            $array_rate_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_unit_price')];
            $array_qty_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_quantity')];
            $array_estimated_hours_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_estimated_hours')];
            $array_product_discount_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'max' => '100', 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('discount')];

            $manual             = false;
            $tax_money = 0;
            $invoice_item_taxes = wshop_convert_item_taxes($tax_id, $tax_rate, $tax_name);

        }

        $row .= '<td class="" width="17%">' . $product_name . '</td>';
        $row .= '<td class="" width="26%">' . $description . '</td>';

        $row .= '<td class="unit_price" width="8%">' . render_input($name_unit_price, '', $unit_price, 'number', $array_rate_attr) . '</td>';
        $row .= '<td class="estimated_hours" width="8%">' . 
        render_input($name_estimated_hours, '', $estimated_hours, 'number', $array_estimated_hours_attr, [], 'no-margin').
         '</td>';
        $row .= '<td class="qty" width="8%">' . render_input($name_qty, '', $qty, 'number', $array_qty_attr) . '</td>';

        $row .= '<td class="taxrate" width="8%">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_id, true, $manual) . '</td>';
        $row .= '<td class="discount" width="9%">' . render_input($name_discount, '', $discount, 'number', $array_product_discount_attr) . '</td>';
        $row .= '<td class="amount" align="right" width="11%">' . $subtotal . '</td>';

        $row .= '<td class="hide">' . render_input($name_labour_product_id, '', $labour_product_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_product_name, '', $product_name, 'text', []) . '</td>';
        $row .= '<td class="hide labour_type">' . render_input($name_labour_type, '', $labour_type, 'text', []) . '</td>';
        $row .= '<td class="hide sub_total">' . render_input($name_subtotal, '', $subtotal, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_textarea($name_description, '', $description, ['rows' => 1, 'placeholder' => _l('item_description_placeholder'), 'oninput' => 'auto_grow(this)'] ) . '</td>';


        if ($name == '') {
            $row .= '<td width="5%"><button type="button" onclick="labour_product_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td width="5%"><a href="#" class="btn btn-danger pull-right" onclick="labour_product_delete_item(this,' . $item_id . ',\'.labour_product-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }
        $row .= '</tr>';
        return $row;
    }

    /**
     * create part row template
     * @param  string  $name          
     * @param  string  $item_id       
     * @param  string  $part_name     
     * @param  string  $description   
     * @param  string  $rate          
     * @param  string  $qty           
     * @param  string  $estimated_qty 
     * @param  string  $tax_id        
     * @param  string  $tax_rate      
     * @param  string  $tax_name      
     * @param  string  $discount      
     * @param  string  $subtotal      
     * @param  string  $item_key      
     * @param  boolean $is_edit       
     * @return [type]                 
     */
    public function create_part_row_template($name = '', $item_id = '', $part_name = '', $description = '', $rate = '', $qty = '', $estimated_qty = '', $tax_id = '', $tax_rate = '', $tax_name = '', $discount = '', $subtotal = '', $item_key = '', $is_edit = false ) {
        
        $row = '';

        $name_item_id = 'item_id';
        $name_part_name = 'name';
        $name_description = 'description';
        $name_rate = 'rate';
        $name_qty = 'qty';
        $name_estimated_qty = 'estimated_qty';
        $name_tax_id = 'tax_id';
        $name_tax_rate = 'tax_rate';
        $name_tax_name = 'tax_name';
        $name_discount = 'discount';
        $name_subtotal = 'subtotal';
        $name_tax_id_select = 'tax_select';

        $array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_estimated_hours_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_discount_attr = [ 'min' => '0.0', 'step' => 'any'];

        if ($name == '') {
            $row .= '<tr class="main hide">
                  <td></td>';
            $manual             = true;
            $invoice_item_taxes = '';

        } else {
            $row .= '<tr class="sortable item">
            <td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';

            $name_item_id = $name . '[item_id]';
            $name_part_name = $name . '[name]';
            $name_description = $name . '[description]';
            $name_rate = $name . '[rate]';
            $name_qty = $name . '[qty]';
            $name_estimated_qty = $name . '[estimated_qty]';
            $name_tax_id = $name . '[tax_id]';
            $name_tax_rate = $name . '[tax_rate]';
            $name_tax_name = $name . '[tax_name]';
            $name_discount = $name . '[discount]';
            $name_subtotal = $name . '[subtotal]';
            $name_tax_id_select = $name . '[tax_select][]';

            $array_rate_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_rate')];
            $array_qty_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_quantity')];
            $array_estimated_hours_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_estimated_hours')];
            $array_product_discount_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'max' => '100', 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('discount')];

            $manual             = false;
            $tax_money = 0;
            $invoice_item_taxes = wshop_convert_item_taxes($tax_id, $tax_rate, $tax_name);

        }


        $row .= '<td class="" width="17%">' . $part_name . '</td>';
        $row .= '<td class="" width="26%">' . $description . '</td>';

        $row .= '<td class="rate" width="8%">' . render_input($name_rate, '', $rate, 'number', $array_rate_attr) . '</td>';
      
        $row .= '<td class="estimated_qty" width="8%">' . render_input($name_estimated_qty, '', $estimated_qty, 'number', $array_qty_attr) . '</td>';
        $row .= '<td class="qty" width="8%">' . render_input($name_qty, '', $qty, 'number', $array_qty_attr) . '</td>';

        $row .= '<td class="taxrate" width="8%">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_key, true, $manual) . '</td>';
        $row .= '<td class="discount" width="9%">' . render_input($name_discount, '', $discount, 'number', $array_product_discount_attr) . '</td>';
        $row .= '<td class="amount" align="right" width="11%">' . $subtotal . '</td>';

        $row .= '<td class="hide">' . render_input($name_item_id, '', $item_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_part_name, '', $part_name, 'text', []) . '</td>';
        $row .= '<td class="hide sub_total">' . render_input($name_subtotal, '', $subtotal, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_textarea($name_description, '', $description, ['rows' => 1, 'placeholder' => _l('item_description_placeholder'), 'oninput' => 'auto_grow(this)'] ) . '</td>';


        if ($name == '') {
            $row .= '<td width="5%"><button type="button" onclick="labour_product_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td width="5%"><a href="#" class="btn btn-danger pull-right" onclick="part_delete_item(this,' . $item_key . ',\'.part-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }
        $row .= '</tr>';
        return $row;
    }

    /**
     * repair job status mark as
     * @param  [type] $status 
     * @param  [type] $id     
     * @param  string $type   
     * @return [type]         
     */
    public function repair_job_status_mark_as($status, $id, $type = 'repair_job')
    {
        $status_f = false;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_repair_jobs', ['status' => $status]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get html tax labour repair job
     * @param  [type]  $id       
     * @param  integer $currency 
     * @return [type]            
     */
    public function get_html_tax_labour_repair_job($id, $currency = 0)
    {
        $html = '';
        $html_currency = '';
        $preview_html = '';
        $pdf_html = '';
        $taxes = [];
        $t_rate = [];
        $tax_val = [];
        $tax_val_rs = [];
        $tax_name = [];
        $rs = [];
        $pdf_html_currency = '';

        $this->load->model('currencies_model');
        $base_currency = $this->currencies_model->get_base_currency();

        $repair_job = $this->get_repair_job($id);
        if(isset($repair_job->repair_job_labour_products)){
            $details = $repair_job->repair_job_labour_products;

            foreach($details as $row){
                if($row['tax_id'] != ''){
                    $tax_arr = new_explode('|', $row['tax_id']);

                    $tax_rate_arr = [];
                    if($row['tax_rate'] != ''){
                        $tax_rate_arr = new_explode('|', $row['tax_rate']);
                    }

                    foreach($tax_arr as $k => $tax_it){
                        if(!isset($tax_rate_arr[$k]) ){
                            $tax_rate_arr[$k] = $this->tax_rate_by_id($tax_it);
                        }

                        if(!in_array($tax_it, $taxes)){
                            $taxes[$tax_it] = $tax_it;
                            $t_rate[$tax_it] = $tax_rate_arr[$k];
                            $tax_name[$tax_it] = $this->get_tax_name($tax_it).' ('.$tax_rate_arr[$k].'%)';
                        }
                    }
                }
            }

            if(count($tax_name) > 0){
                foreach($tax_name as $key => $tn){
                    $tax_val[$key] = 0;
                    foreach($details as $row_dt){
                        if(!(strpos($row_dt['tax_id'] ?? '', $taxes[$key]) === false)){

                            if($row_dt['labour_type'] == 'fixed'){

                                $tax_val[$key] += ($row_dt['qty']*$row_dt['unit_price']*$t_rate[$key]/100);
                            }else{
                                $tax_val[$key] += ($row_dt['qty']*$row_dt['unit_price']*$row_dt['estimated_hours']*$t_rate[$key]/100);
                            }
                        }
                    }
                    $pdf_html .= '<tr id="subtotal"><td ></td><td></td><td></td><td class="text_left">'.$tn.'</td><td class="text_right">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $preview_html .= '<tr id="subtotal"><td>'.$tn.'</td><td>'.app_format_money($tax_val[$key], $currency).'</td><tr>';
                    $html .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $html_currency .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $tax_val_rs[] = $tax_val[$key];
                    $pdf_html_currency .= '<tr ><td align="right" width="85%">'.$tn.'</td><td align="right" width="15%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                }
            }
        }

        $rs['pdf_html'] = $pdf_html;
        $rs['preview_html'] = $preview_html;
        $rs['html'] = $html;
        $rs['taxes'] = $taxes;
        $rs['taxes_val'] = $tax_val_rs;
        $rs['html_currency'] = $html_currency;
        $rs['pdf_html_currency'] = $pdf_html_currency;
        return $rs;
    }

    /**
     * get html tax part repair job
     * @param  [type]  $id       
     * @param  integer $currency 
     * @return [type]            
     */
    public function get_html_tax_part_repair_job($id, $currency = 0)
    {
        $html = '';
        $html_currency = '';
        $preview_html = '';
        $pdf_html = '';
        $taxes = [];
        $t_rate = [];
        $tax_val = [];
        $tax_val_rs = [];
        $tax_name = [];
        $rs = [];
        $pdf_html_currency = '';

        $this->load->model('currencies_model');
        $base_currency = $this->currencies_model->get_base_currency();

        $repair_job = $this->get_repair_job($id);
        if(isset($repair_job->repair_job_labour_materials)){
            $details = $repair_job->repair_job_labour_materials;

            foreach($details as $row){
                if($row['tax_id'] != ''){
                    $tax_arr = new_explode('|', $row['tax_id']);

                    $tax_rate_arr = [];
                    if($row['tax_rate'] != ''){
                        $tax_rate_arr = new_explode('|', $row['tax_rate']);
                    }

                    foreach($tax_arr as $k => $tax_it){
                        if(!isset($tax_rate_arr[$k]) ){
                            $tax_rate_arr[$k] = $this->tax_rate_by_id($tax_it);
                        }

                        if(!in_array($tax_it, $taxes)){
                            $taxes[$tax_it] = $tax_it;
                            $t_rate[$tax_it] = $tax_rate_arr[$k];
                            $tax_name[$tax_it] = $this->get_tax_name($tax_it).' ('.$tax_rate_arr[$k].'%)';
                        }
                    }
                }
            }

            if(count($tax_name) > 0){
                foreach($tax_name as $key => $tn){
                    $tax_val[$key] = 0;
                    foreach($details as $row_dt){
                        if(!(strpos($row_dt['tax_id'] ?? '', $taxes[$key]) === false)){
                            $tax_val[$key] += ($row_dt['qty']*$row_dt['rate']*$t_rate[$key]/100);
                        }
                    }
                    $pdf_html .= '<tr id="subtotal"><td ></td><td></td><td></td><td class="text_left">'.$tn.'</td><td class="text_right">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $preview_html .= '<tr id="subtotal"><td>'.$tn.'</td><td>'.app_format_money($tax_val[$key], $currency).'</td><tr>';
                    $html .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $html_currency .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                    $tax_val_rs[] = $tax_val[$key];
                    $pdf_html_currency .= '<tr ><td align="right" width="85%">'.$tn.'</td><td align="right" width="15%">'.app_format_money($tax_val[$key], $currency).'</td></tr>';
                }
            }
        }

        $rs['pdf_html'] = $pdf_html;
        $rs['preview_html'] = $preview_html;
        $rs['html'] = $html;
        $rs['taxes'] = $taxes;
        $rs['taxes_val'] = $tax_val_rs;
        $rs['html_currency'] = $html_currency;
        $rs['pdf_html_currency'] = $pdf_html_currency;
        return $rs;
    }

    /**
     * get repair job calendar data
     * @param  [type]  $start      
     * @param  [type]  $end        
     * @param  string  $client_id  
     * @param  string  $contact_id 
     * @param  boolean $filters    
     * @return [type]              
     */
    public function get_repair_job_calendar_data($start, $end, $client_id = '', $contact_id = '', $filters = false)
    {
        $is_admin                     = is_admin();
        $data                         = [];

        $ff = false;
        if ($filters) {
            $repair_job_status = [];
            // excluded calendar_filters from post
            $ff = (count($filters) > 1 && isset($filters['calendar_filters']) ? true : false);

            if(isset($filters['Booked_In'])){
                $repair_job_status[] = 'Booked_In';
            }
            if(isset($filters['In_Progress'])){
                $repair_job_status[] = 'In_Progress';
            }
            if(isset($filters['Cancelled'])){
                $repair_job_status[] = 'Cancelled';
            }
            if(isset($filters['Waiting_For_Parts'])){
                $repair_job_status[] = 'Waiting_For_Parts';
            }
            if(isset($filters['Job_Complete'])){
                $repair_job_status[] = 'Job_Complete';
            }
            if(isset($filters['Customer_Notified'])){
                $repair_job_status[] = 'Customer_Notified';
            }
            if(isset($filters['Complete_Awaiting_Finalise'])){
                $repair_job_status[] = 'Complete_Awaiting_Finalise';
            }
            if(isset($filters['Finalised'])){
                $repair_job_status[] = 'Finalised';
            }
            if(isset($filters['Waiting_For_User_Approval'])){
                $repair_job_status[] = 'Waiting_For_User_Approval';
            }
        }

        $this->load->model('projects_model');
        if (!$ff || (array_key_exists('Booked_In', $filters) || array_key_exists('In_Progress', $filters) || array_key_exists('Cancelled', $filters) || array_key_exists('Waiting_For_Parts', $filters) || array_key_exists('Job_Complete', $filters) || array_key_exists('Customer_Notified', $filters) || array_key_exists('Complete_Awaiting_Finalise', $filters) || array_key_exists('Finalised', $filters) || array_key_exists('Waiting_For_User_Approval', $filters))) {
            $repair_jobs = $this->get_all_repair_jobs($start, $end);

            foreach ($repair_jobs as $repair_job) {
                if(isset($repair_job_status) && count($repair_job_status) > 0){
                    if(!in_array($repair_job['status'], $repair_job_status)){
                        continue;
                    }
                }

                if($repair_job['status'] == '' || $repair_job['status'] == null){
                    continue;
                }

                if ($repair_job['staffid'] != get_staff_user_id() && !$is_admin) {
                    $repair_job['is_not_creator'] = true;
                    $repair_job['onclick']        = true;
                }

                $sale_agent_name = '';
                $device_name = '';
                $customer = get_company_name($repair_job['client_id']);
                $appointment_date = $repair_job['appointment_date'];
                $estimated_completeion_date = $repair_job['estimated_completion_date'];
                $issue_description = $repair_job['issue_description'];
                if($repair_job['sale_agent'] != 0){
                    $sale_agent_name = get_staff_full_name($repair_job['sale_agent']);
                }
                $device = $this->workshop_model->get_device($repair_job['device_id']);
                if($device){
                    $device_name = $device->name;
                }
                $status_color          = get_repair_job_status_by_id($repair_job['status'], '');

                $repair_job['_tooltip'] = ' [ ' . $repair_job['title']. ' ] -- ['._l('wshop_mechanic').']:'.$sale_agent_name.' -- ['._l('wshop_device').']:'.$device_name.' -- ['._l('customer').']:'.$customer.' -- ['._l('wshop_appointment_date').']:'.$appointment_date.' -- ['._l('wshop_estimated_completion_date').']:'.$estimated_completeion_date.' -- ['._l('wshop_issue_description').']:'.$issue_description;

                $repair_job['color']    = $status_color['color'];

                $repair_job['className'] = ["event_".$repair_job['status']];
                $repair_job['date'] = date('Y-m-d', strtotime($repair_job['start']));
                $start_day = $repair_job['start'];
                if(isset($repair_job['start'])){
                    $end_hour = date('H:i:s', strtotime($repair_job['end'] ?? ''));
                    if($end_hour == '23:59:59' || is_null($repair_job['end'])){
                        unset($repair_job['start']);
                    }else{
                        $start_hour = '';
                        if(date('Y-m-d', strtotime($start_day)) == date('Y-m-d', strtotime($repair_job['end']))){
                        }
                        $repair_job['title']    = $start_hour.' '.$repair_job['title'];
                    }
                }
                if(isset($repair_job['end']) && (date('Y-m-d', strtotime($start_day)) == date('Y-m-d', strtotime($repair_job['end']))) ){
                    unset($repair_job['end']);
                }
                array_push($data, $repair_job);
            }
        }

        return  $data;
    }

    /**
     * get all repair jobs
     * @param  [type] $start 
     * @param  [type] $end   
     * @return [type]        
     */
    public function get_all_repair_jobs($start, $end)
    {
        $get_staff_user_id = get_staff_user_id();

        $this->db->select('job_tracking_number as title,appointment_date as start,estimated_completion_date as end, status, staffid, sale_agent, device_id, client_id, appointment_date, estimated_completion_date, issue_description');

        // Check if is passed start and end date
        $this->db->group_start();
        $this->db->where('(date(appointment_date) >= "'.$start.'" AND date(appointment_date) <= "'.$end.'")');
        $this->db->or_where('appointment_date is NOT NULL AND (date_format(appointment_date,"%Y-%m") = "'.date('Y-m', strtotime($start)).'")');
        $this->db->or_where('estimated_completion_date is NOT NULL AND (date_format(estimated_completion_date,"%Y-%m") = "'.date('Y-m', strtotime($start)).'")');
        $this->db->group_end();

        if(has_permission('workshop_repair_job', '', 'view')){
                // get all
        }elseif(has_permission('workshop_repair_job', '', 'view_own')){
            $where[] = 'AND '.db_prefix().'wshop_repair_jobs.staffid = '.$get_staff_user_id;
        }else{
            $where[] = 'AND 1=2';
        }
        $this->db->order_by('appointment_date', 'ASC');

        return $this->db->get(db_prefix() . 'wshop_repair_jobs')->result_array();
    }

    /**
     * get part row template
     * @param  [type] $name     
     * @param  [type] $item_id  
     * @param  [type] $quantity 
     * @param  [type] $item_key 
     * @return [type]           
     */
    public function get_part_row_template($name, $item_id, $qty = 0, $item_key = '')
    {
        $name = $name;
        $item_id = $item_id;
        $part_name = '';
        $description = '';
        $rate = 0;
        $estimated_qty = 1;
        $tax_id = '';
        $tax_rate = '';
        $tax_name = '';
        $discount = 0;
        $subtotal = 0;
        $item_key = $item_key;

        $this->load->model('Invoice_items_model');
        $product = $this->Invoice_items_model->get($item_id);
        if($product){
            $tax_id_temp = [];
            $tax_rate_temp = [];
            $tax_name_temp = [];
            $part_name = $product->description;
            $description = $product->long_description;
            if(is_numeric($product->taxid) && $product->taxid != 0){
                $get_tax_name = $product->taxname;
                $get_tax_rate = $product->taxrate;
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $product->taxid;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }

            if(is_numeric($product->taxid_2) && $product->taxid_2 != 0){
                $get_tax_name = $product->taxname_2;
                $get_tax_rate = $product->taxrate_2;
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $product->taxid_2;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }
            $tax_id = implode('|', $tax_id_temp);
            $tax_rate = implode('|', $tax_rate_temp);
            $tax_name = implode('|', $tax_name_temp);
            $rate = $product->rate;
            
            $subtotal = (float)$product->rate * (float)$estimated_qty;
        }

        return $this->workshop_model->create_part_row_template($name, $item_id, $part_name, $description, $rate, $qty, $estimated_qty, $tax_id, $tax_rate, $tax_name, $discount, $subtotal, $item_key, false );
    }

    /**
     * repair job label pdf
     * @param  [type] $repair_job_label 
     * @return [type]                   
     */
    public function repair_job_label_pdf($repair_job_label) {
        return app_pdf('repair_job_label', module_dir_path(WORKSHOP_MODULE_NAME, 'libraries/pdf/repair_job_label_pdf.php'), $repair_job_label);
    }

    /**
     * getBarcode
     * @param  [type] $sample 
     * @return [type]         
     */
    function getBarcode($sample)
    {
        if (!$sample) {
            echo "";
        } else {
            $barcodeobj = new TCPDFBarcode($sample ?? '', 'EAN13');
            $code = $barcodeobj->getBarcodeSVGcode(4, 70, 'black');
            file_put_contents(REPAIR_JOB_BARCODE.md5($sample).'.svg', $code);

            return true;
        }
    }

    /**
     * get transaction
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_transaction($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_return_deliveries')->row();
        }
        if ($id == false) {

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_return_deliveries')->result_array();
        }
    }

    /**
     * add transaction
     * @param [type] $data 
     */
    public function add_transaction($data)
    {

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        if($data['expected_delivery_date'] != ''){
            $data['expected_delivery_date'] = to_sql_date($data['expected_delivery_date']);
        }else{
            $data['expected_delivery_date'] = null;
        }

        $this->db->insert(db_prefix().'wshop_return_deliveries',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
           
            return $insert_id;
        }
        return false;
    }

    /**
     * update transaction
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_transaction($data, $id)
    {
        $affected_rows=0;

        if($data['expected_delivery_date'] != ''){
            $data['expected_delivery_date'] = to_sql_date($data['expected_delivery_date']);
        }else{
            $data['expected_delivery_date'] = null;
        }
        

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_return_deliveries', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete transaction
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_transaction($id)
    {
        $affected_rows = 0;
        
        if (is_dir(TRANSACTION_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(TRANSACTION_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_return_deliveries');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

     /**
     * get note
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_note($id = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_return_delivery_notes')->row();
        }
        if ($id == false) {

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('datecreated', 'DESC');

            return $this->db->get(db_prefix() . 'wshop_return_delivery_notes')->result_array();
        }
    }

    /**
     * add note
     * @param [type] $data 
     */
    public function add_note($data)
    {

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wshop_return_delivery_notes',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
           
            return $insert_id;
        }
        return false;
    }

    /**
     * update note
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_note($data, $id)
    {
        $affected_rows=0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_return_delivery_notes', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete note
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_note($id)
    {
        $affected_rows = 0;
        
        if (is_dir(NOTE_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(NOTE_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_return_delivery_notes');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }

    /**
     * get workshop
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_workshop($id = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_workshops')->row();
        }
        if ($id == false) {

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('name', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_workshops')->result_array();
        }
    }

    /**
     * add workshop
     * @param [type] $data 
     */
    public function add_workshop($data)
    {

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        if($data['from_date'] != ''){
            $data['from_date'] = to_sql_date($data['from_date'], true);
        }else{
            $data['from_date'] = null;
        }
        if($data['to_date'] != ''){
            $data['to_date'] = to_sql_date($data['to_date'], true);
        }else{
            $data['to_date'] = null;
        }
        if(isset($data['visible_to_customer']) && $data['visible_to_customer'] == 'on'){
            $data['visible_to_customer'] = 1;
        }else{
            $data['visible_to_customer'] = 0;
        }

        $this->db->insert(db_prefix().'wshop_workshops',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
           
            return $insert_id;
        }
        return false;
    }

    /**
     * update workshop
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_workshop($data, $id)
    {
        $affected_rows=0;
        if($data['from_date'] != ''){
            $data['from_date'] = to_sql_date($data['from_date'], true);
        }else{
            $data['from_date'] = null;
        }
        if($data['to_date'] != ''){
            $data['to_date'] = to_sql_date($data['to_date'], true);
        }else{
            $data['to_date'] = null;
        }

        if(isset($data['visible_to_customer']) && $data['visible_to_customer'] == 'on'){
            $data['visible_to_customer'] = 1;
        }else{
            $data['visible_to_customer'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_workshops', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete workshop
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_workshop($id)
    {
        $affected_rows = 0;
        
        if (is_dir(WORKSHOP_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(WORKSHOP_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_workshops');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }
    
    /**
     * change workshop status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_workshop_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_workshops', [
            'visible_to_customer' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get inspection
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_inspection($id = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $inspection = $this->db->get(db_prefix() . 'wshop_inspections')->row();

            $this->db->select('*, unit_price as rate');
            $this->db->from(db_prefix() . 'wshop_repair_job_labour_products');
            $this->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_products.inspection_form_detail_id', 'left');
            $this->db->where(db_prefix() . 'wshop_repair_job_labour_products.inspection_id', $id);
            $this->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
            $inspection_labour_products = $this->db->get()->result_array();
            if(count($inspection_labour_products) > 0){
                $inspection->inspection_labour_products = $inspection_labour_products;
            }

            $this->db->from(db_prefix() . 'wshop_repair_job_labour_materials');
            $this->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_materials.inspection_form_detail_id', 'left');
            $this->db->where(db_prefix() . 'wshop_repair_job_labour_materials.inspection_id', $id);
            $this->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
            $inspection_labour_materials = $this->db->get()->result_array();
            if(count($inspection_labour_materials) > 0){
                $inspection->inspection_labour_materials = $inspection_labour_materials;
            }

            return $inspection;
        }
        if ($id == false) {

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('id', 'DESC');

            return $this->db->get(db_prefix() . 'wshop_inspections')->result_array();
        }
    }

    /**
     * add inspection
     * @param [type] $data 
     */
    public function add_inspection($data)
    {

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        $data['hash'] = app_generate_hash();
        $data['prefix'] = get_option('wshop_inspection_prefix');
        $data['number_format'] = get_option('wshop_inspection_number_format');
        $data['status'] = 'Open';

        if($data['start_date'] != ''){
            $data['start_date'] = to_sql_date($data['start_date'], true);
        }else{
            $data['start_date'] = null;
        }
        if($data['end_date'] != ''){
            $data['end_date'] = to_sql_date($data['end_date'], true);
        }else{
            $data['end_date'] = null;
        }
        if($data['next_inspection_date'] != ''){
            $data['next_inspection_date'] = to_sql_date($data['next_inspection_date'], true);
            $next_maintenance = date('Y-m-d', strtotime($data['next_inspection_date']));
        }else{
            $data['next_inspection_date'] = null;
        }
        
        if(isset($data['visible_to_customer']) && $data['visible_to_customer'] == 'on'){
            $data['visible_to_customer'] = 1;
        }else{
            $data['visible_to_customer'] = 0;
        }

        $this->db->insert(db_prefix().'wshop_inspections',$data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update next repair job number in settings
            $this->db->where('name', 'wshop_inspection_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            // update device maintenance date
            $update_device_maintenance = [];
            $update_device_maintenance['last_maintenance'] = date('Y-m-d');
            if(isset($next_maintenance)){
                $update_device_maintenance['next_maintenance'] = $next_maintenance;
            }
            $this->db->where('id', $data['device_id']);
            $this->db->update(db_prefix() . 'wshop_devices', $update_device_maintenance);

            return $insert_id;
        }
        return false;
    }

    /**
     * update inspection
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_inspection($data, $id)
    {
        $affected_rows=0;
        if($data['start_date'] != ''){
            $data['start_date'] = to_sql_date($data['start_date']);
        }else{
            $data['start_date'] = null;
        }
        if($data['end_date'] != ''){
            $data['end_date'] = to_sql_date($data['end_date']);
        }else{
            $data['end_date'] = null;
        }
        if($data['next_inspection_date'] != ''){
            $data['next_inspection_date'] = to_sql_date($data['next_inspection_date'], true);
            $next_maintenance = date('Y-m-d', strtotime($data['next_inspection_date']));
        }else{
            $data['next_inspection_date'] = null;
        }

        if(isset($data['visible_to_customer']) && $data['visible_to_customer'] == 'on'){
            $data['visible_to_customer'] = 1;
        }else{
            $data['visible_to_customer'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspections', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        // update device maintenance date
        if(isset($next_maintenance)){
            $update_device_maintenance = [];
            $update_device_maintenance['next_maintenance'] = $next_maintenance;
            $this->db->where('id', $data['device_id']);
            $this->db->update(db_prefix() . 'wshop_devices', $update_device_maintenance);

            if ($this->db->affected_rows() > 0) {
                $affected_rows++;
            }
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * delete inspection
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_inspection($id)
    {
        $affected_rows = 0;
        
        if (is_dir(INSPECTION_FOLDER. $id)) {
                // okey only index.html so we can delete the folder also
            delete_dir(INSPECTION_FOLDER. $id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wshop_inspections');
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        if($affected_rows > 0){
            return true;
        }
        return false;
    }
    
    /**
     * change inspection status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_inspection_visible($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspections', [
            'visible_to_customer' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * inspection status mark_as
     * @param  [type] $status 
     * @param  [type] $id     
     * @param  string $type  
      * @return [type]         
     */
    public function inspection_status_mark_as($status, $id, $type = 'inspection')
    {
        $status_f = false;
        $commpleted_date = NULL;
        if($status == 'Completed'){
            $commpleted_date = date('Y-m-d');
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspections', ['status' => $status, 'commpleted_date' => $commpleted_date]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * insert inpsection template
     * @param  [type] $id 
     * @return [type]     
     */
    public function insert_inpsection_template($id)
    {
        $_affectedRows = 0;
        $inspection = $this->get_inspection($id);
        if($inspection){
            $inspection_template_id = $inspection->inspection_template_id;
            $inspection_template = $this->get_inspection_template($inspection_template_id);
            if($inspection->status == 'Open'){
                $new_status = 'In_Progress';
            }else{
                $new_status = $inspection->status;
            }

            if($inspection_template && is_null($inspection->inspection_template_name)){
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'wshop_inspections', ['inspection_template_name' => $inspection_template->name, 'status' => $new_status]);
                // get inspection template form
                $inspection_template_forms = $this->get_inspection_template_form(false, true, ['inspection_template_id' => $inspection_template->id]);

                foreach ($inspection_template_forms as $key => $inspection_template_form) {
                    $inspection_template_form_id = $inspection_template_form['id'];
                    if(isset($inspection_template_form['id'])){
                        unset($inspection_template_form['id']);
                    }
                    if(isset($inspection_template_form['datecreated'])){
                        unset($inspection_template_form['datecreated']);
                    }
                    if(isset($inspection_template_form['inspection_template_id'])){
                        unset($inspection_template_form['inspection_template_id']);
                    }
                    $inspection_form_data = [];
                    
                    $inspection_form_data = $inspection_template_form;
                    $inspection_form_data['datecreated'] = date('Y-m-d H:i:s');
                    $inspection_form_data['inspection_id'] = $id;
                    $this->db->insert(db_prefix() . 'wshop_inspection_forms', $inspection_form_data);
                    $inspection_form_id = $this->db->insert_id();

                    if ($inspection_form_id) {
                        $_affectedRows++;
                        // get inspection template form detail
                        $inspection_template_form_details = $this->get_inspection_template_form_detail(false, true, ['inspection_template_form_id' => $inspection_template_form_id]);

                        $inspection_form_detail_data = [];

                        foreach ($inspection_template_form_details as $key => $inspection_template_form_detail) {
                            $old_field_to = $inspection_template_form_detail['fieldto'];
                            $new_field_to = 'form_fieldset_'.$inspection_form_id;

                            $inspection_template_form_detail['fieldto'] = str_replace($old_field_to ?? '', $new_field_to ?? '', $inspection_template_form_detail['fieldto'] ?? '');
                            $inspection_template_form_detail['slug'] = str_replace($old_field_to ?? '', $new_field_to ?? '', $inspection_template_form_detail['slug'] ?? '');

                            if(isset($inspection_template_form_detail['id'])){
                                unset($inspection_template_form_detail['id']);
                            }
                            if(isset($inspection_template_form_detail['inspection_template_form_id'])){
                                unset($inspection_template_form_detail['inspection_template_form_id']);
                            }

                            $inspection_template_form_detail['inspection_form_id'] = $inspection_form_id;
                            $inspection_form_detail_data[] = $inspection_template_form_detail;    
                        }

                        if(count($inspection_form_detail_data) > 0){
                            $affectedRows = $this->db->insert_batch(db_prefix() . 'wshop_inspection_form_details', $inspection_form_detail_data);
                            if ($affectedRows > 0) {
                                $_affectedRows++;
                            }
                        }
                    }
                }
            }
        }

        if($_affectedRows > 0){
            return true;
        }
        return false;
    }

    /**
     * get inspection form
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_inspection_form($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_inspection_forms')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('status', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('form_order', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_inspection_forms')->result_array();
        }
    }

    /**
     * get inspection form detail
     * @param  boolean $id     
     * @param  boolean $active 
     * @param  array   $where  
     * @return [type]          
     */
    public function get_inspection_form_detail($id = false, $active = false, $where = []) {

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wshop_inspection_form_details')->row();
        }
        if ($id == false) {
            if($active){
                $this->db->where('active', 1);
            }
            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $this->db->where($where);
            }
            $this->db->order_by('field_order', 'ASC');

            return $this->db->get(db_prefix() . 'wshop_inspection_form_details')->result_array();
        }
    }

    /**
     * update inspection labour and part
     * @param  [type] $data 
     * @param  [type] $id   
     * @return [type]       
     */
    public function update_inspection_labour_and_part($data, $id)
    {
        $repair_job_id = 0;
        $affected_rows=0;
        $newlabouritems = [];
        $update_labouritems = [];
        $remove_labouritems = [];
        $newpartitems = [];
        $update_partitems = [];
        $remove_partitems = [];
        $get_inspection = $this->get_inspection($id);
        if($get_inspection){
            $repair_job_id = (int)$get_inspection->repair_job_id;
        }

        if(isset($data['isedit'])){
            unset($data['isedit']);
        }

        if (isset($data['newlabouritems'])) {
            $newlabouritems = $data['newlabouritems'];
            unset($data['newlabouritems']);
        }
        if (isset($data['labouritems'])) {
            $update_labouritems = $data['labouritems'];
            unset($data['labouritems']);
        }
        if (isset($data['removed_labour_product_items'])) {
            $remove_labouritems = $data['removed_labour_product_items'];
            unset($data['removed_labour_product_items']);
        }

        if (isset($data['newpartitems'])) {
            $newpartitems = $data['newpartitems'];
            unset($data['newpartitems']);
        }
        if (isset($data['partitems'])) {
            $update_partitems = $data['partitems'];
            unset($data['partitems']);
        }
        if (isset($data['removed_part_items'])) {
            $remove_partitems = $data['removed_part_items'];
            unset($data['removed_part_items']);
        }
        
        if (isset($data['field_order'])) {
            unset($data['field_order']);
        }
        if (isset($data['estimated_hours'])) {
            unset($data['estimated_hours']);
        }
        
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wshop_inspections', $data);
        if ($this->db->affected_rows() > 0) {
            $affected_rows++;
        }

        foreach ($update_labouritems as $labouritem) {
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;
            if(isset($labouritem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($labouritem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $labouritem['tax_id'] = $tax_id;
            $labouritem['tax_rate'] = $tax_rate;
            $labouritem['tax_name'] = $tax_name;

            unset($labouritem['order']);
            unset($labouritem['tax_select']);

            $this->db->where('id', $labouritem['id']);
            if ($this->db->update(db_prefix() . 'wshop_repair_job_labour_products', $labouritem)) {
                $affected_rows++;
            }
        }

       // delete labour product
        foreach ($remove_labouritems as $labouritem) {
            $this->db->where('id', $labouritem);
            if ($this->db->delete(db_prefix() . 'wshop_repair_job_labour_products')) {
                $affected_rows++;
            }
        }

        // add labour product
        foreach ($newlabouritems as $labouritem) {
            $labouritem['repair_job_id'] = $id;
            $labouritem['item_order'] = $labouritem['order'];
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;

            if(isset($labouritem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($labouritem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $labouritem['tax_id'] = $tax_id;
            $labouritem['tax_rate'] = $tax_rate;
            $labouritem['tax_name'] = $tax_name;
            $labouritem['repair_job_id'] = $repair_job_id;

            unset($labouritem['order']);
            unset($labouritem['id']);
            unset($labouritem['tax_select']);
            $this->db->insert(db_prefix() . 'wshop_repair_job_labour_products', $labouritem);
            if($this->db->insert_id()){
                $affected_rows++;
            }
        }

        foreach ($update_partitems as $partitem) {
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;
            if(isset($partitem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($partitem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $partitem['tax_id'] = $tax_id;
            $partitem['tax_rate'] = $tax_rate;
            $partitem['tax_name'] = $tax_name;

            unset($partitem['order']);
            unset($partitem['tax_select']);

            $this->db->where('id', $partitem['id']);
            if ($this->db->update(db_prefix() . 'wshop_repair_job_labour_materials', $partitem)) {
                $affected_rows++;
            }
        }

       // delete labour product
        foreach ($remove_partitems as $partitem) {
            $this->db->where('id', $partitem);
            if ($this->db->delete(db_prefix() . 'wshop_repair_job_labour_materials')) {
                $affected_rows++;
            }
        }

        foreach ($newpartitems as $partitem) {
            $partitem['repair_job_id'] = $id;
            $partitem['item_order'] = $partitem['order'];
            $tax_id = null;
            $tax_rate = null;
            $tax_name = null;

            if(isset($partitem['tax_select'])){
                $tax_rate_data = $this->wshop_get_tax_rate($partitem['tax_select']);
                $tax_id = $tax_rate_data['tax_id_str'];
                $tax_rate = $tax_rate_data['tax_rate_str'];
                $tax_name = $tax_rate_data['tax_name_str'];
            }
            $partitem['tax_id'] = $tax_id;
            $partitem['tax_rate'] = $tax_rate;
            $partitem['tax_name'] = $tax_name;
            $partitem['repair_job_id'] = $repair_job_id;

            unset($partitem['order']);
            unset($partitem['id']);
            unset($partitem['tax_select']);
            $this->db->insert(db_prefix() . 'wshop_repair_job_labour_materials', $partitem);
        }

        if($affected_rows > 0){
            return true;
        }

        return false;  
    }

    /**
     * add edit inspection form
     * @param [type] $data          
     * @param [type] $inspection_id 
     */
    public function add_edit_inspection_form($data, $inspection_id)
    {

        $inspection_result = Arr::pull($data, 'inspection_result') ?? [];
        $inspection_comment = Arr::pull($data, 'inspection_comment') ?? [];
        $custom_fields = Arr::pull($data, 'custom_fields') ?? [];
        $attachment_files = $_FILES['custom_fields'] ?? [];
        if ($inspection_id) {
            wshop_handle_inspection_form_fields_post($inspection_id, $custom_fields, true, $inspection_result, $inspection_comment);
            if(count($attachment_files) > 0){
                wshop_handle_inspection_form_attachment_post($inspection_id, $attachment_files, true);
            }
            $this->update_inspection_labour_and_part($data, $inspection_id);

            return $inspection_id;
        }

        return false;
    }

    /**
     * inspection create labour product row template
     * @param  string  $name                      
     * @param  string  $labour_product_id         
     * @param  string  $product_name              
     * @param  string  $description               
     * @param  string  $inspection_id             
     * @param  string  $inspection_form_id        
     * @param  string  $inspection_form_detail_id 
     * @param  string  $labour_type               
     * @param  string  $estimated_hours           
     * @param  string  $unit_price                
     * @param  string  $qty                       
     * @param  string  $tax_id                    
     * @param  string  $tax_rate                  
     * @param  string  $tax_name                  
     * @param  string  $discount                  
     * @param  string  $subtotal                  
     * @param  string  $item_id                   
     * @param  boolean $is_edit                   
     * @return [type]                             
     */
    public function inspection_create_labour_product_row_template($name = '', $labour_product_id = '', $product_name = '', $description = '', $inspection_id = '', $inspection_form_id = '', $inspection_form_detail_id = '', $labour_type = '', $estimated_hours = '', $unit_price = '', $qty = '', $tax_id = '', $tax_rate = '', $tax_name = '', $discount = '', $subtotal = '', $item_id = '', $is_edit = false ) {
        
        $row = '';

        $name_labour_product_id = 'labour_product_id';
        $name_product_name = 'name';
        $name_description = 'description';
        $name_inspection_id = 'inspection_id';
        $name_inspection_form_id = 'inspection_form_id';
        $name_inspection_form_detail_id = 'inspection_form_detail_id';

        $name_labour_type = 'labour_type';
        $name_estimated_hours = 'estimated_hours';
        $name_unit_price = 'unit_price';
        $name_qty = 'qty';
        $name_tax_id = 'tax_id';
        $name_tax_rate = 'tax_rate';
        $name_tax_name = 'tax_name';
        $name_discount = 'discount';
        $name_subtotal = 'subtotal';
        $name_tax_id_select = 'tax_select';

        $array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_estimated_hours_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_discount_attr = [ 'min' => '0.0', 'step' => 'any'];

        if ($name == '') {
            $row .= '<tr class="main hide">
                  <td></td>';
            $manual             = true;
            $invoice_item_taxes = '';

        } else {
            $row .= '<tr class="sortable item">
            <td class="dragger" width=1%"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_id . '"></td>';

            $name_labour_product_id = $name . '[labour_product_id]';
            $name_product_name = $name . '[name]';
            $name_description = $name . '[description]';
            $name_inspection_id = $name . '[inspection_id]';
            $name_inspection_form_id = $name . '[inspection_form_id]';
            $name_inspection_form_detail_id = $name . '[inspection_form_detail_id]';
            $name_labour_type = $name . '[labour_type]';
            $name_estimated_hours = $name . '[estimated_hours]';
            $name_unit_price = $name . '[unit_price]';
            $name_qty = $name . '[qty]';
            $name_tax_id = $name . '[tax_id]';
            $name_tax_rate = $name . '[tax_rate]';
            $name_tax_name = $name . '[tax_name]';
            $name_discount = $name . '[discount]';
            $name_subtotal = $name . '[subtotal]';
            $name_tax_id_select = $name . '[tax_select][]';

            $array_rate_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_unit_price'), 'readonly' => true];
            $array_qty_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_quantity')];
            $array_estimated_hours_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_estimated_hours')];
            $array_product_discount_attr = ['onblur' => 'labour_product_calculate_total();', 'onchange' => 'labour_product_calculate_total();', 'min' => '0.0' , 'max' => '100', 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('discount')];

            $manual             = false;
            $tax_money = 0;
            $invoice_item_taxes = wshop_convert_item_taxes($tax_id, $tax_rate, $tax_name);

        }


        $row .= '<td class="" width="35%">' . $product_name . '</td>';
        $row .= '<td class="hide" width="26%">' . $description . '</td>';

        $row .= '<td class="unit_price" width="10%">' . render_input($name_unit_price, '', $unit_price, 'number', $array_rate_attr) . '</td>';
        $row .= '<td class="estimated_hours" width="10%">' . 
        render_input($name_estimated_hours, '', $estimated_hours, 'number', $array_estimated_hours_attr, [], 'no-margin').
         '</td>';
        $row .= '<td class="qty" width="10%">' . render_input($name_qty, '', $qty, 'number', $array_qty_attr) . '</td>';

        $row .= '<td class="taxrate" width="10%">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_id, true, $manual) . '</td>';
        $row .= '<td class="discount hide" width="9%">' . render_input($name_discount, '', $discount, 'number', $array_product_discount_attr) . '</td>';
        $row .= '<td class="amount" align="right" width="11%">' . $subtotal . '</td>';

        $row .= '<td class="hide">' . render_input($name_labour_product_id, '', $labour_product_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_id, '', $inspection_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_form_id, '', $inspection_form_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_form_detail_id, '', $inspection_form_detail_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_product_name, '', $product_name, 'text', []) . '</td>';
        $row .= '<td class="hide labour_type">' . render_input($name_labour_type, '', $labour_type, 'text', []) . '</td>';
        $row .= '<td class="hide sub_total">' . render_input($name_subtotal, '', $subtotal, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_textarea($name_description, '', $description, ['rows' => 1, 'placeholder' => _l('item_description_placeholder'), 'oninput' => 'auto_grow(this)'] ) . '</td>';


        if ($name == '') {
            $row .= '<td width="5%"><button type="button" onclick="labour_product_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td width="5%"><a href="#" class="btn btn-danger pull-right" onclick="labour_product_delete_item(this,' . $item_id . ',\'.labour_product-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }
        $row .= '</tr>';
        return $row;
    }

    /**
     * inspection get part row template
     * @param  [type] $name                      
     * @param  [type] $item_id                   
     * @param  [type] $inspection_id             
     * @param  [type] $inspection_form_id        
     * @param  [type] $inspection_form_detail_id 
     * @param  [type] $quantity                  
     * @param  [type] $item_key                  
     * @return [type]                            
     */
    public function inspection_get_part_row_template($name, $item_id, $inspection_id, $inspection_form_id, $inspection_form_detail_id, $qty, $item_key)
    {
        $name = $name;
        $item_id = $item_id;
        $part_name = '';
        $description = '';
        $rate = 0;
        $estimated_qty = 1;
        $tax_id = '';
        $tax_rate = '';
        $tax_name = '';
        $discount = 0;
        $subtotal = 0;
        $item_key = $item_key;

        $this->load->model('Invoice_items_model');
        $product = $this->Invoice_items_model->get($item_id);
        if($product){
            $tax_id_temp = [];
            $tax_rate_temp = [];
            $tax_name_temp = [];
            $part_name = $product->description;
            $description = $product->long_description;
            if(is_numeric($product->taxid) && $product->taxid != 0){
                $get_tax_name = $product->taxname;
                $get_tax_rate = $product->taxrate;
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $product->taxid;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }

            if(is_numeric($product->taxid_2) && $product->taxid_2 != 0){
                $get_tax_name = $product->taxname_2;
                $get_tax_rate = $product->taxrate_2;
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $product->taxid_2;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }
            $tax_id = implode('|', $tax_id_temp);
            $tax_rate = implode('|', $tax_rate_temp);
            $tax_name = implode('|', $tax_name_temp);
            $rate = $product->rate;
            
            $subtotal = (float)$product->rate * (float)$estimated_qty;
        }

        return $this->inspection_create_part_row_template($name, $item_id, $part_name, $description, $inspection_id, $inspection_form_id, $inspection_form_detail_id, $rate, $qty, $estimated_qty, $tax_id, $tax_rate, $tax_name, $discount, $subtotal, $item_key, false );
    }

    /**
     * create part row template
     * @param  string  $name          
     * @param  string  $item_id       
     * @param  string  $part_name     
     * @param  string  $description   
     * @param  string  $rate          
     * @param  string  $qty           
     * @param  string  $estimated_qty 
     * @param  string  $tax_id        
     * @param  string  $tax_rate      
     * @param  string  $tax_name      
     * @param  string  $discount      
     * @param  string  $subtotal      
     * @param  string  $item_key      
     * @param  boolean $is_edit       
     * @return [type]                 
     */
    public function inspection_create_part_row_template($name = '', $item_id = '', $part_name = '', $description = '', $inspection_id = '', $inspection_form_id = '', $inspection_form_detail_id = '', $rate = '', $qty = '', $estimated_qty = '', $tax_id = '', $tax_rate = '', $tax_name = '', $discount = '', $subtotal = '', $item_key = '', $is_edit = false ) {
        
        $row = '';

        $name_item_id = 'item_id';
        $name_part_name = 'name';
        $name_description = 'description';
        $name_inspection_id = 'inspection_id';
        $name_inspection_form_id = 'inspection_form_id';
        $name_inspection_form_detail_id = 'inspection_form_detail_id';

        $name_rate = 'rate';
        $name_qty = 'qty';
        $name_estimated_qty = 'estimated_qty';
        $name_tax_id = 'tax_id';
        $name_tax_rate = 'tax_rate';
        $name_tax_name = 'tax_name';
        $name_discount = 'discount';
        $name_subtotal = 'subtotal';
        $name_tax_id_select = 'tax_select';

        $array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_estimated_hours_attr = [ 'min' => '0.0', 'step' => 'any'];
        $array_discount_attr = [ 'min' => '0.0', 'step' => 'any'];

        if ($name == '') {
            $row .= '<tr class="main hide">
                  <td></td>';
            $manual             = true;
            $invoice_item_taxes = '';

        } else {
            $row .= '<tr class="sortable item">
            <td class="dragger" width=1%"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';

            $name_item_id = $name . '[item_id]';
            $name_part_name = $name . '[name]';
            $name_description = $name . '[description]';
            $name_inspection_id = $name . '[inspection_id]';
            $name_inspection_form_id = $name . '[inspection_form_id]';
            $name_inspection_form_detail_id = $name . '[inspection_form_detail_id]';
            $name_rate = $name . '[rate]';
            $name_qty = $name . '[qty]';
            $name_estimated_qty = $name . '[estimated_qty]';
            $name_tax_id = $name . '[tax_id]';
            $name_tax_rate = $name . '[tax_rate]';
            $name_tax_name = $name . '[tax_name]';
            $name_discount = $name . '[discount]';
            $name_subtotal = $name . '[subtotal]';
            $name_tax_id_select = $name . '[tax_select][]';

            $array_rate_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_rate'), 'readonly' => true];
            $array_qty_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_quantity')];
            $array_estimated_hours_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('wshop_estimated_hours')];
            $array_product_discount_attr = ['onblur' => 'part_calculate_total();', 'onchange' => 'part_calculate_total();', 'min' => '0.0' , 'max' => '100', 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('discount')];

            $manual             = false;
            $tax_money = 0;
            $invoice_item_taxes = wshop_convert_item_taxes($tax_id, $tax_rate, $tax_name);

        }


        $row .= '<td class="" width="35%">' . $part_name . '</td>';
        $row .= '<td class="hide" width="26%">' . $description . '</td>';

        $row .= '<td class="rate" width="10%">' . render_input($name_rate, '', $rate, 'number', $array_rate_attr) . '</td>';
      
        $row .= '<td class="estimated_qty hide" width="10%">' . render_input($name_estimated_qty, '', $estimated_qty, 'number', $array_qty_attr) . '</td>';
        $row .= '<td class="qty" width="10%">' . render_input($name_qty, '', $qty, 'number', $array_qty_attr) . '</td>';

        $row .= '<td class="taxrate" width="10%">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_key, true, $manual) . '</td>';
        $row .= '<td class="discount hide" width="9%">' . render_input($name_discount, '', $discount, 'number', $array_product_discount_attr) . '</td>';
        $row .= '<td class="amount" align="right" width="11%">' . $subtotal . '</td>';

        $row .= '<td class="hide">' . render_input($name_item_id, '', $item_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_id, '', $inspection_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_form_id, '', $inspection_form_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_inspection_form_detail_id, '', $inspection_form_detail_id, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_input($name_part_name, '', $part_name, 'text', []) . '</td>';
        $row .= '<td class="hide sub_total">' . render_input($name_subtotal, '', $subtotal, 'number', []) . '</td>';
        $row .= '<td class="hide">' . render_textarea($name_description, '', $description, ['rows' => 1, 'placeholder' => _l('item_description_placeholder'), 'oninput' => 'auto_grow(this)'] ) . '</td>';


        if ($name == '') {
            $row .= '<td width="5%"><button type="button" onclick="labour_product_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td width="5%"><a href="#" class="btn btn-danger pull-right" onclick="part_delete_item(this,' . $item_key . ',\'.part-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }
        $row .= '</tr>';
        return $row;
    }

    /**
     * get_product
     * @param  boolean $id 
     * @return [type]      
     */
    public function get_product($id = false)
    {

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'items')->row();
        }
        if ($id == false) {
            $sql_where = db_prefix().'items.id not in ( SELECT distinct parent_id from '.db_prefix().'items WHERE parent_id is not null AND parent_id != "0" )';
            $this->db->select('*, CONCAT(commodity_code, "_", description) as description');

            $this->db->where($sql_where);
            return $this->db->get(db_prefix() . 'items')->result_array();
        }

    }

    /**
     * check parts available
     * @param  [type] $id   
     * @param  [type] $type 
     * @return [type]       
     */
    public function check_parts_available($id, $type)
    {   
        $result=[];
        $affected_rows=0;
        $flag = 0;
        $part_to_purchase = [];
        $check_availability='';
        $check_availability_message='';
        $warehouse_id='';

        if($type == 'repair_job'){
            $this->db->where('repair_job_id', $id);
            $this->db->where('inspection_id', 0);
            $parts = $this->db->get(db_prefix().'wshop_repair_job_labour_materials')->result_array();

        }else{
            // inspection
            $this->db->from(db_prefix() . 'wshop_repair_job_labour_materials');
            $this->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_materials.inspection_form_detail_id', 'left');
            $this->db->where(db_prefix() . 'wshop_repair_job_labour_materials.inspection_id', $id);
            $this->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
            $parts = $this->db->get()->result_array();
        }

        if($parts){
            foreach ($parts as $part) {
                $flag_inventory = 0;

                $commodity_name='';
                $item_value = $this->get_product($part['item_id']);

                if($item_value){
                    $commodity_name .= $item_value->description;
                }

                $sql = 'SELECT  sum(inventory_number) as inventory_number from ' . db_prefix() . 'inventory_manage where commodity_id = ' . $part['item_id'];

                $value = $this->db->query($sql)->row();

                if ($value) {
                    $inventory_number = $value->inventory_number;

                    if ((float)$value->inventory_number < (float) $part['qty']) {
                        $flag = 1;
                        $flag_inventory = 1;

                        $check_availability_message .= $commodity_name.' '._l('not_enough_inventory').', '._l('available_quantity').': '.(float) $value->inventory_number.'/'.$part['qty'].'<br/>';

                        $part_to_purchase[$part['id']] = [
                            'id' =>$part['id'], 
                            'item_id' =>$part['item_id'],
                            'qty' =>$part['qty'],
                            'quantity_to_purchase' => (float)$part['qty'] - (float)$value->inventory_number,
                        ];

                    }
                } else {
                    $flag = 1;
                    $flag_inventory = 1;

                    $check_availability_message .=$commodity_name.' '. _l('Product_does_not_exist_in_stock').'<br/>';
                    $part_to_purchase[$part['id']] = [
                        'id' =>$part['id'], 
                        'item_id' =>$part['item_id'],
                        'qty' =>$part['qty'],
                        'quantity_to_purchase' => (float)$part['qty'],
                    ];
                }

            }
        }

        if($flag == 1){
            $status = false;
            $message = _l('wshop_parts').'<br>'.$check_availability_message;
        }else{
            $status = true;
            $message = '';
        }

        $result['status'] = $status;
        $result['message'] = $message;
        $result['part_to_purchase'] = $part_to_purchase;

        return $result;
    }

    /**
     * convert transaction to invoice
     * @param  [type]  $id            
     * @param  [type]  $type          
     * @param  boolean $client        
     * @param  boolean $draft_invoice 
     * @return [type]                 
     */
    // TODO
    public function convert_transaction_to_invoice($id, $type, $client = false, $draft_invoice = false)
    {
        $this->load->model('clients_model');
        if($type == 'repair_job'){
            $this->db->where('repair_job_id', $id);
            $this->db->where('inspection_id', 0);
            $parts = $this->db->get(db_prefix().'wshop_repair_job_labour_materials')->result_array();
            $transaction = $this->get_repair_job($id);

            $this->db->select('*, unit_price as rate');
            $this->db->where('repair_job_id', $id);
            $this->db->where('inspection_id', 0);
            $labour_products = $this->db->get(db_prefix().'wshop_repair_job_labour_products')->result_array();

            $sale_agent = $transaction->sale_agent;
            $billing_street = $transaction->billing_street;
            $billing_city = $transaction->billing_city;
            $billing_state = $transaction->billing_state;
            $billing_zip = $transaction->billing_zip;
            $billing_country = $transaction->billing_country;
            $shipping_street = $transaction->shipping_street;
            $shipping_city = $transaction->shipping_city;
            $shipping_state = $transaction->shipping_state;
            $shipping_zip = $transaction->shipping_zip;
            $shipping_country = $transaction->shipping_country;

        }else{
            // inspection
            $this->db->from(db_prefix() . 'wshop_repair_job_labour_materials');
            $this->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_materials.inspection_form_detail_id', 'left');
            $this->db->where(db_prefix() . 'wshop_repair_job_labour_materials.inspection_id', $id);
            $this->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
            $parts = $this->db->get()->result_array();

            if(false){
                $this->db->where('inspection_id', $id);
                $parts = $this->db->get(db_prefix().'wshop_repair_job_labour_materials')->result_array();
            }

            $transaction = $this->get_inspection($id);

            $this->db->select('*, unit_price as rate');
            $this->db->from(db_prefix() . 'wshop_repair_job_labour_products');
            $this->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_products.inspection_form_detail_id', 'left');
            $this->db->where(db_prefix() . 'wshop_repair_job_labour_products.inspection_id', $id);
            $this->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
            $labour_products = $this->db->get()->result_array();

            if(false){
                $this->db->select('*, unit_price as rate');
                $this->db->where('inspection_id', $id);
                $labour_products = $this->db->get(db_prefix().'wshop_repair_job_labour_products')->result_array();
            }

            $client = $this->clients_model->get($transaction->client_id);

            $sale_agent = $transaction->person_in_charge;
            $billing_street = $client->billing_street;
            $billing_city = $client->billing_city;
            $billing_state = $client->billing_state;
            $billing_zip = $client->billing_zip;
            $billing_country = $client->billing_country;
            $shipping_street = $client->shipping_street;
            $shipping_city = $client->shipping_city;
            $shipping_state = $client->shipping_state;
            $shipping_zip = $client->shipping_zip;
            $shipping_country = $client->shipping_country;
        }

        $new_invoice_data = [];
        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }
        $new_invoice_data['clientid']   = $transaction->client_id;
        $new_invoice_data['project_id'] = 0;
        $new_invoice_data['number']     = get_option('next_invoice_number');
        $new_invoice_data['date']       = _d(date('Y-m-d'));
        $new_invoice_data['duedate']    = _d(date('Y-m-d'));
        if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }
        $new_invoice_data['show_quantity_as'] = 1;
        $new_invoice_data['currency']         = $transaction->currency;
        $new_invoice_data['subtotal']         = $transaction->subtotal;
        $new_invoice_data['total']            = $transaction->total;
        $new_invoice_data['adjustment']       = $transaction->discount_total;
        $new_invoice_data['discount_percent'] = 0;
        $new_invoice_data['discount_total']   = 0;
        $new_invoice_data['discount_type']    = '';
        $new_invoice_data['sale_agent']       = $sale_agent;
        $new_invoice_data['addedfrom']        = $transaction->staffid;
        // Since version 1.0.6
        $new_invoice_data['billing_street']   = $billing_street;
        $new_invoice_data['billing_city']     = $billing_city;
        $new_invoice_data['billing_state']    = $billing_state;
        $new_invoice_data['billing_zip']      = $billing_zip;
        $new_invoice_data['billing_country']  = $billing_country;
        $new_invoice_data['shipping_street']  = $shipping_street;
        $new_invoice_data['shipping_city']    = $shipping_city;
        $new_invoice_data['shipping_state']   = $shipping_state;
        $new_invoice_data['shipping_zip']     = $shipping_zip;
        $new_invoice_data['shipping_country'] = $shipping_country;
        $new_invoice_data['include_shipping'] = 0;

        $new_invoice_data['show_shipping_on_invoice'] = 0;
        $new_invoice_data['terms']                    = get_option('predefined_terms_invoice');
        $new_invoice_data['clientnote']               = get_option('predefined_clientnote_invoice');
        // Set to unpaid status automatically
        $new_invoice_data['status']    = 1;
        $new_invoice_data['adminnote'] = '';

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }
        $new_invoice_data['allowed_payment_modes'] = $temp_modes;
        $new_invoice_data['newitems']              = [];

        $item_key                                       = 1;
        foreach ($parts as $index => $item) {

            $description = $item['name'];
            $long_description = $item['description'];

            $taxvalue = [];
            $tax_rates = new_explode('|', $item['tax_rate']);
            $tax_names = new_explode('|', $item['tax_name']);
            $tax_ids = new_explode('|', $item['tax_id']);

            foreach ($tax_names as $key => $tax_name) {
                $tax_rate = isset($tax_rates[$key]) ? $tax_rates[$key] : 0;
                $taxvalue[] = $tax_name. '|' .$tax_rate;
            }

            $new_invoice_data['newitems'][$item_key]['description']      = $description;
            $new_invoice_data['newitems'][$item_key]['long_description'] = $long_description;
            $new_invoice_data['newitems'][$item_key]['qty']              = $item['qty'];
            $new_invoice_data['newitems'][$item_key]['unit']             = '';
            $new_invoice_data['newitems'][$item_key]['taxname']          = $taxvalue;
            $new_invoice_data['newitems'][$item_key]['rate']  = $item['rate'];
            $new_invoice_data['newitems'][$item_key]['order'] = $index;
            
            $item_key++;
        }

        foreach ($labour_products as $index => $item) {

            $description = $item['name'];
            $long_description = $item['description'];

            $taxvalue = [];
            $tax_rates = new_explode('|', $item['tax_rate']);
            $tax_names = new_explode('|', $item['tax_name']);
            $tax_ids = new_explode('|', $item['tax_id']);

            foreach ($tax_names as $key => $tax_name) {
                $tax_rate = isset($tax_rates[$key]) ? $tax_rates[$key] : 0;
                $taxvalue[] = $tax_name. '|' .$tax_rate;
            }

            $new_invoice_data['newitems'][$item_key]['description']      = $description;
            $new_invoice_data['newitems'][$item_key]['long_description'] = $long_description;
            $new_invoice_data['newitems'][$item_key]['qty']              = $item['qty'];
            $new_invoice_data['newitems'][$item_key]['unit']             = '';
            $new_invoice_data['newitems'][$item_key]['taxname']          = $taxvalue;
            if($item['labour_type'] == 'fixed'){
                $new_invoice_data['newitems'][$item_key]['rate']  = $item['rate'];
            }else{
                $new_invoice_data['newitems'][$item_key]['rate']  = (float)$item['rate'] * (float)$item['estimated_hours'];
            }

            $new_invoice_data['newitems'][$item_key]['order'] = $index;

            $item_key++;
        }
        $this->load->model('invoices_model');
        $invoice_id = $this->invoices_model->add($new_invoice_data);
        if ($invoice_id) {
            
            // Update transaction with the new invoice data and set to status accepted
            $this->db->where('id', $id);
            if($type == 'repair_job'){

                $this->db->update(db_prefix() . 'wshop_repair_jobs', [
                    'invoiced_date' => date('Y-m-d H:i:s'),
                    'invoice_id'     => $invoice_id,
                ]);
            }elseif($type == 'inspection'){
                $this->db->update(db_prefix() . 'wshop_inspections', [
                    'invoiced_date' => date('Y-m-d H:i:s'),
                    'invoice_id'     => $invoice_id,
                ]);
            }

            if ($client == false) {
                $this->log_transaction_invoice_activity($id, $type.'_activity_converted', false, serialize([
                    '<a href="' . admin_url('invoices/list_invoices/' . $invoice_id) . '">' . format_invoice_number($invoice_id) . '</a>',
                ]));
            }

            hooks()->do_action('wshop_transaction_converted_to_invoice', ['invoice_id' => $invoice_id, 'transaction_id' => $id]);

            // create Inventory delivery if dont active option automatically create Delivery after Invoice created
            if(wshop_get_status_modules('warehouse')){
                if(get_option('auto_create_goods_delivery') == 0){
                    $this->load->model('warehouse/warehouse_model');
                    $this->warehouse_model->auto_create_goods_delivery_with_invoice($invoice_id, '');
                }
            }
        }

        return $invoice_id;
    }

    /**
     * log transaction invoice activity
     * @param  [type]  $id              
     * @param  string  $description     
     * @param  boolean $client          
     * @param  string  $additional_data 
     * @return [type]                   
     */
    public function log_transaction_invoice_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'sales_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'wh_transaction',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return true;
        }
        return false;
    }

    /**
     * do track repair search
     * @param  [type]  $status 
     * @param  string  $search 
     * @param  integer $page   
     * @param  boolean $count  
     * @param  array   $where  
     * @return [type]          
     */
    public function do_track_repair_search($status, $search = '', $page = 1, $count = false, $where = []) 
    {

        $repair_job_limit = 10;
        $repair_job_where = '';

        $this->db->select('*,'.db_prefix() .'wshop_devices.name as device_name,'.db_prefix() .'wshop_devices.serial_no as serial_no,'.db_prefix() .'wshop_devices.model_id as model_id,'.db_prefix() .'wshop_devices.purchase_date as purchase_date,'.db_prefix() .'wshop_repair_jobs.status as repair_job_status,'.db_prefix() .'wshop_repair_jobs.id as repair_job_id');
        $this->db->from(db_prefix() . 'wshop_repair_jobs');

        $this->db->join(db_prefix() . 'clients', '' . db_prefix() . 'clients.userid = ' . db_prefix() . 'wshop_repair_jobs.client_id', 'left');
        $this->db->join(db_prefix() . 'wshop_appointment_types', '' . db_prefix() . 'wshop_appointment_types.id = ' . db_prefix() . 'wshop_repair_jobs.appointment_type_id', 'left');
        $this->db->join(db_prefix() . 'wshop_devices', '' . db_prefix() . 'wshop_devices.id = ' . db_prefix() . 'wshop_repair_jobs.device_id', 'left');
        $this->db->join(db_prefix() . 'wshop_models', '' . db_prefix() . 'wshop_models.id = ' . db_prefix() . 'wshop_devices.model_id', 'left');

        if (is_client_logged_in()) {
            $this->db->where(db_prefix().'wshop_repair_jobs.client_id', get_client_user_id());
            $this->db->like('job_tracking_number', $search ?? '');
        }else{
            $this->db->where('job_tracking_number', $search);
        }

        $this->db->where($where);

        if ($repair_job_where != '') {
            $this->db->where($repair_job_where);
        }

        $this->db->order_by(db_prefix() . 'wshop_repair_jobs.id', 'desc');

        if ($count == false) {
            if ($page > 1) {
                $page--;
                $position = ($page * $repair_job_limit);
                $this->db->limit($repair_job_limit, $position);
            } else {
                $this->db->limit($repair_job_limit);
            }
        }

        if ($count == false) {
            $data = $this->db->get()->result_array();
            return $data;
        }

        return $this->db->count_all_results();
    }

    /**
     * repair job send mail client
     * @param  [type] $data 
     * @return [type]       
     */
    public function repair_job_send_mail_client($data) {
        $staff_id = get_staff_user_id();

        $inbox = array();

        $inbox['to'] = $data['email'];
        $inbox['sender_name'] = get_staff_full_name($staff_id);
        $inbox['subject'] = _strip_tags($data['subject']);
        $inbox['body'] = ($data['content']);
        $inbox['body'] = nl2br_save_html($inbox['body']);
        $inbox['date_received'] = date('Y-m-d H:i:s');
        $inbox['from_email'] = get_option('smtp_email');

        if (new_strlen(get_option('smtp_host')) > 0 && new_strlen(get_option('smtp_password')) > 0 && new_strlen(get_option('smtp_username')) > 0) {

            $this->wshop_send_simple_email($inbox['to'], $inbox['subject'], $inbox['body']);
        }
        $log_send_mail = array();
        $log_send_mail['sent'] = 1;
        $log_send_mail['datesend'] = date('Y-m-d H:i:s');
        $this->db->where('id', $data['repair_job_id']);
        $this->db->update(db_prefix() . 'wshop_repair_jobs', $log_send_mail);

        return true;
    }

    /**
     * inspection send mail client
     * @param  [type] $data 
     * @return [type]       
     */
    public function inspection_send_mail_client($data) {
        $staff_id = get_staff_user_id();

        $inbox = array();

        $inbox['to'] = $data['email'];
        $inbox['sender_name'] = get_staff_full_name($staff_id);
        $inbox['subject'] = _strip_tags($data['subject']);
        $inbox['body'] = ($data['content']);
        $inbox['body'] = nl2br_save_html($inbox['body']);
        $inbox['date_received'] = date('Y-m-d H:i:s');
        $inbox['from_email'] = get_option('smtp_email');

        if (new_strlen(get_option('smtp_host')) > 0 && new_strlen(get_option('smtp_password')) > 0 && new_strlen(get_option('smtp_username')) > 0) {

            $this->wshop_send_simple_email($inbox['to'], $inbox['subject'], $inbox['body']);
        }
        $log_send_mail = array();
        $log_send_mail['sent'] = 1;
        $log_send_mail['datesend'] = date('Y-m-d H:i:s');
        $this->db->where('id', $data['inspection_id']);
        $this->db->update(db_prefix() . 'wshop_inspections', $log_send_mail);

        return true;
    }

    public function repair_job_by_time_range()
    {
        $today_repair_job = 0;
        $past_week_repair_job = 0;
        $past_month_repair_job = 0;
        $registration_booking = 0;
        $total_repair_job = 0;
        $unassigned_repair_job = 0;

        $today_sql = 'SELECT sum(total) as total
        FROM '.db_prefix().'wshop_repair_jobs 
        WHERE DATE(datecreated) = CURDATE()';

        $today_repair = $this->db->query($today_sql)->row();
        if($today_repair){
            $today_repair_job = $today_repair->total;
        }

        $past_week_sql = 'SELECT sum(total) as total
        FROM '.db_prefix().'wshop_repair_jobs 
        WHERE datecreated >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';

        $past_week = $this->db->query($past_week_sql)->row();
        if($past_week){
            $past_week_repair_job = $past_week->total;
        }

        $past_month_sql = 'SELECT sum(total) as total
        FROM '.db_prefix().'wshop_repair_jobs 
        WHERE datecreated >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';

        $past_month = $this->db->query($past_month_sql)->row();
        if($past_month){
            $past_month_repair_job = $past_month->total;
        }
        
        $registration_booking_sql = 'SELECT count(id) as total
        FROM '.db_prefix().'wshop_repair_jobs 
        WHERE `status` = "Booked_In"';

        $registration_bookings = $this->db->query($registration_booking_sql)->row();
        if($registration_bookings){
            $registration_booking = $registration_bookings->total;
        }
        
        $total_repair_job = $this->db->count_all_results(db_prefix() . 'wshop_repair_jobs');


        $unassigned_repair_sql = 'SELECT count(id) as total
        FROM '.db_prefix().'wshop_repair_jobs 
        WHERE sale_agent = 0';

        $unassigned_repairs = $this->db->query($unassigned_repair_sql)->row();
        if($unassigned_repairs){
            $unassigned_repair_job = $unassigned_repairs->total;
        }

        $data['today_repair_job'] = $today_repair_job;
        $data['past_week_repair_job'] = $past_week_repair_job;
        $data['past_month_repair_job'] = $past_month_repair_job;
        $data['registration_booking'] = $registration_booking;
        $data['total_repair_job'] = $total_repair_job;
        $data['unassigned_repair_job'] = $unassigned_repair_job;

        return $data;
    }

    /**
     * get repair job month
     * @param  [type] $from_date 
     * @param  [type] $to_date   
     * @return [type]            
     */
    public function get_repair_job_month($from_date, $to_date)
    {   
        $chart=[];
        
        $sql_where="SELECT  date_format(datecreated, '%m') as repair_job_month, sum(total) as total, sum(estimated_labour_total) as labour_total, sum(estimated_hours) as estimated_hours FROM ".db_prefix()."wshop_repair_jobs
            where date_format(datecreated, '%Y-%m-%d') >= '".$from_date."' AND date_format(datecreated, '%Y-%m-%d') <= '".$to_date."'
            group by date_format(datecreated, '%m')
            ";

        $repair_jobs = $this->db->query($sql_where)->result_array();
        $repair_job_by_month=[];
        foreach ($repair_jobs as $key => $repair_job_value) {
            $repair_job_by_month[(int)$repair_job_value['repair_job_month']] = $repair_job_value;
        }


        for($_month = 1 ; $_month <= 12; $_month++){

            if(isset($repair_job_by_month[$_month])){

                $chart['total'][] = isset($repair_job_by_month[$_month]['total']) ? (float)$repair_job_by_month[$_month]['total'] : 0;
                $chart['labour_total'][] = isset($repair_job_by_month[$_month]['labour_total']) ? (float)$repair_job_by_month[$_month]['labour_total'] : 0;
                $chart['estimated_hours'][] = isset($repair_job_by_month[$_month]['estimated_hours']) ? (float)$repair_job_by_month[$_month]['estimated_hours'] : 0;
            }else{
                $chart['total'][] =  0;
                $chart['labour_total'][] =  0;
                $chart['estimated_hours'][] =  0;
            }

            if($_month == 5){
                $chart['categories'][] = _l('month_05');
            }else{
                $chart['categories'][] = _l('month_'.$_month);
            }

        }

        return $chart;
    }

    /**
     * get repair job weekly
     * @param  [type] $from_date 
     * @param  [type] $to_date   
     * @return [type]            
     */
    public function get_repair_job_weekly($from_date, $to_date)
    {   

        $startOfWeek = new DateTime('monday this week');
        $endOfWeek = new DateTime('sunday this week');

        $interval = new DateInterval('P1D'); // 1-day interval
        $daterange = new DatePeriod($startOfWeek, $interval, $endOfWeek->modify('+1 day'));

        $weekDays = [];
        foreach ($daterange as $date) {
            $weekDays[] = $date->format('Y-m-d'); // Format: YYYY-MM-DD
        }

        $from_date = $weekDays[0];
        $to_date = $weekDays[6];

        $chart=[];
        
        $sql_where="SELECT  date_format(datecreated, '%Y-%m-%d') as repair_job_month, sum(total) as total, sum(estimated_labour_total) as labour_total, sum(estimated_hours) as estimated_hours FROM ".db_prefix()."wshop_repair_jobs
            where date_format(datecreated, '%Y-%m-%d') >= '".$from_date."' AND date_format(datecreated, '%Y-%m-%d') <= '".$to_date."'
            group by date_format(datecreated, '%Y-%m-%d')
            ";

        $repair_jobs = $this->db->query($sql_where)->result_array();
        $repair_job_by_day=[];
        foreach ($repair_jobs as $key => $repair_job_value) {
            $repair_job_by_day[$repair_job_value['repair_job_month']] = $repair_job_value;
        }

        foreach ($weekDays as $key => $_day) {

            if(isset($repair_job_by_day[$_day])){

                $chart['total'][] = isset($repair_job_by_day[$_day]['total']) ? (float)$repair_job_by_day[$_day]['total'] : 0;
                $chart['labour_total'][] = isset($repair_job_by_day[$_day]['labour_total']) ? (float)$repair_job_by_day[$_day]['labour_total'] : 0;
                $chart['estimated_hours'][] = isset($repair_job_by_day[$_day]['estimated_hours']) ? (float)$repair_job_by_day[$_day]['estimated_hours'] : 0;
            }else{
                $chart['total'][] =  0;
                $chart['labour_total'][] =  0;
                $chart['estimated_hours'][] =  0;
            }

            $chart['categories'][] = $_day;
        }

        return $chart;
    }

    /**
     * get report mechanic performance
     * @param  [type] $from_date 
     * @param  [type] $to_date   
     * @return [type]            
     */
    public function get_report_mechanic_performance($from_date, $to_date)
    {   
        $chart=[];
        $chart['estimated_hours'] = [];
        $chart['categories'] = [];
        $sql_where="SELECT  sale_agent, sum(estimated_hours) as estimated_hours FROM ".db_prefix()."wshop_repair_jobs
            where date_format(datecreated, '%Y-%m-%d') >= '".$from_date."' AND date_format(datecreated, '%Y-%m-%d') <= '".$to_date."'
            group by sale_agent
            ";

        $repair_jobs = $this->db->query($sql_where)->result_array();
        $repair_job_by_sale_agent=[];
        foreach ($repair_jobs as $key => $repair_job_value) {
            $chart['estimated_hours'][] = (float)$repair_job_value['estimated_hours'];
            $chart['categories'][] = get_staff_full_name($repair_job_value['sale_agent']);
        }

        return $chart;
    }

    /**
     * count inspection by status
     * @return [type] 
     */
    public function count_inspection_by_status()
    {
        $status = [];
        
        $sql_where = "SELECT count(id) as total, status FROM ".db_prefix()."wshop_inspections
        GROUP BY ".db_prefix()."wshop_inspections.status;";

        $inspections = $this->db->query($sql_where)->result_array();
        $status['all'] = 0;
        foreach ($inspections as $value) {
            $status[$value['status']] = $value['total'];
            $status['all'] += (float)$value['total'];
        }
        return $status;
    }

    /**
     * receipt report 80_pdf
     * @param  [type] $repair_job_report 
     * @return [type]                    
     */
    public function receipt_report_80_pdf($repair_job_report) {
        return app_pdf('repair_job_report', module_dir_path(WORKSHOP_MODULE_NAME, 'libraries/pdf/receipt_report_80_pdf.php'), $repair_job_report);
    }

    /**
     * receipt a4 report pdf
     * @param  [type] $repair_job_report 
     * @return [type]                    
     */
    public function receipt_a4_report_pdf($repair_job_report) {
        return app_pdf('repair_job_report', module_dir_path(WORKSHOP_MODULE_NAME, 'libraries/pdf/a4_report_pdf.php'), $repair_job_report);
    }

    /**
     * generate movement qrcode
     * @param  [type] $data 
     * @param  [type] $path 
     * @return [type]       
     */
    public function generate_movement_qrcode($data, $path)
    {
        if(!file_exists($path. md5($data).'.svg')){
            $this->getQrcode($data, 10, 10, $path);
        }
    }

    /**
     * getQrcode
     * @param  [type] $sample 
     * @return [type]         
     */
    function getQrcode($sample, $width = 10, $height = 10, $path = '')
    {
        if (!$sample) {
            echo "";
        } else {
            if($path == ''){
                $path = '';
            }
            $barcodeobj = new TCPDF2DBarcode($sample ?? '', 'QRCODE');
            $code = $barcodeobj->getBarcodeSVGcode($width, $height, 'black');
            file_put_contents($path.md5($sample).'.svg', $code);
            return true;
        }
    }

    /**
     * delete workshop permission
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_workshop_permission($id)
    {
        $str_permissions ='';
        foreach (list_workshop_permisstion() as $per_key =>  $per_value) {
            if(new_strlen($str_permissions) > 0){
                $str_permissions .= ",'".$per_value."'";
            }else{
                $str_permissions .= "'".$per_value."'";
            }
        }

        $sql_where = " feature IN (".$str_permissions.") ";

        $this->db->where('staff_id', $id);
        $this->db->where($sql_where);
        $this->db->delete(db_prefix() . 'staff_permissions');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * re caculate inspection
     * @param  [type] $inspection_id 
     * @return [type]                
     */
    public function re_caculate_inspection($inspection_id)
    {
        $estimated_labour_subtotal = 0;
        $estimated_labour_total_tax = 0;
        $estimated_labour_total = 0;

        $estimated_material_subtotal = 0;
        $estimated_material_total_tax = 0;
        $estimated_material_total = 0;

        $subtotal = 0;
        $total_tax = 0;
        $total = 0;

        $inspection = $this->get_inspection($inspection_id);

        if($inspection){

            if(isset($inspection->inspection_labour_products)){
                foreach ($inspection->inspection_labour_products as $key => $value) {
                    $tax_rate = null;
                    $tax_name = null;
                    $tax_id = null;
                    $tax_rate_value = 0;

                    //get tax value
                    if($value['tax_id'] != null && $value['tax_id'] != '') {
                        $tax_id = $value['tax_id'];
                        $arr_tax = new_explode('|', $value['tax_id']);
                        $arr_tax_rate = new_explode('|', $value['tax_rate']);

                        foreach ($arr_tax as $key => $tax_id) {
                            $get_tax_name = $this->get_tax_name($tax_id);

                            if(isset($arr_tax_rate[$key])){
                                $get_tax_rate = $arr_tax_rate[$key];
                            }else{
                                $tax = $this->get_taxe_value($tax_id);
                                $get_tax_rate = (float)$tax->taxrate;
                            }

                            $tax_rate_value += (float)$get_tax_rate;

                            if(new_strlen($tax_rate) > 0){
                                $tax_rate .= '|'.$get_tax_rate;
                            }else{
                                $tax_rate .= $get_tax_rate;
                            }

                            if(new_strlen($tax_name) > 0){
                                $tax_name .= '|'.$get_tax_name;
                            }else{
                                $tax_name .= $get_tax_name;
                            }
                        }
                    }

                    if($value['labour_type'] == 'fixed'){
                        $_amount = (float)$value['rate'] * (float)$value['qty']; 
                    }else{
                        $_amount = (float)$value['rate'] * (float)$value['qty'] * (float)$value['estimated_hours']; 
                    }

                    if((float)$tax_rate_value != 0){
                        $tax_money = (float)$_amount * (float)$tax_rate_value / 100;
                        $total = (float)$_amount + (float)$tax_money;
                    }else{
                        $total_money = (float)$_amount;
                        $total = (float)$_amount;
                    }

                    $estimated_labour_subtotal += (float)$_amount;
                    $estimated_labour_total_tax += (float)$tax_money;
                    $estimated_labour_total += (float)$total;
                }

            }

            if(isset($inspection->inspection_labour_materials)){
                foreach ($inspection->inspection_labour_materials as $key => $value) {
                    $tax_rate = null;
                    $tax_name = null;
                    $tax_id = null;
                    $tax_rate_value = 0;

                    //get tax value
                    if($value['tax_id'] != null && $value['tax_id'] != '') {
                        $tax_id = $value['tax_id'];
                        $arr_tax = new_explode('|', $value['tax_id']);
                        $arr_tax_rate = new_explode('|', $value['tax_rate']);

                        foreach ($arr_tax as $key => $tax_id) {
                            $get_tax_name = $this->get_tax_name($tax_id);

                            if(isset($arr_tax_rate[$key])){
                                $get_tax_rate = $arr_tax_rate[$key];
                            }else{
                                $tax = $this->get_taxe_value($tax_id);
                                $get_tax_rate = (float)$tax->taxrate;
                            }

                            $tax_rate_value += (float)$get_tax_rate;

                            if(new_strlen($tax_rate) > 0){
                                $tax_rate .= '|'.$get_tax_rate;
                            }else{
                                $tax_rate .= $get_tax_rate;
                            }

                            if(new_strlen($tax_name) > 0){
                                $tax_name .= '|'.$get_tax_name;
                            }else{
                                $tax_name .= $get_tax_name;
                            }
                        }
                    }

                    $_amount = (float)$value['rate'] * (float)$value['qty']; 

                    if((float)$tax_rate_value != 0){
                        $tax_money = (float)$_amount * (float)$tax_rate_value / 100;
                        $total = (float)$_amount + (float)$tax_money;
                    }else{
                        $total_money = (float)$_amount;
                        $total = (float)$_amount;
                    }

                    $estimated_material_subtotal += (float)$_amount;
                    $estimated_material_total_tax += (float)$tax_money;
                    $estimated_material_total += (float)$total;
                }
            }

            $subtotal = (float)$estimated_labour_subtotal + (float)$estimated_material_subtotal;
            $total_tax = (float)$estimated_labour_total_tax + (float)$estimated_material_total_tax;
            $total = (float)$estimated_labour_total + (float)$estimated_material_total;
            $this->db->where('id', $inspection_id);
            $this->db->update(db_prefix() . 'wshop_inspections', [
                'estimated_labour_subtotal' => $estimated_labour_subtotal,
                'estimated_labour_total_tax' => $estimated_labour_total_tax,
                'estimated_labour_total' => $estimated_labour_total,
                'estimated_material_subtotal' => $estimated_material_subtotal,
                'estimated_material_total_tax' => $estimated_material_total_tax,
                'estimated_material_total' => $estimated_material_total,
                'subtotal' => $subtotal,
                'total_tax' => $total_tax,
                'total' => $total,
            ]);

            if ($this->db->affected_rows() > 0) {
                return true;
            }
            return false;
        }

    }

    /*end file*/
}
