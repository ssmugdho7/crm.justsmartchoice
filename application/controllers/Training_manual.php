<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Training_manual extends ClientsController
{
    public function customer_books()
    {
        if (!is_client_logged_in()) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
        $data['title'] = 'Customer Books';
        $data['bodyclass'] = 'customer-books-page';
        $this->data($data);
        $this->view('customer_books');
        $this->layout();
    }
}
