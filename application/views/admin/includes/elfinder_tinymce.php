<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.js'); ?>"></script>
    <script src="<?= base_url('assets/plugins/elFinder/js/elfinder.min.js?v=' . get_app_version()); ?>"></script>
    <script src="<?= base_url('assets/plugins/elFinder/js/extras/editors.default.min.js?v=' . get_app_version()); ?>"></script>
    <?= app_compile_css('editor-media'); ?>
    <?php if ($mediaLocale != 'en' && file_exists(FCPATH . 'assets/plugins/elFinder/js/i18n/elfinder.' . $mediaLocale . '.js')) { ?>
    <script src="<?= base_url('assets/plugins/elFinder/js/i18n/elfinder.' . $mediaLocale . '.js?v=' . get_app_version()); ?>"></script>
    <?php } ?>
    <?php hooks()->do_action('elfinder_tinymce_head'); ?>
    <link rel="stylesheet" href="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.css?v=' . get_app_version()); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/elFinder/css/elfinder.min.css?v=' . get_app_version()); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/elFinder/themes/Material/css/theme-gray.css?v=' . get_app_version()); ?>">
    <script>
        var site_url = <?= json_encode(site_url()); ?>;
        var FileBrowserDialogue = {
            init: function(){},
            mySubmit: function(URL){
                parent.tinymce.activeEditor.windowManager.elfinderCallback(URL.url);
                parent.tinymce.activeEditor.windowManager.close();
            }
        };
    </script>
</head>
<body>
    <div id="elfinder"></div>
    <script>
    (function($){
        'use strict';
        $(function(){
            var customData = {};
            if (typeof csrfData !== 'undefined' && csrfData && csrfData.token_name) {
                customData[csrfData.token_name] = csrfData.hash;
            }
            var editors = [];
            try {
                editors = elFinder.prototype._options.commandsOptions.edit.editors || [];
            } catch (e) {}
            $('#elfinder').elfinder({
                onlyMimes: ['image','video','application/pdf'],
                url: <?= json_encode($connector . '?editor=true'); ?>,
                lang: <?= json_encode($mediaLocale); ?>,
                height: 700,
                customData: customData,
                getFileCallback: function(file){ FileBrowserDialogue.mySubmit(file); },
                commandsOptions: {
                    edit: {editors: editors, extraOptions:{creativeCloudApiKey:'',managerUrl:''}},
                    quicklook: {googleDocsMimes:['application/pdf','image/tiff','application/vnd.ms-office','application/msword','application/vnd.ms-word','application/vnd.ms-excel','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']}
                },
                contextmenu: {files:['getfile','|','open','quicklook','|','download','|','copy','cut','paste','duplicate','|','rm','|','edit','rename','|','archive','extract']},
                ui: ['toolbar','tree','path','stat'],
                uiOptions: {toolbar:[['back','forward'],['mkdir','mkfile','upload'],['open','download','getfile'],['quicklook'],['copy','paste'],['rm'],['duplicate','rename','edit'],['extract','archive'],['search'],['view'],['info']]}
            });
        });
    })(window.jQuery);
    </script>
</body>
</html>
