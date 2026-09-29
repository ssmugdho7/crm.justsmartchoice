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

    <body class="">
<?php echo zillapage_fetch_library_section($settingsMap['header_library_url'] ?? ''); ?>
        
        <?php if (!empty($page->social_overlay_text) || !empty($page->social_video_url) || !empty($page->carousel_images)): ?>
<section class="sc-social-hero"><div class="sc-social-media">
<?php if (($page->social_media_type??'image')==='video' && !empty($page->social_video_url)): ?><iframe src="<?php echo html_escape($page->social_video_url); ?>" allow="autoplay; encrypted-media" allowfullscreen></iframe>
<?php elseif (($page->social_media_type??'image')==='carousel' && !empty($page->carousel_images)): $imgs=preg_split('/[\r\n,]+/',$page->carousel_images); ?><div class="sc-carousel"><?php foreach(array_slice(array_filter($imgs),0,3) as $i=>$img): ?><img src="<?php echo html_escape(trim($img)); ?>" class="<?php echo $i===0?'active':''; ?>"><?php endforeach; ?></div>
<?php else: ?><img src="<?php echo html_escape($socialImage); ?>" alt="<?php echo html_escape($page->social_title); ?>"><?php endif; ?>
<?php if(!empty($page->social_overlay_text)): ?><div class="sc-social-overlay"><h1><?php echo nl2br(html_escape($page->social_overlay_text)); ?></h1></div><?php endif; ?></div>
<?php if(!empty($page->appointment_url)): ?><div class="sc-appointment"><a href="<?php echo html_escape($page->appointment_url); ?>" class="sc-appointment-btn"><?php echo html_escape($page->appointment_button_text ?: _l('zillapage_make_appointment')); ?></a></div><?php endif; ?></section>
<?php endif; ?>
<div id="loadingMessage">
          <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        </div>

        <script>
            window._formLink = '<?php echo base_url("zillapage/formsubmission"); ?>';
            window._loadPageLink = '<?php echo base_url("zillapage/getpagejson") ?>';
            window._thankYouURL = '<?php echo zillapageGetThankPageURL($page) ?>';
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>',
      		csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>',
            codePage = '<?php echo $page->code; ?>';
            var blockscss = `<?php echo base_url('zillapage/getblockscss'); ?>`;
        </script>
       
        <script src="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/js/publish.js'); ?>"></script>
        <script src="<?php echo base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/js/main-page.js'); ?>"></script>

    <style>.sc-social-hero{position:relative;background:#f8fafc}.sc-social-media{position:relative;max-height:620px;overflow:hidden}.sc-social-media img,.sc-social-media iframe{width:100%;height:min(62vw,620px);object-fit:cover;border:0}.sc-social-overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:30px;background:linear-gradient(90deg,rgba(16,45,92,.72),rgba(22,145,121,.28));color:#fff;text-align:center}.sc-social-overlay h1{max-width:980px;font-size:clamp(30px,5vw,68px);text-shadow:0 3px 16px rgba(0,0,0,.45)}.sc-appointment{text-align:center;padding:22px}.sc-appointment-btn{display:inline-block;background:#169179;color:#fff;padding:14px 30px;border-radius:9px;font-weight:700;text-decoration:none}.sc-carousel{position:relative}.sc-carousel img{display:none}.sc-carousel img.active{display:block}@media(max-width:767px){.sc-social-media img,.sc-social-media iframe{height:64vw;min-height:280px}}</style><script>(function(){var imgs=document.querySelectorAll('.sc-carousel img');if(imgs.length>1){var i=0;setInterval(function(){imgs[i].classList.remove('active');i=(i+1)%imgs.length;imgs[i].classList.add('active')},4000)}})();</script>
<?php echo zillapage_fetch_library_section($settingsMap['footer_library_url'] ?? ''); ?>
</body>
</html>