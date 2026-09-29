<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointlyinvoices_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create invoice from appointment
     * @param int $appointment_id
     * @return int|false Invoice ID or false on failure
     */
    public function create_invoice_from_appointment($appointment_id)
    {
        // Get appointment details
        $this->db->select('a.*, s.name as service_name, s.price as service_price');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(db_prefix() . 'appointly_services s', 's.id = a.service_id', 'left');
        $this->db->where('a.id', $appointment_id);
        $appointment = $this->db->get()->row_array();

        if (!$appointment) {
            return false;
        }

        // Only create invoice for appointments with contact_id
        if (empty($appointment['contact_id'])) {
            return false;
        }

        // Check if invoice already exists
        if (!empty($appointment['invoice_id'])) {
            return $appointment['invoice_id'];
        }

        // Skip invoice creation for free services (price = 0)
        if (empty($appointment['service_price']) || $appointment['service_price'] <= 0) {
            return false;
        }

        // Get contact details
        $this->db->select('c.*, cl.company');
        $this->db->from(db_prefix() . 'contacts c');
        $this->db->join(db_prefix() . 'clients cl', 'cl.userid = c.userid', 'left');
        $this->db->where('c.id', $appointment['contact_id']);
        $contact = $this->db->get()->row_array();

        if (!$contact) {
            return false;
        }

        // Prepare invoice data
        $invoice_data = [
            'clientid' => $contact['userid'],
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+' . get_option('invoice_due_after', 30) . ' DAY')),
            'currency' => get_base_currency()->id,
            'number' => get_option('next_invoice_number'),
            'subtotal' => 0,
            'total' => 0,
            'adminnote' => 'Invoice created from appointment #' . $appointment_id,
            'status' => 1, // Unpaid
            'addedfrom' => get_staff_user_id() ?: 1,
            'show_quantity_as' => 1,
            'billing_street' => $contact['address'] ?? '',
            'billing_city' => '',
            'billing_state' => '',
            'billing_zip' => '',
            'billing_country' => 0,
            'shipping_street' => '',
            'shipping_city' => '',
            'shipping_state' => '',
            'shipping_zip' => '',
            'shipping_country' => 0,
            'terms' => get_option('predefined_terms_invoice'),
            'clientnote' => get_option('predefined_clientnote_invoice'),
        ];

        // Determine service price
        $service_price = 0;
        if (!empty($appointment['service_price']) && $appointment['service_price'] > 0) {
            $service_price = $appointment['service_price'];
        }

        // Determine tax to apply based on settings
        $tax_names = [];
        $tax_rate_total = 0;
        $tax_type = get_option('appointly_invoice_tax_type', 'none');

        if ($tax_type === 'custom') {
            // Custom percentage tax
            $custom_vat = get_option('appointly_invoice_default_vat', 0);
            if ($custom_vat > 0) {
                $tax_names[] = 'VAT|' . $custom_vat;
                $tax_rate_total += $custom_vat;
            }
        } elseif ($tax_type === 'system') {
            // Use CRM tax rate
            $system_tax_id = get_option('appointly_invoice_system_tax');
            if ($system_tax_id) {
                $this->db->where('id', $system_tax_id);
                $tax = $this->db->get(db_prefix() . 'taxes')->row();
                if ($tax) {
                    $tax_names[] = $tax->name . '|' . $tax->taxrate;
                    $tax_rate_total += $tax->taxrate;
                }
            }
        }

        // Calculate totals manually (Perfex expects these to be pre-calculated)
        $subtotal = $service_price * 1; // qty * rate
        $tax_amount = ($subtotal * $tax_rate_total) / 100;
        $total = $subtotal + $tax_amount;

        // Update invoice data with calculated totals
        $invoice_data['subtotal'] = $subtotal;
        $invoice_data['total'] = $total;

        // Add items to invoice data using Perfex's newitems pattern
        $invoice_data['newitems'] = [
            [
                'description' => $appointment['service_name'] ?: 'Appointment Service',
                'long_description' => $appointment['subject'] . (!empty($appointment['description']) ? "\n" . $appointment['description'] : ''),
                'qty' => 1,
                'unit' => '',
                'rate' => $service_price,
                'taxname' => $tax_names,
                'order' => 1,
            ]
        ];

        // Create invoice
        $this->load->model('invoices_model');

        // Clear any lingering $_POST data that might contaminate invoice creation
        // Perfex's invoices_model->add() checks $_POST for additional items
        $original_post = $_POST;
        $_POST = [];

        $invoice_id = $this->invoices_model->add($invoice_data);

        // Restore original $_POST data
        $_POST = $original_post;

        if ($invoice_id) {
            // Force status to unpaid (Perfex auto-marks $0 invoices as paid)
            $this->db->where('id', $invoice_id);
            $this->db->update(db_prefix() . 'invoices', [
                'status' => 1, // Force unpaid status
            ]);

            // Update appointment with invoice ID
            $this->db->where('id', $appointment_id);
            $this->db->update(db_prefix() . 'appointly_appointments', [
                'invoice_id' => $invoice_id,
                'invoice_date' => date('Y-m-d H:i:s'),
            ]);

            return $invoice_id;
        }

        return false;
    }
}
