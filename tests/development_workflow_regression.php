<?php
declare(strict_types=1);

$controller = file_get_contents(__DIR__ . '/../modules/development/controllers/Development.php');
$view = file_get_contents(__DIR__ . '/../modules/development/views/view.php');
if ($controller === false || $view === false) {
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
echo "PASS repair completion workflow uses POST only\n";
