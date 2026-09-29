<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <span
                    class="copyright-footer"><?= date('Y'); ?>
                    <?= e(_l('clients_copyright', get_option('companyname'))); ?>
                </span>
                <?php if (is_gdpr() && get_option('gdpr_show_terms_and_conditions_in_footer') == '1') { ?>
                - <a href="<?= terms_url(); ?>"
                    class="terms-and-conditions-footer">
                    <?= _l('terms_and_conditions'); ?>
                </a>
                <?php } ?>
                <?php if (is_gdpr() && is_client_logged_in() && get_option('show_gdpr_link_in_footer') == '1') { ?>
                - <a href="<?= site_url('clients/gdpr'); ?>"
                    class="gdpr-footer">
                    <?= _l('gdpr_short'); ?>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
</footer>
<script src="<?= base_url('assets/js/sc_money_format.js'); ?>"></script>
<script src="<?= base_url('assets/js/sc-phone-format.js?v=4.2.8'); ?>"></script>

<script id="sc-client-portal-finishing-v374">
(function($){
  $(function(){
    var $main=$('body.customers .main-content, body.customers #wrapper .content, body.customers .customers-content').first();
    if(!$main.length) $main=$('body.customers .container').last();
    var $active=$('body.customers .navbar-nav>li.active>a').first();
    if($main.length && $active.length && !$main.find('.sc-auto-page-heading,.page-header,.panel-heading').first().length){
      var text=$.trim($active.text()); var icon=$active.find('i').attr('class')||'fa-solid fa-layer-group';
      if(text){ $main.prepend('<div class="sc-auto-page-heading"><i class="'+icon+'"></i><span>'+ $('<div>').text(text).html() +'</span></div>'); }
    }
    $('.customers-nav-item-profile img').css({display:'block',margin:'0 auto',objectFit:'cover'});
  });
})(jQuery);
</script>

<?php $scClientJs = trim((string)get_option('sc_custom_client_js')); if ($scClientJs !== '') { ?><script id="sc-custom-client-js"><?= $scClientJs; ?></script><?php } ?>
<script id="sc-two-language-ui">$(function(){$('select[name="default_language"]').each(function(){var $s=$(this);$s.find('option[value=""]').remove();if(!$s.val()){$s.val('english');}try{$s.selectpicker('refresh');}catch(e){}});});</script>