<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Recruitment Management
Description: Smart Choice Contractors USA recruitment management module for job campaigns, candidates, interviews, applications, and a branded public recruitment portal. Includes Smart Choice applicant portal, staff notifications, sample hiring plan, campaign templates, and detailed help guide.
Version: 1.9.0
Requires at least: 2.3.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('RECRUITMENT_MODULE_NAME', 'recruitment');
define('RECRUITMENT_MODULE_UPLOAD_FOLDER', module_dir_path(RECRUITMENT_MODULE_NAME, 'uploads'));
define('RECRUITMENT_PATH', 'modules/recruitment/uploads/');
define('RECRUITMENT_COMPANY_UPLOAD', module_dir_path(RECRUITMENT_MODULE_NAME, 'uploads/company_images/'));
define('TEMFOLDER_EXPORT_CANDIDATE', module_dir_path(RECRUITMENT_MODULE_NAME, 'uploads/export_candidate/'));
define('CANDIDATE_IMAGE_UPLOAD', module_dir_path(RECRUITMENT_MODULE_NAME, 'uploads/candidate/avartar/'));
define('CANDIDATE_CV_UPLOAD', module_dir_path(RECRUITMENT_MODULE_NAME, 'uploads/candidate/files/'));
define('CANDIDATE_IMPORT_ERROR', 'modules/recruitment/uploads/import_candidates_error/');


hooks()->add_action('admin_init', 'recruitment_permissions');
hooks()->add_action('app_admin_head', 'recruitment_head_components');
hooks()->add_action('app_admin_footer', 'recruitment_add_footer_components');
hooks()->add_action('admin_init', 'recruitment_module_init_menu_items');
hooks()->add_action('admin_init', 'recruitment_register_native_settings');

// Smart Choice candidate-to-employee resume synchronization.
hooks()->add_action('after_staff_added', 'recruitment_sync_resume_after_staff_change');
hooks()->add_action('staff_member_created', 'recruitment_sync_resume_after_staff_change');
hooks()->add_action('after_staff_updated', 'recruitment_sync_resume_after_staff_change');

hooks()->add_action('app_customers_portal_head', 'recruitment_portal_add_head_components');
hooks()->add_action('app_customers_portal_footer', 'recruitment_portal_add_footer_components');
hooks()->add_action('forms_head', 'forms_add_head_components');
hooks()->add_action('forms_footer', 'forms_add_footer_components');

//recruitment add customfield
hooks()->add_action('after_custom_fields_select_options','init_recruitment_customfield');

/*add menu on client portal*/
hooks()->add_action('customers_navigation_after_profile', 'init_recruitment_portal_menu');

/*email template*/
register_merge_fields('recruitment/merge_fields/change_candidate_status_merge_fields');
register_merge_fields('recruitment/merge_fields/new_candidate_have_applied_merge_fields');
register_merge_fields('recruitment/merge_fields/change_candidate_job_applied_status_merge_fields');
register_merge_fields('recruitment/merge_fields/change_candidate_interview_schedule_status_merge_fields');
register_merge_fields('recruitment/merge_fields/send_interview_schedule_merge_fields');
register_merge_fields('recruitment/merge_fields/smart_choice_job_application_merge_fields');

hooks()->add_filter('other_merge_fields_available_for', 'new_candidate_have_applied_register_other_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'change_rec_candidate_status_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'change_candidate_job_applied_status_register_other_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'change_candidate_interview_schedule_status_register_other_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'send_interview_schedule_register_other_merge_fields');


define('RE_REVISION', 1900);

/**
 * Register activation module hook
 */
register_activation_hook(RECRUITMENT_MODULE_NAME, 'recruitment_module_activation_hook');
/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(RECRUITMENT_MODULE_NAME . '/recruitment');

//ReC portal UI
if(rec_get_status_modules('theme_style') == 1){
    hooks()->add_action('app_rec_portal_head', 'theme_style_rec_portal_area_head');
}

