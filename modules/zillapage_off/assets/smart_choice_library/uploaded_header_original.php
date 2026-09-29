<?php
$current_uri = strtolower(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$current_page = strtolower(basename($current_uri));
if (!function_exists('sc_nav_active')) {
    function sc_nav_active($targets) {
        global $current_uri, $current_page;
        foreach ((array) $targets as $target) {
            $target = strtolower((string) $target);
            if ($target === 'home' && ($current_uri === '/' || $current_page === 'index.php')) { return 'active'; }
            if ($current_page === $target || strpos($current_uri, '/' . $target) !== false) { return 'active'; }
        }
        return '';
    }
}
$nav_items = [
    ['href'=>'/index.php','label'=>'Home','targets'=>['home','index.php']],
    ['href'=>'/about.php','label'=>'About','targets'=>['about.php']],
    ['href'=>'/services.php','label'=>'Services','targets'=>['services.php','services']],
    ['href'=>'/pricing.php','label'=>'Pricing','targets'=>['pricing.php']],
    ['href'=>'/financing.php','label'=>'Financing','targets'=>['financing.php']],
    ['href'=>'/contacts.php','label'=>'Contact Us','targets'=>['contacts.php']],
    ['href'=>'/blog.php','label'=>'Blog','targets'=>['blog.php','blog']],
    ['href'=>'/login.php','label'=>'Login','targets'=>['login.php']],
];
?>
<!-- START: GLOBAL SITE HEADER -->
<section class="banner site-header-wrapper" itemscope itemtype="https://schema.org/WPHeader">
  <a class="section section-banner d-none d-lg-block" href="/promo_flooring.php" aria-label="Visit Smart Choice Contractors USA" style="background-image:url('/images/banner/banner-bg-04-1920x60.jpg');">
    <img src="/images/banner/banner-fg-04-1600x60.png" alt="Smart Choice Contractors USA - Tampa Bay Construction and Remodeling Services" width="1600" height="60" loading="eager" decoding="async">
  </a>
  <header class="section page-header" role="banner">
    <div class="rd-navbar-wrap">
      <nav class="rd-navbar" role="navigation" aria-label="Main navigation" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-static" data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-md-stick-up-offset="185px" data-lg-stick-up-offset="185px" data-xl-stick-up-offset="185px" data-xxl-stick-up-offset="185px" data-md-stick-up="true" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
        <div class="rd-navbar-section rd-navbar-static-collapse"><div class="rd-navbar-container"><div class="rd-navbar-panel">
          <button class="rd-navbar-toggle" type="button" data-rd-navbar-toggle=".rd-navbar-nav" aria-label="Open main menu"><span></span></button>
          <div class="rd-navbar-logo" itemscope itemtype="https://schema.org/Organization"><a href="/index.php" itemprop="url" aria-label="Smart Choice Contractors USA Home"><img class="logo-small" src="/images/logo-364x52.png" alt="Smart Choice Contractors USA" width="182" height="26" itemprop="logo"><img class="logo-default" src="/images/logo-638x310.png" alt="Smart Choice Contractors USA Logo" width="319" height="155"></a></div>
          <button class="rd-navbar-info-toggle" type="button" data-rd-navbar-toggle=".rd-navbar-info" aria-label="Open contact information"><span></span></button>
        </div><div class="rd-navbar-info"><div class="info" itemscope itemtype="https://schema.org/LocalBusiness"><meta itemprop="name" content="Smart Choice Contractors USA"><meta itemprop="url" content="https://justsmartchoice.com/"><div class="info-heading">Need Help?</div><a class="info-link" href="tel:+17277553786" itemprop="telephone"><span class="info-icon fa-mobile" aria-hidden="true"></span>(727) 755-3786</a><div class="info-small hours-line"><strong>Work Hours:</strong> Mon-Fri 8:00 AM - 4:00 PM | Sat 8:00 AM - 3:00 PM</div><a class="appointment-button" href="https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2" target="_blank" rel="noopener">Appointment</a></div></div></div></div>
        <div class="rd-navbar-section sc-main-nav-section"><div class="rd-navbar-container"><ul class="rd-navbar-nav" itemscope itemtype="https://schema.org/SiteNavigationElement">
          <?php foreach ($nav_items as $item): ?><?php $active = sc_nav_active($item['targets']); ?><li class="rd-nav-item <?php echo $active; ?>"><a class="rd-nav-link" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" itemprop="url" <?php echo $active ? 'aria-current="page"' : ''; ?>><span itemprop="name"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span></a></li><?php endforeach; ?>
        </ul></div></div>
      </nav>
    </div>
  </header>
</section>
<!-- END: GLOBAL SITE HEADER -->
