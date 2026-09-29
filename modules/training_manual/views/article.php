<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="d-flex">
                            <h4 class="no-margin"><?php echo $title; ?></h4>
                            
                        </div>

                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id'=>'form_main')); ?>
                        <?php if(isset($article)){ ?>
                            <input type="hidden" name="article_id" value="<?php echo $article->id; ?>">
                        <?php } ?>
                        <?php if(isset($clone_id)){ ?>
                            <input type="hidden" name="clone_id" value="<?php echo $clone_id; ?>">
                        <?php } ?>
                        <?php if(isset($back_url)){ ?>
                            <input type="hidden" name="back_url" value="<?php echo $back_url; ?>">
                        <?php } ?>
                        <?php
                            if (isset($article)) {
                                $selected_type = $article->type;
                            }else{
                                $selected_type = 'document';
                            }
                        ?>
                        <div class="roww">
                            <div class="col-md-4">
                                <div>
                                    <?php
                                        $selected = array();
                                        if (isset($article)) {
                                            $selected = $article->book_id;
                                        }
                                    ?>
                                    <?php echo render_select('book_id', $books, array('id', array('name')), 'training_manual_book', $selected, []); ?>
                                </div>
                                <?php $attrs = (isset($article) ? array() : array('autofocus'=>true)); ?>
                                
                                <?php $value = (isset($article) ? $article->title : ''); ?>
                                <?php echo render_input('title', 'training_manual_title', $value, 'text', $attrs); ?>
                                <?php
                                    $creator_selected = isset($article) && !empty($article->author_id) ? $article->author_id : (function_exists('get_staff_user_id') ? get_staff_user_id() : 0);
                                    echo render_select('author_id', isset($staff_members) ? $staff_members : [], ['staffid', ['full_name']], 'Creator', $creator_selected);
                                ?>
                                <?php
                                    $language_selected = isset($article) && !empty($article->language_code) ? $article->language_code : (get_option('training_manual_default_language') ?: 'en');
                                    echo render_select('language_code', [
                                        ['id' => 'en', 'name' => 'English'],
                                        ['id' => 'es', 'name' => 'Spanish'],
                                        ['id' => 'mixed', 'name' => 'Mixed English / Spanish'],
                                    ], ['id', ['name']], 'Language', $language_selected);
                                ?>
                                <?php
                                    $style_selected = isset($article) && !empty($article->style_preset) ? $article->style_preset : (get_option('training_manual_default_style') ?: 'smart_choice');
                                    echo render_select('style_preset', [
                                        ['id' => 'smart_choice', 'name' => 'Smart Choice'],
                                        ['id' => 'clean', 'name' => 'Clean'],
                                        ['id' => 'field', 'name' => 'Field Operations'],
                                    ], ['id', ['name']], 'Style Preset', $style_selected);
                                ?>

                                <?php
                                    $audience_selected = isset($article) && !empty($article->audience) ? $article->audience : 'internal';
                                    echo render_select('audience', [
                                        ['id' => 'internal', 'name' => _l('training_manual_audience_internal')],
                                        ['id' => 'customer_portal', 'name' => _l('training_manual_audience_customer_portal')],
                                    ], ['id', ['name']], _l('training_manual_audience'), $audience_selected);
                                ?>
                                <?php
                                    $content_kind_selected = isset($article) && !empty($article->content_kind) ? $article->content_kind : 'article';
                                    echo render_select('content_kind', [
                                        ['id' => 'article', 'name' => _l('training_manual_content_kind_article')],
                                        ['id' => 'video', 'name' => _l('training_manual_content_kind_video')],
                                    ], ['id', ['name']], _l('training_manual_content_kind'), $content_kind_selected);
                                ?>
                                <p class="text-muted small"><i class="fa fa-info-circle"></i> <?php echo _l('training_manual_customer_videos_help'); ?></p>

                                <?php $thumb_value = (isset($article) && isset($article->thumbnail) ? $article->thumbnail : ''); ?>
                                <div class="form-group">
                                    <label class="control-label"><?php echo _l('training_manual_article_image'); ?></label>
                                    <input type="file" name="thumbnail_file" class="form-control" accept="image/*">
                                    <?php if($thumb_value != ''){ ?>
                                        <div class="training-thumb-preview">
                                            <img src="<?php echo base_url('uploads/training_manual/' . $thumb_value); ?>" alt="<?php echo _l('article_thumbnail'); ?>">
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="refresh_short_link" id="refresh_short_link">
                                    <label for="refresh_short_link"><?php echo _l('short_public_link'); ?> - rebuild from title</label>
                                </div>

                            </div>
                           
                            <div class="col-md-4">
                                <?php $value = (isset($article) ? $article->description : ''); ?>
                                <?php echo render_textarea('description', 'training_manual_description', $value,['rows' => 6]); ?>
                                <?php $value = (isset($article) ? $article->content : ''); ?>
                            </div>

                            <div class="col-md-4">
                                <?php if(isset($article)){ ?>
                                    <div class="checkbox checkbox-primary">
                                        <small><?php echo _l('get_link_help'); ?></small><br>
                                        <input type="checkbox" name="is_publish" id="is_publish" <?php if(isset($article)){if($article->is_publish == 1){echo 'checked';} } else {echo 'checked';} ?>>
                                        <label for="is_publish"><?php echo _l('get_link'); ?></label>
                                    </div>
                                    <div class="training_manual-input-slug-wrap">
                                        <?php $tmp_publish_link = site_url('training_manual/' . (!empty($article->short_code) ? $article->short_code : $article->slug)); ?>
                                        <p>
                                            <a href="<?php echo $tmp_publish_link; ?>" target="_blank"><?php echo $tmp_publish_link; ?></a>
                                            <span class="training_manual-btn-copy" data-copy="<?php echo $tmp_publish_link; ?>" data-lang="<?php echo _l('training_manual_copy_success'); ?>"><button type="button" class="btn btn-default btn-sm"><i class="fa fa-copy"></i></button></span>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div>
                                            <input type="hidden" name="type" value="document">
                                        </div>
                                    </div>
                                    </div>
                            </div>
                        </div>
                        
                        <div class="training_manual-article-type-wrap <?php echo $selected_type == 'document' ? '' : 'hide' ?>" data-type="document">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="training-manual-editor-topbar">
                                        <div>
                                            <strong><?php echo _l('training_manual_content'); ?></strong>: <span class="text-warning">(<?php echo _l('article_help_menu'); ?>)</span><br>
                                            <small><?php echo _l('tinymce-help-article'); ?></small>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-default btn-xs training-manual-editor-undo"><i class="fa fa-undo"></i> Undo</button>
                                            <button type="button" class="btn btn-default btn-xs training-manual-editor-redo"><i class="fa fa-repeat"></i> Redo</button>
                                        </div>
                                    </div>
                                    <?php $value = (isset($article) ? $article->content : ''); ?>
                                    <?php echo render_textarea('content','',$value,array(),array(),'','tinymce-content'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
                            <a href="<?php  echo isset($back_url) ? $back_url : admin_url('training_manual/articles'); ?>" class="btn btn-default"><?php echo _l('back'); ?></a>
                            <?php if(isset($article)){ ?> 
                            <a href="<?php echo admin_url('training_manual/articles/show/' . $article->id) ?>" class="btn btn-success"><?php echo _l('view'); ?></a>
                            <?php } ?>
                            <?php if(isset($article) && has_permission('training_manual_articles','','delete')){ ?>
                                <a href="<?php echo admin_url('training_manual/articles/delete/' . $article->id); ?>" class="btn btn-danger btn-remove" data-lang="<?php echo _l('training_manual_confirm_delete'); ?>"><?php echo _l('delete'); ?></a>
                            <?php } ?>
                            <button type="submit" name="submit" value="ONLY_SAVE" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                         </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/js/article.js'); ?>"></script>
</body>
</html>
