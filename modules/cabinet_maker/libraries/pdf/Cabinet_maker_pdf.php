<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(APPPATH.'libraries/pdf/App_pdf.php');
class Cabinet_maker_pdf extends App_pdf{private $design;private $parts;public function __construct($design,$parts=[]){$this->design=$design;$this->parts=$parts;parent::__construct();$this->SetTitle($design->name);}public function prepare(){$this->set_view_vars(['design'=>$this->design,'parts'=>$this->parts]);return $this->build();}protected function type(){return 'cabinet-maker';}protected function file_path(){return module_views_path('cabinet_maker','pdf/design.php');}}
