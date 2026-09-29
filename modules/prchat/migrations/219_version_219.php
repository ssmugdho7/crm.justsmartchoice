<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_219 extends App_module_migration {
    public function up() {
        if (get_option('prchat_staff_voice_call_mode') === false) { add_option('prchat_staff_voice_call_mode', 'internal'); }
        if (get_option('prchat_ai_model') === false) { add_option('prchat_ai_model', 'gpt-4o-mini'); }
    }
}
