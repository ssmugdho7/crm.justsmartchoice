<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_reschedule_approved_to_contact extends App_mail_template
{
    protected $for = 'contact';

    protected $appointment;

    public $slug = 'appointly-reschedule-approved-to-contact';

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
