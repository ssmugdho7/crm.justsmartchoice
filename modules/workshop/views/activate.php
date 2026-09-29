<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
// Como o módulo é sempre considerado ativado, redirecionamos para a URL original
// Esta página nunca deve ser mostrada
$original_url = isset($_GET['original_url']) ? $_GET['original_url'] : admin_url();
redirect($original_url);
?>