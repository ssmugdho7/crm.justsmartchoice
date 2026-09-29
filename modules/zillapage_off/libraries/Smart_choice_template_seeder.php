<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('zillapage_smart_choice_seed_templates')) {
    function zillapage_smart_choice_seed_templates($force = false)
    {
        $CI = &get_instance();
        $table = db_prefix() . 'landing_page_templates';
        if (!$CI->db->table_exists($table)) { return ['success'=>false,'message'=>'Template table does not exist yet.']; }
        $jsonFile = module_dir_path('zillapage') . 'assets/smart_choice_templates.json';
        if (!file_exists($jsonFile)) { return ['success'=>false,'message'=>'Template JSON file missing.']; }
        $templates = json_decode(file_get_contents($jsonFile), true);
        if (!is_array($templates)) { return ['success'=>false,'message'=>'Template JSON could not be read.']; }
        $now = date('Y-m-d H:i:s'); $inserted = 0; $updated = 0;
        foreach ($templates as $tpl) {
            if (empty($tpl['name'])) { continue; }
            $CI->db->where('name', $tpl['name']); $existing = $CI->db->get($table)->row();
            $data = ['name'=>$tpl['name'],'thumb'=>isset($tpl['thumb'])?$tpl['thumb']:'','thank_you_page'=>isset($tpl['thank_you_page'])?$tpl['thank_you_page']:'','content'=>isset($tpl['content'])?$tpl['content']:'','style'=>isset($tpl['style'])?$tpl['style']:'','active'=>1,'updated_at'=>$now];
            if ($existing) {
                if ($force) { $CI->db->where('id',$existing->id); $CI->db->update($table,$data); $updated++; }
                continue;
            }
            $data['created_at']=$now; $CI->db->insert($table,$data); $inserted++;
        }
        update_option('zillapage_smart_choice_templates_seeded',$now);
        update_option('zillapage_smart_choice_templates_count',(string)count($templates));
        return ['success'=>true,'inserted'=>$inserted,'updated'=>$updated,'total'=>count($templates)];
    }
}

