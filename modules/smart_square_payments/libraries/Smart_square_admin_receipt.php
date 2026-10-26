<?php

defined('BASEPATH') or exit('No direct script access allowed');

// The native template class is loaded by mail_template() before this file.
class Smart_square_admin_receipt extends Invoice_payment_recorded_to_staff
{
    public function build()
    {
        parent::build();
        // Dedupe per payment rather than per staff mailbox. One administrator
        // must receive receipts for multiple invoices and partial payments.
        $this->rel_type = 'invoice_payment';
        $this->set_rel_id((int) $this->payment_id);
    }
}
