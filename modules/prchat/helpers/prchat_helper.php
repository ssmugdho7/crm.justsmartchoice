<?php
/*
Module Name: CRM Chat & AI Chatbot Module
Description: Chat & AI Chatbot Module for Perfex CRM
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

defined('BASEPATH') or exit('No direct script access allowed');
define('CHAT_CURRENT_URI', isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : null);
define('VERSIONING', defined('PR_CHAT_VERSION') ? PR_CHAT_VERSION : (string) get_instance()->app_scripts->core_version());

if (get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('before_staff_login', 'prchat_set_session_variable_before_login_for_notification');
}

if (prchat_staff_can_chat() && get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('app_admin_head', 'pr_chat_add_head_components');
  hooks()->add_action('app_admin_footer', 'pr_chat_init_checkView');
  hooks()->add_action('app_admin_footer', 'pr_chat_load_js');
  hooks()->add_action('app_admin_head', 'pr_chat_add_js_before_admin_render');
  hooks()->add_filter('migration_tables_to_replace_old_links', 'pr_chat_migration_tables_to_replace_old_links');
  hooks()->add_action('staff_member_deleted', 'pr_chat_staff_member_data_transfer');
}

// Check if clients view is enabled in Setup->Settings->Chat Settings
if (isClientsEnabled() && get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('before_client_login', 'prchat_set_session_variable_before_login_for_notification_client');
  hooks()->add_action('app_customers_head', 'handle_clients_css_styles');
  hooks()->add_action('app_customers_footer', 'pr_chat_init_checkViewClients');
}

/**
 * Function that handles css files for customers view.
 *
 * @return void
 */
