<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
(function(){
    "use strict";

    if (typeof window.jQuery === "undefined") {
        return;
    }

    $(function(){
        if (typeof initDataTable === "function" && $('.table-purchasing-hub').length) {
            initDataTable('.table-purchasing-hub', window.location.href);
        }
    });
})();
</script>