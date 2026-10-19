<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Customer dispatch emails. Scheduling never implies that an installer has departed. */
class Appointly_dispatch_model extends App_Model
{
    public function scheduled($appointment)
    {
        $event = ($appointment->status ?? '') === 'pending' ? 'booking-received' : 'confirmed';
        if (!in_array($appointment->status ?? '', ['pending', 'approved'], true)) {
            return 'skipped';
        }
        try { return $this->notify($appointment, $event); }
        catch (Throwable $e) {
            log_message('error', 'Customer scheduling email failed for appointment #' . (int) $appointment->id . '.');
            return 'failed';
        }
    }

    public function message($appointment, $event, $eta = null)
    {
        $staff = !empty($appointment->provider_id) ? get_staff_full_name($appointment->provider_id) : 'Your Smart Choice representative';
        $messages = [
            'booking-received' => 'We received your appointment request. It is awaiting confirmation.',
            'confirmed' => 'Your appointment has been confirmed.',
            'on-the-way' => $staff . ' has confirmed departure and is approximately ' . (int) $eta . ' minutes away.',
            '15-minutes-away' => $staff . ' has confirmed that they are approximately 15 minutes away.',
            'arrived' => $staff . ' has arrived for your appointment.',
            'in-progress' => 'Your appointment is now in progress.',
            'completed' => 'Your appointment has been completed. Thank you for choosing Smart Choice Contractors USA.',
            'follow-up' => 'Your appointment is ready for follow-up.',
            'cancelled' => 'Your appointment has been cancelled.',
            'no-show' => 'This appointment was marked as no-show. Please contact us to arrange the next steps.',
        ];
        if (!isset($messages[$event])) {
            throw new InvalidArgumentException('Invalid dispatch event.');
        }
        if ($event === 'on-the-way' && (!is_int($eta) || $eta < 1 || $eta > 240)) {
            throw new InvalidArgumentException('Enter an ETA between 1 and 240 minutes.');
        }
        return $messages[$event] . "\nAppointment: " . ($appointment->subject ?? 'Appointment')
            . "\nScheduled: " . _d($appointment->date) . ' ' . ($appointment->start_hour ?? '')
            . ' - ' . ($appointment->end_hour ?? '') . " (" . date_default_timezone_get() . ")" . "\nAssigned representative: " . $staff;
    }

    public function notify($appointment, $event, $eta = null)
    {
        if (get_option('appointly_sc_email_enabled') !== '1') { return 'disabled'; }
        if (($appointment->source ?? '') === 'internal_staff' || !filter_var($appointment->email ?? '', FILTER_VALIDATE_EMAIL)) { return 'no-recipient'; }
        if (in_array($event, ['on-the-way', '15-minutes-away'], true) && empty($appointment->provider_id)) { return 'no-installer'; }
        $message = $this->message($appointment, $event, $eta);
        $table = db_prefix() . 'appointly_smartchoice_notifications';
        if (!$this->db->table_exists($table) || !$this->db->field_exists('customer_email', $table)) { return 'unavailable'; }
        // A database-level lock serializes concurrent clicks and schedule/approval requests.
        $lock = 'sc_dispatch_' . substr(hash('sha256', db_prefix() . ':' . $appointment->id), 0, 40);
        $acquired = $this->db->query('SELECT GET_LOCK(?, 3) AS acquired', [$lock])->row();
        if (!$acquired || (int) $acquired->acquired !== 1) { return 'busy'; }
        try {
            $previous = $this->db->where('appointment_id', (int) $appointment->id)
                ->where('channel', 'email')->where('event_type', $event)
                ->where('customer_email', $appointment->email)->where('message', $message)
                ->order_by('id', 'DESC')->get($table)->row();
            if ($previous && in_array($previous->status, ['sent', 'accepted', 'processing'], true)) {
                return $previous->status === 'processing' ? 'processing' : 'duplicate';
            }
            $row = ['appointment_id' => (int) $appointment->id, 'staff_id' => $appointment->provider_id ?: null,
                'channel' => 'email', 'event_type' => $event, 'message' => $message,
                'customer_email' => $appointment->email, 'status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'), 'sent_at' => null];
            if (!$this->db->insert($table, $row)) { return 'unavailable'; }
            $logId = $this->db->insert_id();
            $result = 'failed';
            try {
                $template = mail_template('appointly_dispatch_customer_status', 'appointly', $appointment, $event, $message);
                if ($template && $template->send()) { $result = 'accepted'; }
            } catch (Throwable $e) {
                log_message('error', 'Dispatch email failed for appointment #' . (int) $appointment->id . '. Check the email configuration and template.');
            }
            $this->db->where('id', $logId)->update($table, ['status' => $result,
                'sent_at' => $result === 'accepted' ? date('Y-m-d H:i:s') : null]);
            return $result;
        } finally {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$lock]);
        }
    }
}
