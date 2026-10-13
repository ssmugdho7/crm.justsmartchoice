<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Appointly_dispatch_customer_status extends App_mail_template
{
    protected $for = 'contact';
    protected $skipQueue = true;
    public $slug = 'appointly-dispatch-customer-status';
    public $rel_type = 'appointly';
    protected $appointment;
    protected $status;
    protected $message;
    public function __construct($appointment, $status, $message)
    {
        parent::__construct();
        $this->appointment = $appointment;
        $this->status = $status;
        $this->message = $message;
        $provider = !empty($appointment->provider_id) ? get_staff_full_name($appointment->provider_id) : 'Smart Choice representative';
        $photo = !empty($appointment->provider_id) ? staff_profile_image_url($appointment->provider_id, 'small') : staff_profile_image_url(0, 'small');
        $destination = trim((string)($appointment->address ?? ''));
        $map = $destination !== '' ? 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($destination) : '';
        $this->set_merge_fields([
            '{appointment_subject}' => html_escape($appointment->subject ?? 'Appointment'),
            '{appointment_status}' => html_escape(ucwords(str_replace('-', ' ', $status))),
            '{appointment_dispatch_message}' => nl2br(html_escape($message)),
            '{appointment_staff_name}' => html_escape($provider),
            '{appointment_staff_photo}' => html_escape($photo),
            '{appointment_google_meet_link}' => in_array($status, ['booking-received', 'confirmed'], true) ? ($appointment->google_meet_link ?? '') : '',
            '{appointment_map_link}' => html_escape($map),
        ]);
    }
    public function build()
    {
        $this->to($this->appointment->email);
    }
}
