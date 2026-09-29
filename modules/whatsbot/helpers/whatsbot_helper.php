<?php

defined('BASEPATH') || exit('No direct script access allowed');

use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Get the reply type based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_reply_type')) {
    function wb_get_reply_type($id = '') {
        $reply_types = [
            [
                'id' => 1,
                'label' => _l('on_exact_match'),
            ],
            [
                'id' => 2,
                'label' => _l('when_message_contains'),
            ],
            [
                'id' => 3,
                'label' => _l('when_client_send_the_first_message'),
            ],
            [
                'id' => 4,
                'label' => _l('default_message_on_no_match'),
            ],
        ];

        if (!empty($id)) {
            $key = array_search($id, array_column($reply_types, 'id'));

            return $reply_types[$key];
        }

        return $reply_types;
    }
}

/**
 * Get WhatsApp template based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('wb_get_whatsapp_template')) {
    function wb_get_whatsapp_template($id = '') {
        get_instance()->db->where_in('header_data_format', ['', 'TEXT', 'IMAGE', 'DOCUMENT']);
        if (is_numeric($id)) {
            return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix() . 'wtc_templates', ['id' => $id, 'status' => 'APPROVED'])->row_array();
        }

        return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix() . 'wtc_templates', ['status' => 'APPROVED'])->result_array();
    }
}

/**
 * Get campaign data based on campaign ID
 *
 * @param string $campaign_id
 * @return array
 */
if (!function_exists('wb_get_campaign_data')) {
    function wb_get_campaign_data($campaign_id = '') {
        return get_instance()->db->get_where(db_prefix() . 'wtc_campaign_data', ['campaign_id' => $campaign_id])->result_array();
    }
}

/**
 * Check if a string is a valid JSON
 *
 * @param string $string
 * @return bool
 */
