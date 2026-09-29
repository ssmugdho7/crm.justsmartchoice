<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Cabinet_maker_merge_fields extends App_merge_fields{
 public function build(){ return [['name'=>'Design Name','key'=>'{design_name}','available'=>['cabinet_maker']],['name'=>'Design Share URL','key'=>'{design_share_url}','available'=>['cabinet_maker']],['name'=>'Design Admin URL','key'=>'{design_admin_url}','available'=>['cabinet_maker']]]; }
 public function format($data){ return ['{design_name}'=>$data['design']->name??'','{design_share_url}'=>$data['share_url']??'','{design_admin_url}'=>isset($data['design'])?admin_url('cabinet_maker/designer/'.$data['design']->id):'']; }
}