if (!function_exists('zillapage_smart_choice_ensure_settings')) {
    function zillapage_smart_choice_ensure_settings()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'landing_page_settings';
        if (!$CI->db->table_exists($table)) { return; }
        $settings = [
            'google_analytics_id'=>'G-CLX99EJXW2',
            'google_tag_manager_id'=>'',
            'bing_tracking_id'=>'',
            'facebook_pixel_id'=>'',
            'ahrefs_analytics_key'=>'9gWoEoaoQSTW3cXLEcA/Qg',
            'landing_media_url'=>'https://justsmartchoice.com/images/landing-pages/',
            'shared_header_html'=>'<!-- START: GLOBAL SITE HEADER -->\n<section class="banner site-header-wrapper" itemscope itemtype="https://schema.org/WPHeader">\n  <a class="section section-banner d-none d-lg-block" href="https://justsmartchoice.com/promo_flooring.php" aria-label="Visit Smart Choice Contractors USA" style="background-image:url(\'https://justsmartchoice.com/images/banner/banner-bg-04-1920x60.jpg\');">\n    <img src="https://justsmartchoice.com/images/banner/banner-fg-04-1600x60.png" alt="Smart Choice Contractors USA - Tampa Bay Construction and Remodeling Services" width="1600" height="60" loading="eager" decoding="async">\n  </a>\n  <header class="section page-header" role="banner">\n    <div class="rd-navbar-wrap">\n      <nav class="rd-navbar" role="navigation" aria-label="Main navigation" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-static" data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-md-stick-up-offset="185px" data-lg-stick-up-offset="185px" data-xl-stick-up-offset="185px" data-xxl-stick-up-offset="185px" data-md-stick-up="true" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">\n        <div class="rd-navbar-section rd-navbar-static-collapse"><div class="rd-navbar-container"><div class="rd-navbar-panel">\n          <button class="rd-navbar-toggle" type="button" data-rd-navbar-toggle=".rd-navbar-nav" aria-label="Open main menu"><span></span></button>\n          <div class="rd-navbar-logo" itemscope itemtype="https://schema.org/Organization"><a href="https://justsmartchoice.com/index.php" itemprop="url" aria-label="Smart Choice Contractors USA Home"><img class="logo-small" src="https://justsmartchoice.com/images/logo-364x52.png" alt="Smart Choice Contractors USA" width="182" height="26" itemprop="logo"><img class="logo-default" src="https://justsmartchoice.com/images/logo-638x310.png" alt="Smart Choice Contractors USA Logo" width="319" height="155"></a></div>\n          <button class="rd-navbar-info-toggle" type="button" data-rd-navbar-toggle=".rd-navbar-info" aria-label="Open contact information"><span></span></button>\n        </div><div class="rd-navbar-info"><div class="info" itemscope itemtype="https://schema.org/LocalBusiness"><meta itemprop="name" content="Smart Choice Contractors USA"><meta itemprop="url" content="https://justsmartchoice.com/"><div class="info-heading">Need Help?</div><a class="info-link" href="tel:+17277553786" itemprop="telephone"><span class="info-icon fa-mobile" aria-hidden="true"></span>(727) 755-3786</a><div class="info-small hours-line"><strong>Work Hours:</strong> Mon-Fri 8:00 AM - 4:00 PM | Sat 8:00 AM - 3:00 PM</div><a class="appointment-button" href="https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2" target="_blank" rel="noopener">Appointment</a></div></div></div></div>\n        <div class="rd-navbar-section sc-main-nav-section"><div class="rd-navbar-container"><ul class="rd-navbar-nav" itemscope itemtype="https://schema.org/SiteNavigationElement">\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/index.php" itemprop="url"><span itemprop="name">Home</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/about.php" itemprop="url"><span itemprop="name">About</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/services.php" itemprop="url"><span itemprop="name">Services</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/pricing.php" itemprop="url"><span itemprop="name">Pricing</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/financing.php" itemprop="url"><span itemprop="name">Financing</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/contacts.php" itemprop="url"><span itemprop="name">Contact Us</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://justsmartchoice.com/blog.php" itemprop="url"><span itemprop="name">Blog</span></a></li>\n          <li class="rd-nav-item"><a class="rd-nav-link" href="https://crm.justsmartchoice.com/clients/login" itemprop="url"><span itemprop="name">Client Portal</span></a></li>\n        </ul></div></div>\n      </nav>\n    </div>\n  </header>\n</section>\n<!-- END: GLOBAL SITE HEADER -->',
            'shared_footer_html'=>'<!-- START: Footer Section -->\n<section class="footer-section">\n  <footer class="footer section-xs" role="contentinfo">\n    <div class="container">\n      <div class="row">\n        <div class="col-md-12 text-center">\n\n          <p class="footer-cookie-link">\n            <a href="#" id="open-cookie-preferences" rel="nofollow">Cookie Preferences</a>\n          </p>\n\n          <div class="footer-social-icons" aria-label="Social media links">\n            <a href="https://www.facebook.com/smartchoicsolar" target="_blank" rel="noopener" aria-label="Facebook">\n              <img src="https://justsmartchoice.com/images/social/facebook.png" alt="Facebook" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://x.com/justsmartchoice" target="_blank" rel="noopener" aria-label="Twitter / X">\n              <img src="https://justsmartchoice.com/images/social/x.png" alt="Twitter / X" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.instagram.com/justsmartchoice/" target="_blank" rel="noopener" aria-label="Instagram">\n              <img src="https://justsmartchoice.com/images/social/instagram.png" alt="Instagram" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.linkedin.com/company/justsmartchoice" target="_blank" rel="noopener" aria-label="LinkedIn">\n              <img src="https://justsmartchoice.com/images/social/linkedin.png" alt="LinkedIn" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.tiktok.com/@justsmartchoice" target="_blank" rel="noopener" aria-label="TikTok">\n              <img src="https://justsmartchoice.com/images/social/tiktok.png" alt="TikTok" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.pinterest.com/justsmartchoice" target="_blank" rel="noopener" aria-label="Pinterest">\n              <img src="https://justsmartchoice.com/images/social/pinterest.png" alt="Pinterest" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.youtube.com/@Justsmartchoice" target="_blank" rel="noopener" aria-label="YouTube">\n              <img src="https://justsmartchoice.com/images/social/youtube.png" alt="YouTube" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://wa.me/181686896239" target="_blank" rel="noopener" aria-label="WhatsApp">\n              <img src="https://justsmartchoice.com/images/social/whatsapp.png" alt="WhatsApp" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://www.trustpilot.com/review/justsmartchoice.com" target="_blank" rel="noopener" aria-label="Trustpilot Reviews">\n              <img src="https://justsmartchoice.com/images/social/trustpilot.png" alt="Trustpilot Reviews" width="35" height="35" loading="lazy">\n            </a>\n            <a href="https://g.page/r/CSGK2pvNH07nEAI/review" target="_blank" rel="noopener" aria-label="Google Reviews">\n              <img src="https://justsmartchoice.com/images/social/google-reviews.png" alt="Google Reviews" width="35" height="35" loading="lazy">\n            </a>\n          </div>\n\n          <p class="rights footer-rights">\n            Smart Choice Contractors USA &copy; 2026. All Rights Reserved.<br>\n            Design by <a href="https://www.justsmartchoice.com/">Smart Choice USA</a>\n          </p>\n\n          <div class="footer-licenses">\n            <strong>CFC1433090</strong> Certified Plumbing Contractor &nbsp;|&nbsp;\n            <strong>CGC1505749</strong> Certified General Contractor &nbsp;|&nbsp;\n            <strong>EC13013330</strong> Certified Electrical Contractor &nbsp;|&nbsp;\n            <strong>CCC1332199</strong> Certified Roofing Contractor\n          </div>\n\n          <div class="footer-review-blocks">\n            <div class="footer-review-box">\n              <a href="https://www.trustpilot.com/review/justsmartchoice.com?utm_medium=trustbox&utm_source=TrustBoxReviewCollector" target="_blank" rel="noopener">\n                <img src="https://justsmartchoice.com/images/social/trustpilot.png" alt="Trustpilot" width="35" height="35" loading="lazy">\n                <span>\n                  <strong>Leave us a review on</strong>\n                  <small>Trustpilot</small>\n                </span>\n              </a>\n            </div>\n\n            <div class="footer-review-box">\n              <a href="https://g.page/r/CSGK2pvNH07nEAI/review" target="_blank" rel="noopener">\n                <img src="https://justsmartchoice.com/images/social/google-reviews.png" alt="Google Reviews" width="35" height="35" loading="lazy">\n                <span>\n                  <strong>Leave us a review on</strong>\n                  <small>Google</small>\n                </span>\n              </a>\n            </div>\n\n            <div class="footer-review-box">\n              <a href="https://www.facebook.com/profile.php?id=100079490918155&sk=reviews" target="_blank" rel="noopener">\n                <img src="https://justsmartchoice.com/images/social/facebook.png" alt="Facebook Reviews" width="35" height="35" loading="lazy">\n                <span>\n                  <strong>Leave us a review on</strong>\n                  <small>Facebook</small>\n                </span>\n              </a>\n            </div>\n          </div>\n\n          <div class="row footer-links">\n            <div class="col-md-4">\n              <h5>Company</h5>\n              <ul>\n                <li><a href="https://justsmartchoice.com/about.php">About Us</a></li>\n                <li><a href="https://justsmartchoice.com/services.php">Services</a></li>\n                <li><a href="https://justsmartchoice.com/pricing.php">Pricing</a></li>\n                <li><a href="https://justsmartchoice.com/financing.php">Financing</a></li>\n                <li><a href="https://justsmartchoice.com/contacts.php">Contact Us</a></li>\n                <li><a href="https://justsmartchoice.com/login.php">Login</a></li>\n              </ul>\n            </div>\n\n            <div class="col-md-4">\n              <h5>Resources</h5>\n              <ul>\n                <li><a href="https://justsmartchoice.com/contracts.php">Contracts</a></li>\n                <li><a href="https://justsmartchoice.com/forms.php">Forms</a></li>\n                <li><a href="https://justsmartchoice.com/faqs.php">FAQs</a></li>\n                <li><a href="https://justsmartchoice.com/toolbox.php">Toolbox</a></li>\n                <li><a href="https://justsmartchoice.com/career.php">Career</a></li>\n                <li><a href="https://justsmartchoice.com/partners.php">Partners</a></li>\n                <li><a href="https://justsmartchoice.com/investors.php">Investors</a></li>\n              </ul>\n            </div>\n\n            <div class="col-md-4">\n              <h5>Legal & Policies</h5>\n              <ul>\n                <li><a href="https://justsmartchoice.com/terms.php">Terms of Service</a></li>\n                <li><a href="https://justsmartchoice.com/privacy.php">Privacy Policy</a></li>\n                <li><a href="https://justsmartchoice.com/cookie-policy.php">Cookies Policy</a></li>\n                <li><a href="https://justsmartchoice.com/legal.php">Legal</a></li>\n                <li><a href="https://justsmartchoice.com/sitemap.php">SiteMap</a></li>\n              </ul>\n            </div>\n          </div>\n\n        </div>\n      </div>\n    </div>\n  </footer>\n\n  <div class="form-output snackbars" id="form-output-global"></div>\n\n  <script src="https://justsmartchoice.com/js/core.min.js"></script>\n  <script src="https://justsmartchoice.com/js/script.js"></script>\n  <script src="https://justsmartchoice.com/js/smartchoice-mobile-fix.js?v=20260630"></script>\n<div id="smartchoice-ai-chatbot" data-chatbot="/ai-chatbot.php"></div>\n</section>\n<!-- END: Footer Section -->',
            'default_phone'=>'727-755-3786',
            'default_email'=>'admin@justsmartchoice.com',
            'client_portal_url'=>'https://crm.justsmartchoice.com/clients/login',
            'recruitment_portal_url'=>'https://crm.justsmartchoice.com/recruitment/recruitment_portal',
        ];
        foreach ($settings as $key=>$value) {
            $CI->db->where('key',$key); $exists=$CI->db->get($table)->row();
            if (!$exists) { $CI->db->insert($table,['key'=>$key,'value'=>$value]); } else { if (in_array($key, ['landing_media_url','shared_header_html','shared_footer_html','default_phone','default_email','client_portal_url','recruitment_portal_url'])) { $CI->db->where('id', $exists->id); $CI->db->update($table, ['value'=>$value]); } }
        }
    }
}


