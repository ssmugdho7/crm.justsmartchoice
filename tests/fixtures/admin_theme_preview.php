<?php
// Local-only visual fixture: synthetic data, no CRM bootstrap or database access.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) { http_response_code(404); exit; }
define('BASEPATH',__DIR__);define('FCPATH',dirname(__DIR__,2).'/');
$lang=[];require FCPATH.'application/language/english/english_lang.php';require FCPATH.'modules/theme_style/language/english/theme_style_lang.php';
function _l($key){return $GLOBALS['lang'][$key]??$key;}
function get_option($key){return $key==='smartchoice_admin_theme'&&($_GET['theme']??'')==='classic'?'classic':'';}
class FixtureHooks{function add_action(...$args){}function add_filter(...$args){}function apply_filters($key,$v){return $v;}}
function hooks(){static $h;return $h??($h=new FixtureHooks());}
function base_url($path){return '/'.$path;}
function admin_url($path){return '#';}
function html_escape($v){return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
function get_styling_areas($type){return [];}
function form_open($path){return '<form action="#">';}
function form_close(){return '</form>';}
require FCPATH.'application/helpers/my_functions_helper.php';
function init_head(){echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/builds/vendor-admin.css"><link rel="stylesheet" href="/assets/builds/tailwind.css"><link rel="stylesheet" href="/assets/css/style.min.css">';smartchoice_enqueue_modern_theme();echo '</head><body class="admin sc-theme-'.smartchoice_admin_theme().'"><header id="header" style="left:0;right:0"><span style="padding:18px;display:block">Smart Choice — synthetic admin preview</span></header><aside id="menu" class="sidebar"><ul id="side-menu" class="nav"><li><a href="#">Dashboard</a></li><li><a href="#">Sales</a></li><li><a href="#">Settings</a></li></ul></aside>';}
function init_tail(){echo '<section class="panel_s" style="margin:20px 24px"><div class="panel-body"><h3>Shared year selector</h3><div class="bootstrap-select" style="width:79px"><button type="button" class="btn dropdown-toggle btn-default" style="width:100%" aria-label="Year 2026"><span class="filter-option">2026</span><span class="bs-caret"><span class="caret"></span></span></button></div></div></section><script src="/assets/builds/vendor-admin.js"></script><script>function fixtureLayout(){document.body.classList.toggle("page-small",innerWidth<768)}fixtureLayout();addEventListener("resize",fixtureLayout);</script>';}

require FCPATH.'modules/theme_style/views/theme_style.php';
