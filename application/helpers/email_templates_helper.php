<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->app_mail_template->prepare($email, $template);
    $slug             = $CI->app_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}
/**
 * Parse email template with the merge fields
 * @param  mixed $template     template
 * @param  array  $merge_fields
 * @return object
 */
function parse_email_template($template, $merge_fields = [])
{
    $CI = & get_instance();
    if (!is_object($template) || $CI->input->post('template_name')) {
        $original_template = $template;

        if (!class_exists('emails_model', false)) {
            $CI->load->model('emails_model');
        }

        if ($CI->input->post('template_name')) {
            $template = $CI->input->post('template_name');
        }

        $template = $CI->emails_model->get(['slug' => $template], 'row');

        if ($CI->input->post('email_template_custom')) {
            $template->message = $CI->input->post('email_template_custom', false);
            // Replace the subject too
            $template->subject = $original_template->subject;
        }
    }

    $template = parse_email_template_merge_fields($template, $merge_fields);
    $template = sc_link_sales_document_email($template, $merge_fields);

    // Used in hooks eq for emails tracking
    $template->tmp_id = app_generate_hash();

    return hooks()->apply_filters('email_template_parsed', $template);
}

/**
 * This function will parse email template merge fields and replace with the corresponding merge fields passed before sending email
 * @param  object $template     template from database
 * @param  array $merge_fields available merge fields
 * @return object
 */
function parse_email_template_merge_fields($template, $merge_fields)
{
    $CI = &get_instance();

    if (!class_exists('other_merge_fields', false)) {
        $CI->load->library('merge_fields/other_merge_fields');
    }

    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());

    foreach ($merge_fields as $key => $val) {
        foreach (['message', 'fromname', 'subject'] as $section) {
            $template->{$section} = stripos($template->{$section}, $key) !== false
            ? str_replace($key, $val, $template->{$section})
            : str_replace($key, '', $template->{$section});
        }
    }

    return $template;
}

/**
 * Link an unlinked sales document name in its customer delivery email.
 * Run on the body only, before the shared header/footer are added. Template
 * content and explicit links remain controlled by the administrator.
 */
function sc_link_sales_document_email($template, $merge_fields)
{
    $documents = [
        'proposal-send-to-customer' => 'proposal',
        'estimate-send-to-client'   => 'estimate',
        'estimate-already-send'     => 'estimate',
        'invoice-send-to-client'    => 'invoice',
        'invoice-already-send'      => 'invoice',
        'send-contract'            => 'contract',
    ];
    $type = $documents[$template->slug ?? ''] ?? null;
    if (!$type || !empty($template->plaintext)) {
        return $template;
    }

    $url = $merge_fields['{' . $type . '_link}'] ?? '';
    // Accept only the secure public route generated for this document on this
    // installation. Never infer a document from a word or another merge field.
    if (!is_string($url) || !preg_match(
        '#^' . preg_quote(rtrim(site_url($type), '/'), '#') . '/[1-9][0-9]*/[a-zA-Z0-9]+$#D',
        $url
    ) || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        return $template;
    }

    // Keep markup byte-for-byte; only alter visible text outside existing links
    // and non-content elements. Quoted attributes may themselves contain >.
    $parts = preg_split(
        '~(<!--.*?-->|<(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)~s',
        $template->message,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );
    if ($parts === false) {
        return $template;
    }
    $protected = [];
    $href = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    foreach ($parts as &$part) {
        if ($part === '') {
            continue;
        }
        if ($part[0] === '<') {
            if (preg_match('~^<\s*(/?)\s*(a|script|style|code|pre|textarea)\b~i', $part, $tag)) {
                $name = strtolower($tag[2]);
                if ($tag[1] === '/') {
                    unset($protected[$name]);
                } else {
                    $protected[$name] = true;
                }
            }
            continue;
        }
        if ($protected) {
            continue;
        }
        // A raw URL or email address containing /proposal/, for example, must
        // survive for the normal URL auto-linker later in the mail pipeline.
        $text = preg_split('~((?:[a-z][a-z0-9+.-]*://|www\.)[^\s<>]+|[^\s<>]+@[^\s<>]+)~i', $part, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($text as $index => &$segment) {
            if ($index % 2 === 0) {
                $segment = preg_replace_callback(
                    '~(?<![\pL\pN_])' . $type . '(?![\pL\pN_])~iu',
                    static function ($match) use ($href) {
                        return '<a href="' . $href . '">' . $match[0] . '</a>';
                    },
                    $segment
                );
            }
        }
        unset($segment);
        $part = implode('', $text);
    }
    unset($part);
    $template->message = implode('', $parts);

    return $template;
}

/**
 * Send mail template
 * @since  2.3.0
 * @return mixed
 */
function send_mail_template()
{
    $params = func_get_args();

    return mail_template(...$params)->send();
}

/**
 * Prepare mail template class
 * @param  string $class mail template class name
 * @return mixed
 */
function mail_template($class)
{
    $CI = &get_instance();

    $params = func_get_args();

    // First params is the $class param
    unset($params[0]);

    $params = array_values($params);

    $path = get_mail_template_path($class, $params);

    if (!file_exists($path)) {
        if (!defined('CRON')) {
            show_error('Mail Class Does Not Exists [' . $path . ']');
        } else {
            return false;
        }
    }

    // Include the mailable class
    if (!class_exists($class, false)) {
        include_once($path);
    }

    // Initialize the class and pass the params
    $instance = new $class(...$params);

    // Call the send method
    return $instance;
}

function get_mail_template_path($class, &$params)
{
    $CI  = &get_instance();
    $dir = APPPATH . 'libraries/mails/';

    // Check if second parameter is module and is activated so we can get the class from the module path
    // Also check if the first value is not equal to '/' e.q. when import is performed we set
    // for some values which are blank to "/"
    if (isset($params[0]) && is_string($params[0]) && $params[0] !== '/' && is_dir(module_dir_path($params[0]))) {
        $module = $CI->app_modules->get($params[0]);

        if ($module['activated'] === 1) {
            $dir = module_libs_path($params[0]) . 'mails/';
        }

        unset($params[0]);
        $params = array_values($params);
    }

    return $dir . ucfirst($class) . '.php';
}
/**
 * Create new email template
 * @param  string  $subject the predefined email template subject
 * @param  string  $message the predefined email template message
 * @param  string  $type    for what feature this email template is related e.q. invoice|ticket
 * @param  string  $name    the email template name which user see in Setup->Email Template, this is used for easier email template recognition
 * @param  string  $slug    unique email template slug
 * @param  integer $active  whether by default this email template is active
 * @return mixed
 */
function create_email_template($subject, $message, $type, $name, $slug, $active = 1)
{
    if (total_rows('emailtemplates', ['slug' => $slug]) > 0) {
        return false;
    }

    $data['subject']   = $subject;
    $data['message']   = $message;
    $data['type']      = $type;
    $data['name']      = $name;
    $data['slug']      = $slug;
    $data['language']  = 'english';
    $data['active']    = $active;
    $data['plaintext'] = 0;
    $data['fromname'] = '{companyname} | CRM';

    $CI                = &get_instance();
    $CI->load->model('emails_model');

    return $CI->emails_model->add_template($data);
}

/**
 * Check whether an email template is active based on given slug
 *
 * @since 2.7.0
 *
 * @param  string  $slug
 *
 * @return boolean
 */
function is_email_template_active($slug)
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => $slug, 'active' => 1]) > 0;
}
