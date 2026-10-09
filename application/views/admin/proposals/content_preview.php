<?php defined('BASEPATH') or exit('No direct script access allowed');
// The item table is already rendered above. Keep the template unchanged for editing/export.
$scProposalContentPreview = preg_replace('/\{\{\s*proposal_items\s*\}\}|\{\s*proposal_items\s*\}/i', '', (string) $proposal->content);
?>
<div id="sc-proposal-content-preview" class="tc-content mtop15">
    <?= $scProposalContentPreview; ?>
</div>
<p id="sc-proposal-items-location" class="text-muted mtop10"><?= _l('sc_proposal_items_location'); ?></p>
<?php if (staff_can('edit', 'proposals') || staff_can('create', 'proposals')) { ?>
<details id="sc-proposal-content-editor" class="mtop15">
    <summary class="tw-cursor-pointer tw-font-medium tw-text-primary tw-py-2"><?= _l('proposal_edit') . ' — ' . _l('proposal'); ?></summary>
    <p class="text-muted mtop10"><?= _l('proposal_content_html_help'); ?></p>
    <?php if (!empty($proposal_merge_fields)) { ?>
    <p class="bold text-right"><a href="#"
        onclick="slideToggle('.avilable_merge_fields'); return false;"><?= _l('available_merge_fields'); ?></a>
    </p>
    <hr class="hr-panel-separator" />
    <div class="hide avilable_merge_fields mtop15">
        <div class="row">
            <div class="col-md-12">
                <ul class="list-group">
                    <?php foreach ($proposal_merge_fields as $field) {
                        foreach ($field as $f) {
                            echo '<li class="list-group-item"><b>' . $f['name'] . '</b> <a href="#" class="pull-right" onclick="insert_proposal_merge_field(this); return false;">' . $f['key'] . '</a></li>';
                        }
                    } ?>
                </ul>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="editable proposal tc-content" id="proposal_content_area"
        style="border:1px solid #d2d2d2;min-height:70px;border-radius:4px;">
        <?php if (empty($proposal->content)) {
            echo '<span class="text-danger text-uppercase mtop15 editor-add-content-notice"> ' . _l('click_to_add_content') . '</span>';
        } else {
            echo $proposal->content;
        } ?>
    </div>
</details>
<script>
(function () {
    var disclosure = document.getElementById('sc-proposal-content-editor');
    var preview = document.getElementById('sc-proposal-content-preview');
    var initialized = false;
    disclosure.addEventListener('toggle', function () {
        preview.hidden = disclosure.open;
        if (disclosure.open && !initialized) {
            initialized = true;
            init_proposal_editor();
        } else if (!disclosure.open && initialized) {
            var editor = tinymce.get('proposal_content_area');
            if (editor) {
                preview.innerHTML = editor.getContent().replace(/\{\{\s*proposal_items\s*\}\}|\{\s*proposal_items\s*\}/gi, '');
            }
        }
    });
})();
</script>
<?php } ?>
