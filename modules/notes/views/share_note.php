<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="<?php echo html_escape(get_option('active_language') ?: 'english'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title); ?></title>
    <style>
        body{margin:0;background:#f3f4f6;color:#1f2937;font-family:Arial,sans-serif}.wrap{max-width:860px;margin:40px auto;padding:20px}.card{background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}.head{padding:22px 26px;border-left:6px solid <?php echo html_escape($note->note_color ?: '#3598DB'); ?>}.body{padding:26px;line-height:1.6}.meta{font-size:13px;color:#6b7280;margin-top:8px}@media(max-width:600px){.wrap{margin:10px auto;padding:10px}.head,.body{padding:18px}}
    </style>
</head>
<body><div class="wrap"><div class="card"><div class="head"><h1><?php echo html_escape($title); ?></h1><div class="meta"><?php echo html_escape(_l('notes_shared_note')); ?></div></div><div class="body"><?php echo check_for_links($note->description); ?></div></div></div></body>
</html>
