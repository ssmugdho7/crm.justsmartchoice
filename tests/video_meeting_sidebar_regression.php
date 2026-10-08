<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function is_admin_sidebar_background_light() { return false; }
function staff_profile_image(...$args) { return ''; }
function get_staff_full_name() { return 'Test Staff'; }
function get_staff() { return (object) ['email' => 'staff@example.test']; }
function e($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function _l($text, ...$args) { return $text; }
function admin_url($path = '') { return '/admin/' . $path; }
function is_language_disabled() { return true; }
function hooks() { return new class { function do_action(...$args) {} }; }
function _attributes_to_string($attributes) { return ''; }
class SidebarRenderer {
    public $app, $load;
    function __construct() {
        $this->app = new class { function show_setup_menu() { return false; } };
        $this->load = new class { function view(...$args) {} };
    }
    function render($sidebar_menu) {
        $current_user = (object) ['staffid' => 1];
        ob_start(); include dirname(__DIR__) . '/application/views/admin/includes/aside.php'; return ob_get_clean();
    }
}
function item($slug, $children = []) { return ['slug' => $slug, 'name' => $slug, 'href' => '/admin/' . $slug, 'children' => $children, 'icon' => 'fa fa-video-camera']; }
function child($slug) { return ['slug' => $slug, 'name' => $slug, 'href' => '/admin/google_meet/' . $slug]; }
$renderer = new SidebarRenderer(); $checks = 0;
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); $GLOBALS['checks']++; }
$full = $renderer->render([item('dashboard'), item('video-meetings'), item('google-meet', [child('new'), child('reports')])]);
check(strpos($full, 'class="menu-item-video-meetings"') === false, 'Redundant standalone link omitted');
check(strpos($full, 'class="menu-item-google-meet"') !== false && strpos($full, 'sub-menu-item-reports') !== false, 'Jitsi parent and children preserved');
check(strpos($full, 'menu-item-dashboard') !== false, 'Unrelated sidebar entry preserved');
$legacyOnly = $renderer->render([item('video-meetings')]);
check(strpos($legacyOnly, 'menu-item-video-meetings') !== false, 'Staff without the Jitsi submenu retain their sole existing meeting entry');
$noChildren = $renderer->render([item('video-meetings'), item('google-meet')]);
check(strpos($noChildren, 'menu-item-video-meetings') !== false, 'No accidental removal when the authorized submenu is empty');
$bothHaveChildren = $renderer->render([item('video-meetings', [child('legacy')]), item('google-meet', [child('new')])]);
check(strpos($bothHaveChildren, 'menu-item-video-meetings') !== false, 'Only the entry without submenus is suppressed');
$restricted = $renderer->render([item('video-meetings'), item('google-meet', [child('reports')])]);
check(strpos($restricted, 'sub-menu-item-reports') !== false && strpos($restricted, 'sub-menu-item-new') === false, 'No new children or permissions introduced');
echo "PASS: $checks duplicate meeting menu, restricted access and sidebar preservation checks\n";
