<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Verification extends ClientsController
{
    public function index()
    {
        if (is_contact_email_verified()) {
            redirect(site_url('clients'));
        }

        $data['title'] = _l('email_verification_required');

        $this->view('verification_required');
        $this->data($data);
        $this->layout();
    }

    public function verify($id = null, $key = null)
    {
        if ((!is_string($id) && !is_int($id)) || !ctype_digit((string) $id) || (int) $id < 1) {
            show_404();
            return;
        }

        $contact = $this->clients_model->get_contact($id);

        if (!$contact) {
            show_404();
        }

        if (!is_null($contact->email_verified_at)) {
            set_alert('info', _l('email_already_verified'));
            redirect(site_url('clients'));
        }

        // A missing key must never verify an unverified contact. Old links for
        // already-verified contacts are handled by the branch above.
        if (!is_string($key) || $key === '' || empty($contact->email_verification_key)
            || !hash_equals((string) $contact->email_verification_key, $key)) {
            show_error(_l('invalid_verification_key'), 400);
            return;
        }

        $timestamp_now_minus_2_days = time() - (2 * 86400);
        $contact_registered         = strtotime((string) $contact->email_verification_sent_at);

        if ($contact_registered === false || $timestamp_now_minus_2_days > $contact_registered) {
            show_error(_l('verification_key_expired'), 410);
        }

        if (!$this->clients_model->mark_email_as_verified($contact->id)) {
            show_error('Your email could not be verified. Please try again.', 503);
            return;
        }

        // User not yet confirmed
        // from option customers_register_require_confirmation
        if (total_rows(db_prefix() . 'clients', ['userid' => $contact->userid, 'registration_confirmed' => 0]) > 0) {
            set_alert('info', _l('email_successfully_verified_but_required_admin_confirmation'));

            hooks()->do_action('contact_email_verified_but_requires_admin_confirmation', $contact);
        } else {
            set_alert('success', _l('email_successfully_verified'));

            hooks()->do_action('contact_email_verified', $contact);
        }

        $redUri = is_client_logged_in() ? 'clients' : 'authentication';
        redirect(site_url($redUri));
    }

    public function resend()
    {
        if (is_contact_email_verified() || !is_client_logged_in()) {
            redirect(site_url('clients'));
        }

        if ($this->clients_model->send_verification_email(get_contact_user_id())) {
            set_alert('success', _l('email_verification_mail_sent_successully'));
        } else {
            set_alert('danger', 'Failed to sent email verification mail, contact webmaster for more information.');
        }

        redirect(site_url('verification'));
    }
}
