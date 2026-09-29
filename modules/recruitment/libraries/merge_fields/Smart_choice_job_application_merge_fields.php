<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_job_application_merge_fields extends App_merge_fields
{
    public function build(){ return [
        ['name'=>'Candidate First Name','key'=>'{candidate_name}','available'=>['smart_choice_job_application_received','smart_choice_new_job_application_internal']],
        ['name'=>'Candidate Last Name','key'=>'{last_name}','available'=>['smart_choice_job_application_received','smart_choice_new_job_application_internal']],
        ['name'=>'Candidate Profile Link','key'=>'{candidate_link}','available'=>['smart_choice_new_job_application_internal']],
        ['name'=>'Position','key'=>'{position}','available'=>['smart_choice_job_application_received','smart_choice_new_job_application_internal','recruitment_skill_test_internal']],
        ['name'=>'Candidate Name','key'=>'{candidate_name}','available'=>['recruitment_skill_test_internal']],
        ['name'=>'Test Score','key'=>'{test_score}','available'=>['recruitment_skill_test_internal']],['name'=>'Correct Answers','key'=>'{correct_answers}','available'=>['recruitment_skill_test_internal']],['name'=>'Total Questions','key'=>'{total_questions}','available'=>['recruitment_skill_test_internal']],['name'=>'Test IP','key'=>'{test_ip}','available'=>['recruitment_skill_test_internal']],['name'=>'Test Date Time','key'=>'{test_datetime}','available'=>['recruitment_skill_test_internal']],
    ]; }
    public function format($candidate){ return ['{candidate_name}'=>$candidate->candidate_name ?? '','{last_name}'=>$candidate->last_name ?? '','{candidate_link}'=>$candidate->candidate_link ?? '','{position}'=>$candidate->position ?? '','{test_score}'=>$candidate->test_score ?? '','{correct_answers}'=>$candidate->correct_answers ?? '','{total_questions}'=>$candidate->total_questions ?? '','{test_ip}'=>$candidate->test_ip ?? '','{test_datetime}'=>$candidate->test_datetime ?? '']; }
}
