<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_181 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix().'rec_candidate';
        if ($CI->db->table_exists($table)) {
            $columns = [
                'application_position'=>'VARCHAR(191) NULL','available_date'=>'DATE NULL','employment_type'=>'VARCHAR(80) NULL',
                'vehicle_type'=>'VARCHAR(80) NULL','education_level'=>'VARCHAR(100) NULL','construction_certificate'=>'VARCHAR(40) NULL',
                'criminal_record_answer'=>'VARCHAR(20) NULL','legal_authorized_answer'=>'VARCHAR(20) NULL','driver_license_answer'=>'VARCHAR(20) NULL',
                'screening_answers'=>'LONGTEXT NULL','reference_name'=>'VARCHAR(191) NULL','reference_phone'=>'VARCHAR(80) NULL',
                'emergency_name'=>'VARCHAR(191) NULL','emergency_phone'=>'VARCHAR(80) NULL','emergency_relation'=>'VARCHAR(100) NULL',
                'digital_signature'=>'VARCHAR(191) NULL','signature_date'=>'DATE NULL','application_source'=>'VARCHAR(100) NULL',
                'matched_staff_id'=>'INT(11) NULL','resume_synced_at'=>'DATETIME NULL'
            ];
            foreach ($columns as $column=>$definition) {
                if (!$CI->db->field_exists($column,$table)) {
                    $CI->db->query('ALTER TABLE `'.$table.'` ADD `'.$column.'` '.$definition);
                }
            }
        }
        $link = db_prefix().'rec_candidate_staff_links';
        if (!$CI->db->table_exists($link)) {
            $CI->db->query('CREATE TABLE `'.$link.'` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`candidate_id` INT NOT NULL,`staff_id` INT NOT NULL,`matched_by` VARCHAR(50) NULL,`synced_at` DATETIME NULL,PRIMARY KEY (`id`),UNIQUE KEY `candidate_staff` (`candidate_id`,`staff_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        }
        if (get_option('smart_choice_recruitment_w4_url') === false) {
            add_option('smart_choice_recruitment_w4_url','https://justsmartchoice.com/dev-tools/uploads/fw4.pdf');
        }
        if (get_option('smart_choice_recruitment_internal_recipients') === false) {
            add_option('smart_choice_recruitment_internal_recipients','haroldcabrerausa@gmail.com,harold.cabrera2012@gmail.com,admin@justsmartchoice.com');
        }
        $this->install_templates();
    }
    private function install_templates()
    {
        $CI=&get_instance();
        if (!function_exists('create_email_template')) { return; }
        $this->ensure_template('Smart Choice Job Application Received','<p>{logo_image_with_url}</p><p>Dear {candidate_name} {last_name},</p><p>Thank you for applying to <strong>Smart Choice Contractors USA</strong> for the {position} position. Our recruitment team has received your application and resume.</p><p>We will review your qualifications and contact you if your experience matches an available position.</p><p>{email_signature}</p>','smart_choice_job_application_received','Job application received','smart-choice-job-application-received','english');
        $this->ensure_template('Solicitud de Empleo Recibida','<p>{logo_image_with_url}</p><p>Estimado/a {candidate_name} {last_name},</p><p>Gracias por solicitar empleo en <strong>Smart Choice Contractors USA</strong> para la posición de {position}. Nuestro equipo de reclutamiento recibió su solicitud y currículum.</p><p>Revisaremos sus calificaciones y nos comunicaremos con usted si su experiencia coincide con una posición disponible.</p><p>{email_signature}</p>','smart_choice_job_application_received','Solicitud de empleo recibida','smart-choice-job-application-received','spanish');
        $this->ensure_template('New Smart Choice Job Application','<p>{logo_image_with_url}</p><p>A new job application was submitted by <strong>{candidate_name} {last_name}</strong> for <strong>{position}</strong>.</p><p><a href="{candidate_link}">Open Candidate Profile</a></p><p>{email_signature}</p>','smart_choice_new_job_application_internal','New job application submitted','smart-choice-new-job-application-internal','english');
        $this->ensure_template('Nueva Solicitud de Empleo','<p>{logo_image_with_url}</p><p><strong>{candidate_name} {last_name}</strong> envió una nueva solicitud para la posición de <strong>{position}</strong>.</p><p><a href="{candidate_link}">Abrir Perfil del Candidato</a></p><p>{email_signature}</p>','smart_choice_new_job_application_internal','Nueva solicitud de empleo','smart-choice-new-job-application-internal','spanish');
    }
    private function ensure_template($name,$message,$type,$subject,$slug,$language)
    {
        $CI=&get_instance(); $table=db_prefix().'emailtemplates';
        if (!$CI->db->table_exists($table)) { return; }
        $existing=$CI->db->where('slug',$slug)->where('language',$language)->get($table)->row();
        if ($existing) { return; }
        if ($language==='english') { create_email_template($subject,$message,$type,$name,$slug); return; }
        $base=$CI->db->where('slug',$slug)->where('language','english')->get($table)->row_array();
        if (!$base) { return; }
        unset($base['emailtemplateid']); $base['language']=$language; $base['name']=$name; $base['subject']=$subject; $base['message']=$message;
        $CI->db->insert($table,$base);
    }
    public function down() { }
}