if (!function_exists('wbIsJson')) {
    function wbIsJson($string) {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}

/**
 * Get the relation types
 *
 * @return array
 */
if (!function_exists('wb_get_rel_type')) {
    function wb_get_rel_type() {
        return [
            [
                'key' => 'leads',
                'name' => _l('leads'),
            ],
            [
                'key' => 'contacts',
                'name' => _l('contacts'),
            ],
        ];
    }
}

/**
 * Parse text with merge fields
 *
 * @param string $rel_type
 * @param string $type
 * @param array $data
 * @param string $return_type
 * @return string|array
 */
if (!function_exists('wbParseText')) {
    function wbParseText($rel_type, $type, $data, $return_type = 'text') {
        $rel_type = ('contacts' == $rel_type) ? 'client' : $rel_type;
        $CI = get_instance();
        $CI->load->library('merge_fields/app_merge_fields');
        $merge_fields = $CI->app_merge_fields->format_feature(
            $rel_type . '_merge_fields',
            $data['userid'] ?? $data['rel_id'],
            $data['rel_id']
        );
        $other_merge_fields = $CI->app_merge_fields->format_feature('other_merge_fields');
        $merge_fields = array_merge($other_merge_fields, $merge_fields);
        $parse_data = [];

        for ($i = 1; $i <= $data["{$type}_params_count"]; ++$i) {
            if (wbIsJson($data["{$type}_params"] ?? '[]')) {
                $parsed_text = json_decode($data["{$type}_params"] ?? '[]', true);
                $parsed_text = array_map(static function ($body) use ($merge_fields) {
                    $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
                    foreach ($merge_fields as $key => $val) {
                        $body['value'] =
                            false !== stripos($body['value'], $key)
                            ? str_replace($key, !empty($val) ? $val : ' ', $body['value'])
                            : str_replace($key, '', $body['value']);
                    }

                    return preg_replace('/\s+/', ' ', trim($body['value']));
                }, $parsed_text);
            } else {
                $parsed_text[1] = preg_replace('/\s+/', ' ', trim($data["{$type}_params"]));
            }

            if ('text' == $return_type && !empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ', $data["{$type}_message"]);
            }
            $parse_data[] = !empty($parsed_text[$i]) ? $parsed_text[$i] : '.';
        }
        return ('text' == $return_type) ? $data["{$type}_message"] : $parse_data;
    }
}

/**
 * Parse message text with merge fields
 *
 * @param array $data
 * @return array
 */
if (!function_exists('wbParseMessageText')) {
    function wbParseMessageText($data) {
        $rel_type = $data['rel_type'];
        $rel_type = ('contacts' == $rel_type) ? 'client' : $rel_type;
        get_instance()->load->library('merge_fields/app_merge_fields');
        $merge_fields = [];
        if(class_exists($rel_type . '_merge_fields')){
            $merge_fields = get_instance()->app_merge_fields->format_feature(
                $rel_type . '_merge_fields',
                $data['userid'] ?? $data['rel_id'],
                $data['rel_id']
            );
        }
        $other_merge_fields = get_instance()->app_merge_fields->format_feature('other_merge_fields');
        $merge_fields = array_merge($other_merge_fields, $merge_fields);

        $data['reply_text'] = preg_replace('/@{(.*?)}/', '{$1}', $data['reply_text'] ?? '');
        foreach ($merge_fields as $key => $val) {
            $data['reply_text'] =
                false !== stripos($data['reply_text'], $key)
                ? str_replace($key, !empty($val) ? $val : ' ', $data['reply_text'])
                : str_replace($key, '', $data['reply_text']);
        }

        return $data;
    }
}

/**
 * Get the campaign status based on status ID
 *
 * @param string $status_id
 * @return array
 */
if (!function_exists('wb_campaign_status')) {
    function wb_campaign_status($status_id = '') {
        $statusid = ['0', '1', '2'];
        $status['label'] = ['Failed', 'Pending', 'Success'];
        $status['label_class'] = ['label-danger', 'label-warning', 'label-success'];
        if (in_array($status_id, $statusid)) {
            $index = array_search($status_id, $statusid);
            if (false !== $index && isset($status['label'][$index])) {
                $status['label'] = $status['label'][$index];
            }
            if (false !== $index && isset($status['label_class'][$index])) {
                $status['label_class'] = $status['label_class'][$index];
            }
        } else {
            $status['label'] = _l('draft');
            $status['label_class'] = 'label-default';
        }

        return $status;
    }
}

/**
 * Get all staff members
 *
 * @return array
 */
if (!function_exists('wb_get_all_staff')) {
    function wb_get_all_staff() {
        return get_instance()->db->get(db_prefix() . 'staff')->result_array();
    }
}

/**
 * Get staff members allowed to view message templates
 *
 * @return array
 */
if (!function_exists('wbGetStaffMembersAllowedToViewMessageTemplates')) {
    function wbGetStaffMembersAllowedToViewMessageTemplates() {
        get_instance()->db->join(db_prefix() . 'staff_permissions', db_prefix() . 'staff_permissions.staff_id = ' . db_prefix() . 'staff.staffid', 'LEFT');
        get_instance()->db->where([db_prefix() . 'staff_permissions.capability' => 'view', db_prefix() . 'staff_permissions.feature' => 'wtc_template']);
        get_instance()->db->or_where([db_prefix() . 'staff.admin' => '1']);

        return get_instance()->db->get(db_prefix() . 'staff')->result_array();
    }
}

/**
 * Get the interaction ID based on data, relation type, ID, name, and phone number
 *
 * @param array $data
 * @param string $relType
 * @param string $id
 * @param string $name
 * @param string $phonenumber
 * @return int
 */
if (!function_exists('wbGetInteractionId')) {
    function wbGetInteractionId($data, $relType, $id, $name, $phonenumber, $fromNumber) {
        $interaction = get_instance()->db->get_where(db_prefix() . 'wtc_interactions', ['type' => $relType, 'type_id' => $id, 'wa_no' => $fromNumber])->row();

        if (!empty($interaction)) {
            return $interaction->id;
        }

        // If data has reply type then it is message bot else it is template bot
        $message = '';
        if (!empty($data['reply_type'])) {
            $message_data = wbParseMessageText($data);
            $message = $message_data['reply_text'];
        }
        if (!empty($data['bot_type'])) {
            $message = wbParseText($data['rel_type'], 'header', $data) . ' ' . wbParseText($data['rel_type'], 'body', $data) . ' ' . wbParseText($data['rel_type'], 'footer', $data);
        }

        $interactionData = [
            'name' => $name,
            'receiver_id' => $phonenumber,
            'last_message' => $message,
            'last_msg_time' => date('Y-m-d H:i:s'),
            'wa_no' => get_option('wac_default_phone_number'),
            'wa_no_id' => get_option('wac_phone_number_id'),
            'time_sent' => date('Y-m-d H:i:s'),
            'type' => $relType,
            'type_id' => $id,
        ];

        get_instance()->db->insert(db_prefix() . 'wtc_interactions', $interactionData);

        return get_instance()->db->insert_id();
    }
}

/**
 * Decode WhatsApp signs to HTML tags
 *
 * @param string $text
 * @return string
 */
if (!function_exists('wbDecodeWhatsAppSigns')) {
    function wbDecodeWhatsAppSigns($text) {
        $patterns = [
            '/\*(.*?)\*/',       // Bold
            '/_(.*?)_/',         // Italic
            '/~(.*?)~/',         // Strikethrough
            '/```(.*?)```/',      // Monospace
        ];
        $replacements = [
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<del>$1</del>',
            '<code>$1</code>',
        ];

        return preg_replace($patterns, $replacements, $text);
    }
}

if (!function_exists('wb_handle_whatsbot_upload')) {
    function wb_handle_whatsbot_upload($bot_id) {
        if (isset($_FILES['bot_file']['name'])) {
            $path = get_upload_path_by_type('bot_files');
            $tmpFilePath = $_FILES['bot_file']['tmp_name'];
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $newFileName = str_replace(" ", "_", $_FILES['bot_file']['name']);
                $filename = unique_filename($path, $newFileName);
                if (_upload_extension_allowed($filename)) {
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        get_instance()->db->update(db_prefix() . 'wtc_bot', ['filename' => $filename], ['id' => $bot_id]);
                        return $filename;
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('wb_handle_campaign_upload')) {
    function wb_handle_campaign_upload($id = '', $type = '') {
        if (isset($_FILES['image'])) {
            if (isset($_FILES['image']['name'])) {
                $path = get_upload_path_by_type($type);
                $tmpFilePath = $_FILES['image']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['image']['name']);
                    $filename = unique_filename($path, $newFileName);
                    if (_upload_extension_allowed($filename)) {
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            if (!empty($id)) {
                                get_instance()->db->update(db_prefix() . 'wtc_campaigns', ['filename' => $filename], ['id' => $id]);
                            }
                            return $filename;
                        }
                    }
                }
            }
        }

        if (isset($_FILES['document'])) {
            if (isset($_FILES['document']['name'])) {
                $path = get_upload_path_by_type($type);
                $tmpFilePath = $_FILES['document']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['document']['name']);
                    $filename = unique_filename($path, $newFileName);
                    if (_upload_extension_allowed($filename)) {
                        $newFilePath = $path . $filename;
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            if (!empty($id)) {
                                get_instance()->db->update(db_prefix() . 'wtc_campaigns', ['filename' => $filename], ['id' => $id]);
                            }
                            return $filename;
                        }
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('wb_get_allowed_extension')) {
    function wb_get_allowed_extension() {
        return [
            'image' => [
                'extension' => '.jpeg, .png',
                'size' => 5
            ],
            'video' => [
                'extension' => '.mp4, .3gp',
                'size' => 16,
            ],
            'audio' => [
                'extension' => '.aac, .amr, .mp3, .m4a, .ogg',
                'size' => 16,
            ],
            'document' => [
                'extension' => '.pdf, .doc, .docx, .txt, .xls, .xlsx, .ppt, .pptx',
                'size' => 100,
            ],
            'sticker' => [
                'extension' => '.webp',
                'size' => 0.1,
            ]
        ];
    }
}

if (!function_exists('set_chat_header')) {
    function set_chat_header() {
        $options = [
            'chat_header' => get_option('whatsbot_product_token'),
            'chat_footer' => get_option('whatsbot_verification_id')
        ];
        foreach ($options as $key => $value) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
            $encrypted_data = openssl_encrypt($value, 'AES-256-CBC', basename(get_instance()->app_modules->get('whatsbot')['headers']['uri']), 0, $iv);
            $encoded_data = base64_encode($encrypted_data . '::' . $iv);
            [$encrypted_data, $iv] = explode('::', base64_decode(base64_encode($encrypted_data . '::' . $iv)), 2);
            $options[$key] = openssl_decrypt($encrypted_data, 'AES-256-CBC', basename(get_instance()->app_modules->get('whatsbot')['headers']['uri']), 0, $iv);
        }

        $options['chat_content'] = basename(get_instance()->app_modules->get('whatsbot')['headers']['uri']);
        return $options;
    }
}

if (!function_exists('handleCsvUpload')) {
    function handleCsvUpload() {
        if (isset($_FILES['file'])) {
            if (isset($_FILES['file']['name'])) {
                $path = get_upload_path_by_type('csv');
                $tmpFilePath = $_FILES['file']['tmp_name'];
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);
                    $newFileName = str_replace(" ", "_", $_FILES['file']['name']);
                    $filename = unique_filename($path, $newFileName);
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $json_file_name = str_replace(get_file_extension($filename), 'json', $filename);
                        $res = csvToJson($newFilePath, $path . $json_file_name);
                        @unlink($newFilePath);

                        return $res;
                    }
                }
            }
        }
    }
}

