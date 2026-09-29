<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Setting extends AdminController {


    public function __construct()
    {

        parent::__construct();

        $this->check_db_extrasetting();

    }

    public function index()
    {


        if (!has_permission('extra_setting', '', 'extra_setting')) {

            access_denied('es_settings');

        }



        $data['title'] = _l('es_settings');

        $data['tabs'] = $this->setting_tabs();

        $active_tab = $this->input->get('tab');

        if ( empty( $active_tab ) )
            $active_tab = 'invoice';

        $data['active_tab'] = $active_tab;

        $this->load->view('v_extra_settings' , $data);

    }

    private function setting_tabs()
    {

        $tabs = [];

        $tabs[] = [  'slug' => 'invoice'    , 'text' => _l('invoice') , 'icon' => 'fa fa-file' ];

        $tabs[] = [  'slug' => 'estimate'   , 'text' => _l('estimate') , 'icon' => 'fa fa-file' ];

        $tabs[] = [  'slug' => 'proposal'   , 'text' => _l('proposal') , 'icon' => 'fa fa-file' ];

        $tabs[] = [  'slug' => 'customer'   , 'text' => _l('client') , 'icon' => 'fa fa-user' ];

        $tabs[] = [  'slug' => 'contract'   , 'text' => _l('contract') , 'icon' => 'fa fa-file-contract' ];

        $tabs[] = [  'slug' => 'expenses'   , 'text' => _l('expenses') , 'icon' => 'fa fa-file-lines' ];

        $tabs[] = [  'slug' => 'project'    , 'text' => _l('project') , 'icon' => 'fa fa-chart-gantt' ];

        $tabs[] = [  'slug' => 'tasks'      , 'text' => _l('tasks') , 'icon' => 'fa fa-circle-check' ];

        $tabs[] = [  'slug' => 'leads'      , 'text' => _l('leads') , 'icon' => 'fa fa-tty' ];

        $tabs[] = [  'slug' => 'general'    , 'text' => _l('settings_group_general') , 'icon' => 'fa fa-cog' ];


        return $tabs;

    }


    public function save_setting()
    {

        if ( $this->input->is_ajax_request() )
        {

            $name = $this->input->post('name');
            $value = $this->input->post('value');

            if ( is_array( $value ) )
                $value = json_encode( $value );

            extra_setting_update_option_value( $name , $value );

            echo json_encode( [  'message' => _l('updated_successfully' , _l('setting') ) ] );

        }

    }



    /**
     * @Version 1.0.3
     */
    public function second_currency( $rel_type , $rel_id = 0 )
    {

        $es_target_exchange_rate = '';
        $es_target_currency = '';
        $es_target_currency_amount = 0;

        if ( !empty( $rel_id ) )
        {

            $rel_data = $this->db->select('es_target_exchange_rate,es_target_currency,es_target_currency_amount')->from(db_prefix().$rel_type)->where('id',$rel_id)->get()->row();

            if ( !empty( $rel_data->es_target_exchange_rate ) )
            {

                $es_target_exchange_rate    = $rel_data->es_target_exchange_rate;
                $es_target_currency         = $rel_data->es_target_currency;
                $es_target_currency_amount  = $rel_data->es_target_currency_amount;

            }

        }


        if ( !class_exists( 'Currencies_model' , false ) )
            $this->load->model('currencies_model');

        $currencies = $this->currencies_model->get();

        $es_target_currency_content = '<div class="row es_target_currency_content_remove">
    
                                        <div class="col-md-6">
                                        
                                            <div class="row">
                                                <div class="col-md-6">
                                                    '. render_select('es_target_currency', $currencies, ['id', 'name', 'symbol'], 'es_target_currency' , $es_target_currency ) .'                   
                                                </div>
                                    
                                                <div class="col-md-6">
                                                    '. render_input('es_target_exchange_rate' , 'es_target_exchange_rate' , $es_target_exchange_rate , 'number' , [ 'step' => '0.000001' ] ) .'
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                
                                    </div>';


        $es_target_currency_amount = '
                                    <tr class="es_target_currency_content_remove">
                
                                        <td>
                
                                            <span class="bold tw-text-neutral-700">'. _l('es_target_currency_amount') .' :</span>
                                            <input type="hidden" id="es_target_currency_amount" name="es_target_currency_amount" value="'.$es_target_currency_amount.'">
                                            
                                        </td>
                
                                        <td class="es_target_currency_amount">
                                            '.app_format_money($es_target_currency_amount,$es_target_currency).'
                                        </td>
                
                                    </tr>';

        $json_data = [

            'es_target_currency_content' => $es_target_currency_content ,

            'es_target_currency_amount' => $es_target_currency_amount ,

        ];


        echo json_encode( $json_data );

    }


    /**
     * @Version  1.0.4
     */
    public function contract_currency( $contract_id = 0 )
    {

        if ( !class_exists( 'Currencies_model' ) )
            $this->load->model('currencies_model');

        if ( !class_exists( 'Contracts_model' ) )
            $this->load->model('contracts_model');

        $contract = $this->contracts_model->get( $contract_id );

        $currencies = $this->currencies_model->get();

        $es_currency = '';

        if ( !empty( $contract->es_currency ) )
        {

            $es_currency = $contract->es_currency;

        }
        else
        {

            $base_currency = get_base_currency();

            if ( !empty( $base_currency->id ) )
                $es_currency = $base_currency->id;

        }

        $es_currency_content = '<div class="row es_target_currency_content_remove">
    
                                            <div class="col-md-6">
                                           
                                                <div class="form-group">
                                                
                                                    <label for="contract_value">'._l('contract_value').'</label>
                                           
                                                    <input type="number" class="form-control" name="contract_value" value="'.( $contract->contract_value ?? '' ).'" '. ( !empty($contract) && $contract->signed == 1 ? ' disabled' : '' ) .'>
                                            
                                                </div>
                                            
                                            </div>
    
                                            <div class="col-md-6">
                                            
                                                '. render_select('es_currency', $currencies, ['id', 'name', 'symbol'], 'currency' , $es_currency ) .'                   
                                            
                                            </div>                                        
                                             
                                        </div>';

        $json_data = [

            'es_currency_content' => $es_currency_content ,
        ];


        echo json_encode( $json_data );

    }


    /**
     * Checking db
     */

    public function check_db_extrasetting()
    {

        $CI = &get_instance();

        if( !$CI->db->field_exists('es_target_currency', db_prefix() .'invoices') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'invoices`
                                ADD COLUMN `es_target_currency` int NULL DEFAULT 0 AFTER `short_link`,
                                ADD COLUMN `es_target_exchange_rate` decimal(15, 6) NULL DEFAULT 0 AFTER `es_target_currency`,
                                ADD COLUMN `es_target_currency_amount` decimal(15, 2) NULL DEFAULT 0 AFTER `es_target_exchange_rate`;');

        }


        if( !$CI->db->field_exists('es_target_currency', db_prefix() .'estimates') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'estimates`
                                ADD COLUMN `es_target_currency` int NULL DEFAULT 0 AFTER `short_link`,
                                ADD COLUMN `es_target_exchange_rate` decimal(15, 6) NULL DEFAULT 0 AFTER `es_target_currency`,
                                ADD COLUMN `es_target_currency_amount` decimal(15, 2) NULL DEFAULT 0 AFTER `es_target_exchange_rate`;');

        }


        if( !$CI->db->field_exists('es_target_currency', db_prefix() .'proposals') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'proposals`
                                ADD COLUMN `es_target_currency` int NULL DEFAULT 0 AFTER `short_link`,
                                ADD COLUMN `es_target_exchange_rate` decimal(15, 6) NULL DEFAULT 0 AFTER `es_target_currency`,
                                ADD COLUMN `es_target_currency_amount` decimal(15, 2) NULL DEFAULT 0 AFTER `es_target_exchange_rate`;');

        }




        if( !$CI->db->field_exists('es_currency', db_prefix() .'contracts') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'contracts`
                                ADD COLUMN `es_currency` int NULL AFTER `signature`;');

        }


        if( !$CI->db->field_exists('es_status_change_date', db_prefix() .'invoices') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'invoices`
                                ADD COLUMN `es_status_change_date` datetime NULL AFTER `es_target_currency_amount`;');

        }


        if( !$CI->db->field_exists('es_status_change_date', db_prefix() .'clients') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'clients` 
                                ADD COLUMN `es_status_change_date` datetime NULL AFTER `show_primary_contact`;');

        }


        if( !$CI->db->field_exists('es_status_change_date', db_prefix() .'projects') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'projects` 
                                ADD COLUMN `es_status_change_date` datetime NULL AFTER `addedfrom`;');

        }


        if( !$CI->db->field_exists('es_status_change_date', db_prefix() .'tasks') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'tasks` 
                                ADD COLUMN `es_status_change_date` datetime NULL AFTER `milestone`;');

        }


        if( !$CI->db->field_exists('es_status_change_date', db_prefix() .'leads') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'leads` 
                                ADD COLUMN `es_status_change_date` datetime NULL AFTER `dateadded`;');

        }


        if( !$CI->db->field_exists('es_otp_code', db_prefix() .'contacts') )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'contacts`
                                ADD COLUMN `es_otp_code` varchar(20) NULL AFTER `profile_image`;');

        }

    }


    public function custom_fields( $rel_type )
    {

        $custom_field_content = '';

        $option_value = extra_setting_get_option_value( 'es_show_custom_fields_on_merge_fields_'.$rel_type , 0 );

        if ( !empty( $option_value ) )
        {

            $custom_fields = get_custom_fields($rel_type);

            if ( !empty( $custom_fields ) )
            {

                foreach ($custom_fields as $field)
                {

                    $custom_field_content .= '<li class="list-group-item"><b>' .$field['name']. '</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false"> {' . $field['slug'] . '} </a></li>';

                }


            }


        }

        echo $custom_field_content;

    }

}