function handle_clients_css_styles()
{
  echo '<link href="' . base_url('modules/prchat/assets/clients/styles.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" >';
}

/*
 * Check if can have permissions then apply new tab in settings
 */
if (staff_can('view', 'settings')) {
  hooks()->add_action('admin_init', 'prchat_add_settings_tab');
}

/**
 * [prchat_add_settings_tab add new tab in setup->settings].
 *
 * @return void
 */
function prchat_add_settings_tab()
{
  $CI = &get_instance();
  $CI->app->add_settings_section('perfex_chat_settings', [
    'title' => 'CRM Chat',
    'position' => 1,
    'children' => [
      [
        'position' => 1,
        'name' => _l('chat_settings_name'),
        'view' => 'prchat/perfex_chat_settings',
        'icon' => 'fa-regular fa-message',
      ],
      [
        'position' => 2,
        'name' => _l('chat_health_check'),
        'view' => 'prchat/settings_health_check',
        'icon' => 'fa fa-heartbeat',
      ],
      [
        'position' => 3,
        'name' => _l('chat_project_media'),
        'view' => 'prchat/settings_project_media',
        'icon' => 'fa fa-folder-open',
      ],
    ],
  ]);
}

/**
 * [Set session variable before login > this is for html5 live desktop notifications].
 *
 * @return void
 */
function prchat_set_session_variable_before_login_for_notification()
{
  get_instance()->session->set_userdata('prchat_user_before_login', true);
}

/**
 * [Set session variable before login > this is for html5 live desktop notifications].
 *
 * @return void
 */
function prchat_set_session_variable_before_login_for_notification_client()
{
  get_instance()->session->set_userdata('prchat_client_before_login', true);
}

/**
 * [pr_chat_load_js inject javascript files].
 *
 * @return void
 */
function pr_chat_load_js()
{
  echo '<script>window.prchatSpeechLanguage=' . json_encode(get_option('prchat_speech_language') ?: 'auto') . ';window.prchatInactivityMs=' . ((int)(get_option('prchat_inactivity_seconds') ?: 10) * 1000) . ';window.prchatAiImproveUrl=' . json_encode(admin_url('prchat/improve-message')) . ';</script>';
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<script>var prchatLang = { lastSeenNever: "' . addslashes(_l('chat_last_seen_never')) . '" };</script>';
    echo '<script src="' . module_dir_url('prchat', 'assets/js/pr-chat.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
  /**
   * Mentions js (legacy + new PrchatMentions)
   */
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/underscore.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/jquery-elastic.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/mentions.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/prchat-mentions.js' . '?v=' . VERSIONING . '') . '"></script>';

  // New centralized sound management system
  echo '<script src="' . base_url('modules/prchat/assets/js/ChatSoundManager.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script>window.PRCHAT_AI_IMPROVE_URL=' . json_encode(site_url('prchat/Prchat_Controller/improve_message')) . ';window.prchatAiImproveUrl=window.PRCHAT_AI_IMPROVE_URL;</script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/smart-choice-ai-composer.js' . '?v=' . VERSIONING . '') . '"></script>';
}

/**
 * Function that will inject the chat messages tables when user changing domain and need to replace old links.
 *
 * @param array $tables
 *
 * @return array
 */
function pr_chat_migration_tables_to_replace_old_links($tables)
{
  $tables[] = [
    'table' => db_prefix() . 'chatmessages',
    'field' => 'message',
  ];

  return $tables;
}

/**
 * Injects chat CSS.
 *
 * @return null
 */
function pr_chat_add_head_components()
{
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<link href="' . base_url('modules/prchat/assets/css/smart_choice_chat_patch.css' . '?v=' . VERSIONING . '') . '" rel="stylesheet" type="text/css" >';
  echo '<link href="' . base_url('modules/prchat/assets/css/chat_styles.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" >';
  } else {
    chat_check_theme_options();
  }
  // Mutual files for both chat views
  echo '<link href="' . base_url('modules/prchat/assets/css/inline-video-embed.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
  echo '<link href="' . base_url('modules/prchat/assets/css/mentions.css') . '" rel="stylesheet" type="text/css"/>';
  echo '<link href="' . base_url('modules/prchat/assets/css/smart-choice-ai-composer.css' . '?v=' . VERSIONING . '') . '" rel="stylesheet" type="text/css"/>';
}

/**
 * Inject chat JS plugins.
 */
function pr_chat_add_js_before_admin_render()
{
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<script src="' . base_url('modules/prchat/assets/js/jscolor.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
  echo '<script src="' . base_url('modules/prchat/assets/js/inline-video-embed.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/voice-recorder.js' . '?v=' . VERSIONING . '') . '"></script>';

  // Calls scaffolding (feature-flagged)
  if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1') {
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/signaling-client.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/media-manager.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/call-ui.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/call-manager.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
}

/**
 * Theme options.
 *
 * @return load css file
 */
function chat_check_theme_options()
{
  // Dark mode is handled via body.chat_dark class in the same CSS file
  echo '<link href="' . base_url('modules/prchat/assets/css/chat_full_view.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
  echo '<link href="' . base_url('modules/prchat/assets/css/inline-video-embed.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
}

/**
 * Loads the chat view.
 *
 * @return null
 */
function pr_chat_init_checkView()
{
  $CI = &get_instance();

  // Note: prchat_user_before_login is cleared in pusher_auth() after Pusher reads it.
  // Do NOT unset it here — the footer renders before the async Pusher auth AJAX fires.

  $CI->load->model('prchat/prchat_model', 'chat_model');
  $unreadMessages = $CI->chat_model->getUnread();

  echo $CI->load->view('prchat/initViewCheck', ['unreadMessages' => $unreadMessages], true);
}

/**
 * Loads the chat view.
 *
 * @return null
 */
function pr_chat_init_checkViewClients()
{
  $CI = &get_instance();

  // Note: prchat_client_before_login is cleared in pusherCustomersAuth() after Pusher reads it.
  // Do NOT unset it here — the footer renders before the async Pusher auth AJAX fires.

  echo $CI->load->view('prchat/initViewCheckClients');
}

/*
 * Function that will convert links to iamges if meets the regex
 */
function pr_chat_convertLinkImageToString($string)
{
  $regexImg = '~(http.*\.)(jpe?g|png|gif|[tg]iff?|svg)~i';

  if (preg_match_all($regexImg, $string)) {
    $string = preg_replace_callback($regexImg, function ($matches) {
      $fullUrl = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
      $filename = basename(parse_url($fullUrl, PHP_URL_PATH));
      $safeUrl = htmlspecialchars($fullUrl, ENT_QUOTES, 'UTF-8');
      $safeName = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
      return '<a href="' . $safeUrl . '" target="_blank" data-chat-file="image" data-file-url="' . $safeUrl . '" data-filename="' . $safeName . '"><img class="prchat_convertedImage" src="' . $safeUrl . '" alt="' . $safeName . '"/></a>';
    }, $string);
  }

  return $string;
}

/**
 * Merge audio/webm into CI mimes for .webm (MediaRecorder voice uses audio/webm).
 */
function pr_chat_patch_upload_mimes_for_voice()
{
  $mimes = &get_mimes();
  if (!is_array($mimes)) {
    return;
  }
  if (isset($mimes['webm'])) {
    if ($mimes['webm'] === 'video/webm') {
      $mimes['webm'] = ['video/webm', 'audio/webm'];
    } elseif (is_array($mimes['webm']) && !in_array('audio/webm', $mimes['webm'], true)) {
      $mimes['webm'][] = 'audio/webm';
    }
  }
  if (isset($mimes['m4a'])) {
    if (is_string($mimes['m4a'])) {
      $mimes['m4a'] = [$mimes['m4a'], 'audio/mp4', 'audio/m4a'];
    } elseif (is_array($mimes['m4a'])) {
      foreach (['audio/mp4', 'audio/m4a'] as $am) {
        if (!in_array($am, $mimes['m4a'], true)) {
          $mimes['m4a'][] = $am;
        }
      }
    }
  }
}

/**
 * Voice-note extensions only (stored under module uploads; not tied to CRM allowed_files).
 */
function pr_chat_voice_upload_extensions_string()
{
  return 'webm|ogg|oga|m4a|mp3|wav|aac';
}

/**
 * Normalize Perfex CRM allowed file extensions into a CodeIgniter upload string.
 * Handles comma, pipe, dot, space, uppercase, and newline formats safely.
 */
function pr_chat_normalize_allowed_file_types($raw = null, $includeCommonImages = true)
{
  if ($raw === null) {
    $raw = get_option('allowed_files');
  }

  $raw = is_string($raw) ? strtolower($raw) : '';
  $raw = str_replace(["\r", "\n", "\t", ';'], ',', $raw);
  $raw = str_replace('|', ',', $raw);

  $parts = preg_split('/[,\s]+/', $raw);
  $types = [];

  foreach ($parts as $part) {
    $part = trim((string) $part);
    $part = ltrim($part, '.');
    $part = preg_replace('/[^a-z0-9]/', '', $part);
    if ($part !== '') {
      $types[] = $part;
    }
  }

  if ($includeCommonImages) {
    $types = array_merge($types, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'heic', 'heif']);
  }

  $types = array_merge($types, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip', 'rar']);

  $denyList = [
    'php', 'phar', 'phtml', 'php3', 'php4', 'php5', 'cgi', 'pl', 'py', 'sh', 'bash',
    'bat', 'cmd', 'exe', 'dll', 'so', 'js', 'html', 'htm', 'shtml', 'svgz', 'htaccess', 'htpasswd'
  ];

  $types = array_values(array_unique(array_diff($types, $denyList)));
  $types = array_values(array_filter($types, static function ($ext) {
    return preg_match('/^[a-z0-9]{1,12}$/', $ext) === 1;
  }));

  return !empty($types) ? implode('|', $types) : 'jpg|jpeg|png|gif|webp|pdf|doc|docx|xls|xlsx|csv|txt|zip';
}

/**
 * Patch CodeIgniter mime map for file types commonly allowed by Perfex CRM and modern phones.
 */
function pr_chat_patch_upload_mimes_for_crm_files()
{
  $mimes = &get_mimes();
  if (!is_array($mimes)) {
    return;
  }

  $patch = [
    'jpg'  => ['image/jpeg', 'image/pjpeg'],
    'jpeg' => ['image/jpeg', 'image/pjpeg'],
    'png'  => ['image/png', 'image/x-png'],
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'bmp'  => ['image/bmp', 'image/x-ms-bmp'],
    'tif'  => ['image/tiff', 'image/tif'],
    'tiff' => ['image/tiff', 'image/tif'],
    'heic' => ['image/heic', 'image/heif', 'application/octet-stream'],
    'heif' => ['image/heif', 'image/heic', 'application/octet-stream'],
    'pdf'  => ['application/pdf', 'application/x-pdf'],
    'csv'  => ['text/csv', 'text/plain', 'application/csv', 'text/x-csv', 'application/vnd.ms-excel'],
    'txt'  => ['text/plain', 'text/x-log'],
    'doc'  => ['application/msword', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
    'xls'  => ['application/vnd.ms-excel', 'application/msexcel', 'application/octet-stream'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
    'zip'  => ['application/zip', 'application/x-zip', 'application/x-zip-compressed', 'multipart/x-zip'],
    'rar'  => ['application/vnd.rar', 'application/x-rar', 'application/x-rar-compressed', 'application/octet-stream'],
  ];

  foreach ($patch as $ext => $values) {
    $values = (array) $values;
    if (!isset($mimes[$ext])) {
      $mimes[$ext] = count($values) === 1 ? $values[0] : $values;
      continue;
    }
    $existing = (array) $mimes[$ext];
    foreach ($values as $value) {
      if (!in_array($value, $existing, true)) {
        $existing[] = $value;
      }
    }
    $mimes[$ext] = count($existing) === 1 ? $existing[0] : $existing;
  }
}

/**
 * Attachment extensions from CRM settings (client portal file picker), minus deny list.
 */
function pr_chat_client_crm_attachment_types_string()
{
  return pr_chat_normalize_allowed_file_types(get_option('allowed_files'), true);
}

/**
 * Get chat color by user id.
 *
 * @param mixed $id
 * @param mixed $name
 *
 * @return mixed
 */
function pr_get_chat_color($id, $name = '')
{
  $CI = &get_instance();

  if ($CI->db->field_exists('value', db_prefix() . 'chatsettings')) {
    return pr_get_chat_option($id, $name);
  } else {
    $CI->db->select('chat_color');
    $CI->db->where('user_id', $id);
  }
  $result = $CI->db->get(db_prefix() . 'chatsettings')->row();

  if (!$result) {
    return '';
  }

  return $result->chat_color;
}

/**
 * Get chat get chat color on subscribe.
 *
 * @param mixed $id
 * @param mixed $name
 *
 * @return mixed
 */
function pr_get_chat_option($id, $name)
{
  $CI = &get_instance();
  $CI->db->select('value');
  $CI->db->where('name', $name);
  $CI->db->where('user_id', $id);

  $result = $CI->db->get(db_prefix() . 'chatsettings')->row();

  if (!$result) {
    return '';
  }

  return $result->value;
}

/**
 * Function that will check check if current message contains image.
 *
 * @param string $string
 *
 * @return string
 */
function prchat_checkMessageIfFileExists($message)
{
  $regexImg = '/^[^?]*\.(unknown|gif|jpg|jpeg|tiff|png|swf|rar|zip|mp3|ogg|mp4|mov|flv|wmv|avi|doc|docx|pdf|xls|xlsx|zip|rar|txt|php|html|css|PNG|JPG|JPEG)/';
  if (preg_match_all($regexImg, $message)) {
    return true;
  } else {
    return false;
  }
}

/**
 * Check if message has any images or files links containing.
 *
 * @param string $image
 *
 * @return string
 */
function getImageFullName($file)
{
  $url_arr = explode('/', $file);

  return $url_arr[count($url_arr) - 1];
}

/**
 * Get staff current role.
 *
 * @param [type] $role_id
 *
 * @return string
 */
function get_staff_userrole($role_id)
{
  $CI = &get_instance();
  $CI->db->select('name');
  $CI->db->where('roleid', $role_id);

  $result = $CI->db->get(db_prefix() . 'roles')->row_array();
  if ($result !== null) {
    return $result['name'];
  }
}

/**
 * Theme options.
 *
 * @return string
 */
function get_chat_theme_option()
{
  get_instance()->db->where('user_id', get_staff_user_id());
  get_instance()->db->where('name', 'current_theme');

  return get_instance()->db->get(db_prefix() . 'chatsettings')->row('value');
}

/**
 * Function that will check chat URL images and will convert to link.
 *
 * @param string $string
 *
 * @return string
 */
function make_url_clickable_cb($matches)
{
  $ret = '';
  $url = $matches[2];
  if (empty($url)) {
    return $matches[0];
  }

  if (in_array(substr($url, -1), ['.', ',', ';', ':']) === true) {
    $ret = substr($url, -1);
    $url = substr($url, 0, strlen($url) - 1);
  }

  $rawUrl = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
  if (preg_match('/^(javascript|vbscript|data):/i', trim($rawUrl))) {
    return $matches[0];
  }

  $hrefDest = str_replace('http://', '//', $rawUrl);
  $safeHref = htmlspecialchars($hrefDest, ENT_QUOTES, 'UTF-8');
  $safeDisplay = htmlspecialchars($rawUrl, ENT_QUOTES, 'UTF-8');

  $youtubeRegex = '/(youtube(-nocookie)?\.com|youtu\.be)/i';
  $vimeoRegex = '/(vimeo(pro)?.com)/i';
  $facebookVideoRegex = '/(facebook\.com)\/([a-z0-9_-]*)\/videos\//i';
  $googlemapsRegex = '/((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i';

  $isVideoUrl = preg_match($youtubeRegex, $rawUrl) || preg_match($vimeoRegex, $rawUrl) || preg_match($facebookVideoRegex, $rawUrl) || preg_match($googlemapsRegex, $rawUrl);
  $dataVideoEmbed = $isVideoUrl ? ' data-video-embed="true"' : '';

  if (strpos($rawUrl, '/modules/prchat/uploads/') !== false) {
    $filename = htmlspecialchars(basename($rawUrl), ENT_QUOTES, 'UTF-8');
    return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\" target=\"_blank\" data-chat-file=\"file\" data-file-url=\"$safeHref\" data-filename=\"$filename\">$filename</a>" . $ret;
  }

  return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\"$dataVideoEmbed target=\"_blank\">$safeDisplay</a>" . $ret;
}

/**
 * Callback for clickable.
 */
function make_web_ftp_clickable_cb($matches)
{
  $ret = '';
  $dest = $matches[2];
  $dest = 'http://' . $dest;
  if (empty($dest)) {
    return $matches[0];
  }

  if (in_array(substr($dest, -1), ['.', ',', ';', ':']) === true) {
    $ret = substr($dest, -1);
    $dest = substr($dest, 0, strlen($dest) - 1);
  }

  $rawDest = html_entity_decode($dest, ENT_QUOTES, 'UTF-8');
  if (preg_match('/^(javascript|vbscript|data):/i', trim($rawDest))) {
    return $matches[0];
  }

  $hrefDest = str_replace('http://', '//', $rawDest);
  $safeHref = htmlspecialchars($hrefDest, ENT_QUOTES, 'UTF-8');
  $safeDisplay = htmlspecialchars($rawDest, ENT_QUOTES, 'UTF-8');

  $youtubeRegex = '/(youtube(-nocookie)?\.com|youtu\.be)/i';
  $vimeoRegex = '/(vimeo(pro)?.com)/i';
  $facebookVideoRegex = '/(facebook\.com)\/([a-z0-9_-]*)\/videos\//i';
  $googlemapsRegex = '/((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i';
  $instagramRegex = '/(instagram\.com)/i';
  $tiktokRegex = '/(tiktok\.com)/i';

  $isVideoUrl = preg_match($youtubeRegex, $rawDest) || preg_match($vimeoRegex, $rawDest) || preg_match($facebookVideoRegex, $rawDest) || preg_match($googlemapsRegex, $rawDest) || preg_match($instagramRegex, $rawDest) || preg_match($tiktokRegex, $rawDest);
  $dataVideoEmbed = $isVideoUrl ? ' data-video-embed="true"' : '';

  if (strpos($rawDest, '/modules/prchat/uploads/') !== false) {
    $filename = htmlspecialchars(basename($rawDest), ENT_QUOTES, 'UTF-8');
    return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\" target=\"_blank\" data-chat-file=\"file\" data-file-url=\"$safeHref\" data-filename=\"$filename\">$filename</a>" . $ret;
  }

  return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\"$dataVideoEmbed target=\"_blank\">$safeDisplay</a>" . $ret;
}

/**
 * Callback for clickable.
 */
function make_email_clickable_cb($matches)
{
  $email = htmlspecialchars($matches[2] . '@' . $matches[3], ENT_QUOTES, 'UTF-8');

  return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}

/**
 * Check for links/emails/ftp in string to wrap in href.
 *
 * @param string $ret
 *
 * @return string formatted string with href in any found
 */
function clickable($ret)
{
  $ret = ' ' . $ret;
  // in testing, using arrays here was found to be faster
  $ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', 'make_url_clickable_cb', $ret);
  $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', 'make_web_ftp_clickable_cb', $ret);
  $ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', 'make_email_clickable_cb', $ret);
  // this one is not in an array because we need it to run last, for cleanup of accidental links within links
  $ret = preg_replace('#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', '$1$3</a>', $ret);
  $ret = trim($ret);

  return $ret;
}


/**
 * Function that handles member data upon member deletion.
 *
 * @param [array] $data
 *
 * @return boolean
 */
function pr_chat_staff_member_data_transfer($data)
{
  $deleted = $data['id'];
  $transfer_data_to = $data['transfer_data_to'];
  $CI = &get_instance();

  $CI->db->trans_start();

  $CI->db->where('member_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPMEMBERS);

  $CI->db->where('sender_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPMESSAGES);

  $CI->db->group_start();
  $CI->db->where('sender_id', $deleted);
  $CI->db->or_where('reciever_id', $deleted);
  $CI->db->group_end();
  $CI->db->delete(db_prefix() . 'chatmessages');

  $CI->db->where('user_id', $deleted);
  $CI->db->delete(db_prefix() . 'chatsettings');

  $CI->db->where('created_by_id', $deleted);
  $CI->db->update(TABLE_CHATGROUPS, ['created_by_id' => $transfer_data_to]);

  $group_files = $CI->db->select('file_name')->where('sender_id', $deleted)->get(db_prefix() . 'chatgroupsharedfiles')->result_array();
  // File tracking removed - files tracked via message content

  foreach ($group_files as $group_file) {
    if (is_dir(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER)) {
      unlink(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . $group_file['file_name']);
    }
  }


  $CI->db->where('sender_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPSHAREDFILES);

  $CI->db->where('sender_id', $deleted);
  // File tracking table removed - files tracked via message content

  if ($CI->db->trans_complete()) {
    return true;
  }

  return false;
}

// Helpers for gradient colors
function validateChatColorBeforeApply($color, $model_check = '')
{
  $validColor = '';
  if (
    colorStartsWith($color, '#') && !colorEndsWith($color, ';')
    || colorStartsWith($color, 'linear-gradient(') && colorEndsWith($color, ');')
  ) {
    $validColor = $color;
  } else {
    $validColor = '#546bf1';
  }
  if ($model_check == true && $validColor === '#546bf1') {
    return 'unknownColor';
  }

  return $validColor;
}

// Helpers for gradient colors
function colorStartsWith($haystack, $needle)
{
  $length = strlen($needle);

  return substr($haystack, 0, $length) === $needle;
}

function colorEndsWith($haystack, $needle)
{
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }

  return substr($haystack, -$length) === $needle;
}

/**
 * Returns the customer for a specific (mixed) contact_id.
 *
 * @return row_array
 */
function getOwnClient($id)
{
  $CI = &get_instance();

  $CI->db->select('clients.userid as client_id, ' . db_prefix() . 'contacts.id as contact_id, firstname, lastname, company');
  $CI->db->from(db_prefix() . 'clients as clients');
  $CI->db->where(db_prefix() . 'contacts.id', $id);
  $CI->db->where(db_prefix() . 'contacts.active', 1);
  $CI->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid = clients.userid');

  $result = $CI->db->get()->row_array();

  return $result;
}

/**
 * Fetches from database all staff assigned customers
 * If admin fetches all customers.
 *
 * @param int $limit
 * @param int $offset
 *
 * @return json
 */
function get_staff_customers($limit = 30, $offset = 0, $arrayData = false)
{
  $CI = &get_instance();

  $staff_can_access = get_option('chat_staff_can_access_clients') == '1';
  if (!$staff_can_access && !is_admin()) {
    if ($arrayData) {
      return [];
    }
    echo json_encode([]);
    return;
  }

  $staffCanViewAllClients = staff_can('view', 'customers') && !prchat_staff_own_scope();
  $current_staff_id = get_staff_user_id();

  $CI->db->select('firstname, lastname, ' . db_prefix() . 'contacts.id as contact_id, ' . get_sql_select_client_company());
  $CI->db->where(db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'contacts.active = 1');
  $CI->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
  $CI->db->select(db_prefix() . 'clients.userid as client_id, title, is_primary, ' . db_prefix() . 'contacts.last_login');

  if (!$staffCanViewAllClients) {
    $CI->db->where('(' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id="' . $current_staff_id . '"))');
  }

  $CI->db->limit($limit, $offset);

  $result = $CI->db->get(db_prefix() . 'contacts')->result_array();

  foreach ($result as $key => $contact) {
    $result[$key]['profile_image_url'] = contact_profile_image_url($contact['contact_id']);
  }

  if ($arrayData) {
    return $result;
  }

  if ($CI->db->affected_rows() !== 0) {
    echo json_encode(['customers' => $result]);
  } else {
    echo json_encode(['customers' => []]);
  }
}

/**
 * Fetches all customer assigned admins + any staff who have messaged this contact.
 * Includes staff from customer_admins table AND staff who have chat history with this contact.
 *
 * @return json
 */
function get_customer_admins()
{
  $CI = &get_instance();

  $customer_id = get_client_user_id();
  $contact_id = get_contact_user_id();

  $visibility = get_option('chat_client_staff_visibility') ?: 'assigned_and_responded';

  // Treat legacy 'assigned_only' as 'assigned_and_responded'
  if ($visibility === 'assigned_only') {
    $visibility = 'assigned_and_responded';
  }

  $staffIds = [];

  // Include all active staff with chat permission when visibility is 'all_staff'
  if ($visibility === 'all_staff') {
    $allStaff = $CI->db->select('staffid')
      ->where('active', 1)
      ->get(db_prefix() . 'staff')
      ->result_array();

    foreach ($allStaff as $row) {
      $staffIds[] = (int) $row['staffid'];
    }
  }

  // Always include staff assigned to this client
  $assignedStaff = $CI->db->select('staff_id')
    ->where('customer_id', (int) $customer_id)
    ->get(db_prefix() . 'customer_admins')
    ->result_array();

  foreach ($assignedStaff as $row) {
    if (!in_array((int) $row['staff_id'], $staffIds)) {
      $staffIds[] = (int) $row['staff_id'];
    }
  }

  // Always include staff who have communicated with this client
  // so the client can continue existing conversations
  $messages = $CI->db->select('sender_id, reciever_id')
    ->where('(sender_id = "client_' . (int) $contact_id . '" OR reciever_id = "client_' . (int) $contact_id . '")')
    ->where('(sender_id LIKE "staff_%" OR reciever_id LIKE "staff_%")')
    ->get(db_prefix() . 'chatclientmessages')
    ->result_array();

  foreach ($messages as $msg) {
    $staff_id_str = (strpos($msg['sender_id'], 'staff_') === 0) ? $msg['sender_id'] : $msg['reciever_id'];
    $sid = (int) str_replace('staff_', '', $staff_id_str);
    if ($sid > 0 && !in_array($sid, $staffIds)) {
      $staffIds[] = $sid;
    }
  }

  if (empty($staffIds)) {
    return [];
  }

  $CI->db->select('firstname, lastname, staffid, profile_image, admin, role');
  $CI->db->where_in('staffid', $staffIds);
  $CI->db->where('active', 1);
  $customer_admins = $CI->db->get(db_prefix() . 'staff')->result_array();

  foreach ($customer_admins as $key => &$admin) {
    if (!prchat_staff_can_contact($contact_id, $admin['staffid'])) {
      unset($customer_admins[$key]);
      continue;
    }

    if ($admin['admin']) {
      $admin['role'] = ' ' . _l('chat_role_administrator');
    } else {
      $role_name = get_staff_userrole($admin['role']);
      if ($role_name) {
        $admin['role'] = ' ' . $role_name;
      } else {
        $admin['role'] = ' ' . _l('chat_role_staff');
      }
    }

    // Never expose admin flag or email to client side
    unset($admin['admin']);
    unset($admin['email']);
  }

  return array_values($customer_admins);
}

/**
 * Check if current staff can access a contact for chat (and thus for client notes).
 * Staff can access if they have view customers permission OR are assigned as customer admin.
 *
 * @param int $contact_id Contact ID
 * @return bool
 */
function staff_can_access_contact_for_chat($contact_id)
{
  return prchat_staff_can_contact($contact_id);
}

/**
 * Check if staff can delete messages
 *
 * @return bool
 */
function chatStaffCanDelete()
{
  if (is_admin()) {
    return true;
  }
  return staff_can('delete', PR_CHAT_MODULE_NAME);
}

/**
 * Check if staff can delete entire chat groups.
 * Administrators always retain this capability; other staff must be granted
 * the dedicated Delete Groups permission from the Perfex role/staff screen.
 *
 * @return bool
 */
function chatStaffCanDeleteGroups()
{
  if (is_admin()) {
    return true;
  }
  return staff_can('delete_groups', PR_CHAT_MODULE_NAME);
}


/**
 * Get contact user id from contacts table
 * Used for when creating support ticket from messages
 *
 * @return string
 */
function get_contact_customer_user_id($contact_id)
{
  $CI = &get_instance();
  $CI->db->select('userid');
  $CI->db->where('id', $contact_id);
  $result = $CI->db->get(db_prefix() . 'contacts')->row_array();
  if ($result !== null) {
    return $result['userid'];
  }
}


/**
 * Get last ticket_id inserted
 *
 * @return string
 */
function chat_get_tickets_last_inserted_row()
{
  return get_instance()->db->select('ticketid')->order_by('ticketid', "desc")->limit(1)->get(db_prefix() . 'tickets')->row()->ticketid;
}

/**
 * Receives user active chat status
 *
 * @return string
 */
function get_user_chat_status()
{
  $CI = &get_instance();
  $CI->db->where('user_id', get_staff_user_id());
  $CI->db->where('name', 'chat_status');
  $result = $CI->db->get(db_prefix() . 'chatsettings')->row_array();
  if ($result !== null) {
    return $result['value'];
  }
}

/**
 * Check if clients are enabled.
 *
 * @return boolean
 */
function isClientsEnabled()
{
  return get_option('chat_client_enabled');
}

/**
 * Check if staff can access the clients tab.
 * Admins always have access. Non-admins need the setting enabled.
 *
 * @return bool
 */
function staffCanAccessClientsTab()
{
  if (!prchat_staff_can_chat()) { return false; }
  if (is_admin()) {
    return true;
  }

  $option_value = get_option('chat_staff_can_access_clients');

  return $option_value == '1';
}

/**
 * Returns groups name with who capitals eg My_Group and underscore.
 *
 * @param $name
 *
 * @return string
 */
function slugifyGroupName($name)
{
  return rtrim(stripslashes(ucwords(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', ucwords($name))))), '-');
}

/**
 * Template components js loader
 *
 * @param       $name
 * @param array $params is user over all components do not remove in any case
 *
 * @return mixed
 */
function loadChatComponent($name, $params = [])
{    // Used in chat_full_view as ['prop' => 'class or something else']
  // Then reuse this in the $name.php $params['prop']
  require('modules/prchat/assets/module_includes/Components/' . $name . '.php');
}

/**
 * Build the iceServers array for WebRTC peer connections.
 *
 * Priority: custom TURN from chat_constants.php ▸ Open Relay free TURN ▸ STUN-only.
 * Open Relay (metered.ca) provides 20 GB/month of free TURN relay traffic on
 * ports 80/443 with TURNS+SSL, using shared-secret (TURN REST API) auth.
 *
 * @return array  Ready-to-JSON iceServers array.
 */
function prchat_build_ice_servers()
{
  $ice = [
    ['urls' => ['stun:stun.l.google.com:19302']],
  ];

  // Priority 1: Cloudflare Calls TURN (short-lived credentials via API)
  $cfTokenId  = get_option('chat_calls_cf_turn_token_id');
  $cfApiToken = get_option('chat_calls_cf_turn_api_token');

  if (!empty($cfTokenId) && !empty($cfApiToken)) {
    $cfIce = prchat_cloudflare_turn_credentials($cfTokenId, $cfApiToken);
    if ($cfIce !== null) {
      return $cfIce;
    }
  }

  // Priority 2: Custom TURN (static credentials from admin panel)
  $turnUrl  = get_option('chat_calls_turn_url');
  $turnUser = get_option('chat_calls_turn_username');
  $turnCred = get_option('chat_calls_turn_credential');

  // Priority 3: constants fallback (chat_constants.php)
  if (empty($turnUrl) && defined('CHAT_CALLS_TURN_URL') && CHAT_CALLS_TURN_URL !== '') {
    $turnUrl  = CHAT_CALLS_TURN_URL;
    $turnUser = defined('CHAT_CALLS_TURN_USERNAME') ? CHAT_CALLS_TURN_USERNAME : '';
    $turnCred = defined('CHAT_CALLS_TURN_CREDENTIAL') ? CHAT_CALLS_TURN_CREDENTIAL : '';
  }

  if (!empty($turnUrl)) {
    $turn = ['urls' => [$turnUrl]];
    if (!empty($turnUser)) $turn['username']   = $turnUser;
    if (!empty($turnCred)) $turn['credential'] = $turnCred;
    $ice[] = $turn;
  }

  return $ice;
}


/**
 * Return the largest safe chat upload size configured for this module, in KB.
 * This respects PHP/Bluehost limits indirectly because PHP rejects files above server limits before CodeIgniter receives them.
 */
function pr_chat_max_upload_size_kb()
{
  $configured = (int) get_option('prchat_max_upload_size_kb');
  if ($configured <= 0) {
    $configured = 204800; // 200 MB default ceiling; PHP hosting limits may still be lower.
  }
  return max(10240, $configured);
}

/**
 * Ensure chat and CRM media project folders exist.
 */
function pr_chat_ensure_directory($dir)
{
  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }
  if (is_dir($dir) && !file_exists($dir . '/index.html')) {
    @file_put_contents($dir . '/index.html', '');
  }
  return is_dir($dir) && is_writable($dir);
}

/**
 * Copy uploaded group files into CRM media/projects/chat_groups/group_ID so staff can reuse them without re-uploading.
 */
function pr_chat_copy_group_upload_to_project_media($sourcePath, $fileName, $groupId)
{
  if (!defined('PR_CHAT_MEDIA_PROJECTS_FOLDER')) {
    return false;
  }
  $groupId = (int) $groupId;
  if ($groupId <= 0 || !is_file($sourcePath)) {
    return false;
  }
  $targetDir = PR_CHAT_MEDIA_PROJECTS_FOLDER . '/chat_groups/group_' . $groupId;
  if (!pr_chat_ensure_directory($targetDir)) {
    return false;
  }
  $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($fileName));
  $target = $targetDir . '/' . $safeName;
  return @copy($sourcePath, $target);
}

function pr_chat_projects_media_path_display()
{
  return defined('PR_CHAT_MEDIA_PROJECTS_FOLDER') ? PR_CHAT_MEDIA_PROJECTS_FOLDER : FCPATH . 'uploads/media/projects';
}


/**
 * Smart Choice Template UI repair controls.
 * Applies safe visual fixes from Setup > Settings > CRM Chat > Template UI.
 */
function prchat_template_ui_option($name, $default = '')
{
  $value = get_option($name);
  if ($value === false || $value === '') {
    return $default;
  }
  return $value;
}

function prchat_template_ui_css_number($name, $default, $min, $max)
{
  $value = (int) prchat_template_ui_option($name, $default);
  if ($value < $min) { $value = $min; }
  if ($value > $max) { $value = $max; }
  return $value;
}

function prchat_template_ui_css_color($name, $default)
{
  $value = trim((string) prchat_template_ui_option($name, $default));
  if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
    return $default;
  }
  return $value;
}

function prchat_template_ui_head_styles()
{
  if (prchat_template_ui_option('prchat_template_ui_enabled', '1') !== '1') {
    return;
  }

  $navbar_height = prchat_template_ui_css_number('prchat_template_navbar_height', 58, 44, 90);
  $logo_height = prchat_template_ui_css_number('prchat_template_logo_max_height', 52, 34, 84);
  $hamburger_size = prchat_template_ui_css_number('prchat_template_hamburger_size', 22, 16, 36);
  $dropdown_height = prchat_template_ui_css_number('prchat_template_dropdown_max_height', 300, 160, 620);
  $table_font = prchat_template_ui_css_number('prchat_template_table_font_size', 12, 10, 16);
  $full_name_width = prchat_template_ui_css_number('prchat_template_full_name_width', 170, 120, 320);
  $email_width = prchat_template_ui_css_number('prchat_template_email_width', 145, 90, 260);
  $client_login_font = prchat_template_ui_css_number('prchat_template_client_login_font_size', 12, 10, 16);
  $client_login_padding = prchat_template_ui_css_number('prchat_template_client_login_padding', 7, 4, 16);
  $nav_start = prchat_template_ui_css_color('prchat_template_gradient_start', '#0f766e');
  $nav_end = prchat_template_ui_css_color('prchat_template_gradient_end', '#1d4ed8');
  $nav_text = prchat_template_ui_css_color('prchat_template_gradient_text', '#ffffff');
  $hover_text = prchat_template_ui_css_color('prchat_template_hover_text', '#111827');
  $button_bg = prchat_template_ui_css_color('prchat_template_button_bg', '#169179');
  $button_text = prchat_template_ui_css_color('prchat_template_button_text', '#ffffff');

  echo '<style id="prchat-template-ui-repair">\n';
  echo ':root{--sc-template-navbar-height:' . $navbar_height . 'px;--sc-template-logo-height:' . $logo_height . 'px;--sc-template-hamburger-size:' . $hamburger_size . 'px;--sc-template-dropdown-height:' . $dropdown_height . 'px;--sc-template-table-font:' . $table_font . 'px;--sc-template-full-name-width:' . $full_name_width . 'px;--sc-template-email-width:' . $email_width . 'px;--sc-template-client-login-font:' . $client_login_font . 'px;--sc-template-client-login-padding:' . $client_login_padding . 'px;--sc-template-gradient-start:' . $nav_start . ';--sc-template-gradient-end:' . $nav_end . ';--sc-template-gradient-text:' . $nav_text . ';--sc-template-hover-text:' . $hover_text . ';--sc-template-button-bg:' . $button_bg . ';--sc-template-button-text:' . $button_text . ';}\n';
  echo '.hide-menu,.hide-menu svg,.hide-menu i{color:#fff!important;stroke:#fff!important}.hide-menu:hover,.hide-menu:focus,.hide-menu:hover svg,.hide-menu:focus svg{color:#111827!important;stroke:#111827!important}.hide-menu svg{width:var(--sc-template-hamburger-size)!important;height:var(--sc-template-hamburger-size)!important}.hide-menu{min-width:calc(var(--sc-template-hamburger-size) + 16px)!important;min-height:calc(var(--sc-template-hamburger-size) + 16px)!important;align-items:center!important;justify-content:center!important}\n';
  echo '.navbar img.img-responsive[src*="/uploads/company/"],#header img.img-responsive[src*="/uploads/company/"],.navbar-brand img,.company-logo img{max-height:var(--sc-template-logo-height)!important;width:auto!important;object-fit:contain!important}.navbar,.navbar-nav>li>a,#header{min-height:var(--sc-template-navbar-height)!important}.navbar-brand{min-height:var(--sc-template-navbar-height)!important;display:flex!important;align-items:center!important}\n';
  echo '.dropdown-menu.open{overflow:visible!important;max-height:none!important;min-height:0!important}.bootstrap-select .dropdown-menu.open>.inner.open,.bootstrap-select .dropdown-menu.inner{overflow-y:auto!important;overflow-x:hidden!important;max-height:var(--sc-template-dropdown-height)!important;min-height:0!important}.bootstrap-select .dropdown-menu.open .dropdown-menu.inner{max-height:var(--sc-template-dropdown-height)!important}.bootstrap-select .dropdown-menu li a{white-space:normal!important;line-height:1.25!important;padding:7px 12px!important}.bootstrap-select .bs-actionsbox .btn{font-size:11px!important;padding:5px 7px!important;border-radius:6px!important}\n';
  echo '.dataTable,.table.dataTable{font-size:var(--sc-template-table-font)!important;table-layout:fixed!important;width:100%!important}.dataTable th,.dataTable td{vertical-align:middle!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.dataTable th[aria-label*="Full name"],.dataTable td:nth-child(1){max-width:var(--sc-template-full-name-width)!important;width:var(--sc-template-full-name-width)!important}.dataTable th[aria-label*="Email"],.dataTable td a[href^="mailto:"],.dataTable td:nth-child(2){max-width:var(--sc-template-email-width)!important;width:var(--sc-template-email-width)!important}.dataTables_wrapper{overflow-x:auto!important}\n';
  echo 'body a.btn,body button.btn,.actions-btn{border-radius:8px!important}.customers .navbar .btn,.customers .navbar a.btn,.clients .navbar .btn,.clients .navbar a.btn,a[href*="authentication/login"].btn,a[href*="clients/login"].btn{font-size:var(--sc-template-client-login-font)!important;padding:var(--sc-template-client-login-padding)!important;line-height:1.2!important;border-radius:8px!important}\n';
  echo '[style*="linear-gradient"],[class*="gradient"],[class*="bg-gradient"],.tw-bg-gradient-to-r,.tw-bg-gradient-to-l,.tw-bg-gradient-to-b{color:var(--sc-template-gradient-text)!important}[style*="linear-gradient"] a,[class*="gradient"] a,[class*="bg-gradient"] a,.tw-bg-gradient-to-r a,.tw-bg-gradient-to-l a,.tw-bg-gradient-to-b a{color:var(--sc-template-gradient-text)!important}[style*="linear-gradient"] a:hover,[class*="gradient"] a:hover,[class*="bg-gradient"] a:hover,.tw-bg-gradient-to-r a:hover,.tw-bg-gradient-to-l a:hover,.tw-bg-gradient-to-b a:hover{color:var(--sc-template-hover-text)!important}\n';
  echo '.smart-choice-links-trigger,.scl-star-shell,.tw-bg-primary-600{background:var(--sc-template-button-bg)!important;color:var(--sc-template-button-text)!important}.smart-choice-links-trigger:hover,.scl-star-shell:hover{color:var(--sc-template-hover-text)!important}\n';
  echo '@media(max-width:768px){.hide-menu{color:#fff!important}.navbar,#header{min-height:calc(var(--sc-template-navbar-height) + 6px)!important}.navbar-brand img,.navbar img.img-responsive[src*="/uploads/company/"]{max-height:calc(var(--sc-template-logo-height) + 4px)!important}.dropdown-menu,.smart-choice-links-dropdown{min-width:280px!important}.navbar-nav>li>a{font-size:15px!important;padding-top:14px!important;padding-bottom:14px!important}.side-nav,.sidebar,.admin #side-menu{font-size:15px!important}.side-nav li a,.sidebar li a,#side-menu li a{padding-top:12px!important;padding-bottom:12px!important}}\n';
  echo '</style>';
}

function prchat_template_ui_footer_scripts()
{
  if (prchat_template_ui_option('prchat_template_ui_enabled', '1') !== '1') {
    return;
  }
  echo '<script id="prchat-template-ui-fixes">(function(){var jq=window.jQuery||window.$;if(jq){jq(function($){$(".bootstrap-select").on("shown.bs.select",function(){var $m=$(this).find(".dropdown-menu.open");$m.css({overflow:"visible",minHeight:"0",maxHeight:"none"});$m.find(">.inner.open,.dropdown-menu.inner").css({overflowY:"auto",overflowX:"hidden",maxHeight:getComputedStyle(document.documentElement).getPropertyValue("--sc-template-dropdown-height")||"300px",minHeight:"0"});});$("table.dataTable").each(function(){var $table=$(this);$table.find("th,td").css({overflow:"hidden",textOverflow:"ellipsis",whiteSpace:"nowrap"});});});}window.$=window.$||window.jQuery;})();</script>';
}