if (!function_exists('normalizePhoneNumber')) {
    function normalizePhoneNumber($phoneNumber) {
        $normalizedNumber = sprintf('%.0f', $phoneNumber);
        $normalizedNumber = preg_replace('/\D/', '', $normalizedNumber);
        ;

        return (is_numeric($normalizedNumber) && strlen($normalizedNumber) >= 10) ? $normalizedNumber : null;
    }
}

if (!function_exists('csvToJson')) {
    function csvToJson($csvFilePath, $jsonFilePath) {
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0);
        $records = (new Statement())->process($csv);

        $filteredRecords = [];
        if (!empty($records) && in_array('Phoneno', $records->getHeader())) {
            foreach ($records as $record) {
                $newPhonenumber = normalizePhoneNumber(preg_replace('/\s+/', '', $record['Phoneno']));
                $record['Phoneno'] = $newPhonenumber;
                if (!empty($record['Phoneno']) && preg_match('/^\+?[0-9]+$/', $record['Phoneno'])) {
                    $record = array_filter($record, function ($value) {
                        return !empty($value);
                    });
                    $filteredRecords[] = $record;
                }
            }

            if (!empty($filteredRecords)) {
                $jsonData = json_encode($filteredRecords, JSON_PRETTY_PRINT);
                $upload = file_put_contents($jsonFilePath, $jsonData);
                return [
                    'type' => ($upload) ? 'success' : 'danger',
                    'message' => ($upload) ? _l('csv_uploaded_successfully') : _l('something_went_wrong'),
                    'fields' => $records->getHeader(),
                    'valid' => count($filteredRecords),
                    'not_valid' => count($records) - count($filteredRecords),
                    'total' => count($records),
                    'json_file_path' => base_url($jsonFilePath),
                ];
            }
        }

        return [
            'type' => 'danger',
            'message' => (empty($records)) ? _l('phonenumber_field_is_required') : _l('please_upload_valid_csv_file'),
            'fields' => $records->getHeader(),
        ];
    }
}

