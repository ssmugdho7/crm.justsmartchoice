$(function(){
	"use strict";

    // init editor
    var smartChoiceEditorCss = "body{line-height:1.55;margin:18px auto;max-width:1040px;font-family:Arial,Helvetica,sans-serif;color:#1f2937;background:#ffffff;} .sc-manual{max-width:1040px;margin:0 auto;padding:18px;} .sc-cover{border:1px solid #d9e2ec;border-radius:14px;padding:24px;background:linear-gradient(135deg,#f0fff4,#ffffff 45%,#fff7ed);margin-bottom:20px;} .sc-cover h1{font-size:28px;margin:0 0 8px;color:#1f2937;} .sc-cover h2{font-size:18px;margin:0;color:#1f8f4d;} .sc-badge{display:inline-block;background:#ffffff;border:1px solid #d9e2ec;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;margin:3px;} .sc-badge.green{background:#e8f8ef;color:#166534;border-color:#bbf7d0;} .sc-badge.orange{background:#fff7ed;color:#9a3412;border-color:#fed7aa;} .sc-section{margin:24px 0;padding-bottom:14px;border-bottom:1px solid #d9e2ec;} .sc-section h2{font-size:22px;color:#1f2937;border-left:5px solid #1f8f4d;padding-left:12px;margin-bottom:8px;} .sc-section h3{font-size:17px;color:#1f8f4d;margin-top:18px;} .sc-note{border-left:4px solid #2563eb;background:#eff6ff;padding:12px 14px;border-radius:8px;margin:14px 0;} .sc-success{border-left:4px solid #1f8f4d;background:#f0fdf4;padding:12px 14px;border-radius:8px;margin:14px 0;} .sc-warning{border-left:4px solid #f7941d;background:#fff7ed;padding:12px 14px;border-radius:8px;margin:14px 0;} .sc-danger{border-left:4px solid #dc2626;background:#fef2f2;padding:12px 14px;border-radius:8px;margin:14px 0;} .sc-card{border:1px solid #d9e2ec;border-radius:12px;padding:14px;background:#ffffff;box-shadow:0 6px 18px rgba(15,23,42,.06);margin:10px 0;} .sc-table{width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;} .sc-table th{background:#f1f5f9;color:#111827;border:1px solid #d9e2ec;padding:9px;text-align:left;} .sc-table td{border:1px solid #d9e2ec;padding:9px;} .sc-folder{background:#0f172a;color:#e5e7eb;padding:16px;border-radius:12px;font-family:Consolas,monospace;white-space:pre-wrap;margin:12px 0;} .sc-flow{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:16px 0;} .sc-flow-box{background:#fff;border:2px solid #1f8f4d;border-radius:12px;padding:10px 12px;font-weight:700;min-width:105px;text-align:center;} .sc-flow-arrow{font-size:22px;color:#f7941d;font-weight:900;} table{border-collapse:collapse;} table th,table td{border:1px solid #d9e2ec;padding:8px;} img{max-width:100%;}";
    init_editor('.tinymce-content',{
      toolbar1: 'visualblocks fullscreen styleselect fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | image link codesample | bullist numlist | restoredraft',
      visualblocks_default_state: true,
      content_style: smartChoiceEditorCss,
      extended_valid_elements: '*[*]',
      valid_children: '+body[style],+div[style],+section[style],+span[style],+table[style],+tr[style],+td[style],+th[style],+h1[style],+h2[style],+h3[style],+p[style],+a[style]',
      custom_elements: '~section',
      verify_html: false,
      cleanup: false
     }
    );


   

    // bind slug
    var fnGenerateSlug = function(sTitle){
        return sTitle.toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,'');
    }
    $('#title').on('change', function(){
        if($('#is_publish').is(':checked') && $('#slug').val() == ''){
            $('#slug').val(fnGenerateSlug($(this).val())).trigger('keyup');
        }
    });
    var fnCheckPublish = function (){
        if($('#is_publish').is(':checked')){
            $('.training_manual-input-slug-wrap').removeClass('hide');
         }else{
             $('.training_manual-input-slug-wrap').addClass('hide');
         }
         var title = $('#title').val();
         var slug = $('#slug').val();
         if (!slug) {
            $('#slug').val(fnGenerateSlug(title));
         }

    }
    $('#is_publish').on('change', function(){
        fnCheckPublish();
    });
    fnCheckPublish();

    appValidateForm($('#form_main'), {
        title: 'required',
        description: 'required',
        book_id: 'required',
        type: 'required',
    });

    // remove article
    $(".btn-remove").on('click',function(){
        var lang = $(this).data('lang');
        return confirm(lang);
    });

    // action copy
    $(document).ready(function(){
        var actionShow = null;
        var spanAlert = null;
        $('body').on('click', '.training_manual-btn-copy', function(){
            if(actionShow != null){
                window.clearTimeout(actionShow);
                actionShow = null;
                spanAlert.remove();
            }
            var lang = $(this).data('lang');
            var content = $(this).data('copy');
            var dumpInput = document.createElement('input');
            dumpInput.value = content;
            document.getElementsByTagName('body')[0].appendChild(dumpInput);
            dumpInput.select();
            dumpInput.setSelectionRange(0, 99999);
            document.execCommand("copy");
            dumpInput.remove();
            spanAlert = $(` <span class="d-inline-block text-denter text-success">${lang}<span>`);
            $(this).append(spanAlert);
            actionShow = setTimeout(function(){
                spanAlert.remove();
            }, 3000);
        });
    });

});

