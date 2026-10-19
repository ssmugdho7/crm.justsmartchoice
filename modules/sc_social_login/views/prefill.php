<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>(function(){var field=document.querySelector('.register-email-group input[type="email"]');if(field&&!field.value)field.value=<?= json_encode($email,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;})();</script>
