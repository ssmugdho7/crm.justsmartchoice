<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html><html lang="<?= html_escape(get_option('active_language')==='spanish'?'es':'en'); ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= _l('scsl_finish'); ?></title>
<style>body{font:16px system-ui,sans-serif;background:#f3f4f6;color:#263238;margin:0;padding:24px}main{max-width:540px;margin:8vh auto;padding:28px;background:white;border:1px solid #d8dde5;border-radius:12px}a{display:inline-block;padding:12px 18px;margin:8px 8px 0 0;border-radius:6px;background:#00838f;color:white;text-decoration:none}a:focus{outline:3px solid #ff9800}</style></head>
<body><main><h1><?= _l('scsl_finish'); ?></h1><p><?= _l('scsl_finish_hint'); ?></p>
<a href="<?= $audience==='staff'?admin_url('authentication'):site_url('authentication/login'); ?>"><?= _l('scsl_existing'); ?></a>
<?php if ($can_register): ?><a href="<?= site_url('authentication/register'); ?>"><?= _l('scsl_register'); ?></a><p><?= _l('scsl_register_hint'); ?></p><?php endif; ?>
</main></body></html>
