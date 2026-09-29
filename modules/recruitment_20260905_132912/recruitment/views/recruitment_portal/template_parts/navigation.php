<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$sc_lang = isset($_GET['lang']) && in_array($_GET['lang'], ['english', 'spanish'], true)
    ? $_GET['lang']
    : (isset($_COOKIE['sc_recruitment_language']) && in_array($_COOKIE['sc_recruitment_language'], ['english', 'spanish'], true)
        ? $_COOKIE['sc_recruitment_language']
        : 'english');
$sc_base_url = current_url();
$sc_query = $_GET;
unset($sc_query['lang']);
$sc_english_query = http_build_query(array_merge($sc_query, ['lang' => 'english']));
$sc_spanish_query = http_build_query(array_merge($sc_query, ['lang' => 'spanish']));
$sc_logo_url = get_recruitment_option('recruitment_portal_logo_url');
?>
<header class="sc-recruitment-portal-header" aria-label="<?php echo html_escape(_l('recruitment_portal')); ?>">
  <div class="sc-recruitment-header-shell">
    <a href="<?php echo site_url('recruitment/recruitment_portal'); ?>" class="sc-recruitment-header-brand" aria-label="Smart Choice Contractors USA">
      <?php if (!empty($sc_logo_url)) { ?>
        <img src="<?php echo html_escape($sc_logo_url); ?>" alt="Smart Choice Contractors USA" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
        <span class="sc-recruitment-header-brand-text" style="display:none;">Smart Choice Contractors USA</span>
      <?php } else { ?>
        <span class="sc-recruitment-header-logo-fallback"><?php echo get_dark_company_logo(); ?></span>
      <?php } ?>
    </a>

    <nav class="sc-recruitment-header-actions" aria-label="<?php echo html_escape(_l('recruitment_portal')); ?>">
      <a class="sc-recruitment-header-link" href="<?php echo site_url('recruitment/recruitment_portal'); ?>"><?php echo _l('recruitment_portal'); ?></a>
      <a class="sc-recruitment-header-link" href="<?php echo site_url('recruitment/recruitment_portal/login'); ?>"><?php echo _l('login'); ?></a>
      <div class="sc-recruitment-header-language" aria-label="Language selector">
        <span><?php echo _l('recruitment_portal_language_label'); ?>:</span>
        <a href="<?php echo html_escape($sc_base_url . '?' . $sc_english_query); ?>" class="<?php echo $sc_lang === 'english' ? 'active' : ''; ?>"><?php echo _l('recruitment_portal_language_english'); ?></a>
        <a href="<?php echo html_escape($sc_base_url . '?' . $sc_spanish_query); ?>" class="<?php echo $sc_lang === 'spanish' ? 'active' : ''; ?>"><?php echo _l('recruitment_portal_language_spanish'); ?></a>
      </div>
    </nav>
  </div>
</header>