if (!function_exists('zillapage_smart_choice_normalize_all_templates')) {
    function zillapage_smart_choice_normalize_all_templates()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'landing_page_templates';
        if (!$CI->db->table_exists($table)) { return ['success'=>false,'message'=>'Template table missing']; }
        $settingsTable = db_prefix() . 'landing_page_settings';
        $header = '';
        $footer = '';
        if ($CI->db->table_exists($settingsTable)) {
            $CI->db->where('key','shared_header_html'); $row = $CI->db->get($settingsTable)->row(); if ($row) { $header = $row->value; }
            $CI->db->where('key','shared_footer_html'); $row = $CI->db->get($settingsTable)->row(); if ($row) { $footer = $row->value; }
        }
        $start = '<!-- START: SMART CHOICE WEBSITE HEADER -->';
        $end = '<!-- END: SMART CHOICE WEBSITE HEADER -->';
        $fstart = '<!-- START: SMART CHOICE WEBSITE FOOTER -->';
        $fend = '<!-- END: SMART CHOICE WEBSITE FOOTER -->';
        $headerBlock = $start . "\n" . $header . "\n" . $end . "\n";
        $footerBlock = "\n" . $fstart . "\n" . $footer . "\n" . $fend;
        $rows = $CI->db->get($table)->result();
        $updated = 0;
        foreach ($rows as $row) {
            $content = (string)$row->content;
            $content = preg_replace('/<!-- START: SMART CHOICE WEBSITE HEADER -->.*?<!-- END: SMART CHOICE WEBSITE HEADER -->/s', '', $content);
            $content = preg_replace('/<!-- START: SMART CHOICE WEBSITE FOOTER -->.*?<!-- END: SMART CHOICE WEBSITE FOOTER -->/s', '', $content);
            $content = preg_replace('/<footer\b.*?<\/footer>/is', '', $content);
            if (strpos($content, 'sc-landing-page') === false) {
                $content = '<main class="sc-landing-page sc-legacy-upgraded"><section class="sc-section"><div class="sc-container">' . $content . '</div></section></main>';
            }
            $newContent = $headerBlock . $content . $footerBlock;
            $style = (string)$row->style;
            if (strpos($style, 'Smart Choice v1.1.6 global landing template repair') === false) {
                $style .= "\n/* Smart Choice v1.1.6 global landing template repair */\n.sc-landing-page h1,.sc-hero h1{font-size:clamp(42px,6vw,74px)!important;line-height:1.02!important;font-weight:900!important}.sc-phone-large,.sc-phone-large a{font-size:clamp(28px,4vw,48px)!important;font-weight:900!important;color:#f47c20!important}.sc-landing-page img{max-width:100%;height:auto;object-fit:cover}.sc-photo-card img{width:100%;aspect-ratio:16/10;border-radius:18px;box-shadow:0 18px 44px rgba(0,0,0,.22)}\n";
            }
            $CI->db->where('id', $row->id);
            $CI->db->update($table, ['content'=>$newContent, 'style'=>$style, 'updated_at'=>date('Y-m-d H:i:s')]);
            $updated++;
        }
        return ['success'=>true,'updated'=>$updated];
    }
}
