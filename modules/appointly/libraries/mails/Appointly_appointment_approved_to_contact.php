<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_approved_to_contact extends App_mail_template
{
    public $slug = 'appointment-approved-to-contact';
    /**
     * Relation ID, e.q. appointment id
     *
     * @var mixed
     */
    public $rel_id;
    /**
     * Relation ID, e.q. appointment
     *
     * @var mixed
     */
    public $rel_type;
    protected $for = 'contact';
    protected $appointment;
    /**
     * Relation ID, e.q. staf
     *
     * @var mixed
     */
    protected $staff;

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;
        $this->rel_id = $appointment->id;
        $this->rel_type = 'appointment';

        $attendees = $appointment->attendees;


        foreach ($attendees as $attendee) {
            $this->set_merge_fields('staff_merge_fields', $attendee->staff_id);

        }
        // For SMS and merge fields for email
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build()
    {
        $this->to($this->appointment->email);
    }
}
