<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_updated_to_contact extends App_mail_template
{
    protected $appointment;

    public $slug = 'appointly-appointment-updated-to-contact';
    public $rel_type = 'appointly';

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build()
    {
        $this->to($this->appointment->email);
    }
}