function recruitment_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(RECRUITMENT_MODULE_NAME, [RECRUITMENT_MODULE_NAME]);

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function recruitment_module_init_menu_items() {

	$CI = &get_instance();
	if (has_permission('recruitment', '', 'view')) {
		$CI->app_menu->add_sidebar_menu_item('recruitment', [
			'name' => _l('recruitment'),
			'icon' => 'fa fa-address-book',
			'position' => 30,
		]);
		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'recruitment_dashboard',
			'name' => _l('dashboard'),
			'icon' => 'fa fa-home',
			'href' => admin_url('recruitment/dashboard'),
			'position' => 1,
		]);

		if (get_recruitment_option('recruitment_create_campaign_with_plan') == 1) {
			$CI->app_menu->add_sidebar_children_item('recruitment', [
				'slug' => 'recruitment-proposal',
				'name' => _l('_proposal'),
				'icon' => 'fa fa-address-card',
				'href' => admin_url('recruitment/recruitment_proposal'),
				'position' => 2,
			]);
		}

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'recruitment-campaign',
			'name' => _l('campaign'),
			'icon' => 'fa fa-sitemap',
			'href' => admin_url('recruitment/recruitment_campaign'),
			'position' => 3,
		]);

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'candidate-profile',
			'name' => _l('candidate_profile'),
			'icon' => 'fa fa-user',
			'href' => admin_url('recruitment/candidate_profile'),
			'position' => 4,
		]);

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'interview-schedule',
			'name' => _l('interview_schedule'),
			'icon' => 'fa fa-calendar',
			'href' => admin_url('recruitment/interview_schedule'),
			'position' => 5,
		]);

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'recruitment-channel',
			'name' => _l('_recruitment_channel'),
			'icon' => 'fa fa-feed',
			'href' => admin_url('recruitment/recruitment_channel'),
			'position' => 6,
		]);

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'recruitment-portal',
			'name' => _l('recruitment_portal'),
			'icon' => 'fa fa-bars menu-icon',
			'href' => site_url('recruitment/recruitment_portal'),
			'target' => '_blank',
			'position' => 7,
		]);

        $CI->app_menu->add_sidebar_children_item('recruitment', [
            'slug' => 'recruitment_public_application',
            'name' => _l('recruitment_public_application'),
            'icon' => 'fa fa-file-text-o',
            'href' => site_url('recruitment/forms/application'),
            'target' => '_blank',
            'position' => 8,
        ]);

		$CI->app_menu->add_sidebar_children_item('recruitment', [
			'slug' => 'rec_help_guide',
			'name' => _l('recruitment_help_guide'),
			'icon' => 'fa fa-question-circle',
			'href' => admin_url('recruitment/help_guide'),
			'position' => 9,
		]);
	}

}


/** Register the module inside the native Setup -> Settings screen. */
function recruitment_register_native_settings()
{
    $CI = &get_instance();
    if (!is_admin() || !isset($CI->app)) {
        return;
    }

    $child = [
        'id'                    => 'recruitment_settings',
        'name'                  => _l('recruitment_settings_title'),
        'view'                  => 'recruitment/settings/native_settings',
        'icon'                  => 'fa fa-address-card fa-fw',
        'position'              => 10,
        'without_submit_button' => true,
    ];

    if (version_compare(get_app_version(), '3.2.0', '<')) {
        if (method_exists($CI->app, 'add_settings_section_child')) {
            $CI->app->add_settings_section_child('other', 'recruitment_settings', $child);
        }
        return;
    }

    if (method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('recruitment-settings', [
            'title'    => _l('recruitment'),
            'position' => 62,
            'children' => [$child],
        ]);
    }
}

/**
 * recruitment permissions
 * @return
 */
function recruitment_permissions() {
	$capabilities = [];
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view') . ' (' . _l('permission_own') . ')',
        'view' => _l('permission_view') . ' (' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('permission_edit'),
		'delete' => _l('permission_delete'),
	];
	register_staff_capabilities('recruitment', $capabilities, _l('recruitment'));
}

/**
 * add head components
 */
