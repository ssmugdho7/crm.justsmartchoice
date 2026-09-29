<?php

defined('BASEPATH') or exit('No direct script access allowed');

class opportunity_merge_fields extends App_merge_fields
{
    /**
     * This function builds an array of custom email templates keys.
     * The provided keys will be available in perfex email template editor for the supported templates.
     * @return array
     */
    public function build()
    {
        // List of email templates used by the plugin
        $templates = [
            'opportunity_send_email',
        ];
        $available = ['opportunity'];
        return [
            [
                'name' => 'opportunity Subject',
                'key' => '{subject}', // Key for instance name
                'available' => $available,
                'templates' => $templates,
            ],
            [
                'name' => 'opportunity Message',
                'key' => '{message}', // Key for instance name
                'available' => $available,
                'templates' => $templates,
            ],
        ];
    }

    /**
     * Format merge fields for company instance
     * @param object $opportunity
     * @return array
     */
    public function format($opportunity)
    {
        $fields = [];
        $fields['{subject}'] = $opportunity->subject;
        $fields['{message}'] = $opportunity->message;
        return $fields;
    }

}
