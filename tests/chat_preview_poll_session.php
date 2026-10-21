<?php
// Exercise the actual AdminController session cleanup block without booting CRM or writing sessions.
$source = file_get_contents(__DIR__.'/../application/core/AdminController.php');
$start = strpos($source, "        if (\$this->uri->segment(3) != 'notifications_check'");
$end = strpos($source, '        // Update staff last activity', $start);
if ($start === false || $end === false) { throw new RuntimeException('Admin session cleanup boundary not found'); }
$block = substr($source, $start, $end - $start);
$fixture = new class {
    public $session; public $input; public $router; public $uri;
    public function run($block, $class, $method, $ajax) {
        $this->session = new class {
            public $values = ['staff_user_id'=>18,'staff_logged_in'=>true,'client_user_id'=>32,'contact_user_id'=>32,'client_logged_in'=>true,'logged_in_as_client'=>true];
            function has_userdata($key) { return array_key_exists($key, $this->values); }
            function unset_userdata($key) { unset($this->values[$key]); }
        };
        $this->input = new class($ajax) { private $ajax; function __construct($ajax) {$this->ajax=$ajax;} function is_ajax_request(){return $this->ajax;} };
        $this->router = new class($class,$method) { private $class; private $method; function __construct($class,$method){$this->class=$class;$this->method=$method;} function fetch_class(){return $this->class;} function fetch_method(){return $this->method;} };
        $this->uri = new class($method) { private $method; function __construct($method){$this->method=$method;} function segment($number){return $number===3?$this->method:null;} };
        eval($block);
        return $this->session->values;
    }
};
foreach (['users','pusher_auth','getMessages','getGroupMessages'] as $method) {
    $result = $fixture->run($block, 'Prchat_Controller', $method, true);
    if (($result['contact_user_id'] ?? null) !== 32 || empty($result['logged_in_as_client'])) { throw new RuntimeException($method.' background poll ends a valid customer preview during recording/upload'); }
}
$result = $fixture->run($block,'Prchat_Controller','pusher_auth',false);
if (($result['contact_user_id'] ?? null) !== 32) { throw new RuntimeException('JSONP realtime authentication must preserve preview too'); }
foreach ([['Prchat_Controller','chat_full_view',false],['Prchat_Controller','getGroupMessages',false],['Prchat_Controller','send',true],['Clients','client',true],['Authentication','logout',false]] as [$class,$method,$ajax]) {
    $result = $fixture->run($block,$class,$method,$ajax);
    foreach (['client_user_id','contact_user_id','client_logged_in','logged_in_as_client'] as $key) {
        if (array_key_exists($key,$result)) { throw new RuntimeException('Admin navigation/actions must still end the client preview: '.$class.'/'.$method); }
    }
    if (empty($result['staff_logged_in']) || $result['staff_user_id'] !== 18) { throw new RuntimeException('Staff identity changed'); }
}
echo "PASS background chat polls preserve preview; admin navigation/actions retain native cleanup and staff identity\n";
