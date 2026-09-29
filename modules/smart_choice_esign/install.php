<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI=&get_instance();
foreach (['contracts','estimates','proposals'] as $table) {
  $name=db_prefix().$table;
  if ($CI->db->table_exists($name) && !$CI->db->field_exists('initials',$name)) {
    $CI->db->query("ALTER TABLE `{$name}` ADD `initials` VARCHAR(80) NULL AFTER `signature`");
  }
}