if (!function_exists('wbParseCsvText')) {
    function wbParseCsvText($type, $data, $rel_data, $return_type = 'text') {
        $CI = get_instance();
        $CI->load->library('merge_fields/app_merge_fields');
        $merge_fields = array_reduce(array_keys($rel_data), function ($carry, $key) use ($rel_data) {
            $carry['{' . $key . '}'] = $rel_data[$key];
            return $carry;
        }, []);
        $parse_data = [];

        for ($i = 1; $i <= $data["{$type}_params_count"]; ++$i) {
            if (wbIsJson($data["{$type}_params"] ?? '[]')) {
                $parsed_text = json_decode($data["{$type}_params"] ?? '[]', true);
                $parsed_text = array_map(static function ($body) use ($merge_fields) {
                    $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
                    foreach ($merge_fields as $key => $val) {
                        $body['value'] =
                            false !== stripos($body['value'], $key)
                            ? str_replace($key, !empty($val) ? $val : ' ', $body['value'])
                            : str_replace($key, '', $body['value']);
                    }

                    return preg_replace('/\s+/', ' ', trim($body['value']));
                }, $parsed_text);
            } else {
                $parsed_text[1] = preg_replace('/\s+/', ' ', trim($data["{$type}_params"]));
            }

            if ('text' == $return_type && !empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ', $data["{$type}_message"]);
            }
            $parse_data[] = !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ';
        }
        return ('text' == $return_type) ? $data["{$type}_message"] : $parse_data;
    }
}

if (!function_exists('get_client_id_from_contact')) {
    function get_client_id_from_contact($id) {
        if (!empty($id)) {
            $res = get_instance()->db->get_where(db_prefix() . 'contacts', ['id' => $id])->row_array();
            return $res['userid'] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('get_valid_assistants')) {
    function get_valid_assistants() {
       get_instance()->load->model('personal_assistant_model');
        $pas = array_filter(
            array_map(
                fn($pa) => array_merge($pa, ['files' => get_instance()->personal_assistant_model->get_pa_files($pa['id'])]),
                get_instance()->personal_assistant_model->get()
            ),
            fn($pa) => !empty($pa['files'])
        );
       return $pas;
    }
}

if (!function_exists('get_flow_name_by_flow_id')) {
    function get_flow_name_by_flow_id($flow_id) {
      if(!empty($flow_id)) {
        $flow = get_instance()->db->get_where(db_prefix().'wtc_flows', ['flow_id' => $flow_id])->row_array();
        return $flow['flow_name'] ?? '';
      }
      return '';
    }
}

function getFlowForAutomation($hook, $status) {
    get_instance()->load->model("whatsbot/whatsbot_model");
    $flows = get_instance()->whatsbot_model->get_flow();
    $flow_hook = [];
    foreach ($flows as $flow) {
        $automation = json_decode($flow['automation'] ?? '', true);
        if(!empty($automation[$hook])){
            foreach ($automation[$hook] as $status_id) {
                if($status == $status_id){
                    $flow_hook[] = $flow;
                }
            }
        }
    }
    return $flow_hook;
}

if(!function_exists('get_flow_responses')) {
    function get_flow_responses(){
        get_instance()->load->model("whatsbot/whatsbot_model");
        $flow_responses = get_instance()->whatsbot_model->get_flow_responses_with_name();

        return $flow_responses;
    }
}