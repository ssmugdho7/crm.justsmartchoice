<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="header">
    <button type="button"
        class="hide-menu tw-inline-flex tw-bg-transparent tw-border-0 tw-p-1 tw-mt-4 hover:tw-bg-neutral-600/10 tw-text-neutral-600 hover:tw-text-neutral-800 focus:tw-text-neutral-800 focus:tw-outline-none tw-rounded-md tw-mx-4 ltr:md:tw-ml-4 rtl:md:tw-mr-4 ltr:tw-float-left  rtl:tw-float-right">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="tw-h-4 tw-w-4 tw-text-current">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M2.25 18.003h19.5m-19.5-6h19.5m-19.5-6h19.5"></path>
        </svg>
    </button>
    <nav>
        <div class="tw-flex tw-justify-between">
            <div class="tw-overflow-hidden tw-shrink-0">
                <div id="logo"
                    class="tw-h-[57px] tw-hidden md:tw-flex tw-items-center [&_img]:tw-h-9 [&_img]:tw-w-auto">
                    <?php $logo = get_admin_header_logo_url(); ?>
                    <?php if (! $logo) { ?>
                    <a class="logo logo-text tw-text-2xl tw-font-semibold"
                        href="<?= hooks()->apply_filters('admin_header_logo_href', admin_url()); ?>">
                        <?= e(get_option('companyname')); ?>
                    </a>
                    <?php } else { ?>
                    <a class="logo"
                        href="<?= hooks()->apply_filters('admin_header_logo_href', admin_url()); ?>">
                        <img src="<?= e($logo); ?>"
                            class="img-responsive"
                            alt="<?= e(get_option('companyname')); ?>" />
                    </a>
                    <?php } ?>
                </div>
            </div>
            <div class="tw-flex tw-flex-1 sm:tw-flex-initial">
                <div id="top_search"
                    class="tw-inline-flex tw-relative dropdown sm:tw-ml-1.5 sm:tw-mr-3 tw-max-w-xl tw-flex-auto tw-group/top-search"
                    data-toggle="tooltip" data-placement="bottom"
                    data-title="<?= _l('search_by_tags'); ?>">
                    <input type="search" id="search_input"
                        class="ltr:tw-pr-4 ltr:tw-pl-9 rtl:tw-pr-9 rtl:tw-pl-4 tw-ml-1 tw-mt-2 focus:!tw-ring-0 tw-w-full !tw-placeholder-neutral-500 !tw-shadow-none tw-text-neutral-800 focus:!tw-placeholder-neutral-600 hover:!tw-placeholder-neutral-600 sm:tw-w-[350px] tw-h-[38px] tw-border-0 tw-border-solid !tw-border-white !tw-bg-neutral-100 !tw-rounded-lg"
                        placeholder="<?= _l('top_search_placeholder'); ?>"
                        autocomplete="off">
                    <div id="top_search_button" class="tw-absolute rtl:tw-right-2 ltr:tw-left-2 tw-top-2.5">
                        <button
                            class="tw-outline-none tw-border-0 tw-p-2 tw-text-neutral-400 group-focus-within/top-search:tw-text-neutral-600 tw-bg-transparent">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                    <div id="search_results">
                    </div>
                    <ul class="dropdown-menu search-results animated fadeIn search-history" id="search-history">
                    </ul>

                </div>
                <ul class="nav navbar-nav visible-md visible-lg">
                    <?php $quickActions = collect($this->app->get_quick_actions_links())->reject(function ($action) {
                        return isset($action['permission']) && staff_cant('create', $action['permission']);
                    }); ?>
                    <?php if ($quickActions->isNotEmpty()) { ?>
                    <li class="icon tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5 -tw-mt-1"
                        title="<?= _l('quick_create'); ?>"
                        data-toggle="tooltip" data-placement="bottom">
                        <a href="#" class="!tw-px-0 tw-group !tw-text-white" data-toggle="dropdown">
                            <span
                                class="tw-rounded-full tw-bg-primary-600 tw-text-white tw-inline-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 -tw-mt-1 group-hover:!tw-bg-primary-700">
                                <i class="fa-regular fa-plus fa-lg"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base">
                            <li class="dropdown-header tw-mb-1">
                                <?= _l('quick_create'); ?>
                            </li>
                            <?php foreach ($quickActions as $key => $item) {
                                $url = '';
                                if (isset($item['permission'])) {
                                    if (staff_cant('create', $item['permission'])) {
                                        continue;
                                    }
                                }
                                if (isset($item['custom_url'])) {
                                    $url = $item['url'];
                                } else {
                                    $url = admin_url('' . $item['url']);
                                }
                                $href_attributes = '';
                                if (isset($item['href_attributes'])) {
                                    foreach ($item['href_attributes'] as $key => $val) {
                                        $href_attributes .= $key . '="' . $val . '"';
                                    }
                                } ?>
                            <li>
                                <a href="<?= e($url); ?>"
                                    <?= $href_attributes; ?>
                                    class="tw-group tw-inline-flex tw-space-x-0.5 tw-text-neutral-700">
                                    <?php if (isset($item['icon'])) { ?>
                                    <i
                                        class="<?= e($item['icon']); ?> tw-text-neutral-400 group-hover:tw-text-neutral-600 tw-h-5 tw-w-5"></i>
                                    <?php } ?>
                                    <span>
                                        <?= e($item['name']); ?>
                                    </span>
                                </a>
                            </li>
                            <?php
                            } ?>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="mobile-menu tw-shrink-0 ltr:tw-ml-4 rtl:tw-mr-4">
                <button type="button"
                    class="navbar-toggle visible-md visible-sm visible-xs mobile-menu-toggle collapsed tw-ml-1.5 tw-text-neutral-600 hover:tw-text-neutral-800"
                    data-toggle="collapse" data-target="#mobile-collapse" aria-expanded="false">
                    <i class="fa fa-chevron-down fa-lg"></i>
                </button>
                <ul class="mobile-icon-menu tw-inline-flex tw-mt-5">
                    <?php
               // To prevent not loading the timers twice
            if (is_mobile()) { ?>
                    <li class="dropdown notifications-wrapper header-notifications tw-block ltr:tw-mr-3 rtl:tw-ml-3">
                        <?php $this->load->view('admin/includes/notifications'); ?>
                    </li>
                    <li class="header-timers ltr:tw-mr-1.5 rtl:tw-ml-1.5">
                        <a href="#" id="top-timers" class="dropdown-toggle top-timers tw-block tw-h-5 tw-w-5"
                            data-toggle="dropdown">
                            <i
                                class="fa-regular fa-clock fa-lg tw-text-neutral-500 group-hover:tw-text-neutral-800 tw-shrink-0<?= count($startedTimers) > 0 ? ' tw-animate-spin-slow' : ''; ?>"></i>
                            <span
                                class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-success tw-z-10 tw-absolute tw-rounded-full -tw-right-3 -tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center icon-started-timers<?= $totalTimers = count($startedTimers) == 0 ? ' hide' : ''; ?>"><?= count($startedTimers); ?></span>
                        </a>
                        <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">
                            <?php $this->load->view('admin/tasks/started_timers', ['startedTimers' => $startedTimers]); ?>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>
                <div class="mobile-navbar collapse" id="mobile-collapse" aria-expanded="false" style="height: 0px;"
                    role="navigation">
                    <ul class="nav navbar-nav">
                        <li class="header-my-profile"><a
                                href="<?= admin_url('profile'); ?>">
                                <?= _l('nav_my_profile'); ?>
                            </a>
                        </li>
                        <li class="header-my-timesheets"><a
                                href="<?= admin_url('staff/timesheets'); ?>">
                                <?= _l('my_timesheets'); ?>
                            </a>
                        </li>
                        <li class="header-edit-profile"><a
                                href="<?= admin_url('staff/edit_profile'); ?>">
                                <?= _l('nav_edit_profile'); ?>
                            </a>
                        </li>
                        <?php if (is_staff_member()) { ?>
                        <li class="header-newsfeed">
                            <a href="#" class="open_newsfeed mobile">
                                <?= _l('whats_on_your_mind'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="header-logout">
                            <a href="#" onclick="logout(); return false;">
                                <?= _l('nav_logout'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <ul class="nav navbar-nav navbar-right -tw-mt-px">
                <?php do_action_deprecated('after_render_top_search', [], '3.0.0', 'admin_navbar_start'); ?>
                <?php hooks()->do_action('admin_navbar_start'); ?>
                <?php
                $scClockJson = (string) get_option('sc_world_clocks');
                $scClocks = json_decode($scClockJson, true);
                if (!is_array($scClocks) || !$scClocks) {
                    $scClocks = [
                        ['label'=>'Florida (Eastern)','zone'=>'America/New_York'],
                        ['label'=>'India','zone'=>'Asia/Kolkata'],
                        ['label'=>'Pakistan','zone'=>'Asia/Karachi'],
                        ['label'=>'Philippines','zone'=>'Asia/Manila'],
                        ['label'=>'Bangladesh','zone'=>'Asia/Dhaka'],
                    ];
                }
                ?>
                <li class="dropdown sc-header-world-clock" title="<?= e(_l('sc_manage_world_clocks')); ?>" data-toggle="tooltip" data-placement="bottom">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-earth-americas fa-lg"></i></a>
                    <ul class="dropdown-menu dropdown-menu-right animated fadeIn width300">
                        <li class="dropdown-header">Smart Choice Team Time</li>
                        <?php foreach ($scClocks as $scClock) { if (empty($scClock['label']) || empty($scClock['zone'])) continue; ?>
                        <li><a href="#" onclick="return false;"><strong><?= e($scClock['label']); ?></strong><span class="pull-right sc-header-clock-value" data-zone="<?= e($scClock['zone']); ?>">--:--</span></a></li>
                        <?php } ?>
                        <li class="divider"></li>
                        <li><a href="#" id="sc-manage-world-clocks"><i class="fa fa-gear"></i> <?= e(_l('sc_manage_world_clocks')); ?></a></li>
                    </ul>
                </li>
                <script>(function(){function scHeaderClockUpdate(){document.querySelectorAll('.sc-header-clock-value').forEach(function(el){try{el.textContent=new Intl.DateTimeFormat(undefined,{timeZone:el.getAttribute('data-zone'),hour:'numeric',minute:'2-digit',hour12:true}).format(new Date());}catch(e){el.textContent='--:--';}});}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',scHeaderClockUpdate);}else{scHeaderClockUpdate();}window.setInterval(scHeaderClockUpdate,60000);})();</script>
                <?php if (staff_can('view', 'settings')) { ?>
                <li>
                    <a
                        href="<?= admin_url('settings'); ?>">
                        <?= _l('settings'); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if (is_staff_member()) { ?>
                <li class="icon header-newsfeed -tw-mr-1.5">
                    <a href="#" class="open_newsfeed desktop" data-toggle="tooltip"
                        title="<?= _l('whats_on_your_mind'); ?>"
                        data-placement="bottom">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor"
                            class="tw-w-[calc(theme(spacing.5)-1px)] tw-h-[calc(theme(spacing.5)-1px)]">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                        </svg>
                    </a>
                </li>
                <?php } ?>

                <li class="icon header-todo">
                    <a href="<?= admin_url('todo'); ?>"
                        data-toggle="tooltip"
                        title="<?= _l('nav_todo_items'); ?>"
                        data-placement="bottom" class="">
                        <i class="fa-regular fa-square-check fa-lg tw-shrink-0"></i>
                        <span
                            class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-warning tw-z-10 tw-absolute tw-rounded-full -tw-right-0.5 tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center nav-total-todos<?= $current_user->total_unfinished_todos == 0 ? ' hide' : ''; ?>">
                            <?= e($current_user->total_unfinished_todos); ?>
                        </span>
                    </a>
                </li>

                <li class="icon header-timers timer-button tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5"
                    data-placement="bottom" data-toggle="tooltip"
                    data-title="<?= _l('my_timesheets'); ?>">
                    <a href="#" id="top-timers" class="top-timers !tw-px-0 tw-group" data-toggle="dropdown">
                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-h-8 tw-w-9 -tw-mt-1.5">
                            <i
                                class="fa-regular fa-clock fa-lg tw-text-neutral-500 group-hover:tw-text-neutral-800 tw-shrink-0<?= count($startedTimers) > 0 ? ' tw-animate-spin-slow' : ''; ?>"></i>
                        </span>
                        <span
                            class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-success tw-z-10 tw-absolute tw-rounded-full -tw-right-1.5 tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center icon-started-timers<?= $totalTimers = count($startedTimers) == 0 ? ' hide' : ''; ?>">
                            <?= count($startedTimers); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">
                        <?php $this->load->view('admin/tasks/started_timers', ['startedTimers' => $startedTimers]); ?>
                    </ul>
                </li>

                <li class="icon dropdown tw-relative tw-block notifications-wrapper header-notifications rtl:tw-ml-3"
                    data-toggle="tooltip"
                    title="<?= _l('nav_notifications'); ?>"
                    data-placement="bottom">
                    <?php $this->load->view('admin/includes/notifications'); ?>
                </li>

                <?php hooks()->do_action('admin_navbar_end'); ?>
            </ul>
        </div>
    </nav>
</div>

<!-- Smart Choice enhancements retained after native Perfex header -->
<script>
window.SmartChoiceTinyMceNormalize = function(cfg){
    cfg = cfg || {};
    if (!cfg.language || cfg.language === '') { delete cfg.language; }
    function normalizeList(v){
        if (Array.isArray(v)) { return v.join(' '); }
        if (typeof v === 'string') { return v.replace(/,/g,' ').replace(/\s+/g,' ').trim(); }
        return v;
    }
    if (cfg.plugins) { cfg.plugins = normalizeList(cfg.plugins); }
    if (cfg.toolbar) { cfg.toolbar = normalizeList(cfg.toolbar); }
    return cfg;
};
(function(){
    var tries=0;
    var timer=setInterval(function(){
        tries++;
        if (window.tinymce && tinymce.init && !tinymce.SmartChoicePatched) {
            var original=tinymce.init;
            tinymce.init=function(cfg){ return original.call(this, window.SmartChoiceTinyMceNormalize(cfg)); };
            tinymce.SmartChoicePatched=true;
            clearInterval(timer);
        }
        if(tries>80){clearInterval(timer);}
    },50);
})();
</script>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style id="smart-choice-final-header-fixes">
#header .dropdown-menu>li>a{padding-top:5px!important;padding-bottom:5px!important;line-height:1.2!important;min-height:0!important}
#header .tw-text-neutral-500.tw-text-sm{font-size:10px!important;max-width:118px!important;width:118px!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}
#sc-header-datetime{display:flex;align-items:center;padding:0 10px;color:#fff;font-size:12px;white-space:nowrap}
@media(max-width:991px){#sc-header-datetime{display:none}#header #top_search{max-width:44vw}.table-responsive{overflow-x:auto!important;-webkit-overflow-scrolling:touch}.panel-body{padding-left:10px!important;padding-right:10px!important}}
</style>
<script>document.addEventListener('DOMContentLoaded',function(){var nav=document.querySelector('#header nav .tw-flex.tw-justify-between');if(!nav||document.getElementById('sc-header-datetime'))return;var d=document.createElement('div');d.id='sc-header-datetime';var tick=function(){d.textContent=new Intl.DateTimeFormat(undefined,{weekday:'short',month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'}).format(new Date());};tick();setInterval(tick,30000);var right=nav.lastElementChild;nav.insertBefore(d,right);});</script>

<?php $startedTimers = (isset($startedTimers) && is_array($startedTimers)) ? $startedTimers : []; ?>
<style id="smart-choice-core-ui-v345">
:root{--sc-green:#00A651;--sc-dark-green:#007A3D;--sc-orange:#F96302;--sc-yellow:#F5B400;--sc-blue:#0077CC;--sc-light-blue:#2CA8FF;--sc-light-gray:#F5F5F5;--sc-dark-gray:#555555;}
#header{background:linear-gradient(90deg,var(--sc-blue),var(--sc-light-blue),var(--sc-orange))!important;}
#header .hide-menu{margin-top:14px!important;color:#fff!important;}
#header #logo{height:64px!important;min-width:235px!important;display:flex!important;align-items:center!important;padding:2px 10px!important;overflow:visible!important;}#header #logo>a.logo{display:flex!important;align-items:center!important;height:100%!important;width:100%!important;overflow:visible!important;}#header #logo img{height:58px!important;max-height:58px!important;width:auto!important;max-width:220px!important;object-fit:contain!important;object-position:left center!important;margin:0!important;display:block!important;}
.dropdown-menu>li>a:hover,.dropdown-menu>li>a:focus{background:#eaf7ff!important;color:#0b4f7a!important;}
.alert-success{background:#e9fff2!important;border-color:var(--sc-green)!important;color:#065f2f!important;}
.alert-danger{background:#fff0eb!important;border-color:var(--sc-orange)!important;color:#8a2b00!important;}
.alert-warning{background:#fff8df!important;border-color:var(--sc-yellow)!important;color:#7a5a00!important;}
.table-staff{table-layout:fixed!important;width:100%!important;}
.table-staff th,.table-staff td{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;vertical-align:middle!important;}
.table-staff th:nth-child(1),.table-staff td:nth-child(1){width:34%!important;max-width:34%!important;overflow:visible!important;white-space:normal!important;}
.table-staff th:nth-child(2),.table-staff td:nth-child(2){width:24%!important;max-width:24%!important;}
.table-staff th:nth-child(3),.table-staff td:nth-child(3){width:15%!important;max-width:15%!important;}
.table-staff th:nth-child(4),.table-staff td:nth-child(4){width:17%!important;max-width:17%!important;}
.table-staff th:nth-child(5),.table-staff td:nth-child(5){width:10%!important;max-width:10%!important;}
.table-staff img.staff-profile-image-small,.table-staff img,img.staff-profile-image-small{width:32px!important;height:32px!important;min-width:32px!important;border-radius:50%!important;object-fit:cover!important;margin-right:7px!important;}
.table-staff .row-options{display:block!important;margin-top:4px!important;font-size:11px!important;line-height:1.3!important;white-space:normal!important;}
.table-staff .row-options a{display:inline-block!important;margin-right:5px!important;}
body .bootstrap-select .dropdown-menu.inner{max-height:320px!important;overflow-y:auto!important;overflow-x:hidden!important;}
body .bootstrap-select .dropdown-menu{overflow-x:hidden!important;}
#item_select + .dropdown-toggle{background:#fff!important;border-color:#d9e9f7!important;color:#333!important;}
#item_select + .dropdown-toggle .filter-option-inner-inner:empty:before{content:'Start typing or select an item';color:#555;}
.smart-choice-discount-note,.smart-choice-down-payment-help{font-size:12px;color:#555;margin-top:4px;}
.table.dataTable{width:100%!important;max-width:100%!important;}
.dataTables_empty{background:#fff!important;background-image:none!important;color:#6b7280!important;text-align:center!important;padding:24px!important;border:1px solid #e5e7eb!important;}
table.dataTable tbody tr.odd>.dataTables_empty,table.dataTable tbody tr.even>.dataTables_empty{box-shadow:none!important;}
.dataTables_scrollHead,.dataTables_scrollBody,.dataTables_scrollFoot{background:#fff!important;}
.table-responsive{background:#fff!important;}
body .dropdown-menu{scrollbar-width:thin!important;}


/* Smart Choice v1.0.2 header/sidebar correction */
html body #aside .sidebar-user-profile{margin-top:10px!important;padding-top:4px!important;}
html body #aside{padding-top:0!important;}
html body #side-menu{margin-top:0!important;}
html body #header #logo img{display:block!important;}
html body #header #logo a{overflow:hidden!important;}
</style>


<style id="sc-sidebar-profile-fix">
#aside .sidebar-user-profile .tw-w-\[140px\]{width:132px!important;max-width:132px!important}
#aside .sidebar-user-profile span.tw-text-xs,#aside .sidebar-user-profile span.tw-text-sm{font-size:10px!important;line-height:1.15!important;max-width:132px!important}
#setup-menu-wrapper .sc-setup-profile-card{margin-top:-43px!important;margin-bottom:4px!important}
#setup-menu-wrapper .sc-setup-profile-card a{padding:8px 10px!important}
#setup-menu-wrapper .sc-setup-profile-card .staff-profile-image-small{width:30px!important;height:30px!important}
#side-menu i.menu-icon,#setup-menu i.menu-icon{color:<?= e(get_option('sc_menu_icon_color') ?: '#169179'); ?>!important}
#side-menu .nav-second-level i.menu-icon,#setup-menu .nav-second-level i.menu-icon{color:<?= e(get_option('sc_submenu_icon_color') ?: '#374151'); ?>!important}
#header .dropdown-menu>li>a{padding-top:5px!important;padding-bottom:5px!important;line-height:1.25!important}
</style>
<script>
(function(){
  var ap=Node.prototype.appendChild, ib=Node.prototype.insertBefore;
  var highchartsSources={};
  Array.prototype.forEach.call(document.querySelectorAll('script[src]'),function(script){
    if(/highcharts(?:\.min)?\.js/i.test(script.src||'')){ highchartsSources[script.src]=true; }
  });
  function duplicateHighcharts(node){
    if(!node||node.tagName!=='SCRIPT'||!/highcharts(?:\.min)?\.js/i.test(node.src||'')){return false;}
    if(window.Highcharts||highchartsSources[node.src]){return true;}
    highchartsSources[node.src]=true; return false;
  }
  Node.prototype.appendChild=function(node){if(duplicateHighcharts(node)){return node;}return ap.call(this,node);};
  Node.prototype.insertBefore=function(node,ref){if(duplicateHighcharts(node)){return node;}return ib.call(this,node,ref);};
})();
</script>

<script src="<?= base_url('assets/js/smart-choice-core-upgrade.js?v=108'); ?>"></script>

<script id="sc-favorites-click-fix">
document.addEventListener('DOMContentLoaded',function(){
  var lastClick=0;
  /* Native Bootstrap/module dropdown handlers retained; no global capture toggle. */
  new MutationObserver(function(){if(typeof window.ensureIcons==='function'){window.ensureIcons(document);}}).observe(document.body,{childList:true,subtree:true});
});
</script>

<script>window.scIanaTimezones=<?= json_encode(timezone_identifiers_list()); ?>;</script>
