<?php
// Exercise the failed page with synthetic rows; no CRM or database required.
define('BASEPATH', __DIR__);
define('SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME', 'smart_choice_enterprise_core');
class App_Model {}
require dirname(__DIR__) . '/modules/smart_choice_enterprise_core/models/Smart_choice_enterprise_core_model.php';
$model = new Smart_choice_enterprise_core_model();
$rows = [
    ['staffid'=>1, 'employee'=>'Example <Staff>', 'email'=>'test@example.invalid', 'profile_image'=>'example.png', 'resolved_path'=>str_repeat('long-folder/', 30).'example.png', 'image_url'=>'/example.png', 'status'=>'Found'],
    ['staffid'=>2, 'employee'=>'Missing photo', 'email'=>'missing@example.invalid', 'profile_image'=>'missing.png', 'resolved_path'=>'Not found', 'image_url'=>'/placeholder.png', 'status'=>'Missing'],
];
$summary = $model->summarize_staff_images($rows);
if ($summary !== ['total'=>2, 'found'=>1, 'missing'=>1] || $model->summarize_staff_images([]) !== ['total'=>0, 'found'=>0, 'missing'=>0]) {
    throw new RuntimeException('Staff image summary is incorrect');
}
function init_head() {}
function init_tail() {}
function _l($key) { return $key; }
function admin_url($path) { return '/admin/'.$path; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function has_permission(...$args) { return false; }
$title = 'Staff Image Health';
ob_start();
require dirname(__DIR__) . '/modules/smart_choice_enterprise_core/views/staff_images.php';
$html = ob_get_clean();
if (!str_contains($html, 'table-responsive sc-staff-image-table-wrap') || !str_contains($html, 'Example &lt;Staff&gt;') || str_contains($html, 'export_staff_images')) {
    throw new RuntimeException('Staff image page did not retain its responsive, escaped, permission-filtered view');
}
echo "PASS Staff Image Health renders with correct empty/mixed summaries, escaped data and responsive table\n";
