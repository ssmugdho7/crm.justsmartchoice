<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_project_allowed($id) {
    $CI = &get_instance(); $CI->load->model('projects_model');
    return $CI->projects_model->get((int)$id) && (staff_can('view','projects') || $CI->projects_model->is_member((int)$id));
}
/** Explicit Day/Step sections only. Multiline details stay with their heading. */
function scaf_scope_tasks($scope, $start) {
    $scope = html_entity_decode(preg_replace('/<\/(?:p|div|li)>|<br\s*\/?\s*>/i', "\n", (string)$scope), ENT_QUOTES, 'UTF-8');
    $lines = preg_split('/\R/u', strip_tags($scope)); $sections=[]; $current=null;
    foreach($lines as $line) {
        $line=trim($line); if($line==='')continue;
        if(preg_match('/^(Day|Step)\s*(\d+)\s*[:–—-]\s*(.+)$/iu',$line,$m)) {
            if($current)$sections[]=$current;
            $day=(int)$m[2]; if($day<1 || $day>366)throw new InvalidArgumentException('Day numbers must be between 1 and 366.');
            $current=['name'=>$m[1].' '.$day.': '.$m[3], 'body'=>$line, 'day'=>$day];
        } elseif($current) { $current['body'].="\n".$line; }
    }
    if($current)$sections[]=$current;
    if(count($sections)>100)throw new InvalidArgumentException('Limit scope conversion to 100 tasks at a time.');
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$start);
    if(!$date || $date->format('Y-m-d')!==$start)throw new InvalidArgumentException('Set a valid project start date before converting scope.');
    foreach($sections as &$section) { $section['date']=$date->modify('+'.($section['day']-1).' days')->format('Y-m-d'); $section['marker']='[SCAF-SCOPE:'.hash('sha256',mb_strtolower(preg_replace('/\s+/u',' ',trim($section['name'])))).']'; }
    unset($section); return $sections;
}
function scaf_project_scope($project) {
    $CI=&get_instance();
    $field=$CI->db->where('fieldto','projects')->where('active',1)->where('name','Scope of Work')->get(db_prefix().'customfields')->row_array();
    if($field) { $value=get_custom_field_value($project->id,$field['id'],'projects',false); if(trim(strip_tags((string)$value))!=='')return $value; }
    return $project->description ?? '';
}