function recruitment_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, '/admin/recruitment') === false)) {
        echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/smart_choice_recruitment_v181.css') . '?v=' . RE_REVISION . '" rel="stylesheet" type="text/css" />';
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/styles.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/smart_choice_recruitment_portal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
        echo '<style>:root{--sc-primary:'.html_escape(get_option('recruitment_portal_primary_color') ?: '#F28C28').';--sc-secondary:'.html_escape(get_option('recruitment_portal_secondary_color') ?: '#169179').';--sc-dark:'.html_escape(get_option('recruitment_portal_dark_color') ?: '#0E6F5B').';--sc-page:'.html_escape(get_option('recruitment_portal_page_bg') ?: '#F5F7F6').';--sc-card:'.html_escape(get_option('recruitment_portal_card_bg') ?: '#FFFFFF').';--sc-text:'.html_escape(get_option('recruitment_portal_text_color') ?: '#263238').';--sc-muted:'.html_escape(get_option('recruitment_portal_muted_color') ?: '#607D76').';--sc-border:'.html_escape(get_option('recruitment_portal_border_color') ?: '#D9E2DF').';--sc-btn:'.html_escape(get_option('recruitment_portal_button_color') ?: '#F28C28').';--sc-btn-text:'.html_escape(get_option('recruitment_portal_button_text') ?: '#FFFFFF').';--sc-badge:'.html_escape(get_option('recruitment_portal_badge_bg') ?: '#EEF3F1').';--sc-badge-text:'.html_escape(get_option('recruitment_portal_badge_text') ?: '#0E6F5B').';}</style>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/dashboard') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/dashboard.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/candidates') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/candidate.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/candidate') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/candidate_detail.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/setting.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/interview_schedule') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/interview_schedule_preview.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_campaign') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/campaign_preview.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/candidate_profile') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/candidate_profile.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_proposal') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/recruitment_proposal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_campaign') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/recruitment_proposal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=company') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/company.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

	if (!(strpos($viewuri, '/admin/recruitment/recruitment_portal/job_detail') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/recruitment_proposal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

	if (!(strpos($viewuri, '/admin/recruitment/import_candidate') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/import_candidate.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

}

/**
 * add footer_components
 * @return
 */
function recruitment_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];

	if (!(strpos($viewuri, '/admin/recruitment') === false)) {
        echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/smart_choice_recruitment_v181.css') . '?v=' . RE_REVISION . '" rel="stylesheet" type="text/css" />';

	}

	if (!(strpos($viewuri, '/admin/recruitment/dashboard') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '"></script>';
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '"></script>';
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '"></script>';
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '"></script>';
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '"></script>';
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_proposal') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/proposal.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/candidates') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/candidate.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/candidate_profile') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/candidate_profile.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/transfer_to_hr') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/transferhr.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=evaluation_criteria') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/evaluation_criteria.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=evaluation_form') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/evaluation_form.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=job_position') === false) || !(strpos($viewuri, '/admin/recruitment/setting') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/job_position.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=tranfer_personnel') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/tranfer_personnel.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/interview_schedule') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/interview_schedule.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_campaign') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/campaign.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_campaign') === false)) {
	}
	if (!(strpos($viewuri, '/admin/recruitment/recruitment_channel') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/channel.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/calendar_interview_schedule') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/interview_schedule.js') . '?v=' . RE_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/recruitment/setting?group=skills') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/skill.js') . '?v=' . RE_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, '/admin/recruitment/setting?group=recruitment_campaign_setting') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/recruitment_campaign_setting.js') . '?v=' . RE_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, '/admin/recruitment/setting?group=industry_list') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/industry.js') . '?v=' . RE_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, '/recruitment_portal/job_detail') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/job_detail_portal.js') . '?v=' . RE_REVISION . '"></script>';
	}

}

/**
 * recruitment portal add head components
 *
 */
function recruitment_portal_add_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];

	if (!(strpos($viewuri, 'recruitment') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/recruitment_portal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/smart_choice_recruitment_portal.css') . '?v=' . RE_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

}

function recruitment_appint(){
    // Smart Choice Contractors build: license activation check removed.
    return true;
}
function recruitment_preactivate($module_name){
    // Smart Choice Contractors build: no external activation is required.
    return true;
}
function recruitment_predeactivate($module_name){
    return true;
}
function recruitment_uninstall($module_name){
    return true;
}

/**
 * recruitment portal add footer components
 *
 */
function recruitment_portal_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];

	if (!(strpos($viewuri, 'recruitment/recruitment_portal') === false)) {
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/recruitment_portal.js') . '?v=' . RE_REVISION . '"></script>';
	}

	if(!(strpos($viewuri,'recruitment/recruitment_portal') === false)){
		echo '<script src="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/js/recruitment_portals/main.js') . '?v=' . RE_REVISION . '"></script>';
		echo '<script src="' . base_url('assets/plugins/internal/desktop-notifications/notifications.js') . '?v=' . RE_REVISION . '"></script>';

	}

}

/**
 * forms add head components
 *
 */
function forms_add_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];

	if (!(strpos($viewuri, 'recruitment/forms') === false)) {
		echo '<link href="' . module_dir_url(RECRUITMENT_MODULE_NAME, 'assets/css/forms.css') . '"  rel="stylesheet" type="text/css" />';

	}

}

/**
 * forms add footer components
 *
 */
function forms_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];

}