(function(){
    // control switch type field
    var fnCheckAndSwitch = function(){
        var value = $('[name="type"]').val();
        $('.training_manual-article-type-wrap').addClass('hide');
        if(value != undefined && value != null && value != ''){
            $('.training_manual-article-type-wrap[data-type="' + value + '"]').removeClass('hide');
        }
    }
    $('[name="type"]').on('change', function(){
        fnCheckAndSwitch();
    });

})();
// Smart Choice editor undo/redo controls
$(function(){
    $(document).on('click', '.training-manual-editor-undo', function(){
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
            tinymce.activeEditor.undoManager.undo();
        }
    });
    $(document).on('click', '.training-manual-editor-redo', function(){
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
            tinymce.activeEditor.undoManager.redo();
        }
    });
});


// Smart Choice inline style protection for CRM/TinyMCE editors.
// This converts the standard manual classes into inline styles before saving,
// because many embedded CRM editors sanitize <style> blocks and external CSS inside article content.
(function(){
    function tmApplyStyle($root, selector, styles){
        $root.find(selector).each(function(){
            var current = $(this).attr('style') || '';
            if (current && current.trim().slice(-1) !== ';') { current += ';'; }
            $(this).attr('style', current + styles);
        });
    }
    window.trainingManualInlineStyles = function(html){
        var $tmp = $('<div></div>').html(html || '');
        tmApplyStyle($tmp, '.sc-manual', 'max-width:1040px;margin:0 auto;padding:18px;font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.55;background:#ffffff;');
        tmApplyStyle($tmp, '.sc-cover', 'border:1px solid #d9e2ec;border-radius:14px;padding:24px;background:#f0fff4;margin-bottom:20px;');
        tmApplyStyle($tmp, '.sc-cover h1', 'font-size:28px;margin:0 0 8px;color:#1f2937;font-weight:700;');
        tmApplyStyle($tmp, '.sc-cover h2', 'font-size:18px;margin:0;color:#1f8f4d;font-weight:700;');
        tmApplyStyle($tmp, '.sc-badge', 'display:inline-block;background:#ffffff;border:1px solid #d9e2ec;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;margin:3px;');
        tmApplyStyle($tmp, '.sc-badge.green', 'background:#e8f8ef;color:#166534;border-color:#bbf7d0;');
        tmApplyStyle($tmp, '.sc-badge.orange', 'background:#fff7ed;color:#9a3412;border-color:#fed7aa;');
        tmApplyStyle($tmp, '.sc-section', 'margin:24px 0;padding-bottom:14px;border-bottom:1px solid #d9e2ec;');
        tmApplyStyle($tmp, '.sc-section h2', 'font-size:22px;color:#1f2937;border-left:5px solid #1f8f4d;padding-left:12px;margin-bottom:8px;font-weight:700;');
        tmApplyStyle($tmp, '.sc-section h3', 'font-size:17px;color:#1f8f4d;margin-top:18px;font-weight:700;');
        tmApplyStyle($tmp, '.sc-note', 'border-left:4px solid #2563eb;background:#eff6ff;padding:12px 14px;border-radius:8px;margin:14px 0;');
        tmApplyStyle($tmp, '.sc-success', 'border-left:4px solid #1f8f4d;background:#f0fdf4;padding:12px 14px;border-radius:8px;margin:14px 0;');
        tmApplyStyle($tmp, '.sc-warning', 'border-left:4px solid #f7941d;background:#fff7ed;padding:12px 14px;border-radius:8px;margin:14px 0;');
        tmApplyStyle($tmp, '.sc-danger', 'border-left:4px solid #dc2626;background:#fef2f2;padding:12px 14px;border-radius:8px;margin:14px 0;');
        tmApplyStyle($tmp, '.sc-card', 'border:1px solid #d9e2ec;border-radius:12px;padding:14px;background:#ffffff;box-shadow:0 6px 18px rgba(15,23,42,.06);margin:10px 0;');
        tmApplyStyle($tmp, '.sc-table', 'width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;');
        tmApplyStyle($tmp, '.sc-table th', 'background:#f1f5f9;color:#111827;border:1px solid #d9e2ec;padding:9px;text-align:left;');
        tmApplyStyle($tmp, '.sc-table td', 'border:1px solid #d9e2ec;padding:9px;');
        tmApplyStyle($tmp, '.sc-folder', 'background:#0f172a;color:#e5e7eb;padding:16px;border-radius:12px;font-family:Consolas,monospace;white-space:pre-wrap;margin:12px 0;');
        tmApplyStyle($tmp, '.sc-flow', 'display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:16px 0;');
        tmApplyStyle($tmp, '.sc-flow-box', 'background:#fff;border:2px solid #1f8f4d;border-radius:12px;padding:10px 12px;font-weight:700;min-width:105px;text-align:center;');
        tmApplyStyle($tmp, '.sc-flow-arrow', 'font-size:22px;color:#f7941d;font-weight:900;');
        return $tmp.html();
    };
    $(document).on('submit', '#form_main', function(){
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
            var editor = tinymce.get('content');
            if (editor) {
                editor.setContent(window.trainingManualInlineStyles(editor.getContent()));
                tinymce.triggerSave();
            } else {
                $('.tinymce-content').each(function(){
                    $(this).val(window.trainingManualInlineStyles($(this).val()));
                });
            }
        }
    });
})();
