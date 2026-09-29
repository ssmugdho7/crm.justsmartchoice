<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper">
    <div class="content">
        <h3 class="no-margin"><?php echo html_escape($title); ?></h3><hr class="hr-panel-heading" />
        <div class="training_manual-buttons-wrap">
            <div>
                <?php if(empty($customer_books_mode) && has_permission('training_manual_books','','create')){ ?>
                    <a href="<?php echo admin_url('training_manual/books/book'); ?>" class="btn btn-primary pull-left display-block"><?php echo _l('training_manual_new_book'); ?></a>
                <?php } ?>
            </div>
            <div class="input-group">
                <a href="javascript:void(0);" class="input-group-addon btn-search-list">
                    <span class="fa fa-search"></span>
                </a>
                <input type="search" class="form-control input-search-list" value="<?php echo $filter_query; ?>" placeholder="<?php echo _l('training_manual_search'); ?>...">
            </div>
        </div>
        <?php echo form_open($this->uri->uri_string(), array('id'=>'form_search', 'class' =>'hide', 'method'=>'get')); ?>
            <input type="hidden" name="filter_query" value="<?php echo $filter_query; ?>">
        <?php echo form_close(); ?>
        <div class="clearfix"></div>
        <hr class="training_manual-hr-header" />
        <div class="row">
        <?php if(count($books) == 0){ ?>
            <div class="col-xs-12">
                <h3 class="text-center training_manual-empty-results"><?php echo _l('training_manual_empty_results'); ?></h3>
            </div>
        <?php } ?>
        <?php foreach($books as $book){ ?>
            <div class="col-md-4">
                <div class="panel_s training_manual-item-panel training_manual-book-panel">
                    <?php 
                        $hasAction = true;
                    ?>
                    <div class="panel-body">
                        <div class="training_manual-book-title">
                            <div class="training_manual-book-header"><?php $cover=!empty($book['cover_image'])?base_url('uploads/training_manual/'.$book['cover_image']):base_url(TRAINING_MANUAL_ASSETS_PATH.'/img/customer-guides/default-cover.svg'); ?><img src="<?php echo $cover; ?>" alt="<?php echo html_escape($book['name']); ?>" style="width:100%;height:150px;object-fit:cover;border-radius:10px;margin-bottom:12px"></div>

                            <div class="training_manual-book-heading">
                                <h4>
                                <span class="label <?php echo !empty($book['customer_visible'])?'label-info':'label-success'; ?>"><i class="fa <?php echo !empty($book['customer_visible'])?'fa-users':'fa-building'; ?>"></i> <?php echo html_escape(!empty($book['area_label'])?$book['area_label']:(!empty($book['customer_visible'])?'Customer Area':'CRM Center')); ?></span><br>
                                <?php if($hasAction && has_permission('training_manual_books','','edit')){ ?>
                                    <a href="<?php echo admin_url('training_manual/books/book/'.$book['id']) ?>"><?php echo $book['name']; ?></a>
                                    <?php } else { ?>
                                        <?php echo $book['name']; ?>
                                        <?php } ?>
                                </h4>
                            </div>
                            
                        </div>
                        <div class="training_manual-book-decription training_manual-item-description">
                            <p><?php echo $book['short_description']; ?></p>
                        </div>
                    </div>
                    <div class="panel-body" style="padding-top:0"><?php if(has_permission('training_manual_books','','edit')){ ?><a class="btn btn-info btn-xs" href="<?php echo admin_url('training_manual/books/book/'.$book['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><?php } ?> <?php if(has_permission('training_manual_books','','delete')){ ?><a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('training_manual/books/delete/'.$book['id']); ?>"><i class="fa fa-trash"></i> Delete</a><?php } ?></div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-xs-6">
                                <a href="<?php echo admin_url('training_manual/articles') ?>?filter_book_id=<?php echo $book['id']; ?>" class="text-center training_manual-footer-option">
                                    <span class="training_manual-counter-value"><strong><?php echo $book['articles_total']; ?></strong></span>
                                    <div class="training_manual-counter-name"><?php echo _l('training_manual_articles'); ?></div>
                                </a>
                            </div>
                            <div class="col-xs-6">
                                <div class="text-center training_manual-footer-option">
                                    <span class="training_manual-counter-value"><strong><?php echo count(training_manual_unserialize($book['assign_ids'], '')); ?></strong></span>
                                    <div class="training_manual-counter-name"><?php echo _l('training_manual_peoples_teams'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/js/books_manage.js'); ?>"></script>
</body>
</html>
