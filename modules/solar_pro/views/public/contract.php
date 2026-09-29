<?php
defined('BASEPATH') or exit('No direct script access allowed');
$initialKeys = [];
preg_match_all('/\{solar_initial:([a-zA-Z0-9_-]+)\}/', $contract['content'], $mm);
$initialKeys = array_values(array_unique($mm[1] ?? []));
$content = htmlspecialchars_decode($contract['content'], ENT_QUOTES);
if ($contract['status'] === 'signed') {
    $savedInitials = $this->db->where('contract_id', $contract['id'])->get(db_prefix() . 'solar_contract_initials')->result_array();
    $initialMap = [];
    foreach ($savedInitials as $si) {
        $initialMap[$si['field_key']] = $si['initials_data'];
    }
    foreach ($initialKeys as $key) {
        $img = !empty($initialMap[$key]) ? '<img class="sp-signed-initial" src="' . html_escape($initialMap[$key]) . '" alt="' . html_escape(_l('solar_pro_initials')) . '">' : '';
        $content = str_replace('{solar_initial:' . $key . '}', $img, $content);
    }
    $sigImg = !empty($contract['signature_data']) ? '<img class="sp-signed-signature" src="' . html_escape($contract['signature_data']) . '" alt="' . html_escape(_l('solar_pro_signature')) . '">' : '';
    $content = str_replace('{solar_signature}', $sigImg, $content);
} else {
    foreach ($initialKeys as $key) {
        $content = str_replace(
            '{solar_initial:' . $key . '}',
            '<div class="sp-initial-placeholder" data-initial-key="' . html_escape($key) . '"><canvas width="150" height="70"></canvas><button type="button" class="sp-clear">' . _l('solar_pro_clear') . '</button><input type="hidden" name="initials[' . html_escape($key) . ']"></div>',
            $content
        );
    }
    $content = str_replace('{solar_signature}', '<span class="sp-signature-marker">' . _l('solar_pro_signature_below') . '</span>', $content);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo html_escape($title); ?></title>
<link rel="stylesheet" href="<?php echo module_dir_url('solar_pro', 'assets/css/solar-pro-public.css?v=' . SOLAR_PRO_VERSION); ?>">
</head>
<body>
<div class="sp-public contract">
  <div class="sp-public-hero report">
    <div class="sp-crm-logo"><?php get_dark_company_logo(); ?></div>
    <span><?php echo _l('solar_pro_secure_signature'); ?></span>
    <h1><?php echo html_escape($contract['title']); ?></h1>
    <p><?php echo _l('solar_pro_contract_version'); ?> <?php echo (int) $contract['version_no']; ?></p>
    <button type="button" class="sp-secondary sp-print" onclick="window.print()"><?php echo _l('solar_pro_print_save_pdf'); ?></button>
  </div>
  <?php if (!empty($error)) { ?><div class="sp-alert error"><?php echo html_escape($error); ?></div><?php } ?>
  <?php if (!empty($success)) { ?><div class="sp-alert success"><?php echo html_escape($success); ?></div><?php } ?>
  <?php if ($contract['status'] !== 'signed') { echo form_open(site_url('solar_pro/portal/' . $contract['public_token'] . '/sign'), ['id' => 'sp-sign-form']); } ?>
  <div class="sp-card sp-contract-content">
    <?php echo $content; ?>
    <?php if ($contract['status'] === 'signed') { ?>
    <div class="sp-signing-audit">
      <h3><?php echo _l('solar_pro_signing_audit'); ?></h3>
      <p><strong><?php echo _l('solar_pro_name'); ?>:</strong> <?php echo html_escape($contract['signed_name']); ?></p>
      <p><strong><?php echo _l('solar_pro_email'); ?>:</strong> <?php echo html_escape($contract['signed_email']); ?> &nbsp; <strong><?php echo _l('solar_pro_phone'); ?>:</strong> <?php echo html_escape($contract['signed_phone']); ?></p>
      <p><strong>IP:</strong> <?php echo html_escape($contract['signature_ip']); ?> &nbsp; <strong><?php echo _l('solar_pro_signed_at'); ?>:</strong> <?php echo _dt($contract['signed_at']); ?></p>
    </div>
    <?php } ?>
  </div>
  <?php if ($contract['status'] !== 'signed') { ?>
  <div class="sp-card">
    <h2><?php echo _l('solar_pro_sign_contract'); ?></h2>
    <div class="sp-grid">
      <label><?php echo _l('solar_pro_first_name'); ?><input type="text" name="signed_first_name" required></label>
      <label><?php echo _l('solar_pro_last_name'); ?><input type="text" name="signed_last_name" required></label>
      <label><?php echo _l('solar_pro_full_legal_name'); ?><input type="text" name="signed_name" required></label>
      <label><?php echo _l('solar_pro_email'); ?><input type="email" name="signed_email" required></label>
      <label><?php echo _l('solar_pro_phone'); ?><input type="tel" name="signed_phone" required></label>
    </div>
    <label><?php echo _l('solar_pro_signature'); ?><canvas id="sp-signature" class="sp-sign-canvas" width="700" height="180"></canvas><input type="hidden" name="signature_data" id="signature_data"></label>
    <button type="button" class="sp-secondary" id="sp-clear-signature"><?php echo _l('solar_pro_clear_signature'); ?></button>
    <button type="submit" class="sp-button"><?php echo _l('solar_pro_accept_sign'); ?></button>
    <p class="sp-small"><?php echo _l('solar_pro_signature_consent'); ?></p>
  </div>
  <?php echo form_close(); ?>
  <?php } else { ?>
  <div class="sp-card sp-cta"><h2><?php echo _l('solar_pro_contract_signed'); ?></h2><p><?php echo html_escape($contract['signed_name']); ?> · <?php echo html_escape($contract['signed_at']); ?></p><p class="sp-small"><?php echo _l('solar_pro_document_hash'); ?>: <?php echo html_escape($contract['document_hash']); ?></p></div>
  <?php } ?>
</div>
<script src="<?php echo module_dir_url('solar_pro', 'assets/js/solar-pro-signature.js?v=' . SOLAR_PRO_VERSION); ?>"></script>
</body>
</html>
