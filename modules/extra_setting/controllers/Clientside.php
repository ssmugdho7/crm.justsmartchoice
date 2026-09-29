<?php

class Clientside extends App_Controller
{

    public $send_otp_type = 'phone';

    public function __construct()
    {

        parent::__construct();

        $this->send_otp_type = extra_setting_get_option_value( 'es_enable_contact_login_using_otp_type' , 'phone' );

    }

    public function check_data()
    {

        if ( $this->input->is_ajax_request() )
        {


            if ( $this->send_otp_type == 'email' )
                $this->check_email();
            else
                $this->check_phone_number();

        }


    }

    /**
     * sending code via sms
     */
    private function check_phone_number()
    {


        list( $success , $contact_id , $check_phone_number ) = $this->check_input_for_phone();


        if ( $success == 0 )
        {
            echo json_encode( [ 'success' => false , 'message' => _l('es_client_phone_number_is_not_correct') ] );
            die();
        }

        if ( $success > 1 )
        {
            echo json_encode( [ 'success' => false , 'message' => _l('es_client_phone_number_more_record') ] );
            die();
        }

        if ( $success == 1 )
        {

            // send sms to company
            $user_data = [
                'extra_contact_id' => $contact_id
            ];

            $this->session->set_userdata($user_data);

            $result = $this->send_sms_code( $contact_id , $check_phone_number );

            if ( $result )
            {
                echo json_encode( [ 'success' => true ] );
            }
            else
            {
                echo json_encode( [ 'success' => false , 'message' => _l('es_error_send_sms') ] );
            }

        }



    }

    /**
     * sending code via email
     */
    private function check_email()
    {

        list( $success , $contact_id , $check_phone_number ) = $this->check_input_for_email();


        if ( $success == 0 )
        {
            echo json_encode( [ 'success' => false , 'message' => _l('es_client_email_is_not_correct') ] );
            die();
        }

        if ( $success > 1 )
        {
            echo json_encode( [ 'success' => false , 'message' => _l('es_client_phone_number_more_record') ] );
            die();
        }

        if ( $success == 1 )
        {

            // send sms to company
            $user_data = [
                'extra_contact_id' => $contact_id
            ];

            $this->session->set_userdata($user_data);

            $result = $this->send_sms_code( $contact_id , $check_phone_number );

            if ( $result )
            {
                echo json_encode( [ 'success' => true ] );
            }
            else
            {
                echo json_encode( [ 'success' => false , 'message' => _l('es_error_send_sms') ] );
            }

        }

    }

    private function check_input_for_phone()
    {

        $check_phone_number = extra_setting_number_cast( $this->input->post('phone_number') );

        if ( empty( $check_phone_number ) )
        {

            echo json_encode( [ 'success' => false , 'message' => _l('es_client_phone_number_required') ] );
            die();

        }


        $contacts = $this->db->select('con.phonenumber, con.id')
                            ->from(db_prefix().'clients c')
                            ->join(db_prefix().'contacts con','con.userid = c.userid')
                            ->where('c.active',1)
                            ->where('con.active',1)
                            ->where("COALESCE( con.phonenumber , '') != ''",null,false)
                            ->get()
                            ->result();

        $success = 0;
        $contact_id = 0;

        if ( !empty( $contacts ) )
        {

            foreach ( $contacts as $contact )
            {

                $con_phone = extra_setting_number_cast( $contact->phonenumber );

                if ( $con_phone == $check_phone_number )
                {

                    $success++;
                    $contact_id = $contact->id;

                }

            }

        }

        return [ $success , $contact_id , $check_phone_number ];

    }


    private function check_input_for_email()
    {

        $check_phone_number = $this->input->post('phone_number');

        if ( empty( $check_phone_number ) )
        {

            echo json_encode( [ 'success' => false , 'message' => _l('es_client_email_required') ] );
            die();

        }


        $contacts = $this->db->select('con.email, con.id')
                            ->from(db_prefix().'clients c')
                            ->join(db_prefix().'contacts con','con.userid = c.userid')
                            ->where('c.active',1)
                            ->where('con.active',1)
                            ->where("COALESCE( con.email , '') != ''",null,false)
                            ->get()
                            ->result();

        $success = 0;
        $contact_id = 0;

        if ( !empty( $contacts ) )
        {

            foreach ( $contacts as $contact )
            {

                if ( $contact->email == $check_phone_number )
                {

                    $success++;
                    $contact_id = $contact->id;

                }

            }

        }


        return [ $success , $contact_id , $check_phone_number ];

    }


