<!doctype html>
<html lang="en" dir="ltr">
    <head>
<?php
$CI = &get_instance();
$CI->load->model('zillapage/landingpage_model');
$settingsMap = method_exists($CI->landingpage_model, 'get_settings_map') ? $CI->landingpage_model->get_settings_map() : [];
$socialImage = !empty($page->social_image) ? base_url(ZILLAPAGE_IMAGE_PATH.'/uploads/'.$page->social_image) : base_url(ZILLAPAGE_ASSETS_PATH.'/images/thumb_templates/template-placeholder.png');
?>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title><?php echo html_escape($page->seo_title); ?></title>
        <meta name="description" content="<?php echo html_escape($page->seo_description); ?>">
        <meta name="keywords" content="<?php echo html_escape($page->seo_keywords); ?>">
        <!-- Apple Stuff -->
        <link rel="apple-touch-icon" href="<?php echo base_url(ZILLAPAGE_IMAGE_PATH.'/uploads/'. $page->favicon); ?>">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Title">
        <!-- Google / Search Engine Tags -->
        <meta itemprop="name" content="<?php echo html_escape($page->seo_title); ?>">
        <meta itemprop="description" content="<?php echo html_escape($page->seo_description); ?>">
        <meta itemprop="image" content="<?php echo html_escape($socialImage); ?>">

        <!-- Facebook Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?php echo html_escape($page->social_title); ?>">
        <meta property="og:description" content="<?php echo html_escape($page->social_description); ?>">
        <meta property="og:image" content="<?php echo html_escape($socialImage); ?>">
        <meta property="og:url" content="<?php echo base_url(uri_string()); ?>">
<?php if (!empty($page->social_video_url)): ?><meta property="og:video" content="<?php echo html_escape($page->social_video_url); ?>"><meta property="og:video:type" content="video/mp4"><?php endif; ?>
        
        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo html_escape($page->social_title); ?>">
        <meta name="twitter:description" content="<?php echo  $page->social_description; ?>">
        <meta name="twitter:image" content="<?php echo html_escape($socialImage); ?>">
        <link rel="icon" href="<?php echo base_url(ZILLAPAGE_IMAGE_PATH.'/uploads/'. $page->favicon); ?>" type="image/png">
        <!-- MS Tile - for Microsoft apps-->
        <meta name="msapplication-TileImage" content="<?php echo base_url(ZILLAPAGE_IMAGE_PATH.'/uploads/'. $page->favicon); ?>">

        <link rel="stylesheet" href="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/css/template.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/css/custom-publish.css'); ?>">
    <?php if (!empty($settingsMap['google_analytics_id'])): ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo html_escape($settingsMap['google_analytics_id']); ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?php echo html_escape($settingsMap['google_analytics_id']); ?>');</script><?php endif; ?>
<?php if (!empty($settingsMap['google_tag_manager_id'])): ?><script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f)})(window,document,'script','dataLayer','<?php echo html_escape($settingsMap['google_tag_manager_id']); ?>');</script><?php endif; ?>
</head>

    <body class="sc-thank-body">
<?php echo zillapage_fetch_library_section($settingsMap['header_library_url'] ?? ''); ?>
        
        <div id="loadingMessage">
          <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        </div>

        <script type="text/javascript">
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>',
            csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>',
            codePage = '<?php echo $page->code; ?>';
            window._loadPageLink = '<?php echo base_url("zillapage/getpagejson") ?>';
        </script>
        <script src="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/js/publish.js'); ?>"></script>
        <script src="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/js/thank-page.js'); ?>"></script>

    <style>.sc-thank-body{background:linear-gradient(135deg,#102d5c,#169179);min-height:100vh}.sc-thank-body #loadingMessage{color:#fff}</style><?php echo zillapage_fetch_library_section($settingsMap['footer_library_url'] ?? ''); ?>
</body>
</html>