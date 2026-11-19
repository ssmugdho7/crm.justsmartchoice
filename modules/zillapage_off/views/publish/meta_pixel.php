<?php defined('BASEPATH') or exit('No direct script access allowed');
$pixelId = trim((string) ($settingsMap['facebook_pixel_id'] ?? ''));
if (!preg_match('/^[0-9]{5,25}$/D', $pixelId)) { return; }
?>
<div id="sc-meta-consent" role="region" aria-label="Advertising cookie preferences" data-pixel-id="<?= html_escape($pixelId); ?>" hidden>
 <p>Allow advertising cookies? We use Meta Pixel to measure visits and estimate requests. Your form answers are not sent to Meta.</p>
 <button type="button" data-consent="allow">Allow</button>
 <button type="button" data-consent="deny">Decline</button>
</div>
<button id="sc-meta-preferences" type="button" hidden>Cookie preferences</button>
<style>#sc-meta-consent{position:fixed;bottom:16px;left:16px;right:16px;max-width:560px;z-index:10000;padding:18px;background:#fff;color:#172b24;border:1px solid #cbd5d1;border-radius:10px;box-shadow:0 4px 20px #0002;font:15px/1.5 Arial,sans-serif}#sc-meta-consent[hidden],#sc-meta-preferences[hidden]{display:none!important}#sc-meta-consent p{margin:0 0 12px}#sc-meta-consent button,#sc-meta-preferences{padding:9px 16px;border:1px solid #17634c;border-radius:6px;background:#fff;color:#17634c;cursor:pointer}#sc-meta-consent button[data-consent=allow]{background:#17634c;color:white;margin-right:8px}#sc-meta-preferences{position:fixed;bottom:8px;left:8px;z-index:9999;font:12px Arial,sans-serif}</style>
<script defer src="<?= base_url(ZILLAPAGE_ASSETS_PATH.'/landingpage/js/meta-pixel.js'); ?>?v=<?= filemtime(__DIR__.'/../../assets/landingpage/js/meta-pixel.js'); ?>"></script>
