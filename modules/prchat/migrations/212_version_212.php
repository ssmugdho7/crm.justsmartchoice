<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_212 extends App_module_migration {
 public function up(){
  foreach(['prchat_voice_to_text_enabled'=>'1','prchat_ai_improve_enabled'=>'1','prchat_speech_language'=>'auto','prchat_inactivity_seconds'=>'10','prchat_ai_model'=>'gpt-4o-mini'] as $k=>$v){ if(get_option($k)===''){ add_option($k,$v); } }
  update_option('prchat_version','2.1.2');
 }
 public function down(){}
}
