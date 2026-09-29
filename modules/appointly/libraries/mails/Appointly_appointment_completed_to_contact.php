<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_completed_to_contact extends App_mail_template
{
    protected $appointment;

    public $slug = 'appointment-completed-to-contact';
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
