<?php
declare(strict_types=1);

$controller = file_get_contents(__DIR__ . '/../modules/development/controllers/Development.php');
$view = file_get_contents(__DIR__ . '/../modules/development/views/view.php');
$form = file_get_contents(__DIR__ . '/../modules/development/views/form.php');
if ($controller === false || $view === false || $form === false) {
    throw new RuntimeException('Unable to read development module files');
}
if (strpos($controller, 'if($this->input->method(true)!==\'POST\')') === false) {
    throw new RuntimeException('Completion endpoint must reject non-POST requests');
}
if (strpos($view, "form_open(admin_url('development/complete/'") === false) {
    throw new RuntimeException('Completion control must submit a POST form');
}
if (strpos($view, 'href="<?=admin_url(\'development/complete/') !== false) {
    throw new RuntimeException('Completion must not be a mutating GET link');
}
if (strpos($controller, 'in_array((int)$raw,$assignedIds,true)') === false) {
    throw new RuntimeException('Completion endpoint must restrict completed_by to assigned staff');
}
if (strpos($view, '$assignedIds') === false || strpos($view, 'if(!in_array((int)$s[\'staffid\'],$assignedIds,true))continue;') === false) {
    throw new RuntimeException('Completion dropdown must filter to assigned staff');
}
if (strpos($form, '$assignedIds') === false || strpos($form, 'if(!in_array((int)$s[\'staffid\'],$assignedIds,true))continue;') === false) {
    throw new RuntimeException('Edit completed-by dropdown must filter to assigned staff');
}
echo "PASS repair completion workflow uses POST only and assigned-staff filtering\n";
