<?php
// php tests/toast_master_settings.php — no database writes or application bootstrap.
if (PHP_SAPI !== 'cli') { exit(1); }
define('BASEPATH', __DIR__);
class SettingsRedirect extends RuntimeException {}
class SettingsDenied extends RuntimeException {}
class SettingsInput { public $data; public function post() { return $this->data; } }
class SettingsLoader { public function model($name) {} public function helper($name) {} }
class SettingsWriter { public $saved; public function update($data) { $this->saved = $data; return 1; } }
class AdminController {
    public $input, $load, $settings_model;
    public function __construct() { $this->input = new SettingsInput(); $this->load = new SettingsLoader(); $this->settings_model = new SettingsWriter(); }
}
function is_admin() { return $GLOBALS['admin'] ?? true; }
function access_denied($name) { throw new SettingsDenied(); }
function _l($key) { return $key; }
function set_alert(...$args) {}
function admin_url($path) { return '/admin/' . $path; }
function redirect($url) { throw new SettingsRedirect(); }
function get_option($name) { return $GLOBALS['storedOptions'][$name] ?? ''; }
function update_option($name, $value) { $GLOBALS['storedOptions'][$name] = $value; }
function add_option($name, $value) { $GLOBALS['storedOptions'][$name] = $value; }
function &get_instance() { return $GLOBALS['ci']; }
function db_prefix() { return 'tbl'; }
$GLOBALS['ci'] = (object)['db' => new class { public function table_exists($name) { return true; } }];
require __DIR__ . '/../modules/toast_master/helpers/toast_master_helper.php';
require __DIR__ . '/../modules/toast_master/controllers/Toast_master.php';
function check($value, $label) { if (!$value) { throw new RuntimeException($label); } echo "PASS $label\n"; }
function save($settings) {
    $controller = new Toast_master(); $controller->input->data = ['settings' => $settings, 'unrelated' => 'ignored'];
    try { $controller->settings(); } catch (SettingsRedirect $e) {}
    return $controller->settings_model->saved['settings'];
}
$saved = save(['toast_master_enable' => '0', 'toast_master_sound_enable' => '0', 'toast_master_pause_on_hover' => '0',
    'toast_master_progress_bar' => '0', 'toast_master_history_enable' => '0', 'toast_master_intercept_ajax' => 'on',
    'toaster_style' => '12', 'toaster_position' => 'bottom-center', 'toast_master_animation' => 'flip',
    'toast_master_sound_name' => 'bell', 'toast_master_volume' => '0', 'toast_master_duration' => '3500', 'smtp_password' => 'ignored']);
foreach (['enable', 'sound_enable', 'pause_on_hover', 'progress_bar', 'history_enable'] as $key) {
    check($saved['toast_master_' . $key] === '0', "$key preserves No");
}
check($saved['toast_master_intercept_ajax'] === '1' && $saved['toast_master_intercept_messages'] === '0', 'checked and unchecked sources persist');
check($saved['toaster_style'] === '12' && $saved['toaster_position'] === 'bottom-center' && $saved['toast_master_animation'] === 'flip', 'appearance persists');
check($saved['toast_master_volume'] === '0' && !isset($saved['smtp_password']), 'zero volume and settings boundary');
$invalid = save(['toaster_style' => ['bad'], 'toast_master_duration' => '999999', 'toast_master_volume' => '-2']);
check($invalid['toaster_style'] === '1' && $invalid['toast_master_duration'] === '60000' && $invalid['toast_master_volume'] === '0', 'invalid values are bounded');
$GLOBALS['storedOptions'] = $saved;
$runtime = toast_master_get_runtime_settings();
check(!$runtime['enabled'] && !$runtime['soundEnabled'] && !$runtime['historyEnabled'] && $runtime['volume'] === 0, 'runtime honors disabled values and zero');
require __DIR__ . '/../modules/toast_master/install.php';
check(get_option('toast_master_enable') === '0' && get_option('toast_master_volume') === '0', 'activation preserves saved disabled and zero settings');
$GLOBALS['admin'] = false;
try { save(['toast_master_enable' => '1']); throw new RuntimeException('Unauthorized save accepted'); }
catch (SettingsDenied $e) { echo "PASS non-admin settings denied\n"; }
require __DIR__ . '/../application/services/messages/AbstractMessage.php';
require __DIR__ . '/../application/services/messages/CronJobFailure.php';
$cron = new \app\services\messages\CronJobFailure();
check(strpos($cron->openHtml(), 'data-toast-source="cron"') !== false, 'dashboard cron warning identifies its source');
