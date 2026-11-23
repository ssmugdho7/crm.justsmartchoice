<?php
// Regression: every Settings sidebar heading must have a clickable child, and
// childless modules registered with a top-level 'view' must resolve via ?group=.
// Runs without a live CRM: loads the real App class and calls it via reflection.
define('BASEPATH', __DIR__);
define('FCPATH', dirname(__DIR__) . '/');
require FCPATH . 'application/services/utilities/Arr.php';
function app_sort_by_position($array, $keepIndex = false) { return \app\services\utilities\Arr::sortBy($array, 'position', $keepIndex); }
require FCPATH . 'application/libraries/App.php';

$failures = 0;
function check($ok, $message) { global $failures; echo ($ok ? 'PASS ' : 'FAIL ') . $message . PHP_EOL; if (!$ok) { $failures++; } }

// Same lookup admin/Settings::index() performs for ?group=<id>.
function resolve_group(array $sections, string $group) {
    foreach ($sections as $section) { foreach ($section['children'] as $child) { if ($child['id'] == $group) { return $child; } } }
    return null;
}

$app = (new ReflectionClass('App'))->newInstanceWithoutConstructor();
$app->add_settings_section('general', ['title' => 'General', 'position' => 1, 'children' => [
    ['id' => 'general', 'name' => 'General', 'view' => 'admin/settings/includes/general', 'position' => 5, 'icon' => 'fa fa-cog'],
    ['name' => 'Company', 'view' => 'admin/settings/includes/company', 'position' => 10],
]]);
$app->add_settings_section('payroll_hub', ['id' => 'payroll_hub', 'group' => 'payroll_hub', 'name' => 'Payroll Hub', 'view' => 'hr_payroll/payroll_hub_settings', 'position' => 45, 'icon' => 'fa-solid fa-money-check-dollar', 'without_submit_button' => true]);
$app->add_settings_section('sql_doctor', ['name' => 'SQL Doctor Settings', 'view' => 'sql_doctor/settings', 'position' => 65]);
$app->add_settings_section('empty_heading', ['title' => 'Empty Heading', 'position' => 70]);
$app->add_settings_section('other', ['title' => 'Other', 'position' => 80]);
$app->add_settings_section_child('other', 'sms', ['name' => 'SMS', 'view' => 'admin/settings/includes/sms', 'position' => 1]);

$sections = $app->get_settings_sections();

check(!isset($sections['empty_heading']), 'section with neither children nor view is removed');
foreach ($sections as $key => $section) { check(count($section['children']) > 0, "heading '$key' has at least one child"); }

$g = $sections['general']['children'];
check(count($g) === 2 && $g[0]['id'] === 'general' && $g[1]['id'] === 'company' && $g[0]['icon'] === 'fa fa-cog', 'existing children untouched (ids, order, icon)');
check(!isset($sections['general']['view']), 'section with children gets no synthetic view');

$p = $sections['payroll_hub'];
check(count($p['children']) === 1, 'childless-with-view section gets exactly one child');
check($p['children'][0]['id'] === 'payroll_hub' && $p['children'][0]['view'] === 'hr_payroll/payroll_hub_settings', 'payroll child id/view copied');
check($p['children'][0]['name'] === 'Payroll Hub' && $p['children'][0]['icon'] === 'fa-solid fa-money-check-dollar', 'payroll child name/icon copied');
check(($p['children'][0]['without_submit_button'] ?? false) === true, 'extra keys (without_submit_button) preserved');
check($p['title'] === 'Payroll Hub', 'heading title falls back to name');

$s = $sections['sql_doctor']['children'];
check(count($s) === 1 && $s[0]['id'] === 'sql_doctor' && $s[0]['view'] === 'sql_doctor/settings', 'child id falls back to section key when no group/id');

check(count($sections['other']['children']) === 1 && $sections['other']['children'][0]['id'] === 'sms', 'add_settings_section_child still works');

foreach (['general', 'company', 'payroll_hub', 'sql_doctor', 'sms'] as $id) { check(resolve_group($sections, $id) !== null, "?group=$id resolves (no 404)"); }
check(resolve_group($sections, 'empty_heading') === null, '?group=empty_heading still 404s');

// Real purchasing_hub registration: one heading, three children with legacy group ids.
$src = file_get_contents(FCPATH . 'modules/purchasing_hub/purchasing_hub.php');
foreach (['purchasing_hub_settings', 'purchasing_hub_health', 'purchasing_hub_help'] as $id) { check(strpos($src, "'id'       => '$id'") !== false, "purchasing_hub child id $id kept"); }
check(substr_count($src, 'add_settings_section(') === 1, 'purchasing_hub registers a single settings heading');

echo $failures === 0 ? "ALL PASSED\n" : "$failures FAILED\n";
exit($failures === 0 ? 0 : 1);
