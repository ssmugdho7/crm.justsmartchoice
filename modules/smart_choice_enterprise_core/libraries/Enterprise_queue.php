<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enterprise_queue
{
    private $CI;
    public function __construct() { $this->CI = &get_instance(); }

    public function push($handler, array $payload = [], $queue = 'default', $availableAt = null, $priority = 100, $jobKey = null)
    {
        $table = db_prefix().'sce_queue_jobs';
        if (!$this->CI->db->table_exists($table)) { return false; }
        $jobKey = $jobKey ?: hash('sha256', $handler.'|'.json_encode($payload).'|'.microtime(true).'|'.random_int(1, PHP_INT_MAX));
        $this->CI->db->insert($table, [
            'queue_name'=>substr($queue,0,100), 'job_key'=>$jobKey, 'handler'=>substr($handler,0,191),
            'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'status'=>'pending','priority'=>(int)$priority,'attempts'=>0,
            'max_attempts'=>(int)(get_option('smart_choice_enterprise_queue_max_attempts') ?: 3),
            'available_at'=>$availableAt ?: date('Y-m-d H:i:s'),'created_by'=>get_staff_user_id() ?: null,
            'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        return $this->CI->db->insert_id();
    }

    public function process($limit = 10)
    {
        $table = db_prefix().'sce_queue_jobs';
        if (!$this->CI->db->table_exists($table)) { return 0; }
        $jobs = $this->CI->db->where('status','pending')->where('available_at <=',date('Y-m-d H:i:s'))
            ->order_by('priority','ASC')->order_by('id','ASC')->limit(max(1,min(100,(int)$limit)))->get($table)->result_array();
        $processed = 0;
        foreach ($jobs as $job) {
            if (!$this->reserve((int)$job['id'])) { continue; }
            try {
                $payload = json_decode((string)$job['payload'], true) ?: [];
                $result = hooks()->apply_filters('smart_choice_enterprise_queue_handler_'.$this->safeKey($job['handler']), null, $payload, $job);
                if ($result === null && $job['handler'] !== 'enterprise.noop') {
                    throw new RuntimeException('No queue handler is registered for this job.');
                }
                $this->CI->db->where('id',$job['id'])->update($table,['status'=>'completed','completed_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                $processed++;
            } catch (Throwable $e) {
                $attempts = ((int)$job['attempts']) + 1;
                $failed = $attempts >= (int)$job['max_attempts'];
                $this->CI->db->where('id',$job['id'])->update($table,[
                    'status'=>$failed?'failed':'pending','attempts'=>$attempts,'reserved_at'=>null,
                    'available_at'=>$failed?$job['available_at']:date('Y-m-d H:i:s', time()+min(3600,60*$attempts)),
                    'failed_at'=>$failed?date('Y-m-d H:i:s'):null,'last_error'=>substr($e->getMessage(),0,65000),'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }
        }
        return $processed;
    }

    private function reserve($id)
    {
        $table=db_prefix().'sce_queue_jobs';
        $this->CI->db->where('id',$id)->where('status','pending')->update($table,['status'=>'processing','reserved_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        return $this->CI->db->affected_rows() === 1;
    }
    private function safeKey($key) { return preg_replace('/[^a-zA-Z0-9_]/','_',str_replace(['.','-'],'_',$key)); }
}