/**
 * init recruitment customfield
 * @param  string $custom field 
 * @return [type]               
 */
function init_recruitment_customfield($custom_field = ''){
    $select = '';
    $recruitment_campaign_select = '';
    $recruitment_candidate_profile_select = '';
    $recruitment_interview_select = '';
    if($custom_field != ''){
        if($custom_field->fieldto == 'plan'){
            $select = 'selected';
        }
        if($custom_field->fieldto == 'campaign'){
            $recruitment_campaign_select = 'selected';
        }
        if($custom_field->fieldto == 'candidate'){
            $recruitment_candidate_profile_select = 'selected';
        }
        if($custom_field->fieldto == 'interview'){
            $recruitment_interview_select = 'selected';
        }
        
    }

    $html = '<option value="plan" '.$select.' >'. _l('recruitment_plan').'</option>';
    $html .= '<option value="campaign" '.$recruitment_campaign_select.' >'. _l('recruitment_campaign').'</option>';
    $html .= '<option value="candidate" '.$recruitment_candidate_profile_select.' >'. _l('rec_candidate_profile').'</option>';
    $html .= '<option value="interview" '.$recruitment_interview_select.' >'. _l('rec_interview_schedule').'</option>';

    echo new_html_entity_decode($html);
}

/**
 * init recruitment portal menu
 * @return [type] 
 */
function init_recruitment_portal_menu()
{
	$CI = &get_instance();
	$item ='';
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'recruitment/recruitment_portal') === false) || !(strpos($viewuri, 'recruitment/authentication_candidate') === false) ) {
		$item .= '<li class="customers-nav-item">';
		$item .= '<a href="'.site_url('recruitment/recruitment_portal').'">'._l("recruitment_portal").'';        
		$item .= '</a>';
		$item .= '</li>';

		if(!is_candidate_logged_in() && !(strpos($viewuri, 'recruitment/authentication_candidate/login') != false)){
			$item .= '<li class="customers-nav-item-login">';
			$item .= '<a href="'.site_url('recruitment/authentication_candidate/login').'">'._l("login").'';        
			$item .= '</a>';
			$item .= '</li>';
		}elseif(is_candidate_logged_in()){
			$data = [];
			$CI->load->model('recruitment/recruitment_model');

			$currentCandidate = $CI->recruitment_model->get_candidate_v1(get_candidate_id());
			$GLOBALS['current_candidate'] = $currentCandidate;
			$data['current_candidate'] = $currentCandidate;



			$item .= '<li class="dropdown customers-nav-item-profile">';
			$item .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
			'.candidate_profile_image(get_candidate_id(),[
                    'staff-profile-image-small mright5',
                    ], 'small', ['data-toggle' => 'tooltip', 'data-title' => get_candidate_name(get_candidate_id()), 'data-placement' => 'bottom' ]).'			
			<span class="caret"></span>
			</a>
			<ul class="dropdown-menu animated fadeIn">
			<li class="customers-nav-item-edit-profile">
			<a href="'.site_url('recruitment/recruitment_portal/profile').'">
			'. _l('clients_nav_profile').'
			</a>
			</li>';
			if (is_gdpr() && get_option('show_gdpr_in_customers_menu') == '1'){

			$item .= '<li class="customers-nav-item-logout">
			<a href="'. site_url('recruitment/recruitment_portal/gdpr').'">
			'. _l('gdpr_short').'
			</a>
			</li>';
			}


			$item .= '<li class="customers-nav-item-logout">
			<a href="'. site_url('recruitment/recruitment_portal/applied_jobs').'">
			'. _l('re_applied_jobs').'
			</a>
			</li>';

			$item .= '<li class="customers-nav-item-logout">
			<a href="'. site_url('recruitment/recruitment_portal/interview_schedules').'">
			'. _l('rec_interview_schedules').'
			</a>
			</li>';

			$item .= '<li class="customers-nav-item-logout">
			<a href="'. site_url('recruitment/authentication_candidate/logout').'">
			'. _l('clients_nav_logout').'
			</a>
			</li>';

			$item .= '</ul>
			</li>';

			$item .= $CI->load->view('recruitment_portal/rec_portal/notifications', $data);
		}
	}
	echo new_html_entity_decode($item);

}

/**
 * change candidate status register other merge fields
 * @param  [type] $for 
 * @return [type]      
 */
function change_rec_candidate_status_merge_fields($for) {
	$for[] = 'change_candidate_status';

	return $for;
}

