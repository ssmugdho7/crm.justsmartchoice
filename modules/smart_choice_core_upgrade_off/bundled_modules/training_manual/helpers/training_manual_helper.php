<?php

defined('BASEPATH') or exit('No direct script access allowed');

function training_manual_generate_code($s){
  return  md5(uniqid($s, true));
}

function training_manual_get_mindmap_thumb($filename = ''){
  if($filename != ''){
    return base_url(WIKI_UPLOAD_PATH.'/storage/mindmap') . '/' . $filename;
  }else{
    return base_url(TRAINING_MANUAL_ASSETS_PATH.'/builder/ui/default_thumb.png');
  }
}

function training_manual_get_mindmap_content(){
  return '{"data":{"text":"My New Mind Map"},"template":"default","theme":"fresh-blue","version":"1.3.5"}';
}