<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.css?v=' . get_app_version()); ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/elFinder/css/elfinder.min.css?v=' . get_app_version()); ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/elFinder/themes/Material/css/theme-gray.css?v=' . get_app_version()); ?>">
<style id="sc-media-native-layout-fix">
/* Keep Utilities > Media inside the native Perfex content column and prevent theme/module overrides from stretching elFinder. */
#sc-native-media-panel .panel-body{padding:0!important;overflow:hidden!important}
#sc-native-media-panel #elfinder{width:100%!important;max-width:100%!important;min-height:700px!important}
#sc-native-media-panel .elfinder{width:100%!important;max-width:100%!important;box-sizing:border-box!important}
#sc-native-media-panel .elfinder-workzone{min-height:560px!important}
#sc-native-media-panel .elfinder-navbar{min-width:220px!important;max-width:34%!important}
#sc-native-media-panel .elfinder-cwd-wrapper{overflow:auto!important}
#sc-native-media-panel .elfinder-toolbar{white-space:normal!important;height:auto!important;min-height:38px!important}
#sc-native-media-panel .elfinder-toolbar-button-separator{height:28px!important}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s" id="sc-native-media-panel">
                    <div class="panel-body">
                        <div id="elfinder"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.js?v=' . get_app_version()); ?>"></script>
<script>
/*
 * Utilities > Media deliberately uses Perfex's already-loaded jQuery instance.
 * Do not load a second jQuery through RequireJS: the customized CRM protects
 * the global $ reference and a second jQuery loader causes a read-only-property
 * exception before elFinder can finish booting.
 */
window.scMediaJQuery = window.jQuery;
</script>
<script src="<?= base_url('assets/plugins/elFinder/js/elfinder.min.js?v=' . get_app_version()); ?>"></script>
<script src="<?= base_url('assets/plugins/elFinder/js/extras/editors.default.min.js?v=' . get_app_version()); ?>"></script>
<?php $mediaLocale = get_media_locale($locale); ?>
<?php if ($mediaLocale !== 'en' && file_exists(FCPATH . 'assets/plugins/elFinder/js/i18n/elfinder.' . $mediaLocale . '.js')) { ?>
<script src="<?= base_url('assets/plugins/elFinder/js/i18n/elfinder.' . $mediaLocale . '.js?v=' . get_app_version()); ?>"></script>
<?php } ?>
<script>
(function($){
    'use strict';
    if (!$ || !$.fn) {
        console.error('Perfex jQuery was not available for the Media Manager.');
        return;
    }
    if (typeof $.fn.elfinder !== 'function') {
        console.error('elFinder failed to load for the Media Manager.');
        return;
    }

    $(function(){
        var customData = {};
        if (typeof csrfData !== 'undefined' && csrfData && csrfData.token_name) {
            customData[csrfData.token_name] = csrfData.hash;
        }

        var editors = [];
        try {
            if (window.elFinder && elFinder.prototype && elFinder.prototype._options &&
                elFinder.prototype._options.commandsOptions &&
                elFinder.prototype._options.commandsOptions.edit &&
                Array.isArray(elFinder.prototype._options.commandsOptions.edit.editors)) {
                editors = elFinder.prototype._options.commandsOptions.edit.editors;
            }
        } catch (e) {}

        $('#elfinder').elfinder({
            url: <?= json_encode($connector); ?>,
            lang: <?= json_encode($mediaLocale); ?>,
            height: 700,
            customData: customData,
            commandsOptions: {
                edit: {
                    editors: editors,
                    extraOptions: {
                        creativeCloudApiKey: '',
                        managerUrl: ''
                    }
                },
                quicklook: {
                    googleDocsMimes: [
                        'application/pdf','image/tiff','application/vnd.ms-office','application/msword',
                        'application/vnd.ms-word','application/vnd.ms-excel','application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ]
                }
            },
            contextmenu: {
                files: ['getfile','|','open','quicklook','|','download','|','copy','cut','paste','duplicate','|','rm','|','edit','rename','|','archive','extract']
            },
            ui: ['toolbar','tree','path','stat'],
            uiOptions: {
                toolbar: [
                    ['back','forward'],
                    ['mkdir','mkfile','upload'],
                    ['open','download','getfile'],
                    ['quicklook'],
                    ['copy','paste'],
                    ['rm'],
                    ['duplicate','rename','edit'],
                    ['extract','archive'],
                    ['search'],
                    ['view'],
                    ['info']
                ]
            },
            bootCallback: function(fm){
                var originalTitle = document.title;
                fm.bind('open', function(){
                    var cwd = fm.cwd();
                    var path = cwd ? (fm.path(cwd.hash) || '') : '';
                    document.title = path ? path + ':' + originalTitle : originalTitle;
                }).bind('destroy', function(){
                    document.title = originalTitle;
                });
            }
        });
    });
})(window.scMediaJQuery || window.jQuery);
</script>
</body>
</html>
