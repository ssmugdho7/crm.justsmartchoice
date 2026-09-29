<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo smartsource_admin_submenu('help'); ?>

        <div class="panel_s smartsource-panel">
            <div class="panel-body smartsource-help">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h3 class="smartsource-help-title">Subcontractors Help Guide</h3>
                        <p class="text-muted tw-mb-0">Training guide for the SmartSource Subcontractors module. Sections stay closed until you click Edit.</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="smartsource-add-help-section"><i class="fa fa-plus"></i> Add Instruction Section</button>
                    </div>
                </div>
                <hr>

                <?php echo form_open(admin_url('smartsource_subcontractors/help')); ?>
                <input type="hidden" name="save_help_sections" value="1">
                <div id="smartsource-help-sections">
                    <?php foreach ($help_sections as $index => $section) { ?>
                        <div class="smartsource-help-view-card" data-index="<?php echo (int) $index; ?>">
                            <div class="smartsource-help-view-header">
                                <div>
                                    <h4><?php echo html_escape($section['title'] ?? 'Instruction Section'); ?></h4>
                                </div>
                                <div class="smartsource-help-actions">
                                    <button type="button" class="btn btn-info btn-sm smartsource-toggle-help-edit"><i class="fa fa-pencil"></i> Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm smartsource-remove-help-section"><i class="fa fa-trash"></i> Delete</button>
                                </div>
                            </div>
                            <div class="smartsource-help-preview-section">
                                <div><?php echo $section['content'] ?? ''; ?></div>
                            </div>
                            <div class="smartsource-help-edit-card" style="display:none;">
                                <?php echo render_input('help_title[]', 'Title', $section['title'] ?? '', 'text'); ?>
                                <div class="form-group">
                                    <label>Instructions</label>
                                    <textarea name="help_content[]" class="tinymce" rows="8"><?php echo html_escape($section['content'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="text-right tw-mt-4">
                    <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Save Help Guide</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var addBtn = document.getElementById('smartsource-add-help-section');
    var wrap = document.getElementById('smartsource-help-sections');
    if(!addBtn || !wrap){ return; }

    function nextIndex(){ return wrap.querySelectorAll('.smartsource-help-view-card').length; }

    addBtn.addEventListener('click', function(){
        var i = nextIndex();
        var div = document.createElement('div');
        div.className = 'smartsource-help-view-card';
        div.setAttribute('data-index', i);
        div.innerHTML = ''+
            '<div class="smartsource-help-view-header"><div><h4>New Instruction Section</h4></div><div class="smartsource-help-actions"><button type="button" class="btn btn-info btn-sm smartsource-toggle-help-edit"><i class="fa fa-pencil"></i> Edit</button><button type="button" class="btn btn-danger btn-sm smartsource-remove-help-section"><i class="fa fa-trash"></i> Delete</button></div></div>'+            
            '<div class="smartsource-help-preview-section"><div><p>Enter the new instruction details here.</p></div></div>'+            
            '<div class="smartsource-help-edit-card" style="display:block;"><div class="form-group"><label for="help_title_'+i+'">Title</label><input type="text" id="help_title_'+i+'" name="help_title[]" class="form-control" value="New Instruction Section"></div><div class="form-group"><label>Instructions</label><textarea name="help_content[]" class="tinymce" rows="8"><p>Enter the new instruction details here.</p></textarea></div></div>';
        wrap.appendChild(div);
        if(typeof init_editor === 'function'){
            init_editor();
        }
    });

    document.addEventListener('click', function(e){
        var editBtn = e.target.closest ? e.target.closest('.smartsource-toggle-help-edit') : null;
        if(editBtn){
            var editCard = editBtn.closest('.smartsource-help-view-card').querySelector('.smartsource-help-edit-card');
            if(editCard){
                editCard.style.display = editCard.style.display === 'none' || editCard.style.display === '' ? 'block' : 'none';
                editBtn.innerHTML = editCard.style.display === 'block' ? '<i class="fa fa-eye"></i> Hide Editor' : '<i class="fa fa-pencil"></i> Edit';
                if(typeof init_editor === 'function'){
                    init_editor();
                }
            }
            return;
        }

        var btn = e.target.closest ? e.target.closest('.smartsource-remove-help-section') : null;
        if(!btn){ return; }
        var card = btn.closest('.smartsource-help-view-card');
        if(card && confirm('Delete this instruction section?')){
            card.parentNode.removeChild(card);
        }
    });
})();
</script>
<?php init_tail(); ?>
