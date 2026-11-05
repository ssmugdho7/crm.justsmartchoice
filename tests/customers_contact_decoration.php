<?php
// Execute the update-safe row hook with authorized native row fixtures; no database.
define('BASEPATH', __DIR__);
function hooks() { return new class { function add_filter(...$args) {} function add_action(...$args) {} }; }
function get_instance() { return (object)['router' => new class { function fetch_class() { return 'clients'; } }]; }
function scl_licensed($module) { return true; }
function e($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
require dirname(__DIR__) . '/modules/sc_customers_workspace/includes/customers.php';
function check($value, $message) { if (!$value) { throw new RuntimeException($message); } }
$base = ['<img src="https://crm.example/assets/images/user-placeholder.jpg" class="client-profile-image-small"><a href="#" onclick="contact(4,9);return false;">Ana</a><div class="row-options"><a href="/delete/9">Delete</a></div>', '<a href="mailto:a@example.com">a@example.com</a>'];
$row=sccw_contact_row($base,['firstname'=>'Ana','lastname'=>'<Rivera>','is_primary'=>1]);
check(strpos($row[0],'A&lt;')!==false,'Initials escaped');
check(strpos($row[0],'is-primary')!==false,'Actual primary flag reflected');
check(strpos($row[0],'contact(4,9)')!==false && strpos($row[0],'/delete/9')!==false,'Native action URLs and handlers preserved');
check($row[1]===$base[1],'Other data unchanged');
$base[0]=str_replace('assets/images/user-placeholder.jpg','uploads/client_profile_images/9/small_portrait.png',$base[0]);
$row=sccw_contact_row($base,['full_name'=>'Ana Rivera','is_primary'=>0]);
check(strpos($row[0],'small_portrait.png')!==false && strpos($row[0],'sccw-contact-initials')===false,'Uploaded portraits retained');
echo "PASS contact initials escaping, primary flag, native action preservation, existing portrait retention\n";
