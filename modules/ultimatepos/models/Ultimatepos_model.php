<?php

defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Ultimatepos_model extends App_Model
{
    public function sync_customers()
    {
        log_message('error', 'Starting sync_customers function.');

        // Obtain access token for UltimatePOS
        $access_token = $this->get_access_token();
        if (!$access_token) {
            log_message('error', 'Failed to obtain access token from UltimatePOS.');
            return;
        }

        log_message('error', 'Successfully obtained access token for UltimatePOS.');

        // Fetch all customers from UltimatePOS
        $customers = $this->get_customers_from_ultimatepos($access_token);

        if (empty($customers)) {
            log_message('error', 'No customers found in UltimatePOS.');
            return;
        }

        log_message('error', 'Fetched ' . count($customers) . ' customers from UltimatePOS.');

        // Iterate over each customer
        foreach ($customers as $customer) {
            log_message('error', 'Processing customer with mobile number: ' . $customer['mobile']);

            // Check if the customer already exists in Perfex CRM
            if ($this->is_customer_unique_in_perfex($customer['mobile'])) {
                log_message('error', 'Customer with mobile number ' . $customer['mobile'] . ' is unique in Perfex CRM. Creating lead.');
                
                // If the customer is unique, create a new lead in Perfex CRM
                $this->create_lead_in_perfex($customer);
            } else {
                log_message('error', 'Customer with mobile number ' . $customer['mobile'] . ' already exists in Perfex CRM. Skipping.');
            }
        }

        log_message('error', 'Completed sync_customers function.');
    }

    private function get_access_token()
    {
        log_message('error', 'Attempting to retrieve access token for UltimatePOS.');

        $token_url = rtrim(get_option('ultimatepos_api_url'), '/') . '/oauth/token';
        $client = new Client();

        try {
            $response = $client->post($token_url, [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => get_option('ultimatepos_client_id'),
                    'client_secret' => get_option('ultimatepos_client_secret'),
                    'username' => get_option('ultimatepos_username'),
                    'password' => get_option('ultimatepos_password'),
                    'scope' => '*',
                ],
            ]);

            $data = json_decode((string)$response->getBody(), true);

            if (isset($data['access_token'])) {
                log_message('error', 'Successfully retrieved access token.');
                return $data['access_token'];
            } else {
                log_message('error', 'Access token not found in response from UltimatePOS.');
                return false;
            }

        } catch (ClientException $e) {
            log_message('error', 'Guzzle Client error during token retrieval: ' . $e->getResponse()->getBody()->getContents());
            return false;
        } catch (Exception $e) {
            log_message('error', 'Error during token retrieval: ' . $e->getMessage());
            return false;
        }
    }

    private function get_customers_from_ultimatepos($access_token)
    {
        log_message('error', 'Fetching customers from UltimatePOS.');

        $client = new Client();
        $api_url = rtrim(get_option('ultimatepos_api_url'), '/') . '/connector/api/contactapi';

        try {
            $response = $client->get($api_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'type' => 'customer',
                    'per_page' => get_option('ultimatepos_fetch_limit'),
                    'order_by' => 'created_at',
                    'direction' => 'desc'
                ],
            ]);

            $result = json_decode((string)$response->getBody(), true);

            log_message('error', 'API response for fetching customers: ' . print_r($result, true));

            return $result['data'] ?? [];

        } catch (ClientException $e) {
            log_message('error', 'Guzzle Client error while fetching customers: ' . $e->getResponse()->getBody()->getContents());
            return [];
        } catch (Exception $e) {
            log_message('error', 'Error while fetching customers: ' . $e->getMessage());
            return [];
        }
    }

    private function is_customer_unique_in_perfex($mobile)
    {
        log_message('error', 'Checking if customer with mobile number ' . $mobile . ' is unique in Perfex CRM.');

        $this->db->select('*');
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('phonenumber', $mobile);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            log_message('error', 'Customer with mobile number ' . $mobile . ' already exists in Perfex CRM.');
            return false;
        }

        log_message('error', 'Customer with mobile number ' . $mobile . ' is unique in Perfex CRM.');
        return true;
    }

    private function create_lead_in_perfex($customer)
    {
        log_message('error', 'Creating lead in Perfex CRM for customer with mobile number: ' . $customer['mobile']);
       $name =
            (!empty($customer['prefix']) ? $customer['prefix'] . ' ' : '') .
            ($customer['first_name'] ?? '') .
            (!empty($customer['middle_name']) ? ' ' . $customer['middle_name'] : '') .
            (!empty($customer['last_name']) ? ' ' . $customer['last_name'] : '');


       $data = [
            'name' => $name,
            'phonenumber' => $customer['mobile'],
            'company' => $customer['supplier_business_name'],
            'email' => $customer['email'] ?? '',
            'address' => $customer['address_line_1'] ?? '',
            'city' => $customer['city'] ?? '',
            'state' => $customer['state'] ?? '',
            'country' => get_option('customer_default_country'),
            'zip' => $customer['zip_code'] ?? '',
            'source' => get_option('ultimatepos_lead_source'),
            'status' => get_option('ultimatepos_lead_status'), // This will be the effective status
            'assigned' => get_option('ultimatepos_lead_assigned'),
            'dateadded' => date('Y-m-d H:i:s') // Ensures correct format of current timestamp
        ];

        log_message('error', 'Data to be saved in Perfex CRM: ' . print_r($data, true));

        $this->db->insert(db_prefix() . 'leads', $data);

        if ($this->db->affected_rows() > 0) {
            log_message('error', 'Lead created in Perfex CRM with ID: ' . $this->db->insert_id());
        } else {
            log_message('error', 'Failed to create lead in Perfex CRM.');
        }
    }
}
