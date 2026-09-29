<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content smart-choice-links-page">
        <div class="row">
            <div class="col-md-12">
                <div class="scl-hero panel_s">
                    <div class="panel-body">
                        <div class="scl-hero-flex">
                            <div>
                                <h3><i class="fa fa-star"></i> <?php echo _l('smart_choice_links'); ?></h3>
                                <p><?php echo _l('smart_choice_links_description'); ?></p>
                            </div>
                            <div class="scl-hero-actions">
                                <a href="#" onclick="smartChoiceLinksModal(0); return false;" class="btn btn-primary btn-sm scl-add-link-btn">
                                    <i class="fa fa-plus"></i> <?php echo _l('smart_choice_links_add'); ?>
                                </a>
                                <a href="<?php echo admin_url('smart_choice_links/import_old_favorites'); ?>" class="btn btn-default btn-sm">
                                    <i class="fa fa-download"></i> <?php echo _l('smart_choice_links_import_old_favorites'); ?>
                                </a>
                                <a href="<?php echo admin_url('smart_choice_links/settings'); ?>" class="btn btn-default btn-sm">
                                    <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="scl-module-tabs">
                    <a href="<?php echo admin_url('smart_choice_links'); ?>" class="active"><i class="fa fa-list"></i> <?php echo _l('smart_choice_links_manage'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/settings'); ?>"><i class="fa fa-cog"></i> <?php echo _l('smart_choice_links_settings'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/help'); ?>"><i class="fa fa-question-circle"></i> <?php echo _l('smart_choice_links_how_to_use'); ?></a>
                    <a href="<?php echo admin_url('smart_choice_links/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('smart_choice_links_health_check'); ?></a>
                </div>

                <div class="panel_s scl-links-panel">
                    <div class="panel-body">
                        <div class="scl-toolbar">
                            <div class="scl-table-note">
                                <i class="fa fa-arrows"></i> <?php echo _l('smart_choice_links_drag_help'); ?>
                            </div>
                            <div class="scl-toolbar-right">
                                <input type="text" id="scl-table-search" class="form-control input-sm" placeholder="<?php echo _l('smart_choice_links_search_links'); ?>">
                                <button type="button" class="btn btn-default btn-sm" onclick="location.reload();"><i class="fa fa-sync"></i> <?php echo _l('smart_choice_links_reload'); ?></button>
                            </div>
                        </div>

                        <div class="table-responsive scl-table-wrap">
                            <table class="table table-condensed scl-links-table" id="scl-links-table">
                                <thead>
                                    <tr>
                                        <th class="scl-drag-col"><?php echo _l('smart_choice_links_drag'); ?></th>
                                        <th><?php echo _l('smart_choice_links_title'); ?></th>
                                        <th><?php echo _l('smart_choice_links_category'); ?></th>
                                        <th><?php echo _l('smart_choice_links_placement'); ?></th>
                                        <th><?php echo _l('smart_choice_links_url'); ?></th>
                                        <th class="scl-position-col"><?php echo _l('smart_choice_links_position'); ?></th>
                                        <th><?php echo _l('smart_choice_links_active'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="smart-choice-links-sortable">
                                    <?php foreach ($links as $link) { ?>
                                        <?php
                                        $textColor = !empty($link->text_color) ? $link->text_color : '';
                                        $shadow = !empty($link->text_shadow) ? 'text-shadow:0 1px 2px rgba(0,0,0,.35);' : '';
                                        $titleStyle = trim(($textColor ? 'color:' . html_escape($textColor) . ';' : '') . $shadow);
                                        $placement = isset($link->placement) ? $link->placement : 'both';
                                        ?>
                                        <tr draggable="true" data-id="<?php echo (int) $link->id; ?>">
                                            <td class="scl-drag-handle"><i class="fa fa-grip-vertical"></i></td>
                                            <td class="scl-title-cell">
                                                <i class="<?php echo html_escape($link->icon); ?>"></i>
                                                <span style="<?php echo $titleStyle; ?>"><?php echo html_escape($link->title); ?></span>
                                            </td>
                                            <td><?php echo html_escape($link->category); ?></td>
                                            <td><span class="label label-info"><?php echo _l('smart_choice_links_placement_' . $placement); ?></span></td>
                                            <td class="scl-url-cell"><a href="<?php echo html_escape(smart_choice_links_normalize_url($link->url)); ?>"><?php echo html_escape(smart_choice_links_url_for_edit($link->url)); ?></a></td>
                                            <td class="scl-position-value"><?php echo (int) $link->position; ?></td>
                                            <td><?php echo $link->is_active ? '<span class="label label-success">' . _l('smart_choice_links_active') . '</span>' : '<span class="label label-default">' . _l('smart_choice_links_inactive') . '</span>'; ?></td>
                                            <td class="scl-actions">
                                                <a href="#" onclick="smartChoiceLinksModal(<?php echo (int) $link->id; ?>); return false;" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                                                <a href="<?php echo admin_url('smart_choice_links/delete/' . (int) $link->id); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="scl-guide" class="panel_s scl-guide-panel">
                    <div class="panel-body">
                        <h4><i class="fa fa-question-circle"></i> <?php echo _l('smart_choice_links_how_to_use'); ?></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="scl-guide-card">
                                    <h5><?php echo _l('smart_choice_links_guide_what_it_does'); ?></h5>
                                    <p><?php echo _l('smart_choice_links_guide_what_it_does_text'); ?></p>
                                </div>
                                <div class="scl-guide-card">
                                    <h5><?php echo _l('smart_choice_links_guide_left_right'); ?></h5>
                                    <p><?php echo _l('smart_choice_links_guide_left_right_text'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="scl-guide-card">
                                    <h5><?php echo _l('smart_choice_links_guide_cleanup'); ?></h5>
                                    <p><?php echo _l('smart_choice_links_guide_cleanup_text'); ?></p>
                                </div>
                                <div class="scl-guide-card">
                                    <h5><?php echo _l('smart_choice_links_guide_best_links'); ?></h5>
                                    <p><?php echo _l('smart_choice_links_guide_best_links_text'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="smart-choice-links-modal-wrapper" class="modal fade" tabindex="-1" role="dialog"></div>
            </div>
        </div>
    </div>
</div>

<script>
window.smartChoiceLinksModalEndpoint = '<?php echo admin_url('smart_choice_links/modal/'); ?>';
window.smartChoiceLinksReorderEndpoint = '<?php echo admin_url('smart_choice_links/reorder'); ?>';
</script>
<?php init_tail(); ?>
