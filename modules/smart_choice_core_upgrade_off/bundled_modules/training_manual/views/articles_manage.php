<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper">
    <div class="content training-manual-admin-page">
        <div class="training-manual-toolbar panel_s">
            <div class="panel-body">
                <div class="training-manual-toolbar-row">
                    <div class="training-manual-toolbar-left">
                        <?php if(has_permission('training_manual_articles','','create')){ ?>
                            <a href="<?php echo admin_url('training_manual/articles/article'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('training_manual_new_article'); ?></a>
                        <?php } ?>
                        <a href="<?php echo admin_url('training_manual/articles/export_articles'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</a>
                        <a href="<?php echo admin_url('training_manual/articles/sample_articles'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
                        <?php if(has_permission('training_manual_articles','','create')){ ?>
                            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#trainingManualImportModal"><i class="fa fa-upload"></i> Import</button>
                        <?php } ?>
                        <a href="<?php echo admin_url('training_manual/articles'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
                        <?php if(has_permission('training_manual_articles','','delete')){ ?>
                            <button type="button" class="btn btn-danger btn-sm training-manual-mass-delete"><i class="fa fa-trash"></i> Mass Delete</button>
                        <?php } ?>
                    </div>
                    <div class="training-manual-toolbar-right">
                        <a href="<?php echo admin_url('training_manual/articles?view_mode=cards'); ?>" class="btn btn-default btn-sm <?php echo $view_mode == 'cards' ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> Card View</a>
                        <a href="<?php echo admin_url('training_manual/articles?view_mode=pipeline'); ?>" class="btn btn-default btn-sm <?php echo $view_mode == 'pipeline' ? 'active' : ''; ?>"><i class="fa fa-columns"></i> Pipeline View</a>
                    </div>
                </div>
                <hr class="training_manual-hr-header" />
                <?php if(isset($filter_audience) && $filter_audience == 'customer_portal'){ ?>
                    <div class="alert alert-info training-manual-customer-portal-alert"><i class="fa fa-video-camera"></i> <?php echo _l('training_manual_customer_videos_help'); ?></div>
                <?php } ?>
                <?php echo form_open(admin_url('training_manual/articles'), array('method'=>'get', 'class'=>'training-manual-filter-form')); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo render_select('filter_book_id', $books, array('id', array('name')), 'Training Manual', $filter_book_id ?? '', [], [], ''); ?>
                        </div>
                        <div class="col-md-2">
                            <?php echo render_select('filter_language_code', [
                                ['id'=>'en','name'=>'English'], ['id'=>'es','name'=>'Spanish'], ['id'=>'mixed','name'=>'Mixed']
                            ], array('id', array('name')), 'Language', $filter_language_code ?? '', [], [], ''); ?>
                        </div>
                        <div class="col-md-2">
                            <?php echo render_select('filter_style_preset', [
                                ['id'=>'smart_choice','name'=>'Smart Choice'], ['id'=>'clean','name'=>'Clean'], ['id'=>'field','name'=>'Field Operations']
                            ], array('id', array('name')), 'Style Preset', $filter_style_preset ?? '', [], [], ''); ?>
                        </div>
                        <div class="col-md-2">
                            <?php echo render_select('filter_content_kind', [
                                ['id'=>'article','name'=>_l('training_manual_content_kind_article')], ['id'=>'video','name'=>_l('training_manual_content_kind_video')]
                            ], array('id', array('name')), _l('training_manual_content_kind'), $filter_content_kind ?? '', [], [], ''); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('filter_query', 'Search Articles', $filter_query ?? '', 'search'); ?>
                        </div>
                        <div class="col-md-2 training-manual-filter-buttons">
                            <input type="hidden" name="view_mode" value="<?php echo html_escape($view_mode); ?>">
                            <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-filter"></i> Filter</button>
                            <a href="<?php echo admin_url('training_manual/articles'); ?>" class="btn btn-default btn-sm">Clear</a>
                        </div>
                    </div>
                <?php echo form_close(); ?>
                <ul class="training_manual-nav training-manual-subnav">
                    <?php $tmpActiveItem = (isset($filter_is_owner) && $filter_is_owner == 1) ? 'OWNER' : ((isset($filter_is_bookmark) && $filter_is_bookmark == 1) ? 'BOOKMARK' : 'ALL'); ?>
                    <li class="training_manual-nav-item <?php echo $tmpActiveItem == 'ALL' ? 'active' : '' ?>"><a href="<?php echo admin_url('training_manual/articles'); ?>" class="training_manual-nav-link"><i class="fa fa-files-o"></i> All Articles</a></li>
                    <li class="training_manual-nav-item <?php echo $tmpActiveItem == 'OWNER' ? 'active' : '' ?>"><a href="<?php echo admin_url('training_manual/articles?filter_is_owner=1'); ?>" class="training_manual-nav-link"><i class="fa fa-user-circle-o"></i> Created By Me</a></li>
                    <li class="training_manual-nav-item <?php echo $tmpActiveItem == 'BOOKMARK' ? 'active' : '' ?>"><a href="<?php echo admin_url('training_manual/articles?filter_is_bookmark=1'); ?>" class="training_manual-nav-link"><i class="fa fa-bookmark"></i> Bookmarks</a></li>
                </ul>
            </div>
        </div>

        <?php echo form_open(admin_url('training_manual/articles/mass_action'), array('id'=>'trainingManualMassForm')); ?>
        <input type="hidden" name="mass_action" value="delete">
        <?php if(count($articles) == 0){ ?>
            <div class="panel_s"><div class="panel-body"><h4 class="text-center training_manual-empty-results">No training articles found.</h4></div></div>
        <?php } ?>

        <?php if($view_mode == 'pipeline'){ ?>
            <div class="training-manual-pipeline">
                <?php $grouped = []; foreach($articles as $a){ $grouped[$a['book_name'] ?? 'Unassigned'][] = $a; } ?>
                <?php foreach($grouped as $bookName => $items){ ?>
                    <div class="training-manual-pipeline-column">
                        <div class="training-manual-pipeline-title"><?php echo html_escape($bookName); ?> <span class="badge"><?php echo count($items); ?></span></div>
                        <?php foreach($items as $article){ include module_views_path(TRAINING_MANUAL_MODULE_NAME, 'partials/article_card.php'); } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="row">
                <?php foreach($articles as $article){ ?>
                    <div class="col-md-4">
                        <?php include module_views_path(TRAINING_MANUAL_MODULE_NAME, 'partials/article_card.php'); ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="modal fade" id="trainingManualImportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart(admin_url('training_manual/articles/import_articles')); ?>
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Import Training Articles</h4></div>
            <div class="modal-body">
                <p>Upload a CSV file using the sample header format. Use HTML/source content for designed training articles.</p>
                <div class="form-group"><label>CSV File</label><input type="file" name="import_file" class="form-control" accept=".csv" required></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="has_header" value="1" id="tm_has_header" checked><label for="tm_has_header">File has header row</label></div>
                <div class="alert alert-info">Columns: book_id, title, description, content, language_code, style_preset, is_publish</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary btn-sm">Import</button></div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="trainingManualCreatorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('training_manual/articles/change_creator')); ?>
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Change Creator</h4></div>
            <div class="modal-body">
                <input type="hidden" name="article_id" id="trainingManualCreatorArticleId">
                <?php echo render_select('staff_id', $staff_members ?? [], ['staffid', ['full_name']], 'New Creator', ''); ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary btn-sm">Save Creator</button></div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    const APP_CSRF_TOKEN = "<?php echo $this->security->get_csrf_hash(); ?>";
    const bookmark_switch_url = "<?php echo admin_url('training_manual/articles/bookmark_switch'); ?>";
</script>
<script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/js/articles_manage.js'); ?>"></script>
</body>
</html>