    public function send_code_again()
    {


        if ( $this->input->is_ajax_request() )
        {


            if ( $this->send_otp_type == 'email' )
            {

                list( $success , $contact_id , $check_phone_number ) = $this->check_input_for_email();


                if ( $success == 0 )
                {
                    echo json_encode( [ 'success' => false , 'message' => _l('es_client_email_is_not_correct') ] );
                    die();
                }


            }
            else
            {

                list( $success , $contact_id , $check_phone_number ) = $this->check_input_for_phone();

                if ( $success == 0 )
                {
                    echo json_encode( [ 'success' => false , 'message' => _l('es_client_phone_number_is_not_correct') ] );
                    die();
                }

            }



            if ( $success == 1 )
            {

                $result = $this->send_sms_code( $contact_id , $check_phone_number );

                if ( $result )
                {
                    echo json_encode( [ 'success' => true ] );
                }
                else
                {
                    echo json_encode( [ 'success' => false , 'message' => _l('es_error_send_sms') ] );
                }

            }


        }


    }


    /**
     * checking the code
     *
     * @return void
     */
    public function check_otp_code()
    {

        if( $this->input->is_ajax_request() )
        {

            $extra_contact_id = $this->session->userdata('extra_contact_id');

            $code       = $this->input->post('otp_code');

            $contact_info = $this->db->select('es_otp_code,email,id,userid')->from(db_prefix().'contacts')->where('id',$extra_contact_id)->get()->row();

            if ( $contact_info->es_otp_code == $code )
            {

                $this->login_success( $contact_info );

                echo json_encode( [ 'success' => true ] );

            }
            else
            {

                echo json_encode( [ 'success' => false , 'message' => _l('ex_code_not_correct') ] );

            }

        }

    }

    /**
     * Sending sms to phone number
     * @return void
     */
    private function send_sms_code( $session_contact_id , $phone_email )
    {

        $code =  rand(10000, 99999);


        if ( $this->send_otp_type == 'email' )
        {

            $result = $this->es_send_mail( $phone_email , $code );

            if( $result )
            {

                $this->db->where('id',$session_contact_id)->update( db_prefix().'contacts' , [ 'es_otp_code' => $code ] );

                return true;

            }

        }
        else
        {

            $option_text = get_option( "sms_trigger_es_login_sms" );

            if( empty( $option_text ) )
            {
                $default_sms_text = "Sms code : {opt_code}";


                $this->db->where('name','sms_trigger_es_login_sms')
                        ->delete(db_prefix().'options');

                $this->db->set('name','sms_trigger_es_login_sms')
                            ->set('value',$default_sms_text)
                            ->set('autoload',0)
                            ->insert(db_prefix().'options');

            }

            if( $this->app_sms->trigger( SMS_EXTRA_SETTING_LOGIN_SMS , $phone_email , [ '{opt_code}' => $code ] ) )
            {

                $this->db->where('id',$session_contact_id)->update( db_prefix().'contacts' , [ 'es_otp_code' => $code ] );

                return true;

            }

        }


        return false;

    }


    private function es_send_mail( $email , $code )
    {

        $subject            = get_option('companyname').' | OTP ';

        $view_data          = [
            'code'     => $code
        ];

        $view_name          = 'mail_otp_content';
        $view_path          = module_views_path(EXTRA_SETTING_MODULE_NAME,'include/my_mail_otp_content');

        if ( file_exists( $view_path ) )
        {
            $view_name      = 'my_mail_otp_content';
        }

        $message_content = $this->load->view(EXTRA_SETTING_MODULE_NAME."/include/".$view_name , $view_data ,true);

        $cnf = [

            'from_email' => get_option('smtp_email'),

            'from_name'  => get_option('companyname'),

            'email'      => $email,

        ];


        $this->load->config('email');

        $this->email->from($cnf['from_email'], $cnf['from_name']);

        $this->email->to($cnf['email']);

        $this->email->subject($subject);

        $this->email->message($message_content);


        $systemBCC = get_option('bcc_emails');

        if ($systemBCC != '') {

            $this->email->bcc($systemBCC);

        }

        $result = $this->email->send();

        $this->email->send_queue();

        return $result;

    }





    /**
     * If code is true.
     *
     */
    private function login_success( $contact )
    {


        hooks()->do_action('before_client_login', [

            'email'           => $contact->email,

            'userid'          => $contact->userid,

            'contact_user_id' => $contact->id,

        ]);



        $user_data = [

            'client_user_id'   => $contact->userid,

            'contact_user_id'  => $contact->id,

            'client_logged_in' => true,

        ];


        $this->session->set_userdata($user_data);


        $this->load->model('announcements_model');

        $this->announcements_model->set_announcements_as_read_except_last_one(get_contact_user_id());



        hooks()->do_action('after_contact_login');


    }

}
