<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_cancellation_request_confirmation_to_client extends App_mail_template
{
    protected $for = 'contact';

    protected $appointment;

    public $slug = 'appointment-cancellation-request-confirmation-to-client';

    public $rel_type = 'appointment';

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;
        $this->rel_id = $appointment->id;

        // For SMS and merge fields for email
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build()
    {
        $this->to($this->appointment->email);
    }
}
