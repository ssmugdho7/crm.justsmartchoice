<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_255 extends App_module_migration {
 public function up() { require_once dirname(__DIR__).'/schema_upgrade.php'; if (!feedbacks_upgrade_schema()) { throw new RuntimeException('Questionnaire schema upgrade failed; check CRM logs.'); } set_alert('success', _l('fb_upgrade_success')); return true; }
 public function down() { return true; }
}