/**
 * Init inventory email templates and assign languages
 * @return void
 */
function add_change_candidate_status_email_templates()
{
	$CI = &get_instance();
	$data['change_candidate_status_templates'] = $CI->emails_model->get(['type' => 'change_candidate_status', 'language' => 'english']);
	$data['change_candidate_job_applied_status_templates'] = $CI->emails_model->get(['type' => 'change_candidate_job_applied_status', 'language' => 'english']);
	$data['change_candidate_interview_schedule_status_templates'] = $CI->emails_model->get(['type' => 'change_candidate_interview_schedule_status', 'language' => 'english']);
	$data['new_candidate_have_applied_templates'] = $CI->emails_model->get(['type' => 'new_candidate_have_applied', 'language' => 'english']);
	$data['send_interview_schedule_templates'] = $CI->emails_model->get(['type' => 'send_interview_schedule', 'language' => 'english']);

	$CI->load->view('recruitment/email_templates/change_candidate_status_email_template', $data);
}

/**
 * change candidate status register other merge fields
 * @param  [type] $for 
 * @return [type]      
 */
function change_candidate_job_applied_status_register_other_merge_fields($for) {
	$for[] = 'change_candidate_job_applied_status';

	return $for;
}

/**
 * change candidate status register other merge fields
 * @param  [type] $for 
 * @return [type]      
 */
function change_candidate_interview_schedule_status_register_other_merge_fields($for) {
	$for[] = 'change_candidate_interview_schedule_status';

	return $for;
}

function new_candidate_have_applied_register_other_merge_fields($for) {
	$for[] = 'new_candidate_have_applied';

	return $for;
}

/**
 * change candidate status register other merge fields
 * @param  [type] $for 
 * @return [type]      
 */
function send_interview_schedule_register_other_merge_fields($for) {
	$for[] = 'send_interview_schedule';

	return $for;
}

if(rec_get_status_modules('theme_style') == 1){
    /**
     * Clients area theme applied styles
     * @return null
     */
    function theme_style_rec_portal_area_head()
    {   
        theme_style_render(['general', 'tabs', 'buttons', 'customers', 'modals']);
        theme_style_custom_css_rec('theme_style_custom_clients_area');
    }

    /**
     * Custom CSS
     * @param  string $main_area clients or admin area options
     * @return null
     */
    function theme_style_custom_css_rec($main_area)
    {
        $clients_or_admin_area             = get_option($main_area);
        $custom_css_admin_and_clients_area = get_option('theme_style_custom_clients_and_admin_area');
        if (!empty($clients_or_admin_area) || !empty($custom_css_admin_and_clients_area)) {
            echo '<style id="theme_style_custom_css">' . PHP_EOL;
            if (!empty($clients_or_admin_area)) {
                $clients_or_admin_area = clear_textarea_breaks($clients_or_admin_area);
                echo new_html_entity_decode($clients_or_admin_area) . PHP_EOL;
            }
            if (!empty($custom_css_admin_and_clients_area)) {
                $custom_css_admin_and_clients_area = clear_textarea_breaks($custom_css_admin_and_clients_area);
                echo new_html_entity_decode($custom_css_admin_and_clients_area) . PHP_EOL;
                echo new_html_entity_decode($custom_css_admin_and_clients_area) . PHP_EOL;
            }
            echo '</style>' . PHP_EOL;
        }
    }
}

hooks()->add_action('app_admin_head', 'recruitment_smart_choice_normalize_assets');
function recruitment_smart_choice_normalize_assets()
{
    echo '<link href="' . module_dir_url('recruitment', 'assets/css/smart_choice_module_normalize.css') . '?v=103" rel="stylesheet" type="text/css" />';
}


/**
 * Match a newly created or updated employee to a recruitment candidate and
 * copy the candidate resume into the employee document area.
 */
function recruitment_sync_resume_after_staff_change($staff_id = 0)
{
    if (is_array($staff_id)) {
        $staff_id = isset($staff_id['staffid']) ? (int) $staff_id['staffid'] : 0;
    }
    if (is_object($staff_id)) {
        $staff_id = isset($staff_id->staffid) ? (int) $staff_id->staffid : 0;
    }
    $staff_id = (int) $staff_id;
    if ($staff_id <= 0) {
        return false;
    }
    if (!function_exists('recruitment_sync_candidate_resume_to_staff')) {
        return false;
    }
    return recruitment_sync_candidate_resume_to_staff(0, $staff_id);
}
