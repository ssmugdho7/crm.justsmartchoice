<?php

defined('BASEPATH') or exit('No direct script access allowed');
use app\services\utilities\Arr;

/**
 * Class Workshop
 */
class Workshop extends AdminController
{
    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('workshop_model');
        hooks()->do_action('workshop_init');
        $this->workshop_model->mechanic_role_exists();

    }

    /**
     * setting
     * @return [type] 
     */
    public function setting()
    {
        if (!has_permission('workshop_setting', '', 'edit') && !is_admin() && !has_permission('workshop_setting', '', 'create') && !has_permission('workshop_setting', '', 'delete')) {
            access_denied('workshop_setting');
        }

        $data['group'] = $this->input->get('group');
        $data['title'] = _l('setting');

        $data['tab'][] = 'general_settings';
        $data['tab'][] = 'appointment_types';
        $data['tab'][] = 'holidays';
        $data['tab'][] = 'manufacturers';
        $data['tab'][] = 'categories';
        $data['tab'][] = 'models';
        $data['tab'][] = 'delivery_methods';
        $data['tab'][] = 'fieldsets';
        $data['tab'][] = 'intervals';
        $data['tab'][] = 'inspection_templates';
        $data['tab'][] = 'prefixs';
        $data['tab'][] = 'permissions';
        if(is_admin()){
            $data['tab'][] = 'reset_data';
        }

        if ($data['group'] == 'general_settings') {
            $data['tabs']['view'] = 'settings/general/' . $data['group'];
        }elseif ($data['group'] == 'appointment_types') {
            $data['tabs']['view'] = 'settings/appointment_types/appointment_type';
        }elseif ($data['group'] == 'holidays') {
            $data['tabs']['view'] = 'settings/holidays/holiday';
        }elseif ($data['group'] == 'manufacturers') {
            $data['group'] = 'category';
            $data['tabs']['view'] = 'settings/manufacturers/manufacturer';
        }elseif ($data['group'] == 'categories') {
            $data['group'] = 'categories';
            $data['tabs']['view'] = 'settings/categories/category';
        }elseif ($data['group'] == 'models') {
            $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => "device"]);
            $data['manufacturers'] = $this->workshop_model->get_manufacturer(false, true);
            $data['fieldsets'] = $this->workshop_model->get_fieldset(false, true);
            $data['tabs']['view'] = 'settings/models/model';
        }elseif ($data['group'] == 'delivery_methods') {
            $data['tabs']['view'] = 'settings/delivery_methods/delivery_method';
        }elseif ($data['group'] == 'fieldsets') {
            $data['tabs']['view'] = 'settings/fieldsets/fieldset';
        }elseif ($data['group'] == 'intervals') {
            $data['tabs']['view'] = 'settings/intervals/interval';
        }elseif ($data['group'] == 'inspection_templates') {
            $data['tabs']['view'] = 'settings/inspection_templates/inspection_template';
        }elseif($data['group'] == 'prefixs'){
            $data['tabs']['view'] = 'settings/prefixs/' . $data['group'];
        }elseif($data['group'] == 'permissions'){
            $data['tabs']['view'] = 'settings/permissions/' . $data['group'];
        }elseif($data['group'] == 'reset_data'){
            $data['tabs']['view'] = 'settings/reset_data/' . $data['group'];
        }

        $this->load->view('settings/manage_setting', $data);
    }

    /**
     * general
     * @return [type] 
     */
    public function general()
    {
        if (!has_permission('workshop_setting', '', 'edit') && !is_admin() && !has_permission('workshop_setting', '', 'create')) {
            access_denied('workshop_setting');
        }

        $data = $this->input->post();

        if ($data) {
            if(isset($data['wshop_working_day'])){
                $data['wshop_working_day'] = implode(",", $data['wshop_working_day']);
            }else{
                $data['wshop_working_day'] = '';
            }

            $data['wshop_repair_job_terms'] = $this->input->post('wshop_repair_job_terms', false);
            $data['wshop_report_footer'] = $this->input->post('wshop_report_footer', false);
            $data['wshop_loan_terms'] = $this->input->post('wshop_loan_terms', false);

            $success = $this->workshop_model->update_prefix_number($data);
            if ($success == true) {
                set_alert('success', _l('updated_successfully', _l('wshop_general_settings')));
            }
            redirect(admin_url('workshop/setting?group=general_settings'));
        }
    }

    /**
     * prefix number
     * @return [type] 
     */
    public function prefix_number()
    {
        if (!has_permission('workshop_setting', '', 'edit') && !is_admin() && !has_permission('workshop_setting', '', 'create')) {
            access_denied('wshop_prefixs');
        }

        $data = $this->input->post();

        if ($data) {

            $success = $this->workshop_model->update_prefix_number($data);
            if ($success == true) {
                $message = _l('updated_successfully');
                set_alert('success', $message);
            }

            redirect(admin_url('workshop/setting?group=prefixs'));
        }
    }

    /**
     * holiday table
     * @return [type] 
     */
    public function holiday_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/holidays/holiday_table'));
    }

    /**
     * change holiday status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_holiday_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_holiday_status($id, (int)$status);
            }
        }
    }

    /**
     * holiday
     * @return [type] 
     */
    public function holiday()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_holiday($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_holiday'));
                }
            } else {
                $success = $this->workshop_model->add_holiday($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_holiday'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete holiday
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_holiday($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=holidays'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_prefixs');
        }

        $response = $this->workshop_model->delete_holiday($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=holidays'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=holidays'));
        }

    }

    /**
     * holiday days off exists
     * @return [type] 
     */
    public function holiday_days_off_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_holiday = $this->db->get(db_prefix() . 'wshop_holidays')->row();
                    if ($_current_holiday->days_off == to_sql_date($this->input->post('days_off'))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('days_off', to_sql_date($this->input->post('days_off')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_holidays');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * manufacturer table
     * @return [type] 
     */
    public function manufacturer_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/manufacturers/manufacturer_table'));
    }

    /**
     * load manufacturer modal
     * @return [type] 
     */
    public function load_manufacturer_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $manufacturer_id = $this->input->post('manufacturer_id');
        $data['title'] = _l('wshop_add_manufacturer');
        if(is_numeric($manufacturer_id) && $manufacturer_id != 0){
            $data['manufacturer'] = $this->workshop_model->get_manufacturer($manufacturer_id);
            $data['title'] = _l('wshop_edit_manufacturer');
        }

        $this->load->view('settings/manufacturers/manufacturer_modal', $data);
    }

    /**
     * change manufacturer status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_manufacturer_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_manufacturer_status($id, (int)$status);
            }
        }
    }

    /**
     * manufacturer
     * @return [type] 
     */
    public function add_edit_manufacturer($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if (is_numeric($id) && $id != 0) {
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_manufacturer($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_manufacturer'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);die;
            } else {
                $response = $this->workshop_model->add_manufacturer($this->input->post());
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_manufacturer'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);die;
            }
        }
        
    }

    /**
     * delete manufacturer
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_manufacturer($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=manufacturers'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_manufacturers');
        }

        $response = $this->workshop_model->delete_manufacturer($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=manufacturers'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=manufacturers'));
        }

    }

    /**
     * manufacturer exists
     * @return [type] 
     */
    public function manufacturer_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if manufacturer is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_manufacturer = $this->db->get(db_prefix() . 'wshop_manufacturers')->row();
                    if (strtoupper($_current_manufacturer->name) == strtoupper(($this->input->post('name')))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', ($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_manufacturers');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * delete manufacturer image
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_manufacturer_image($id){

        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_manufacturers', [
            'manufacture_image' => '',
        ]);
        if ($this->db->affected_rows() > 0) {
            $deleted = true;
        }
        if (is_dir(MANUFACTURER_IMAGES_FOLDER. $id)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(MANUFACTURER_IMAGES_FOLDER. $id);
                // okey only index.html so we can delete the folder also
            delete_dir(MANUFACTURER_IMAGES_FOLDER. $id);
        }
        
        echo json_encode($deleted);
    }

    /**
     * category table
     * @return [type] 
     */
    public function category_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/categories/category_table'));
    }

    /**
     * change category status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_category_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_category_status($id, (int)$status);
            }
        }
    }

    /**
     * category
     * @return [type] 
     */
    public function category()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_category($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_category'));
                }
            } else {
                $success = $this->workshop_model->add_category($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_category'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete category
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_category($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=categories'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_category');
        }

        $response = $this->workshop_model->delete_category($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=categories'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=categories'));
        }

    }

    /**
     * category exists
     * @return [type] 
     */
    public function category_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_category = $this->db->get(db_prefix() . 'wshop_categories')->row();
                    if ($_current_category->name == to_sql_date($this->input->post('name'))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', to_sql_date($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_categories');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * delivery_method table
     * @return [type] 
     */
    public function delivery_method_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/delivery_methods/delivery_method_table'));
    }

    /**
     * change delivery_method status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_delivery_method_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_delivery_method_status($id, (int)$status);
            }
        }
    }

    /**
     * delivery_method
     * @return [type] 
     */
    public function delivery_method()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_delivery_method($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_delivery_method'));
                }
            } else {
                $success = $this->workshop_model->add_delivery_method($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_delivery_method'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete delivery_method
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_delivery_method($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=delivery_methods'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_delivery_method');
        }

        $response = $this->workshop_model->delete_delivery_method($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=delivery_methods'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=delivery_methods'));
        }

    }

    /**
     * delivery_method  exists
     * @return [type] 
     */
    public function delivery_method_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_delivery_method = $this->db->get(db_prefix() . 'wshop_delivery_methods')->row();
                    if ($_current_delivery_method->name == $this->input->post('name')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', $this->input->post('name'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_delivery_methods');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * interval table
     * @return [type] 
     */
    public function interval_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/intervals/interval_table'));
    }

    /**
     * change interval status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_interval_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_interval_status($id, (int)$status);
            }
        }
    }

    /**
     * interval
     * @return [type] 
     */
    public function interval()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_interval($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_interval'));
                }
            } else {
                $success = $this->workshop_model->add_interval($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_interval'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete interval
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_interval($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=intervals'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_interval');
        }

        $response = $this->workshop_model->delete_interval($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=intervals'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=intervals'));
        }

    }

    /**
     * interval  exists
     * @return [type] 
     */
    public function interval_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_interval = $this->db->get(db_prefix() . 'wshop_intervals')->row();
                    if (($_current_interval->value == $this->input->post('value')) && ($_current_interval->type == $this->input->post('type'))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('value', $this->input->post('value'));
                $this->db->where('type', $this->input->post('type'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_intervals');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * model table
     * @return [type] 
     */
    public function model_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/models/model_table'));
    }

    /**
     * change model status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_model_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_model_status($id, (int)$status);
            }
        }
    }

    /**
     * model
     * @return [type] 
     */
    public function model()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_model($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_model'));
                }
            } else {
                $success = $this->workshop_model->add_model($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_model'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete model
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_model($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=models'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_model');
        }

        $response = $this->workshop_model->delete_model($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=models'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=models'));
        }

    }

    /**
     * appointment_type table
     * @return [type] 
     */
    public function appointment_type_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/appointment_types/appointment_type_table'));
    }

    /**
     * load appointment type modal
     * @return [type] 
     */
    public function load_appointment_type_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $appointment_type_id = $this->input->post('appointment_type_id');
        $data['title'] = _l('wshop_add_appointment_type');
        $this->load->model('invoice_items_model');
        $data['products'] = $this->workshop_model->get_labour_product();
        if(is_numeric($appointment_type_id) && $appointment_type_id != 0){
            $data['appointment_type'] = $this->workshop_model->get_appointment_type($appointment_type_id);
            $data['appointment_type_products'] = $this->workshop_model->get_appointment_type_products($appointment_type_id, true);

            $data['title'] = _l('wshop_edit_appointment_type');
        }

        $this->load->view('settings/appointment_types/appointment_type_modal', $data);
    }

    /**
     * change appointment_type status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_appointment_type_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_appointment_type_status($id, (int)$status);
            }
        }
    }

    /**
     * appointment_type
     * @return [type] 
     */
    public function add_edit_appointment_type()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_appointment_type($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_appointment_type'));
                }
            } else {
                $success = $this->workshop_model->add_appointment_type($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_appointment_type'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete appointment_type
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_appointment_type($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=appointment_types'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_appointment_type');
        }

        $response = $this->workshop_model->delete_appointment_type($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=appointment_types'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=appointment_types'));
        }

    }

    /**
     * appointment_type_exists
     * @return [type] 
     */
    public function appointment_type_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_category = $this->db->get(db_prefix() . 'wshop_appointment_types')->row();
                    if ($_current_category->name == ($this->input->post('name'))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', ($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_appointment_types');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * fieldset table
     * @return [type] 
     */
    public function fieldset_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/fieldsets/fieldset_table'));
    }

    /**
     * change fieldset status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_fieldset_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_fieldset_status($id, (int)$status);
            }
        }
    }

    /**
     * fieldset
     * @return [type] 
     */
    public function fieldset()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_fieldset($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_fieldset'));
                }
            } else {
                $success = $this->workshop_model->add_fieldset($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_fieldset'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete fieldset
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_fieldset($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=custom_fields'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_fieldset');
        }

        $response = $this->workshop_model->delete_fieldset($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=custom_fields'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=custom_fields'));
        }

    }

    /**
     * fieldset  exists
     * @return [type] 
     */
    public function fieldset_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_fieldset = $this->db->get(db_prefix() . 'wshop_fieldsets')->row();
                    if ($_current_fieldset->name == $this->input->post('name')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', $this->input->post('name'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_fieldsets');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * fieldset detail
     * @param  string $fieldset_id 
     * @return [type]              
     */
    public function fieldset_detail($fieldset_id = '')
    {
        if (!has_permission('workshop_setting', '', 'edit') && !is_admin() && !has_permission('workshop_setting', '', 'create') && !has_permission('workshop_setting', '', 'delete')) {
            access_denied('workshop_custom_fields');
        }
        if(!is_numeric($fieldset_id) || $fieldset_id == ''){
            blank_page('Staff Member Not Found', 'danger');
        }

        $data = [];
        $data['fieldset_id'] = $fieldset_id;
        $this->load->view('settings/custom_fields/custom_field', $data);
    }

    /**
     * custom_field table
     * @return [type] 
     */
    public function custom_field_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/custom_fields/custom_field_table'));
    }

    /**
     * change custom_field status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_custom_field_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_custom_field_status($id, (int)$status);
            }
        }
    }

    /**
     * custom_field
     * @return [type] 
     */
    public function custom_field()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_custom_field($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_custom_field'));
                }
            } else {
                $success = $this->workshop_model->add_custom_field($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_custom_field'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }


    /**
     * custom_field  exists
     * @return [type] 
     */
    public function custom_field_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_custom_field = $this->db->get(db_prefix() . 'wshop_customfields')->row();
                    if ($_current_custom_field->name == $this->input->post('name')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', $this->input->post('name'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_customfields');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * load custom field modal
     * @return [type] 
     */
    public function load_custom_field_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $custom_field_id = $this->input->post('custom_field_id');
        $fieldset_id = $this->input->post('fieldset_id');

        $data['title'] = _l('wshop_add_custom_field');
        $data['fieldsets'] = $this->workshop_model->get_fieldset_for_custom_field();
        $data['fieldset_id'] = $fieldset_id;

        if(is_numeric($custom_field_id) && $custom_field_id != 0){
            $data['custom_field'] = $this->workshop_model->get_custom_field($custom_field_id);
            $data['custom_field_products'] = $this->workshop_model->get_custom_field($custom_field_id);

            $data['title'] = _l('wshop_edit_custom_field');
        }

        $this->load->view('settings/custom_fields/custom_field_modal', $data);
    }

    /**
     * add edit custom field
     * @param string $id 
     */
    public function add_edit_custom_field($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                $id = $this->workshop_model->add_custom_field($this->input->post());
                set_alert('success', _l('added_successfully', _l('custom_field')));

                if($this->input->is_ajax_request()){
                    echo json_encode(['id' => $id]);
                    die;
                }else{
                    $get_custom_field = $this->workshop_model->get_custom_field($id);
                    if($get_custom_field){
                        $fieldset_id = $get_custom_field->fieldset_id;
                        redirect(admin_url('workshop/fieldset_detail/'.$fieldset_id));
                    }else{
                        redirect(admin_url('workshop/setting?group=fieldsets'));
                    }
                }
            }
            $success = $this->workshop_model->update_custom_field($this->input->post(), $id);
            if (is_array($success) && isset($success['cant_change_option_custom_field'])) {
                set_alert('warning', _l('cf_option_in_use'));
            } elseif ($success === true) {
                set_alert('success', _l('updated_successfully', _l('custom_field')));
            }
            if($this->input->is_ajax_request()){
                echo json_encode(['id' => $id]);
                die;
            }else{
                $get_custom_field = $this->workshop_model->get_custom_field($id);
                if($get_custom_field){
                    $fieldset_id = $get_custom_field->fieldset_id;
                    redirect(admin_url('workshop/fieldset_detail/'.$fieldset_id));
                }else{
                    redirect(admin_url('workshop/setting?group=fieldsets'));

                }

            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('custom_field_lowercase'));
        } else {
            $data['custom_field'] = $this->workshop_model->get_custom_field($id);
            $title                = _l('edit', _l('custom_field_lowercase'));
        }

        $data['pdf_fields']             = $this->pdf_fields;
        $data['client_portal_fields']   = $this->client_portal_fields;
        $data['client_editable_fields'] = $this->client_editable_fields;
        $data['title']                  = $title;
        $this->load->view('admin/custom_fields/customfield', $data);
    }

    /* Delete announcement from database */
    public function delete_custom_field($id, $fieldset_id)
    {
        if (!$id) {
            redirect(admin_url('workshop/fieldset_detail/'.$fieldset_id));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_custom_field');
        }

        $response = $this->workshop_model->delete_custom_field($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('custom_field')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('custom_field_lowercase')));
        }
        redirect(admin_url('workshop/fieldset_detail/'.$fieldset_id));
    }

    /**
     * inspection_template table
     * @return [type] 
     */
    public function inspection_template_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'settings/inspection_templates/inspection_template_table'));
    }

    /**
     * change inspection_template status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_inspection_template_status($id, $status) {
        if (has_permission('workshop_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_inspection_template_status($id, (int)$status);
            }
        }
    }

    /**
     * inspection_template
     * @return [type] 
     */
    public function inspection_template()
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $success = $this->workshop_model->update_inspection_template($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('wshop_inspection_template'));
                }
            } else {
                $success = $this->workshop_model->add_inspection_template($this->input->post());
                if ($success == true) {
                    $message = _l('added_successfully', _l('wshop_inspection_template'));
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);die;
    }

    /**
     * delete inspection_template
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_inspection_template($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/setting?group=inspection_templates'));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_inspection_template');
        }

        $response = $this->workshop_model->delete_inspection_template($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/setting?group=inspection_templates'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/setting?group=inspection_templates'));
        }

    }

    /**
     * inspection_template  exists
     * @return [type] 
     */
    public function inspection_template_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if Day off is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_inspection_template = $this->db->get(db_prefix() . 'wshop_inspection_templates')->row();
                    if ($_current_inspection_template->name == $this->input->post('name')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', $this->input->post('name'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_inspection_templates');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * inspection_template detail
     * @param  string $inspection_template_id 
     * @return [type]              
     */
    public function inspection_template_detail($inspection_template_id = '')
    {
        if (!has_permission('workshop_setting', '', 'edit') && !is_admin() && !has_permission('workshop_setting', '', 'create') && !has_permission('workshop_setting', '', 'delete')) {
            access_denied('workshop_inspection_templates');
        }
        if(!is_numeric($inspection_template_id) || $inspection_template_id == ''){
            blank_page('Inspection Template Not Found', 'danger');
        }

        $data = [];
        $data['inspection_template_id'] = $inspection_template_id;
        $data['inspection_template'] = $this->workshop_model->get_inspection_template($inspection_template_id);
        $data['inspection_template_forms'] = $this->workshop_model->get_inspection_template_form(false, false,'inspection_template_id = '. $inspection_template_id);
        $this->load->view('settings/inspection_templates/inspection_template_forms/manage', $data);
    }

    /**
     * load inspection template form modal
     * @return [type] 
     */
    public function load_inspection_template_form_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $form_id = $this->input->post('form_id');
        $inspection_template_id = $this->input->post('inspection_template_id');

        $data['title'] = _l('wshop_add_inspection_template_form');
        $data['inspection_template_id'] = $inspection_template_id;

        if(is_numeric($form_id) && $form_id != 0){
            $data['inspection_template_form'] = $this->workshop_model->get_inspection_template_form($form_id);
            $data['title'] = _l('wshop_edit_inspection_template_form');
        }

        $this->load->view('settings/inspection_templates/inspection_template_forms/modals/inspection_template_form_modal', $data);
    }

    /**
     * add edit custom field
     * @param string $id 
     */
    public function add_edit_inspection_template_form($id = '')
    {
        if ($this->input->post()) {
            $status = false;
            $message = '';

            $inspection_template_id = $this->input->post('inspection_template_id');
            if ($id == '') {
                $id = $this->workshop_model->add_inspection_template_form($this->input->post());
                $name = $this->input->post('name');
                $description = $this->input->post('description');

                $data['inspection_template_forms'] = $this->workshop_model->get_inspection_template_form(false, false,'inspection_template_id = '. $inspection_template_id);
                $data['form_active'] = $id;
                $inspection_template_form_tab_html = $this->load->view('settings/inspection_templates/inspection_template_forms/inspection_template_form_tab', $data, true);

                $inspection_template_form_tab_content = '<div class="tab-pane" id="template_form_'.$id.'" role="tabpanel" aria-labelledby="template_form_'.$id.'-tab">
                <a href="#" onclick="inspection_template_form_detail_modal(0, '.$id.'); return false;" class="btn btn-info pull-right display-block">
                New Question                                                    </a>

                <h4>'.$name.'</h4>
                <p class="tw-flex tw-text-justify">'.$description.'</p>
                <div class="clearfix"></div><hr>

                <div id="form_detail_'.$id.'"></div>
                </div>';

                if($id){
                    $status = true;
                    $message = _l('added_successfully', _l('wshop_inspection_template_form'));
                }
                echo json_encode([
                    'id' => $id,
                    'status' => $status,
                    'message' => $message,
                    'inspection_template_form_tab_html' => $inspection_template_form_tab_html,
                    'is_add' => true,
                    'inspection_template_form_tab_content' => $inspection_template_form_tab_content,
                ]);
                die;
            }
            $success = $this->workshop_model->update_inspection_template_form($this->input->post(), $id);
            if($success){
                $status = true;
                $message = _l('updated_successfully', _l('wshop_inspection_template_form'));
            }
            $data['inspection_template_forms'] = $this->workshop_model->get_inspection_template_form(false, false,'inspection_template_id = '. $inspection_template_id);
            $data['form_active'] = $id;

            $inspection_template_form_tab_html = $this->load->view('settings/inspection_templates/inspection_template_forms/inspection_template_form_tab', $data, true);

            echo json_encode([
                'id' => $id,
                'status' => $status,
                'message' => $message,
                'is_add' => false,
                'inspection_template_form_tab_html' => $inspection_template_form_tab_html,
            ]);
            die;
        }
        redirect(admin_url('workshop/setting?group=inspection_templates'));
    }

    /* Delete announcement from database */
    public function delete_inspection_template_form($id, $template_id)
    {
        if (!$id) {
            redirect(admin_url('workshop/inspection_template_detail/'.$template_id));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_inspection_template_form');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_inspection_template_form($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('custom_field'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }

    /**
     * update inspection template form order
     * @return [type] 
     */
    public function update_inspection_template_form_order()
    {
        $data = $this->input->post();
        foreach ($data['data'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . 'wshop_inspection_template_forms', [
                'form_order' => $order[1],
            ]);
        }
    }

    /**
     * load inspection template form detail modal
     * @return [type] 
     */
    public function load_inspection_template_form_detail_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $inspection_template_form_detail_id = $this->input->post('form_detail_id');
        $inspection_template_form_id = $this->input->post('inspection_template_form_id');

        $data['title'] = _l('wshop_add_inspection_template_form_detail');
        $data['inspection_template_form_id'] = $inspection_template_form_id;

        if(is_numeric($inspection_template_form_detail_id) && $inspection_template_form_detail_id != 0){
            $data['inspection_template_form_detail'] = $this->workshop_model->get_inspection_template_form_detail($inspection_template_form_detail_id);

            $data['title'] = _l('wshop_edit_inspection_template_form_detail');
        }

        $this->load->view('settings/inspection_templates/inspection_template_forms/modals/inspection_template_form_detail_modal', $data);
    }

    /**
     * add edit inspection template form detail
     * @param string $id 
     */
    public function add_edit_inspection_template_form_detail($id = '')
    {
        if ($this->input->post()) {
            $status = false;
            $message = '';
            $inspection_template_form_question_html = '';

            $data = $this->input->post();
            $data['name'] = $this->input->post('name', false);
            $inspection_template_form_id = $this->input->post('inspection_template_form_id');
            if ($id == '') {
                $id = $this->workshop_model->add_inspection_template_form_detail($data);
                if($id){
                    $status = true;
                    $message = _l('added_successfully', _l('wshop_inspection_template_form_question'));
                }

                $inspection_template_form_question_html .= wshop_render_inspection_template_form_fields('form_fieldset_'.$inspection_template_form_id, false, ['id' => $id], ['items_pr' => true]);

                echo json_encode([
                    'type' => 'insert',
                    'id' => $id,
                    'status' => $status,
                    'message' => $message,
                    'inspection_template_form_question_html' => $inspection_template_form_question_html,
                ]);
                die;
            }
            $success = $this->workshop_model->update_inspection_template_form_detail($data, $id);
            if($success){
                $status = true;
                $message = _l('updated_successfully', _l('wshop_inspection_template_form_question'));
            }

            $inspection_template_form_question_html .= wshop_render_inspection_template_form_fields('form_fieldset_'.$inspection_template_form_id, false, ['id' => $id], ['items_pr' => true]);

            echo json_encode([
                'type' => 'update',
                'id' => $id,
                'status' => $status,
                'message' => $message,
                'inspection_template_form_question_html' => $inspection_template_form_question_html,
            ]);
            die;
        }
        redirect(admin_url('workshop/setting?group=inspection_templates'));
    }

    /**
     * get inspection template form details
     * @param  [type] $inspection_template_form_id 
     * @return [type]                              
     */
    public function get_inspection_template_form_details($inspection_template_form_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $inspection_template_form_details = '';
        $data['inspection_template_form_details'] = $this->workshop_model->get_inspection_template_form_detail(false, false,'inspection_template_form_id = '. $inspection_template_form_id);

        $inspection_template_form_details .= wshop_render_inspection_template_form_fields('form_fieldset_'.$inspection_template_form_id, false, [], ['items_pr' => true]);

        echo json_encode([
            'status' => true,
            'inspection_template_form_details' => $inspection_template_form_details,
        ]);
    }

    /**
     * delete inspection template form detail
     * @param  [type] $id          
     * @param  [type] $template_id 
     * @return [type]              
     */
    public function delete_inspection_template_form_detail($id, $template_id)
    {
        if (!$id) {
            redirect(admin_url('workshop/inspection_template_detail/'.$template_id));
        }

        if(!has_permission('workshop_setting', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_inspection_template_form_detail');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_inspection_template_form_detail($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('inspection_template_form_detail_name'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * update question required
     * @param  [type] $question_id       
     * @param  [type] $question_required 
     * @return [type]                    
     */
    public function update_question_required($question_id, $question_required)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $status = false;
        $message = '';
        $this->db->where('id', $question_id);
        $this->db->update(db_prefix().'wshop_inspection_template_form_details', ['required' => $question_required]);
        if ($this->db->affected_rows() > 0) {
            $message = _l('updated_successfully', _l('wshop_inspection_template_form_question'));
            $status = true;
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
        ]);
    }

    /**
     * update inspection template form question order
     * @return [type] 
     */
    public function update_inspection_template_form_question_order()
    {
        $data = $this->input->post();
        foreach ($data['data'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . 'wshop_inspection_template_form_details', [
                'field_order' => $order[1],
            ]);
        }
    }

    public function mechanics() {
        if (!has_permission('workshop_mechanic', '', 'view') && !has_permission('workshop_mechanic', '', 'view_own')) {
            access_denied('wshop_mechanics');
        }

        $this->load->model('roles_model');
        $this->load->model('staff_model');
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('workshop', 'table_staff'));
        }
        $data['departments'] = $this->departments_model->get();
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['roles'] = $this->roles_model->get();
        $data['title'] = _l('wshop_mechanics');

        $this->load->view('mechanics/manage_mechanic', $data);
    }

    /**
     * table
     */
    public function mechanic_table() {
        $this->app->get_table_data(module_views_path('workshop', 'mechanics/mechanic_table'));
    }

    /**
     * delete staff
     * @return [type] 
     */
    public function delete_staff() {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }
        if (has_permission('workshop_mechanic', '', 'delete')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }
        redirect(admin_url('workshop/mechanics'));
    }

    /**
     * new member
     * @return [type]
     */
    public function new_mechanic() {

        if (!has_permission('workshop_mechanic', '', 'create')) {
            access_denied('staff');
        }

        $data['hr_profile_member_add'] = true;
        $title = _l('add_new', _l('staff_member_lowercase'));

        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['roles_value'] = $this->roles_model->get();
        $data['departments'] = $this->departments_model->get();
        $data['title'] = $title;
        $data['staff'] = $this->staff_model->get();
        $data['list_staff'] = $this->staff_model->get();
        $data['funcData'] = ['staff_id' => isset($staff_id) ? $staff_id : null];
        $data['mechanic_role'] = $this->workshop_model->mechanic_role_exists();

        $this->load->view('mechanics/new_mechanic', $data);
    }

    public function mechanic_modal() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->load->model('staff_model');

        if ($this->input->post('slug') === 'create') {

            $this->load->view('hr_record/mew_member', $data);

        } else if ($this->input->post('slug') === 'update') {
            $staff_id = $this->input->post('staff_id');
            $role_id = $this->input->post('role_id');

            $data = ['funcData' => ['staff_id' => isset($staff_id) ? $staff_id : null]];

            if (isset($staff_id)) {
                $data['mechanic'] = $this->staff_model->get($staff_id);
            }

            $data['roles_value'] = $this->roles_model->get();
            $add_new = $this->input->post('add_new');

            if ($add_new == ' hide') {
                $data['add_new'] = ' hide';
                $data['display_staff'] = '';
            } else {
                $data['add_new'] = '';
                $data['display_staff'] = ' hide';
            }
            $this->load->model('currencies_model');

            $data['list_staff'] = $this->staff_model->get();
            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $data['departments'] = $this->departments_model->get();
            $data['staff_departments'] = $this->departments_model->get_staff_departments($staff_id);
            $data['staff_cover_image'] = $this->workshop_model->get_attachment_file($staff_id, 'staff_profile_images');
            $data['manage_staff'] = $this->input->post('manage_staff');
            $data['mechanic_role'] = $this->workshop_model->mechanic_role_exists();

            $this->load->view('mechanics/update_mechanic', $data);
        }
    }

    /**
     * add edit member
     * @param string $id
     */
    public function add_edit_mechanic($id = '') {
        if (!has_permission('workshop_mechanic', '', 'view') && !has_permission('workshop_mechanic', '', 'view_own') && get_staff_user_id() != $id) {
            access_denied('staff');
        }
        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        if ($this->input->post()) {
            $data = $this->input->post();

            if(isset($data['role_v'])){
                $data['role'] = $data['role_v'];
                unset($data['role_v']);
            }

            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = new_html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $data['password'] = $this->input->post('password', false);
            $this->load->model('staff_model');
            if ($id == '') {
                if (!has_permission('workshop_mechanic', '', 'create')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);

                if ($id) {
                    handle_staff_profile_image_upload($id);
                    set_alert('success', _l('added_successfully', _l('wshop_mechanic')));
                    redirect(admin_url('workshop/mechanics'));
                }
            } else {
                if (!has_permission('workshop_mechanic', '', 'edit') && get_staff_user_id() != $id) {
                    access_denied('staff');
                }

                $manage_staff = false;
                if (isset($data['manage_staff'])) {
                    $manage_staff = true;
                    unset($data['manage_staff']);
                }
                handle_staff_profile_image_upload($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('wshop_mechanic')));
                }

                if ($manage_staff) {
                    redirect(admin_url('workshop/mechanics'));
                } else {
                    redirect(admin_url('workshop/mechanics'));
                }
            }
        }

        $title = _l('add_new', _l('staff_member_lowercase'));
        $this->load->model('currencies_model');
        $data['positions'] = $this->workshop_model->get_job_position();
        $data['workplace'] = $this->workshop_model->get_workplace();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['roles_value'] = $this->roles_model->get();
        $data['departments'] = $this->departments_model->get();
        $data['title'] = $title;
        $data['contract_type'] = $this->workshop_model->get_contracttype();
        $data['staff'] = $this->staff_model->get();
        $data['list_staff'] = $this->staff_model->get();
        $data['funcData'] = ['staff_id' => isset($staff_id) ? $staff_id : null];
        $data['staff_code'] = $this->workshop_model->create_code('staff_code');

        $this->load->view('mechanics/new_mechanic', $data);
    }

    /**
     * change staff status: Change status to staff active or inactive
     * @param  [type] $id
     * @param  [type] $status
     * @return [type]
     */
    public function change_staff_status($id, $status) {
        if (has_permission('workshop_mechanic', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->staff_model->change_staff_status($id, $status);
            }
        }
    }

    /**
     * branches
     * @return [type] 
     */
    public function branches()
    {
        if (!has_permission('workshop_branch', '', 'view') && !has_permission('workshop_branch', '', 'view_own')) {
            access_denied('wshop_branches');
        }

        $data['title'] = _l('wshop_branches');

        $this->load->view('branches/manage', $data);
    }

    /**
     * branch table
     * @return [type] 
     */
    public function branch_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'branches/branch_table'));
    }

    /**
     * load branch modal
     * @return [type] 
     */
    public function load_branch_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $branch_id = $this->input->post('branch_id');
        $data['title'] = _l('wshop_add_branch');
        if(is_numeric($branch_id) && $branch_id != 0){
            $data['branch'] = $this->workshop_model->get_branch($branch_id);
            $data['title'] = _l('wshop_edit_branch');
        }

        $this->load->view('branches/branch_modal', $data);
    }

    /**
     * change branch status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_branch_status($id, $status) {
        if (has_permission('workshop_branch', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_branch_status($id, (int)$status);
            }
        }
    }

    /**
     * branch
     * @return [type] 
     */
    public function add_edit_branch($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            if (is_numeric($id) && $id != 0) {
                $data = $this->input->post();
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_branch($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_branch'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);die;
            } else {
                $response = $this->workshop_model->add_branch($this->input->post());
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_branch'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);die;
            }
        }
        
    }

    /**
     * delete branch
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_branch($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/branches'));
        }

        if(!has_permission('workshop_branch', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_branchs');
        }

        $response = $this->workshop_model->delete_branch($id);
        if ($response) {
            set_alert('success', _l('deleted'));
            redirect(admin_url('workshop/branches'));
        } else {
            set_alert('warning', _l('problem_deleting'));
            redirect(admin_url('workshop/branches'));
        }

    }

    /**
     * branch exists
     * @return [type] 
     */
    public function branch_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if branch is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_branch = $this->db->get(db_prefix() . 'wshop_branches')->row();
                    if (strtoupper($_current_branch->name) == strtoupper(($this->input->post('name')))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', ($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_branches');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * send mail to branch
     * @return [type] 
     */
    public function send_mail_to_branch()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['content'] = $this->input->post('email_content', false);
            $rs = $this->workshop_model->send_mail_to_branch($data);
            if ($rs == true) {
                set_alert('success', _l('wshop_send_mail_successfully'));

            }
            redirect(admin_url('workshop/branches'));
        }
    }


    /**
     * devices
     * @return [type] 
     */
    public function devices()
    {
        if (!has_permission('workshop_device', '', 'view') && !has_permission('workshop_device', '', 'view_own')) {
            access_denied('wshop_devices');
        }

        $data['title'] = _l('wshop_devices');
        $data['clients'] = $this->clients_model->get();
        $data['devices'] = $this->workshop_model->get_device();
        $data['models'] = $this->workshop_model->get_model(false, true);


        $this->load->view('devices/manage', $data);
    }

    /**
     * device table
     * @return [type] 
     */
    public function device_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'devices/device_table'));
    }

    /**
     * load device modal
     * @return [type] 
     */
    public function load_device_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $device_id = $this->input->post('device_id');
        $data['title'] = _l('wshop_add_device');
        if(is_numeric($device_id) && $device_id != 0){
            $data['device'] = $this->workshop_model->get_device($device_id);
            $data['title'] = _l('wshop_edit_device');
            $data['product_attachments'] = $this->workshop_model->get_attachment_file($device_id, 'wshop_device');
            $fieldset_id = 0;

            if($data['device'] && $data['device']->model_id != 0){
                $model_id = $data['device']->model_id;
                $model = $this->workshop_model->get_model($model_id);
                if($model && !is_null($model->fieldset_id) && $model->fieldset_id != 0){
                    $fieldset_id = $model->fieldset_id;
                }
            }
            $data['fieldset_id'] = $fieldset_id;

        }
        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get();
        $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => "device"]);
        $data['manufacturers'] = $this->workshop_model->get_manufacturer(false, true);
        $data['fieldsets'] = $this->workshop_model->get_fieldset(false, true);
        $data['models'] = $this->workshop_model->get_model(false, true);

        $this->load->view('devices/device_modal', $data);
    }

    /**
     * change device status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_device_status($id, $status) {
        if (has_permission('workshop_device', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_device_status($id, (int)$status);
            }
        }
    }

    /**
     * device
     * @return [type] 
     */
    public function add_edit_device($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_device($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_device'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'device_id' => $id,
                    'url' => admin_url('workshop/devices'),
                ]);die;
            } else {
                $response = $this->workshop_model->add_device($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_device'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'device_id' => $response,
                    'url' => admin_url('workshop/devices'),

                ]);die;
            }
        }
        
    }

    /**
     * delete device
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_device($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/device'));
        }

        if(!has_permission('workshop_device', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_device');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_device($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('wshop_device'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }

    /**
     * device exists
     * @return [type] 
     */
    public function device_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if device is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_device = $this->db->get(db_prefix() . 'wshop_devices')->row();
                    if (strtoupper($_current_device->name) == strtoupper(($this->input->post('name')))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', ($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_devices');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * delete device image
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_device_image($id){

        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wshop_devices', [
            'manufacture_image' => '',
        ]);
        if ($this->db->affected_rows() > 0) {
            $deleted = true;
        }
        if (is_dir(DEVICES_IMAGES_FOLDER. $id)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(DEVICES_IMAGES_FOLDER. $id);
                // okey only index.html so we can delete the folder also
            delete_dir(DEVICES_IMAGES_FOLDER. $id);
        }
        
        echo json_encode($deleted);
    }

    /**
     * add device attachment
     * @param [type] $id 
     */
    public function add_device_attachment($id)
    {
        wshop_handle_device_attachments($id);
        $url = admin_url('workshop/devices');
        echo json_encode([
            'url' => $url,
            'id' => $id,
        ]);
    }

    /**
     * delete device attachment
     * @param  [type]  $attachment_id 
     * @param  boolean $folder_name   
     * @return [type]                 
     */
    public function delete_device_attachment($attachment_id, $folder_name = false)
    {
        if (!has_permission('workshop_device', '', 'delete') && !is_admin()) {
            access_denied('workshop_device');
        }
        $_folder_name = DEVICES_IMAGES_FOLDER;

        echo json_encode([
            'success' => $this->workshop_model->delete_workshop_file($attachment_id, $_folder_name),
        ]);
    }

    /**
     * get model ajax
     * @param  [type] $model_id  
     * @param  [type] $device_id 
     * @return [type]            
     */
    public function get_model_ajax($model_id, $device_id)
    {
        
        $message = '';
        $success = true;
        $fieldset = '';
        $fieldset_id = 0;
        if ($this->input->get()) {
            if($model_id != 0){
                $fieldset_id = wshop_get_fieldset_id_by_model($model_id);
                $fieldset = wshop_render_custom_fields('fieldset_'.$fieldset_id, $device_id);
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'fieldset' => $fieldset,
        ]);die;
    }

    /**
     * device detail
     * @param  string $id 
     * @return [type]     
     */
    public function device_detail($id = '')
    {
        if (!has_permission('workshop_device', '', 'view') && !has_permission('workshop_device', '', 'view_own') && !has_permission('workshop_device', '', 'edit') && !is_admin() && !has_permission('workshop_device', '', 'create')) {
            access_denied('workshop_device');
        }
        if(!is_numeric($id) || $id == ''){
            blank_page('Device Not Found', 'danger');
        }

        $data = [];
        $data['id'] = $id;
        $data['device'] = $this->workshop_model->get_device($id);
        $data['device_images'] = $this->workshop_model->get_device_images($id);
        $data['device_attachments'] = $this->workshop_model->get_attachment_file($id, 'wshop_device');

        $this->load->view('devices/device_detail', $data);
    }

    /**
     * load transfer ownership modal
     * @return [type] 
     */
    public function load_transfer_ownership_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $device_id = $this->input->post('device_id');
        $data['title'] = _l('wshop_transfer_ownership_of_device');
        if(is_numeric($device_id) && $device_id != 0){
            $data['device'] = $this->workshop_model->get_device($device_id);
        }
        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get();

        $this->load->view('devices/transfer_ownership_modal', $data);
    }

    /**
     * get client data
     * @param  string $client_id 
     * @return [type]            
     */
    public function get_client_data($client_id = '')
    {
        $message = '';
        $success = true;
        $client_address = '---';
        $client_phone = '---';

        $contact_phone = '---';
        $contact_email = '---';
        if ($this->input->get()) {
            if($client_id != ''){
                $this->load->model('clients_model');
                $client = $this->clients_model->get($client_id);
                $invoice = new stdClass();
                $invoice = $client;
                $invoice->client = $client;
                $invoice->clientid = $client_id;

                $client_address = format_customer_info($invoice, 'invoice', 'billing');
                $client_phone = $client->phonenumber;
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($client_id));
                if($contact){
                    $contact_phone = $contact->phonenumber;
                    $contact_email = $contact->email;
                }
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'client_address' => $client_address,
            'client_phone' => $client_phone,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
        ]);die;
    }

    /**
     * edit transfer ownwership
     * @param  string $device_id 
     * @return [type]            
     */
    public function edit_transfer_ownwership($device_id='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {

            if (is_numeric($device_id) && $device_id != 0) {
                $data = $this->input->post();

                $response = $this->workshop_model->update_device(['client_id' => $data['client_id']], $device_id);
                if ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('wshop_device')));
                }
            }
        }
        redirect(admin_url('workshop/device_detail/'.$device_id));
    }


    /**
     * labour_products
     * @return [type] 
     */
    public function labour_products()
    {
        if (!has_permission('workshop_labour_product', '', 'view') && !has_permission('workshop_labour_product', '', 'view_own')) {
            access_denied('wshop_labour_products');
        }

        $data['title'] = _l('wshop_labour_products');
        $data['clients'] = $this->clients_model->get();
        $data['models'] = $this->workshop_model->get_model(false, true);
        $data['staffs'] = $this->staff_model->get();
        $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => "labour_product"]);


        $this->load->view('labour_products/manage', $data);
    }

    /**
     * labour_product table
     * @return [type] 
     */
    public function labour_product_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'labour_products/labour_product_table'));
    }

    /**
     * load labour_product modal
     * @return [type] 
     */
    public function load_labour_product_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $labour_product_id = $this->input->post('labour_product_id');
        $data['title'] = _l('wshop_add_labour_product');
        if(is_numeric($labour_product_id) && $labour_product_id != 0){
            $data['labour_product'] = $this->workshop_model->get_labour_product($labour_product_id);
            $data['title'] = _l('wshop_edit_labour_product');
        }
        $this->load->model('taxes_model');
        $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => "labour_product"]);
        $data['staffs'] = $this->staff_model->get();
        $data['taxes'] = $this->taxes_model->get();

        $this->load->view('labour_products/labour_product_modal', $data);
    }

    /**
     * change labour_product status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_labour_product_status($id, $status) {
        if (has_permission('workshop_labour_product', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_labour_product_status($id, (int)$status);
            }
        }
    }

    /**
     * labour_product
     * @return [type] 
     */
    public function add_edit_labour_product($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_labour_product($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_labour_product'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'labour_product_id' => $id,
                    'url' => admin_url('workshop/labour_products'),
                ]);die;
            } else {

                $response = $this->workshop_model->add_labour_product($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_labour_product'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'labour_product_id' => $response,
                    'url' => admin_url('workshop/labour_products'),

                ]);die;
            }
        }
        
    }

    /**
     * delete labour_product
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_labour_product($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/labour_product'));
        }

        if(!has_permission('workshop_labour_product', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_labour_product');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_labour_product($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('wshop_labour_product'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }

    /**
     * labour_product exists
     * @return [type] 
     */
    public function labour_product_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if labour_product is the same
                $id = $this->input->post('id');
                if ($id != '') {
                    $this->db->where('id', $id);
                    $_current_labour_product = $this->db->get(db_prefix() . 'wshop_labour_products')->row();
                    if (strtoupper($_current_labour_product->name) == strtoupper(($this->input->post('name')))) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('name', ($this->input->post('name')));
                $total_rows = $this->db->count_all_results(db_prefix() . 'wshop_labour_products');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * labour_product detail
     * @param  string $id 
     * @return [type]     
     */
    public function labour_product_detail($id = '')
    {
        if (!has_permission('workshop_labour_product', '', 'view') && !has_permission('workshop_labour_product', '', 'view_own') && !has_permission('workshop_labour_product', '', 'edit') && !is_admin() && !has_permission('workshop_labour_product', '', 'create')) {
            access_denied('workshop_labour_product');
        }
        if(!is_numeric($id) || $id == ''){
            blank_page('Device Not Found', 'danger');
        }

        $data = [];
        $data['id'] = $id;
        $data['labour_product'] = $this->workshop_model->get_labour_product($id);

        $this->load->view('labour_products/labour_product_detail', $data);
    }


    /**
     * material table
     * @return [type] 
     */
    public function material_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'labour_products/materials/material_table'));
    }

    /**
     * load material modal
     * @return [type] 
     */
    public function load_material_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $material_id = $this->input->post('material_id');
        $data['labour_product_id'] = $this->input->post('labour_product_id');
        $data['title'] = _l('wshop_add_material');
        if(is_numeric($material_id) && $material_id != 0){
            $data['material'] = $this->workshop_model->get_material($material_id);
            $data['title'] = _l('wshop_edit_material');
        }
        $this->load->model('invoice_items_model');
        $data['items'] = $this->invoice_items_model->get();
        $this->load->view('labour_products/materials/material_modal', $data);
    }

    /**
     * material
     * @return [type] 
     */
    public function add_edit_material($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_material($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_material'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'material_id' => $id,
                    'url' => admin_url('workshop/materials'),
                ]);die;
            } else {

                $response = $this->workshop_model->add_material($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_material'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'material_id' => $response,
                    'url' => admin_url('workshop/materials'),

                ]);die;
            }
        }

    }

    /**
     * delete material
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_material($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/material'));
        }

        if(!has_permission('workshop_labour_product', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_material');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_material($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('wshop_material'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }

    /**
     * repair_jobs
     * @return [type] 
     */
    public function repair_jobs()
    {
        if (!has_permission('workshop_repair_job', '', 'view') && !has_permission('workshop_repair_job', '', 'view_own')) {
            access_denied('wshop_repair_jobs');
        }

        $data['title'] = _l('wshop_repair_jobs');
        $data['clients'] = $this->clients_model->get();
        $data['repair_jobs'] = $this->workshop_model->get_repair_job();
        $data['models'] = $this->workshop_model->get_model(false, true);
        $data['appointment_types']             = $this->workshop_model->get_appointment_type('', true);

        $this->load->view('repair_jobs/manage', $data);
    }

    /**
     * repair_job table
     * @return [type] 
     */
    public function repair_job_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'repair_jobs/repair_job_table'));
    }

    /**
     * repair_job
     * @return [type] 
     */
    public function add_edit_repair_job($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['terms'] = $this->input->post('terms', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_repair_job($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_repair_job'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'repair_job_id' => $id,
                    'url' => admin_url('workshop/add_edit_repair_job/'.$id),
                ]);die;
            } else {
                $response = $this->workshop_model->add_repair_job($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_repair_job'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'repair_job_id' => $response,
                    'url' => admin_url('workshop/add_edit_repair_job/'.$response),

                ]);die;
            }
        }
        $data = [];
        $labour_product_row_template = '';
        $part_row_template = '';
        $mechanic_role_id = $this->workshop_model->mechanic_role_exists();
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['staff']             = $this->staff_model->get('', 'role = '.$mechanic_role_id.' AND active = 1');
        $data['appointment_types']             = $this->workshop_model->get_appointment_type('', true);
        $data['devices'] = [];
        $data['branches'] = $this->workshop_model->get_branch('', true);
        $data['billing_types'] = $this->workshop_model->get_category(false, true, ['use_for' => "billing_type"]);
        $data['delivery_types'] = $this->workshop_model->get_category(false, true, ['use_for' => "delivery_type"]);
        $data['collection_types'] = $this->workshop_model->get_category(false, true, ['use_for' => "collection_type"]);


        if(is_numeric($id) && $id != 0){
            $repair_job = $this->workshop_model->get_repair_job($id);
            if(!$repair_job){
                blank_page('Repair Job Not Found', 'danger');
            }
            $data['devices'] = $this->workshop_model->get_device(false, true, ['client_id' => $repair_job->client_id]);
            $data['repair_job'] = $repair_job;
            $data['generate_job_tracking_number'] = $data['repair_job']->job_tracking_number;

            if(isset($repair_job->repair_job_labour_products)){
                $labour_index = 0;
                foreach ($repair_job->repair_job_labour_products as $key => $labour_product) {
                    $labour_index++;

                    $labour_product_row_template .= $this->workshop_model->create_labour_product_row_template('labouritems[' . $labour_index . ']', $labour_product['labour_product_id'], $labour_product['name'], $labour_product['description'], $labour_product['labour_type'], $labour_product['estimated_hours'], $labour_product['unit_price'], $labour_product['qty'], $labour_product['tax_id'], $labour_product['tax_rate'], $labour_product['tax_name'], $labour_product['discount'], $labour_product['subtotal'], $labour_product['id'], true);
                }
            }

            if(isset($repair_job->repair_job_labour_materials)){
                $part_index = 0;
                foreach ($repair_job->repair_job_labour_materials as $key => $material) {
                    $part_index++;

                    $part_row_template .= $this->workshop_model->create_part_row_template('partitems[' . $part_index . ']', $material['item_id'], $material['name'], $material['description'], $material['rate'], $material['qty'], $material['estimated_qty'], $material['tax_id'], $material['tax_rate'], $material['tax_name'],  $material['discount'], $material['subtotal'], $material['id'], true);
                }
            }

        }else{
            $data['generate_job_tracking_number'] = $this->workshop_model->generate_job_tracking_number();

        }
        $data['labour_product_row_template'] = $labour_product_row_template;
        $data['part_row_template'] = $part_row_template;


        $this->load->view('repair_jobs/repair_job', $data);
    }

    /**
     * delete repair_job
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_repair_job($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/repair_job'));
        }

        if(!has_permission('workshop_repair_job', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_repair_job');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_repair_job($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('wshop_repair_job'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }

    /**
     * client change data
     * @param  [type] $customer_id     
     * @param  string $current_invoice 
     * @return [type]                  
     */
    public function client_change_data($customer_id, $device_id = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('clients_model');
            $data                     = [];
            $data['billing_shipping'] = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);
            $data['client_currency']  = $this->clients_model->get_customer_default_currency($customer_id);

            $phonenumber = '';
            $contact_email = '';
            $contact_name = '';

            $client = $this->clients_model->get($customer_id);
            if($client){
                $phonenumber = $client->phonenumber;
            }

            $this->db->where('userid', $customer_id);
            $this->db->where('is_primary', 1);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();
            if($contact){
                $contact_email = $contact->email;
                $contact_name = $contact->firstname.' '.$contact->lastname;
            }
            // device html
            $device_html = '';
            $devices = $this->workshop_model->get_device(false, true, ['client_id' => $customer_id]);
            foreach ($devices as $key => $value) {
                $selected='';
                $device_html .= '<option value="'.$value['id'].'" ' .$selected.'>'.$value['name'].'</option>';
            }

            $data['phonenumber'] = $phonenumber;
            $data['contact_email'] = $contact_email;
            $data['contact_name'] = $contact_name;
            $data['device_html'] = $device_html;

            echo json_encode($data);
        }
    }

    /**
     * part table
     * @return [type] 
     */
    public function part_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'repair_jobs/parts/part_table'));
    }

    /**
     * get labour product row template
     * @return [type] 
     */
    public function get_labour_product_row_template()
    {
        $name = $this->input->post('name');
        $labour_product_id = $this->input->post('labour_product_id');
        $product_name = '';
        $description = '';
        $estimated_hours = (float)$this->input->post('estimated_hours');
        $unit_price = 0;
        $qty = 1;
        $tax_id = '';
        $tax_rate = '';
        $tax_name = '';
        $discount = 0;
        $subtotal = 0;
        $item_id = $this->input->post('item_key');
        $part_item_key = $this->input->post('part_item_key');
        $labour_type = 'fixed';

        $labour_product = $this->workshop_model->get_labour_product($labour_product_id);
        if($labour_product){
            $tax_id_temp = [];
            $tax_rate_temp = [];
            $tax_name_temp = [];
            $product_name = $labour_product->name;
            $description = $labour_product->description;
            if(is_numeric($labour_product->tax) && $labour_product->tax != 0){
                $get_tax_name = $this->workshop_model->get_tax_name($labour_product->tax);
                $get_tax_rate = $this->workshop_model->tax_rate_by_id($labour_product->tax);
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $labour_product->tax;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }

            if(is_numeric($labour_product->tax2) && $labour_product->tax2 != 0){
                $get_tax_name = $this->workshop_model->get_tax_name($labour_product->tax2);
                $get_tax_rate = $this->workshop_model->tax_rate_by_id($labour_product->tax2);
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $labour_product->tax2;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }
            $tax_id = implode('|', $tax_id_temp);
            $tax_rate = implode('|', $tax_rate_temp);
            $tax_name = implode('|', $tax_name_temp);
            $labour_type = $labour_product->labour_type;
            $unit_price = $labour_product->labour_cost;
            if($labour_type == 'fixed'){
                $subtotal = (float)$labour_product->labour_cost;
            }else{
                $subtotal = (float)$labour_product->labour_cost * (float)$estimated_hours;
            }
        }

        $labour_product_row_template = $this->workshop_model->create_labour_product_row_template($name, $labour_product_id, $product_name, $description, $labour_type, $estimated_hours, $unit_price, $qty, $tax_id, $tax_rate, $tax_name, $discount, $subtotal, $item_id, false );


        // get part relation
        $part_row_template = '';
        if(isset($labour_product->parts)){
            $part_name = str_replace('newlabouritems', 'newpartitems', $name);
            foreach ($labour_product->parts as $key => $part) {
                $part_name = 'newpartitems['.$part_item_key.']';

                $part_row_template .= $this->workshop_model->get_part_row_template($part_name, $part['item_id'], $part['quantity'], $key+1);
                $part_item_key++;

            }
        }
        echo json_encode([
            'labour_product_row_template'  => $labour_product_row_template,
            'part_row_template'  => $part_row_template,
            'part_item_key'  => $part_item_key,
        ]);die;

    }

    /**
     * get part row template
     * @return [type]           
     */
    public function get_part_row_template()
    {
        $name = $this->input->post('name');
        $item_id = $this->input->post('part_id');
        $quantity = (float)$this->input->post('quantity');
        $item_key = $this->input->post('item_key');

        echo $this->workshop_model->get_part_row_template($name, $item_id, $quantity, $item_key);
    }

    /**
     * TODO
     * calculated estimated completion date
     * @param  [type] $estimated_hours 
     * @return [type]                  
     */
    public function calculated_estimated_completion_date($estimated_hours)
    {
        if ($this->input->is_ajax_request()) {
            $data = [];

            echo json_encode($data);
        }
    }

    /**
     * repair job status mark as
     * @param  [type] $status 
     * @param  [type] $id     
     * @param  string $type   
     * @return [type]         
     */
    public function repair_job_status_mark_as($status, $id, $type = '')
    {
        $success = $this->workshop_model->repair_job_status_mark_as($status, $id, $type);
        $message = '';

        if ($success) {
            $message = _l('wshop_change_repair_job_status_successfully');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message
        ]);die;
    }

    /**
     * repair job detail
     * @param  string $id 
     * @return [type]     
     */
    public function repair_job_detail($id = '')
    {
        if (!has_permission('workshop_repair_job', '', 'view') && !has_permission('workshop_repair_job', '', 'view_own') && !has_permission('workshop_repair_job', '', 'edit') && !is_admin() && !has_permission('workshop_repair_job', '', 'create')) {
            access_denied('workshop_repair_job');
        }
        if(!is_numeric($id) || $id == ''){
            blank_page('Repair Job Not Found', 'danger');
        }

        $data = [];
        $data['id'] = $id;

        $data['repair_job'] = $this->workshop_model->get_repair_job($id);
        if(!file_exists(REPAIR_JOB_BARCODE. md5($data['repair_job']->job_tracking_number).'.svg')){
            $this->workshop_model->getBarcode($data['repair_job']->job_tracking_number);
        }

        _maybe_create_upload_path(REPAIR_JOB_QR_UPLOAD_PATH . $id . '/');
        $this->workshop_model->generate_movement_qrcode(site_url('workshop/client/repair_job_detail/0/'.$data['repair_job']->hash.'?tab=detail'), REPAIR_JOB_QR_UPLOAD_PATH.$id.'/');

        $data['device'] = $this->workshop_model->get_device($data['repair_job']->device_id);
        $data['tax_labour_data'] = $this->workshop_model->get_html_tax_labour_repair_job($id, $data['repair_job']->currency);
        $data['tax_part_data'] = $this->workshop_model->get_html_tax_part_repair_job($id, $data['repair_job']->currency);
        $mechanic_role_id = $this->workshop_model->mechanic_role_exists();
        $data['staffs']             = $this->staff_model->get('', ['role' => $mechanic_role_id,'staffid !=' => $data['repair_job']->sale_agent]);
        $data['returns'] = $this->workshop_model->get_transaction(false, '', ['repair_job_id' => $id, 'transaction_type' => 'return']);
        if(count($data['returns']) > 0){
            $data['return_attachments'] = $this->workshop_model->get_attachment_file($data['returns'][0]['id'], 'wshop_transaction');
            $data['return_notes'] = $this->workshop_model->get_note(false, ['return_delivery_id' => $data['returns'][0]['id'], 'transaction_type' => 'return']);
        }

        $data['deliveries'] = $this->workshop_model->get_transaction(false, '', ['repair_job_id' => $id, 'transaction_type' => 'delivery']);
        if(count($data['deliveries']) > 0){
            $data['delivery_attachments'] = $this->workshop_model->get_attachment_file($data['deliveries'][0]['id'], 'wshop_transaction');
            $data['delivery_notes'] = $this->workshop_model->get_note(false, ['return_delivery_id' => $data['deliveries'][0]['id'], 'transaction_type' => 'delivery']);
        }
        $data['workshops'] = $this->workshop_model->get_workshop(false, ['repair_job_id' => $id]);
        $data['_inspection'] = $this->workshop_model->get_inspection(false, ['repair_job_id' => $id]);

        if(wshop_get_status_modules('warehouse')){
            $data['check_parts_available'] = $this->workshop_model->check_parts_available($id, 'repair_job');
        }else{
            $data['check_parts_available'] = [
                'status' => true,
                'message' => '',
            ];
        }

        $this->load->view('repair_jobs/repair_job_detail', $data);
    }

    /**
     * reassign mechanic
     * @param  [type] $repair_job_id 
     * @param  [type] $mechanic_id   
     * @return [type]                
     */
    public function reassign_mechanic($repair_job_id, $mechanic_id)
    {
        $success = false;
        $message = '';

        $this->db->where('id', $repair_job_id);
        $this->db->update(db_prefix() . 'wshop_repair_jobs', ['sale_agent' => $mechanic_id]);
        if ($this->db->affected_rows() > 0) {
            $success = true;
            $this->workshop_model->log_workshop_activity($repair_job_id, 'wshop_reassign_mechanic_activity', false, '', 'repair_job');
        }

        if ($success) {
            $message = _l('wshop_reassign_mechanic_successfully');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message
        ]);die;
    }

    /**
     * repair job calendar
     * @return [type] 
     */
    public function repair_job_calendar()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $data = $this->input->post();

            $create_timeline = false;
            if(isset($data['create_timeline'])){
                $create_timeline = true;
                unset($data['create_timeline']);
            }else{
                $create_timeline = false;
            }

            if($create_timeline == true){
                $this->load->model('projects_model');
                if(isset($data['id']) && is_numeric($data['id']) && $data['id'] != 0){
                    $success = $this->projects_model->update_timeline($data, $data['id']);
                }else{
                    $success = $this->projects_model->add_timeline($data);
                }
                if(is_numeric($success)){
                   $success = true; 
                }

            }else{
                $success = $this->utilities_model->event($data);
            }

            $message = '';
            if ($create_timeline == true) {
                $message = _l('utility_calendar_event_added_successfully');
            }else{
                if (isset($data['eventid'])) {
                    $message = _l('event_updated');
                } else {
                    $message = _l('utility_calendar_event_added_successfully');
                }
            }
            
            echo json_encode([
                'success' => $success,
                'message' => $message,
            ]);
            die();
        }
        $data['google_ids_calendars'] = $this->misc_model->get_google_calendar_ids();
        $data['google_calendar_api']  = get_option('google_calendar_api_key');
        $data['title']                = _l('calendar');
        add_calendar_assets();

        $this->load->view('repair_jobs/calendar', $data);
    }

    /**
     * get repair job calendar data
     * @return [type] 
     */
    public function get_repair_job_calendar_data()
    {
        echo json_encode($this->workshop_model->get_repair_job_calendar_data(
                date('Y-m-d', strtotime($this->input->get('start'))),
                date('Y-m-d', strtotime($this->input->get('end'))),
                '',
                '',
                $this->input->get()
            ));
        die();
    }

    /**
     * repair job print lable pdf
     * @param  [type] $id 
     * @return [type]     
     */
    public function repair_job_print_lable_pdf($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/repair_jobs'));
        }
        $this->load->model('clients_model');
        $this->load->model('currencies_model');

        $repair_job_number = '';
        $repair_job = $this->workshop_model->get_repair_job($id);

        $base_currency = $this->currencies_model->get_base_currency();
        $currency = $base_currency;
        if(is_numeric($repair_job->currency) && $repair_job->currency != 0){
            $currency = $repair_job->currency;
        }

        $repair_job->client = $this->clients_model->get($repair_job->client_id);
        $repair_job->currency = $currency;

        if($repair_job){
            $repair_job_number .= $repair_job->job_tracking_number;
        }
        try {
            $pdf = $this->workshop_model->repair_job_label_pdf($repair_job);

        } catch (Exception $e) {
            echo new_html_entity_decode($e->getMessage());
            die;
        }

        $type = 'D';
        ob_end_clean();

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($repair_job_number)).'.pdf', $type);
    }

    /**
     * returns
     * @return [type] 
     */
    public function returns()
    {
        if (!has_permission('workshop_inspection', '', 'view') && !has_permission('workshop_inspection', '', 'view_own')) {
            access_denied('workshop_inspection');
        }

        $data['title'] = _l('wshop_returns');
        $data['clients'] = $this->clients_model->get();
        $data['devices'] = $this->workshop_model->get_device();

        $this->load->view('returns/manage', $data);
    }

    /**
     * return table
     * @return [type] 
     */
    public function return_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'returns/table'), ['transaction_type' => 'return']);
    }

    /**
     * delete transaction
     * @param  [type] $id 
     * @transaction [type]     
     */
    public function delete_transaction($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/return'));
        }

        if(!has_permission('workshop_repair_job', '', 'delete')  &&  !is_admin()) {
            access_denied('workshop_repair_job');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_transaction($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('workshop_repair_job'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * load transaction modal
     * @return [type] 
     */
    public function load_transaction_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $repair_job_id = $this->input->post('repair_job_id');
        $transaction_id = $this->input->post('transaction_id');
        $transaction_type = $this->input->post('transaction_type');
        $data['transaction_type'] = $transaction_type;
        if($transaction_type == 'return'){
            $data['title'] = _l('wshop_add_return');
        }else{
            $data['title'] = _l('wshop_add_delivery');
        }

        if(is_numeric($transaction_id) && $transaction_id != 0){
            $data['transaction'] = $this->workshop_model->get_transaction($transaction_id);
            $data['title'] = _l('wshop_edit_'.$transaction_type);
            $data['transaction_attachments'] = $this->workshop_model->get_attachment_file($transaction_id, 'wshop_transaction');
        }

        $this->load->model('clients_model');
        $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Delivery_Type']);
        $data['repair_job'] = $this->workshop_model->get_repair_job($repair_job_id);
        $client_id = $data['repair_job']->client_id;
        $data['clients'] = $this->clients_model->get();
        $data['repair_jobs'] = $this->workshop_model->get_repair_job(false, true, ['id' => $repair_job_id]);
        $data['repair_job_id'] = $repair_job_id;
        $data['client_id'] = $client_id;

        $this->load->view('returns/add_modal', $data);
    }

    /**
     * transaction
     * @return [type] 
     */
    public function add_edit_transaction($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_transaction($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_'.$data['transaction_type']));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'transaction_id' => $id,
                    'url' => admin_url('workshop/devices'),
                ]);die;
            } else {
                $response = $this->workshop_model->add_transaction($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_'.$data['transaction_type']));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'transaction_id' => $response,
                    'url' => admin_url('workshop/devices'),

                ]);die;
            }
        }
        
    }

    /**
     * add transaction attachment
     * @param [type] $id 
     */
    public function add_transaction_attachment($id)
    {
        wshop_handle_transaction_attachments($id);
        $url = admin_url('workshop/repair_jobs');
        echo json_encode([
            'url' => $url,
            'id' => $id,
        ]);
    }

    /**
     * delete transaction attachment
     * @param  [type]  $attachment_id 
     * @param  boolean $folder_name   
     * @return [type]                 
     */
    public function delete_workshop_attachment($attachment_id, $folder_name = false)
    {
        if (!has_permission('workshop_repair_job', '', 'delete') && !is_admin()) {
            access_denied('workshop_repair_job');
        }
        $_folder_name = TRANSACTION_FOLDER;

        if($folder_name == 'NOTE_FOLDER'){
            $_folder_name = NOTE_FOLDER;
        }elseif($folder_name == 'WORKSHOP_FOLDER'){
            $_folder_name = WORKSHOP_FOLDER;
        }elseif($folder_name == 'INSPECTION_FOLDER'){
            $_folder_name = INSPECTION_FOLDER;
        }

        echo json_encode([
            'success' => $this->workshop_model->delete_workshop_file($attachment_id, $_folder_name),
        ]);
    }

    /**
     * workshop pdf file
     * @param  [type] $id     
     * @param  [type] $rel_id 
     * @return [type]         
     */
    public function transaction_pdf_file($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin'] = is_admin();
        $data['file'] = $this->misc_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('returns/preview_pdf_file', $data);
    }

    /**
     * load note modal
     * @return [type] 
     */
    public function load_note_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $repair_job_id = $this->input->post('repair_job_id');
        $return_delivery_id = $this->input->post('return_delivery_id');
        $note_id = $this->input->post('note_id');
        $transaction_type = $this->input->post('transaction_type');
        $data['transaction_type'] = $transaction_type;
        $data['title'] = _l('wshop_add_note');

        if(is_numeric($note_id) && $note_id != 0){
            $data['note'] = $this->workshop_model->get_note($note_id);
            $data['title'] = _l('wshop_edit_note');
            $data['note_attachments'] = $this->workshop_model->get_attachment_file($note_id, 'wshop_note');
        }

        $this->load->model('clients_model');
        $data['categories'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Delivery_Type']);
        $data['repair_job'] = $this->workshop_model->get_repair_job($repair_job_id);
        $client_id = $data['repair_job']->client_id;
        $data['clients'] = $this->clients_model->get();
        $data['repair_jobs'] = $this->workshop_model->get_repair_job(false, true, ['id' => $repair_job_id]);
        $data['repair_job_id'] = $repair_job_id;
        $data['return_delivery_id'] = $return_delivery_id;
        $data['client_id'] = $client_id;

        $this->load->view('returns/notes/add_note_modal', $data);
    }

    /**
     * note
     * @return [type] 
     */
    public function add_edit_note($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_note($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_note'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'note_id' => $id,
                    'url' => admin_url('workshop/devices'),
                ]);die;
            } else {
                $response = $this->workshop_model->add_note($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_note'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'note_id' => $response,
                    'url' => admin_url('workshop/devices'),

                ]);die;
            }
        }
        
    }

    /**
     * add note attachment
     * @param [type] $id 
     */
    public function add_note_attachment($id)
    {
        wshop_handle_note_attachments($id);
        $url = admin_url('workshop/repair_jobs');
        echo json_encode([
            'url' => $url,
            'id' => $id,
        ]);
    }

    /**
     * note pdf file
     * @param  [type] $id     
     * @param  [type] $rel_id 
     * @return [type]         
     */
    public function preview_file($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin'] = is_admin();
        $data['file'] = $this->misc_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $upload_path = TRANSACTION_FOLDER;
        $upload_folder = 'return_deliveries';

        if($data['file']->rel_type == 'wshop_note'){
            $upload_path = NOTE_FOLDER;
            $upload_folder = 'notes';
        }elseif($data['file']->rel_type == 'wshop_workshop'){
            $upload_path = WORKSHOP_FOLDER;
            $upload_folder = 'workshops';
        }elseif($data['file']->rel_type == 'wshop_inspection'){
            $upload_path = INSPECTION_FOLDER;
            $upload_folder = 'inspections';
        }elseif($data['file']->rel_type == 'wshop_inspection_qs'){
            $upload_path = INSPECTION_QUESTION_FOLDER;
            $upload_folder = 'inspection_questions';
        }
        

        $data['upload_path'] = $upload_path;
        $data['upload_folder'] = $upload_folder;

        $this->load->view('returns/preview_pdf_file', $data);
    }

    /**
     * delete note
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_note($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/repair_job'));
        }

        if(!has_permission('workshop_repair_job', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_repair_job');
        }

        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_note($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('wshop_note'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);

    }


    /**
     * workshops
     * @workshop [type] 
     */
    public function workshops()
    {
        if (!has_permission('workshop_workshop', '', 'view') && !has_permission('workshop_workshop', '', 'view_own')) {
            access_denied('wshop_workshop');
        }

        $data['title'] = _l('wshop_workshops');
        $data['report_types'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Report_type']);
        $data['report_statuses'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Report_status']);
        $data['repair_jobs'] = $this->workshop_model->get_repair_job();

        $this->load->view('workshops/manage', $data);
    }

    /**
     * workshop table
     * @workshop [type] 
     */
    public function workshop_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'workshops/table'));
    }

    /**
     * delete workshop
     * @param  [type] $id 
     * @workshop [type]     
     */
    public function delete_workshop($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/workshops'));
        }

        if(!has_permission('workshop_workshop', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_workshop');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_workshop($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('workshop_repair_job'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * load workshop modal
     * @return [type] 
     */
    public function load_workshop_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $repair_job_id = $this->input->post('repair_job_id');
        $workshop_id = $this->input->post('workshop_id');
        $data['title'] = _l('wshop_add_workshop');
        if($repair_job_id) {
            $data['_repair_job_id'] = $repair_job_id;
        }

        if(is_numeric($workshop_id) && $workshop_id != 0){
            $data['workshop'] = $this->workshop_model->get_workshop($workshop_id);
            $data['title'] = _l('wshop_edit_workshop');
            $data['workshop_attachments'] = $this->workshop_model->get_attachment_file($workshop_id, 'wshop_workshop');
        }

        $this->load->model('clients_model');
        $data['report_types'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Report_type']);
        $data['report_statuses'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Report_status']);
        $data['repair_jobs'] = $this->workshop_model->get_repair_job();
        $mechanic_role_id = $this->workshop_model->mechanic_role_exists();
        $data['staffs']             = $this->staff_model->get('', 'role = '.$mechanic_role_id.' AND active = 1');

        $this->load->view('workshops/add_modal', $data);
    }

    /**
     * workshop
     * @return [type] 
     */
    public function add_edit_workshop($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_workshop($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_workshop_name'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'workshop_id' => $id,
                    'url' => admin_url('workshop/workshops'),
                ]);die;
            } else {
                $response = $this->workshop_model->add_workshop($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_workshop_name'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'workshop_id' => $response,
                    'url' => admin_url('workshop/workshops'),

                ]);die;
            }
        }
        
    }

    /**
     * add workshop attachment
     * @param [type] $id 
     */
    public function add_workshop_attachment($id)
    {
        wshop_handle_workshop_attachments($id);
        $url = admin_url('workshop/workshops');
        echo json_encode([
            'url' => $url,
            'id' => $id,
        ]);
    }

    /**
     * change category status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_workshop_status($id, $status) {
        if (has_permission('workshop_workshop', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_workshop_status($id, (int)$status);
            }
        }
    }

    /**
     * inspections
     * @inspection [type] 
     */
    public function inspections()
    {
        if (!has_permission('workshop_inspection', '', 'view') && !has_permission('workshop_inspection', '', 'view_own')) {
            access_denied('wshop_inspection');
        }

        $data['title'] = _l('wshop_inspections');
        $data['repair_jobs'] = $this->workshop_model->get_repair_job();
        $data['inspection_types'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Inspection']);
        $data['clients'] = $this->clients_model->get();
        $data['statuses'] = inspection_status();
        $data['devices'] = $this->workshop_model->get_device();

        $this->load->view('inspections/manage', $data);
    }

    /**
     * inspection table
     * @inspection [type] 
     */
    public function inspection_table()
    {
        $this->app->get_table_data(module_views_path('workshop', 'inspections/table'));
    }

    /**
     * delete inspection
     * @param  [type] $id 
     * @inspection [type]     
     */
    public function delete_inspection($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/inspections'));
        }

        if(!has_permission('workshop_inspection', '', 'delete')  &&  !is_admin()) {
            access_denied('wshop_workshop');
        }
        $success = false;
        $message = '';

        $response = $this->workshop_model->delete_inspection($id);
        if($response){
            $success = true;
            $message = _l('deleted', _l('workshop_inspection'));
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * load inspection modal
     * @return [type] 
     */
    public function load_inspection_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $data = [];
        $repair_job_id = $this->input->post('repair_job_id');
        $inspection_id = $this->input->post('inspection_id');
        $data['title'] = _l('wshop_add_inspection');
        $data['devices'] = [];
        $data['repair_jobs'] = $this->workshop_model->get_repair_job(false, true, 'id NOT IN (SELECT repair_job_id FROM '.db_prefix().'wshop_inspections WHERE repair_job_id > 0)');

        if($repair_job_id) {
            $data['_repair_job_id'] = $repair_job_id;
            $repair_job = $this->workshop_model->get_repair_job($repair_job_id);
            if($repair_job){
                $data['customer_id'] = $repair_job->client_id;
                $data['_device_id'] = $repair_job->device_id;
            }
        }

        if(is_numeric($inspection_id) && $inspection_id != 0){
            $data['inspection'] = $this->workshop_model->get_inspection($inspection_id);
            $data['title'] = _l('wshop_edit_inspection');
            $data['inspection_attachments'] = $this->workshop_model->get_attachment_file($inspection_id, 'wshop_inspection');
            $data['devices'] = $this->workshop_model->get_device(false, true, ['client_id' => $data['inspection']->client_id]);
            if($data['inspection']->repair_job_id != '' && $data['inspection']->repair_job_id > 0){
                $data['repair_jobs'] = $this->workshop_model->get_repair_job(false, true, 'id = '.$data['inspection']->repair_job_id);
            }else{
                $data['repair_jobs'] = $this->workshop_model->get_repair_job(false, true, 'id NOT IN (SELECT repair_job_id FROM '.db_prefix().'wshop_inspections WHERE repair_job_id > 0)');
            }
        }

        $this->load->model('clients_model');
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['inspection_types'] = $this->workshop_model->get_category(false, true, ['use_for' => 'Inspection']);
        $mechanic_role_id = $this->workshop_model->mechanic_role_exists();
        $data['staffs']             = $this->staff_model->get('', 'role = '.$mechanic_role_id.' AND active = 1');
        $data['inspection_templates'] = $this->workshop_model->get_inspection_template(false, true);
        $data['intervals'] = $this->workshop_model->get_interval(false, true);

        $this->load->view('inspections/add_modal', $data);
    }

    /**
     * inspection
     * @return [type] 
     */
    public function add_edit_inspection($id ='')
    {
        $message = '';
        $success = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);

            if (is_numeric($id) && $id != 0) {
                if(isset($data['id'])){
                    unset($data['id']);
                }

                $response = $this->workshop_model->update_inspection($data, $id);
                if ($response == true) {
                    $success = true;
                    $message = _l('updated_successfully', _l('wshop_inspection'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'inspection_id' => $id,
                    'url' => admin_url('workshop/inspections'),
                ]);die;
            } else {
                $response = $this->workshop_model->add_inspection($data);
                if ($response == true) {
                    $success = true;
                    $message = _l('added_successfully', _l('wshop_inspection'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'inspection_id' => $response,
                    'url' => admin_url('workshop/inspections'),

                ]);die;
            }
        }
        
    }

    /**
     * add inspection attachment
     * @param [type] $id 
     */
    public function add_inspection_attachment($id)
    {
        wshop_handle_inspection_attachments($id);
        $url = admin_url('workshop/inspections');
        echo json_encode([
            'url' => $url,
            'id' => $id,
        ]);
    }

    /**
     * change category status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_inspection_visible($id, $status) {
        if (has_permission('workshop_inspection', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->workshop_model->change_inspection_visible($id, (int)$status);
            }
        }
    }

    /**
     * calculate next inspection date
     * @return [type] 
     */
    public function calculate_next_inspection_date()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $start_date = to_sql_date($data['start_date'], true);
            $interval_id = $data['interval_id'];
            $next_inspection_date = '';

            if($data['interval_id'] != '' && $data['interval_id'] != 0){
                $interval = $this->workshop_model->get_interval($data['interval_id']);
                if($interval){
                    switch ($interval->type) {
                        case 'day':
                        $temp_day = $interval->value;
                        $next_inspection_date = date('Y-m-d', strtotime('+'.(int)$temp_day.' days', strtotime($start_date)));
                            break;
                        case 'month':
                            $temp_day = $interval->value;
                        $next_inspection_date = date('Y-m-d', strtotime('+'.(int)$temp_day.' months', strtotime($start_date)));
                            break;
                        case 'year':
                            
                            break;
                        $temp_day = $interval->value;
                        $next_inspection_date = date('Y-m-d', strtotime('+'.(int)$temp_day.' years', strtotime($start_date)));
                        default:
                            // code...
                            break;
                    }
                }
                $next_inspection_date = _d($next_inspection_date);
            }

            echo json_encode([
                'success' => true,
                'next_inspection_date' => $next_inspection_date,
            ]);die;

        }
    }

    /**
     * get repair job infor
     * @return [type] 
     */
    public function get_repair_job_infor($repair_job_id = 0){
        if ($this->input->is_ajax_request()) {
            $status = true;
            $device_id = 0;
            $client_id = 0;
            $client_html = '';
            if(is_numeric($repair_job_id) && $repair_job_id != 0){
                $repair_job = $this->workshop_model->get_repair_job($repair_job_id);
                if($repair_job){
                    $device_id = $repair_job->device_id;

                    $client_html = '';
                    $client_html .= '<option value=""></option>';
                    $selected=' selected';
                    $client_html .= '<option value="'.$repair_job->client_id.'" ' .$selected.'>'.get_company_name($repair_job->client_id).'</option>';

                    $client_id = $repair_job->client_id; 
                }
            }
            echo json_encode([
                'success' => $status,
                'device_id' => $device_id,
                'client_id' => $client_id,
                'client_html' => $client_html,
            ]);
        }
    }

    /**
     * inspection status mark as
     * @param  [type] $status 
     * @param  [type] $id     
     * @param  string $type   
     * @return [type]         
     */
    public function inspection_status_mark_as($status, $id, $type = '')
    {
        $success = $this->workshop_model->inspection_status_mark_as($status, $id, $type);
        $message = '';

        if ($success) {
            $message = _l('wshop_change_inspection_status_successfully');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message
        ]);die;
    }

    /**
     * inspection detail
     * @param  string $id 
     * @return [type]     
     */
    public function inspection_detail($id = '')
    {
        if (!has_permission('workshop_inspection', '', 'view') && !has_permission('workshop_inspection', '', 'view_own') && !has_permission('workshop_inspection', '', 'edit') && !is_admin() && !has_permission('workshop_inspection', '', 'create')) {
            access_denied('workshop_inspection');
        }
        if(!is_numeric($id) || $id == ''){
            blank_page('Inspection Not Found', 'danger');
        }

        $data = [];
        $data['id'] = $id;
        $data['inspection'] = $this->workshop_model->get_inspection($id);
        $data['inspection_attachments'] = $this->workshop_model->get_attachment_file($id, 'wshop_inspection');
        $allow_create_invoice = false;
        if(isset($data['inspection']->inspection_labour_products) || isset($data['inspection']->inspection_labour_materials)){
            $allow_create_invoice = true;
        }
        $data['allow_create_invoice'] = $allow_create_invoice;
        if(wshop_get_status_modules('warehouse')){
            $data['check_parts_available'] = $this->workshop_model->check_parts_available($id, 'inspection');
        }else{
            $data['check_parts_available'] = [
                'status' => true,
                'message' => '',
            ];
        }

        $this->load->view('inspections/inspection_detail', $data);
    }

    /**
     * inspection form
     * @param  string $inspection_id 
     * @return [type]                
     */
    public function inspection_form($inspection_id = '')
    {
        if (!has_permission('workshop_inspection', '', 'edit')) {
            access_denied('workshop_inspection_form');
        }
        $insert_inpsection_template = $this->workshop_model->insert_inpsection_template($inspection_id);

        $data = [];

        $data['inspection'] = $this->workshop_model->get_inspection($inspection_id);
        $data['inspection_forms'] = $this->workshop_model->get_inspection_form(false, false,'inspection_id = '. $inspection_id);

        $this->load->view('inspections/inspection_template_forms/manage', $data);
    }

    /**
     * get inspection form details
     * @param  [type] $inspection_form_id 
     * @return [type]                     
     */
    public function get_inspection_form_details($inspection_form_id, $inspection_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $inspection_form_details = '';
        $data['inspection_form_details'] = $this->workshop_model->get_inspection_form_detail(false, false,'inspection_form_id = '. $inspection_form_id);

        $inspection_form_details .= wshop_render_inspection_form_fields('form_fieldset_'.$inspection_form_id, $inspection_id, [], ['items_pr' => true]);

        echo json_encode([
            'status' => true,
            'inspection_form_details' => $inspection_form_details,
        ]);
    }

    /**
     * add edit inspection form
     * @param string $inspection_id 
     */
    public function add_edit_inspection_form($inspection_id='')
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $status = false;
        $message = '';

        $data = $this->input->post();
        $result = $this->workshop_model->add_edit_inspection_form($data, $inspection_id);
        if($result){
            $status = true;
            $message = _l('updated_successfully');
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
        ]);
    }

    // TOTO
    /**
     * get labour product row template
     * @return [type] 
     */
    public function inspection_get_labour_product_row_template()
    {
        $name = $this->input->post('name');
        $labour_product_id = $this->input->post('labour_product_id');
        $inspection_id = $this->input->post('inspection_id');
        $inspection_form_id = $this->input->post('inspection_form_id');
        $inspection_form_detail_id = $this->input->post('inspection_form_detail_id');

        $product_name = '';
        $description = '';
        $estimated_hours = (float)$this->input->post('estimated_hours');
        $unit_price = 0;
        $qty = 1;
        $tax_id = '';
        $tax_rate = '';
        $tax_name = '';
        $discount = 0;
        $subtotal = 0;
        $item_id = $this->input->post('item_key');
        $part_item_key = $this->input->post('part_item_key');

        $labour_type = 'fixed';

        $labour_product = $this->workshop_model->get_labour_product($labour_product_id);
        if($labour_product){
            $tax_id_temp = [];
            $tax_rate_temp = [];
            $tax_name_temp = [];
            $product_name = $labour_product->name;
            $description = $labour_product->description;
            if(is_numeric($labour_product->tax) && $labour_product->tax != 0){
                $get_tax_name = $this->workshop_model->get_tax_name($labour_product->tax);
                $get_tax_rate = $this->workshop_model->tax_rate_by_id($labour_product->tax);
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $labour_product->tax;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }

            if(is_numeric($labour_product->tax2) && $labour_product->tax2 != 0){
                $get_tax_name = $this->workshop_model->get_tax_name($labour_product->tax2);
                $get_tax_rate = $this->workshop_model->tax_rate_by_id($labour_product->tax2);
                if($get_tax_name != ''){
                    $tax_name_temp[] = $get_tax_name;
                    $tax_id_temp[] = $labour_product->tax2;
                    $tax_rate_temp[] = $get_tax_rate;
                }
            }
            $tax_id = implode('|', $tax_id_temp);
            $tax_rate = implode('|', $tax_rate_temp);
            $tax_name = implode('|', $tax_name_temp);
            $labour_type = $labour_product->labour_type;
            $unit_price = $labour_product->labour_cost;
            if($labour_type == 'fixed'){
                $subtotal = (float)$labour_product->labour_cost;
            }else{
                $subtotal = (float)$labour_product->labour_cost * (float)$estimated_hours;
            }
        }

        $labour_product_row_template = $this->workshop_model->inspection_create_labour_product_row_template($name, $labour_product_id, $product_name, $description, $inspection_id, $inspection_form_id, $inspection_form_detail_id, $labour_type, $estimated_hours, $unit_price, $qty, $tax_id, $tax_rate, $tax_name, $discount, $subtotal, $item_id, false );

        // get part relation
        $part_row_template = '';
        if(isset($labour_product->parts)){
            $part_name = str_replace('newlabouritems', 'newpartitems', $name);

            foreach ($labour_product->parts as $key => $part) {
                $part_name = 'newpartitems['.$part_item_key.']';
                $part_row_template .= $this->workshop_model->inspection_get_part_row_template($part_name, $part['item_id'], $inspection_id, $inspection_form_id, $inspection_form_detail_id, $part['quantity'], $key+1);
                $part_item_key++;
            }
        }

        echo json_encode([
            'labour_product_row_template'  => $labour_product_row_template,
            'part_row_template'  => $part_row_template,
            'part_item_key'  => $part_item_key,
        ]);die;

    }

    /**
     * get part row template
     * @return [type]           
     */
    public function inspection_get_part_row_template()
    {
        $name = $this->input->post('name');
        $item_id = $this->input->post('part_id');
        $inspection_id = $this->input->post('inspection_id');
        $inspection_form_id = $this->input->post('inspection_form_id');
        $inspection_form_detail_id = $this->input->post('inspection_form_detail_id');
        $quantity = (float)$this->input->post('quantity');
        $item_key = $this->input->post('item_key');

        echo $this->workshop_model->inspection_get_part_row_template($name, $item_id, $inspection_id, $inspection_form_id, $inspection_form_detail_id, $quantity, $item_key);
    }

    /**
     * inspection form detail
     * @param  [type] $id 
     * @return [type]     
     */
    public function inspection_form_detail($id)
    {
        if ( !has_permission('workshop_inspection', '', 'edit') ) {
            access_denied('workshop_inspection');
        }
        if(!is_numeric($id) || $id == ''){
            blank_page('Inspection Not Found', 'danger');
        }

        $data = [];
        $data['id'] = $id;
        $data['inspection'] = $this->workshop_model->get_inspection($id);
        $data['inspection_forms'] = $this->workshop_model->get_inspection_form(false, false,'inspection_id = '. $id);
        $data['check_change_inspection_status'] = check_change_inspection_status($id);
        $allow_create_invoice = false;
        if(isset($data['inspection']->inspection_labour_products) || isset($data['inspection']->inspection_labour_materials)){
            $allow_create_invoice = true;
        }
        $data['allow_create_invoice'] = $allow_create_invoice;
        if(wshop_get_status_modules('warehouse')){
            $data['check_parts_available'] = $this->workshop_model->check_parts_available($id, 'inspection');
        }else{
            $data['check_parts_available'] = [
                'status' => true,
                'message' => '',
            ];
        }

        $this->load->view('inspections/inspection_template_forms/inspection_form_detail', $data);
    }

    /**
     * inspection approval form
     * @return [type] 
     */
    public function inspection_approval_form()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $status = false;
        $message = '';
        $response_text = '';
        $update_inspection_status = false;
        $data = $this->input->post();

        if($data['inspection_form_detail_id'] == 0){
            $this->db->where('relid', $data['inspection_id']);
            $update_inspection_status = true;

        }else{
            $this->db->where('relid', $data['inspection_id']);
            $this->db->where('inspection_form_detail_id', $data['inspection_form_detail_id']);
        }
        if($data['approve'] == 'rejected'){
            $response_text = '<span class="text-danger tw-font-semibold">'.$data['approve'].' on '._dt(date('Y-m-d H:i:s')).'</span>';
        }else{
            $response_text = '<span class="text-success tw-font-semibold">'.$data['approve'].' on '._dt(date('Y-m-d H:i:s')).'</span>';
        }
        $this->db->update(db_prefix() . 'wshop_inspection_values', [
            'approve' => $data['approve'],
            'approve_comment' => $data['approve_comment'],
            'approved_date' => date('Y-m-d H:i:s'),
        ]);
        if($this->db->affected_rows() > 0){
            $status = true;
            $message = _l('updated_successfully');

            $this->workshop_model->re_caculate_inspection($data['inspection_id']);
        }

        // check change inspection_status
         $this->db->where('relid', $data['inspection_id']);
         $this->db->where('inspection_result', 'repair');
         $this->db->where('approve is NULL');
         $inspection_value = $this->db->get(db_prefix() . 'wshop_inspection_values')->result_array();
         if(count($inspection_value) == 0){
            $update_inspection_status = true;
         }

        if($update_inspection_status){
            $success = $this->workshop_model->inspection_status_mark_as('Complete_Awaiting_Finalise', $data['inspection_id']);
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
            'update_inspection_status' => $update_inspection_status,
            'response_text' => $response_text,
        ]);
    }

    /* Convert estimate to invoice */
    public function convert_to_invoice($id, $type)
    {
        if (!has_permission('workshop_repair_job', '', 'create') && !has_permission('workshop_inspection', '', 'create') && !has_permission('workshop_repair_job', '', 'edit') && !has_permission('workshop_inspection', '', 'edit') ) {
            access_denied('invoices');
        }
        if (!$id) {
            die('No '.$type.' found');
        }

        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }

        $invoiceid = $this->workshop_model->convert_transaction_to_invoice($id, $type, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('estimate_convert_to_invoice_successfully'));
            redirect(admin_url('invoices/list_invoices/' . $invoiceid));
        } else {
            if($type == 'repair_job'){
                redirect(admin_url('workshop/repair_job_detail/'.$id.'?tab=detail'));
            }else{
                redirect(admin_url('workshop/inspection_detail/'.$id.'?tab=detail'));
            }
        }
    }

    /**
     * repair job send mail client
     * @return [type] 
     */
    public function repair_job_send_mail_client()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $repair_job_id = $data['repair_job_id'];
            $data['content'] = $this->input->post('content', false);
            $rs = $this->workshop_model->repair_job_send_mail_client($data);
            if ($rs == true) {
                set_alert('success', _l('wshop_send_mail_successfully'));
            }
            redirect(admin_url('workshop/repair_job_detail/'.$repair_job_id.'?tab=detail'));
        }
    }

    /**
     * inspection send mail client
     * @return [type] 
     */
    public function inspection_send_mail_client()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $inspection_id = $data['inspection_id'];
            $data['content'] = $this->input->post('content', false);
            $rs = $this->workshop_model->inspection_send_mail_client($data);
            if ($rs == true) {
                set_alert('success', _l('wshop_send_mail_successfully'));
            }
            redirect(admin_url('workshop/inspection_detail/'.$inspection_id.'?tab=detail'));
        }
    }

    /**
     * repair job report by status
     * @return [type] 
     */
    public function report_by_repair_job_month()
    {
        if ($this->input->is_ajax_request()) { 
            $data = $this->input->get();

            $months_report = $data['months_report'];
            $report_from = $data['report_from'];
            $report_to = $data['report_to'];

            if($months_report == ''){

                $from_date = date('Y-m-d', strtotime('1997-01-01'));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'this_month'){
                $from_date = date('Y-m-01');
                $to_date   = date('Y-m-t');
            }

            if($months_report == '1'){ 
                $from_date = date('Y-m-01', strtotime('first day of last month'));
                $to_date   = date('Y-m-t', strtotime('last day of last month'));
            }

            if($months_report == 'this_year'){
                $from_date = date('Y-m-d', strtotime(date('Y-01-01')));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'last_year'){

                $from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
                $to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));  


            }

            if($months_report == '3'){
                $months_report = 3;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');

            }

            if($months_report == '6'){
                $months_report = 6;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == '12'){
                $months_report = 12;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == 'custom'){
                $from_date = to_sql_date($report_from);
                $to_date   = to_sql_date($report_to);
            }
    
            $mo_data = $this->workshop_model->get_repair_job_month($from_date, $to_date);


            echo json_encode([
                'categories' => $mo_data['categories'],
                'total' => $mo_data['total'],
                'labour_total' => $mo_data['labour_total'],
                'estimated_hours' => $mo_data['estimated_hours'],
            ]); 
        }
    }

    /**
     * report by repair job weekly
     * @return [type] 
     */
    public function report_by_repair_job_weekly()
    {
        if ($this->input->is_ajax_request()) { 
            $data = $this->input->get();

            $months_report = $data['months_report'];
            $report_from = $data['report_from'];
            $report_to = $data['report_to'];

            if($months_report == ''){

                $from_date = date('Y-m-d', strtotime('1997-01-01'));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'this_month'){
                $from_date = date('Y-m-01');
                $to_date   = date('Y-m-t');
            }

            if($months_report == '1'){ 
                $from_date = date('Y-m-01', strtotime('first day of last month'));
                $to_date   = date('Y-m-t', strtotime('last day of last month'));
            }

            if($months_report == 'this_year'){
                $from_date = date('Y-m-d', strtotime(date('Y-01-01')));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'last_year'){

                $from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
                $to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));  


            }

            if($months_report == '3'){
                $months_report = 3;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');

            }

            if($months_report == '6'){
                $months_report = 6;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == '12'){
                $months_report = 12;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == 'custom'){
                $from_date = to_sql_date($report_from);
                $to_date   = to_sql_date($report_to);
            }
    
            $mo_data = $this->workshop_model->get_repair_job_weekly($from_date, $to_date);


            echo json_encode([
                'categories' => $mo_data['categories'],
                'total' => $mo_data['total'],
                'labour_total' => $mo_data['labour_total'],
                'estimated_hours' => $mo_data['estimated_hours'],
            ]); 
        }
    }

    /**
     * report by mechanic performance
     * @return [type] 
     */
    public function report_by_mechanic_performance()
    {
        if ($this->input->is_ajax_request()) { 
            $data = $this->input->get();

            $months_report = $data['months_report'];
            $report_from = $data['report_from'];
            $report_to = $data['report_to'];

            if($months_report == ''){

                $from_date = date('Y-m-d', strtotime('1997-01-01'));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'this_month'){
                $from_date = date('Y-m-01');
                $to_date   = date('Y-m-t');
            }

            if($months_report == '1'){ 
                $from_date = date('Y-m-01', strtotime('first day of last month'));
                $to_date   = date('Y-m-t', strtotime('last day of last month'));
            }

            if($months_report == 'this_year'){
                $from_date = date('Y-m-d', strtotime(date('Y-01-01')));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'last_year'){

                $from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
                $to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));  


            }

            if($months_report == '3'){
                $months_report = 3;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');

            }

            if($months_report == '6'){
                $months_report = 6;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == '12'){
                $months_report = 12;
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == 'custom'){
                $from_date = to_sql_date($report_from);
                $to_date   = to_sql_date($report_to);
            }
    
            $mo_data = $this->workshop_model->get_report_mechanic_performance($from_date, $to_date);


            echo json_encode([
                'categories' => $mo_data['categories'],
                'estimated_hours' => $mo_data['estimated_hours'],
            ]); 
        }
    }


    /**
     * dashboard
     * @return [type] 
     */
    public function dashboard()
    {
        if (!has_permission('workshop_dashboard', '', 'view')  && !is_admin()) {
            access_denied('dashboard');
        }

        $data['title'] = _l('wshop_dashboard');
        $data['baseCurrency'] = get_base_currency();
        $data['repair_job_by_time_range'] = $this->workshop_model->repair_job_by_time_range();
        $data['count_inspection_by_status'] = $this->workshop_model->count_inspection_by_status();

        $this->load->view('workshop/dashboards/dashboard', $data);
    }

    /**
     * repair job print report pdf
     * @param  [type] $id 
     * @return [type]     
     */
    public function repair_job_print_report_80_pdf($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/repair_jobs'));
        }
        $this->load->model('clients_model');
        $this->load->model('currencies_model');

        $repair_job_number = '';
        $repair_job = $this->workshop_model->get_repair_job($id);

        $base_currency = $this->currencies_model->get_base_currency();
        $currency = $base_currency;
        if(is_numeric($repair_job->currency) && $repair_job->currency != 0){
            $currency = $repair_job->currency;
        }

        $repair_job->client = $this->clients_model->get($repair_job->client_id);
        $repair_job->currency = $currency;

        $repair_job->workshops = $this->workshop_model->get_workshop(false, ['repair_job_id' => $id]);
        $repair_job->inspection = $this->workshop_model->get_inspection(false, ['repair_job_id' => $id]);
        $repair_job->device = $this->workshop_model->get_device($repair_job->device_id);

        if($repair_job){
            $repair_job_number .= $repair_job->job_tracking_number;
        }
        try {
            $pdf = $this->workshop_model->receipt_report_80_pdf($repair_job);

        } catch (Exception $e) {
            echo new_html_entity_decode($e->getMessage());
            die;
        }

        $type = 'D';
        ob_end_clean();

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($repair_job_number)).'.pdf', $type);
    }

    /**
     * repair job print a4 report pdf
     * @param  [type] $id 
     * @return [type]     
     */
    public function repair_job_print_a4_report_pdf($id)
    {
        if (!$id) {
            redirect(admin_url('workshop/repair_jobs'));
        }
        $this->load->model('clients_model');
        $this->load->model('currencies_model');

        $repair_job_number = '';
        $repair_job = $this->workshop_model->get_repair_job($id);

        $base_currency = $this->currencies_model->get_base_currency();
        $currency = $base_currency;
        if(is_numeric($repair_job->currency) && $repair_job->currency != 0){
            $currency = $repair_job->currency;
        }

        $repair_job->client = $this->clients_model->get($repair_job->client_id);
        $repair_job->currency = $currency;

        $repair_job->workshops = $this->workshop_model->get_workshop(false, ['repair_job_id' => $id]);
        $repair_job->inspection = $this->workshop_model->get_inspection(false, ['repair_job_id' => $id]);
        $repair_job->device = $this->workshop_model->get_device($repair_job->device_id);

        $repair_job->tax_labour_data = $this->workshop_model->get_html_tax_labour_repair_job($id, $repair_job->currency);
        $repair_job->tax_part_data = $this->workshop_model->get_html_tax_part_repair_job($id, $repair_job->currency);

        if(count($repair_job->inspection) > 0){
            if(is_numeric($repair_job->inspection[0]['id'])){
                $inspection_id = $repair_job->inspection[0]['id'];

                $get_inspection = $this->workshop_model->get_inspection($inspection_id);
                $repair_job->inspection_data = $get_inspection;
                if($get_inspection){
                    if(isset($get_inspection->inspection_labour_products)){
                        $repair_job->inspection_labour_products = $get_inspection->inspection_labour_products;
                    }
                    if(isset($get_inspection->inspection_labour_materials)){
                        $repair_job->inspection_parts = $get_inspection->inspection_labour_materials;
                    }
                }

            }
        }

        $client = $this->clients_model->get($repair_job->client_id);
        $client->clientid = $client->userid;
        $client->client = $client;

        $repair_job->clientid = $repair_job->client_id;
        $repair_job->client = $client;


        if($repair_job){
            $repair_job_number .= $repair_job->job_tracking_number;
        }
        try {
            $pdf = $this->workshop_model->receipt_a4_report_pdf($repair_job);

        } catch (Exception $e) {
            echo new_html_entity_decode($e->getMessage());
            die;
        }

        $type = 'D';
        ob_end_clean();

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($repair_job_number)).'.pdf', $type);
    }

    /**
     * real permission table
     * @return [type] 
     */
    public function workshop_permission_table() {
        if ($this->input->is_ajax_request()) {

            $select = [
                'staffid',
                'CONCAT(firstname," ",lastname) as full_name',
                'firstname', //for role name
                'email',
                'phonenumber',
            ];
            $where = [];
            $where[] = 'AND ' . db_prefix() . 'staff.admin != 1';

            $arr_staff_id = workshop_get_staff_id_permissions();

            if (count($arr_staff_id) > 0) {
                $where[] = 'AND ' . db_prefix() . 'staff.staffid IN (' . implode(', ', $arr_staff_id) . ')';
            } else {
                $where[] = 'AND ' . db_prefix() . 'staff.staffid IN ("")';
            }

            $aColumns = $select;
            $sIndexColumn = 'staffid';
            $sTable = db_prefix() . 'staff';
            $join = ['LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role'];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'roles.name as role_name', db_prefix() . 'staff.role']);

            $output = $result['output'];
            $rResult = $result['rResult'];

            $not_hide = '';

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['full_name'] . '</a>';

                $row[] = $aRow['role_name'];
                $row[] = $aRow['email'];
                $row[] = $aRow['phonenumber'];

                $options = '';

                if (has_permission('workshop_setting', '', 'edit')) {
                    $options = icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
                        'title' => _l('edit'),
                        'onclick' => 'workshop_permissions_update(' . $aRow['staffid'] . ', ' . $aRow['role'] . ', ' . $not_hide . '); return false;',
                    ]);
                }

                if (has_permission('workshop_setting', '', 'delete')) {
                    $options .= icon_btn('workshop/delete_workshop_permission/' . $aRow['staffid'], 'fa fa-remove', 'btn-danger _delete', ['title' => _l('delete')]);
                }

                $row[] = $options;

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * permission modal
     * @return [type] 
     */
    public function permission_modal() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->load->model('staff_model');

        if ($this->input->post('slug') === 'update') {
            $staff_id = $this->input->post('staff_id');
            $role_id = $this->input->post('role_id');

            $data = ['funcData' => ['staff_id' => isset($staff_id) ? $staff_id : null]];

            if (isset($staff_id)) {
                $data['member'] = $this->staff_model->get($staff_id);
            }

            $data['roles_value'] = $this->roles_model->get();
            $data['staffs'] = workshop_get_staff_id_dont_permissions();
            $add_new = $this->input->post('add_new');

            if ($add_new == ' hide') {
                $data['add_new'] = ' hide';
                $data['display_staff'] = '';
            } else {
                $data['add_new'] = '';
                $data['display_staff'] = ' hide';
            }
            
            $this->load->view('settings/permissions/permission_modal', $data);
        }
    }

    /**
     * workshop update permissions
     * @param  string $id 
     * @return [type]     
     */
    public function workshop_update_permissions($id = '') {
        if (!is_admin() && !has_permission('workshop_setting', '', 'create') && !has_permission('workshop_setting', '', 'edit')) {
            access_denied('workshop');
        }
        $data = $this->input->post();

        if (!isset($id) || $id == '') {
            $id = $data['staff_id'];
        }

        if (isset($id) && $id != '') {

            $data = hooks()->apply_filters('before_update_staff_member', $data, $id);

            if (is_admin()) {
                if (isset($data['administrator'])) {
                    $data['admin'] = 1;
                    unset($data['administrator']);
                } else {
                    if ($id != get_staff_user_id()) {
                        if ($id == 1) {
                            return [
                                'cant_remove_main_admin' => true,
                            ];
                        }
                    } else {
                        return [
                            'cant_remove_yourself_from_admin' => true,
                        ];
                    }
                    $data['admin'] = 0;
                }
            }

            $this->db->where('staffid', $id);
            $this->db->update(db_prefix() . 'staff', [
                'role' => $data['role'],
            ]);

            $response = $this->staff_model->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $data['permissions']), $id);
        } else {
            $this->load->model('roles_model');

            $role_id = $data['role'];
            unset($data['role']);
            unset($data['staff_id']);

            $data['update_staff_permissions'] = true;

            $response = $this->roles_model->update($data, $role_id);
        }

        if (is_array($response)) {
            if (isset($response['cant_remove_main_admin'])) {
                set_alert('warning', _l('staff_cant_remove_main_admin'));
            } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
            }
        } elseif ($response == true) {
            set_alert('success', _l('updated_successfully', _l('staff_member')));
        }
        redirect(admin_url('workshop/setting?group=permissions'));

    }

    /**
     * delete workshop permission
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_workshop_permission($id) {
        if (!is_admin()) {
            access_denied('hr_profile');
        }

        $response = $this->workshop_model->delete_workshop_permission($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('hr_is_referenced', _l('department_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('hr_department')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('department_lowercase')));
        }
        redirect(admin_url('workshop/setting?group=permissions'));

    }

    /**
     * role changed
     * @param  [type] $id 
     * @return [type]     
     */
    public function role_changed($id)
    {
        echo json_encode($this->roles_model->get($id)->permissions);
    }

    /**
     * reset data
     * @return [type] 
     */
    public function reset_data()
    {

        if ( !is_admin()) {
            access_denied('workshop');
        }
            //delete wshop_repair_jobs
            $this->db->truncate(db_prefix().'wshop_repair_jobs');
            //delete wshop_inspections
            $this->db->truncate(db_prefix().'wshop_inspections');
            //delete goods_receipt_detail
            $this->db->truncate(db_prefix().'goods_receipt_detail');
            //delete wshop_workshops
            $this->db->truncate(db_prefix().'wshop_workshops');
            //delete wshop_return_deliveries
            $this->db->truncate(db_prefix().'wshop_return_deliveries');
            //delete wshop_return_delivery_notes
            $this->db->truncate(db_prefix().'wshop_return_delivery_notes');
            //delete wshop_repair_job_labour_products
            $this->db->truncate(db_prefix().'wshop_repair_job_labour_products');
            //delete wshop_repair_job_labour_materials
            $this->db->truncate(db_prefix().'wshop_repair_job_labour_materials');
            //delete wshop_inspection_values
            $this->db->truncate(db_prefix().'wshop_inspection_values');
            //delete wshop_inspection_forms
            $this->db->truncate(db_prefix().'wshop_inspection_forms');
            //delete wshop_inspection_form_details
            $this->db->truncate(db_prefix().'wshop_inspection_form_details');


            //delete sub folder REPAIR_JOB_BARCODE
            foreach(glob(REPAIR_JOB_BARCODE . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(REPAIR_JOB_BARCODE.$filename);
                }
            }

            //delete sub folder TRANSACTION_FOLDER
            foreach(glob(TRANSACTION_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(TRANSACTION_FOLDER.$filename);
                }
            }

            //delete sub folder NOTE_FOLDER
            foreach(glob(NOTE_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(NOTE_FOLDER.$filename);
                }
            }
            
            //delete sub folder WORKSHOP_FOLDER
            foreach(glob(WORKSHOP_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(WORKSHOP_FOLDER.$filename);
                }
            }

            //delete sub folder INSPECTION_FOLDER
            foreach(glob(INSPECTION_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(INSPECTION_FOLDER.$filename);
                }
            }

            //delete sub folder INSPECTION_QUESTION_FOLDER
            foreach(glob(INSPECTION_QUESTION_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(INSPECTION_QUESTION_FOLDER.$filename);
                }
            }

            //delete sub folder REPAIR_JOB_QR_FOLDER
            foreach(glob(REPAIR_JOB_QR_FOLDER . '*') as $file) { 
                $file_arr = new_explode("/",$file);
                $filename = array_pop($file_arr);

                if(is_dir($file)) {
                    delete_dir(REPAIR_JOB_QR_FOLDER.$filename);
                }
            }
            

            //delete create task rel_type: "wshop_inspection"
            $this->db->where('rel_type', 'wshop_inspection');
            $this->db->delete(db_prefix() . 'tasks');

            set_alert('success',_l('reset_data_successful'));
            
            redirect(admin_url('workshop/setting?group=reset_data'));

    }

    public function re_caculate_inspection($inspection_id){
        $this->workshop_model->re_caculate_inspection($inspection_id);
    }

    /*end file*/
}
