<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>modules/diagramy/assets/css/style.css">
<style>
    #diagramy_content, #diagramy_xml { display:none; }
    #diagramy-editor-frame { width:100%; height:760px; min-height:650px; border:0; }
    #load_ifm { width:100%; min-height:650px; }
    #image { width:100%; min-height:650px; background:#f7f9fb; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
    .diagramy-saving-overlay { display:none; position:fixed; inset:0; background:rgba(255,255,255,.75); z-index:99999; align-items:center; justify-content:center; font-size:18px; font-weight:600; color:#1f2937; }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php if (isset($diagramy)) { echo form_hidden('is_edit', 'true'); } ?>
            <?php echo form_open_multipart($this->uri->uri_string(), ['id'=>'diagramy-form']); ?>
            <div class="col-lg-12">
                <div class="panel_s" id="top-panel">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo (isset($diagramy) && $diagramy->title) ? 'Edit '.$diagramy->title : _l('diagramy_create_new'); ?>
                            <span class="close2" id="close">×</span>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <?php $value = (isset($diagramy) ? $diagramy->title : ''); ?>
                        <?php echo render_input('title', 'Title', $value); ?>
                        <?php
                        $selected = (isset($diagramy) ? $diagramy->diagramy_group_id : '');
                        if (is_admin() || '1' == get_option('staff_members_create_inline_diagramy_group')) {
                            echo render_select_with_input_group('diagramy_group_id', $diagramy_groups, ['id', 'name'], 'diagramy_group', $selected, '<a href="#" onclick="new_group();return false;"><i class="fa fa-plus-square" style="margin-left:10px;font-size:38px"></i></a>');
                        } else {
                            echo render_select('diagramy_group_id', $diagramy_groups, ['id', 'name'], 'diagramy_group', $selected);
                        }
                        ?>
                        <?php $value = (isset($diagramy) ? $diagramy->description : ''); ?>
                        <?php echo render_textarea('description', 'Description', $value, ['rows'=>4], []); ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="related_to" class="control-label"><?php echo _l('related_to'); ?></label>
                                    <select name="related_to" class="selectpicker form-control" id="related_to" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="project" <?php if (isset($diagramy) && 'project' == $diagramy->related_to) { echo 'selected'; } ?>><?php echo _l('project'); ?></option>
                                        <option value="task" <?php if (isset($diagramy) && 'task' == $diagramy->related_to) { echo 'selected'; } ?>><?php echo _l('task'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group<?php if (!isset($diagramy) || empty($diagramy->rel_id)) { echo ' hide'; } ?>" id="rel_id_wrapper">
                                    <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="selectpicker ajax-sesarch" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <?php if (isset($diagramy) && '' != $diagramy->related_to && '' != $diagramy->rel_id) {
                                                $rel_data = get_relation_data($diagramy->related_to, $diagramy->rel_id);
                                                $rel_val  = get_relation_values($rel_data, $diagramy->related_to);
                                                echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo render_input('staffid', '', get_staff_user_id(), 'hidden'); ?>
            <?php $contentValue = (isset($diagramy) ? $diagramy->diagramy_content : ''); ?>
            <?php $xmlValue = (isset($diagramy) && isset($diagramy->diagramy_xml) ? $diagramy->diagramy_xml : ''); ?>
            <textarea id="diagramy_content" name="diagramy_content"><?php echo htmlspecialchars($contentValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <textarea id="diagramy_xml" name="diagramy_xml"><?php echo htmlspecialchars($xmlValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="col-lg-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('diagramy'); ?>
                            <span><button id="expand-button" type="button" class="collapsible btn btn-success">Properties</button></span>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">
                                <div id="map">
                                    <div id="image" data-src="<?php echo htmlspecialchars($contentValue, ENT_QUOTES, 'UTF-8'); ?>"></div>
                                    <div id="load_ifm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn-bottom-toolbar text-right">
                    <button type="button" class="btn btn-info diagramy-btn"><?php echo _l('submit'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<div class="diagramy-saving-overlay" id="diagramy-saving-overlay">Saving diagram...</div>
<div id="diagramy-editor-warning" style="display:none;position:fixed;left:20px;bottom:20px;z-index:9999;background:#fff3cd;border:1px solid #ffecb5;color:#664d03;padding:12px 14px;border-radius:8px;max-width:520px;box-shadow:0 6px 18px rgba(0,0,0,.12);">The BPMN editor is still loading. If this stays blank, allow diagrams.net/embed.diagrams.net in browser or server security settings.</div>
<?php $this->load->view('diagramy/diagramy_group'); ?>
<?php init_tail(); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>modules/diagramy/assets/js/style.js"></script>
<script type="text/javascript">
(function () {
    var editorUrl = 'https://embed.diagrams.net/?embed=1&spin=1&ui=atlas&proto=json&saveAndExit=0&noSaveBtn=1&noExitBtn=1&modified=1';
    var iframe = null;
    var iframeReady = false;
    var submitAfterExport = false;
    var lastXml = $('#diagramy_xml').val() || '';
    var lastPng = $('#diagramy_content').val() || '';
    var localKey = 'diagramy-draft-<?php echo isset($diagramy) ? (int)$diagramy->id : 'new'; ?>';

    function showSaving(show) { $('#diagramy-saving-overlay').css('display', show ? 'flex' : 'none'); }

    function safeParse(data) {
        if (!data || typeof data !== 'string') return null;
        try { return JSON.parse(data); } catch (e) { return null; }
    }

    function ensureFrame() {
        if (iframe) return iframe;
        iframe = document.createElement('iframe');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('id', 'diagramy-editor-frame');
        iframe.setAttribute('src', editorUrl);
        document.getElementById('load_ifm').innerHTML = '';
        document.getElementById('load_ifm').appendChild(iframe);
        return iframe;
    }

    function loadEditor() {
        ensureFrame();
        setTimeout(function(){ if(!iframeReady){ $('#diagramy-editor-warning').fadeIn(200); } }, 5000);
    }

    function requestExport(forSubmit) {
        submitAfterExport = !!forSubmit;
        showSaving(!!forSubmit);
        if (!iframe || !iframe.contentWindow || !iframeReady) {
            if (forSubmit) setTimeout(function(){ requestExport(true); }, 500);
            return;
        }
        iframe.contentWindow.postMessage(JSON.stringify({ action:'export', format:'xmlpng', xml:lastXml, spin:'Saving diagram' }), '*');
    }

    window.addEventListener('message', function (evt) {
        var msg = safeParse(evt.data);
        if (!msg || !iframe || evt.source !== iframe.contentWindow) return;

        if (msg.event === 'init') {
            iframeReady = true;
            $('#diagramy-editor-warning').hide();
            var draft = safeParse(localStorage.getItem(localKey));
            if (draft && draft.xml) {
                lastXml = draft.xml;
                $('#diagramy_xml').val(lastXml);
            }
            if (lastXml) {
                iframe.contentWindow.postMessage(JSON.stringify({ action:'load', autosave:1, xml:lastXml }), '*');
            } else if (lastPng) {
                iframe.contentWindow.postMessage(JSON.stringify({ action:'load', autosave:1, xmlpng:lastPng }), '*');
            } else {
                iframe.contentWindow.postMessage(JSON.stringify({ action:'load', autosave:1, xml:'<mxfile><diagram name="Page-1"><mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/></root></mxGraphModel></diagram></mxfile>' }), '*');
            }
        }

        if (msg.xml) {
            lastXml = msg.xml;
            $('#diagramy_xml').val(lastXml);
            try { localStorage.setItem(localKey, JSON.stringify({ lastModified: new Date().toISOString(), xml: lastXml })); } catch (e) {}
        }

        if (msg.event === 'autosave' || msg.event === 'save') {
            requestExport(false);
        }

        if (msg.event === 'export' && msg.data) {
            lastPng = msg.data;
            $('#diagramy_content').val(lastPng);
            $('#image').attr('data-src', lastPng);
            if (submitAfterExport) {
                try { localStorage.removeItem(localKey); } catch(e) {}
                $('#diagramy-form')[0].submit();
            }
        }
    });

    $(document).ready(function () {
        loadEditor();
        $('#image').on('click', loadEditor);

        $('button.diagramy-btn').on('click', function () {
            validate_diagramy_form();
            if (!$('#diagramy-form').valid()) {
                $('#top-panel').show('slow');
                $('#expand-button').hide();
                return;
            }
            requestExport(true);
        });
    });

    window.validate_diagramy_form = function () {
        appValidateForm($('#diagramy-form'), {
            title: 'required',
            description: 'required',
            diagramy_group_id: 'required',
            related_to: 'required',
            rel_id: 'required'
        });
    };
})();
</script>
<script>
var _rel_id = $('#rel_id'),
    _rel_type = $('#related_to'),
    _rel_id_wrapper = $('#rel_id_wrapper'),
    data = {};
var _milestone_selected_data;
_milestone_selected_data = undefined;
$(function(){
    $("body").off("change", "#rel_id");
    $('.rel_id_label').html(_rel_type.find('option:selected').text());
    _rel_type.on('change', function() {
        var clonedSelect = _rel_id.html('').clone();
        _rel_id.selectpicker('destroy').remove();
        _rel_id = clonedSelect;
        $('#rel_id_select').append(clonedSelect);
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
        task_rel_select();
        if($(this).val() != ''){ _rel_id_wrapper.removeClass('hide'); } else { _rel_id_wrapper.addClass('hide'); }
    });
    init_datepicker();
    init_color_pickers();
    init_selectpicker();
    task_rel_select();
    <?php if (isset($diagramy->related_to) && '' != $diagramy->rel_id) { ?> _rel_id.change(); <?php } ?>
});
function task_rel_select(){
    var serverData = {};
    serverData.rel_id = _rel_id.val();
    data.type = _rel_type.val();
    var url;
    if (data.type == "task") { url = admin_url + "diagramy/search_task"; }
    init_ajax_search(_rel_type.val(), _rel_id, serverData, url);
}
</script>
</body>
</html>
