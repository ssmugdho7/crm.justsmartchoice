<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="scl-hero panel_s">
                    <div class="panel-body">
                        <div class="scl-hero-flex">
                            <div>
                                <h3><?php echo _l('smart_choice_links'); ?></h3>
                                <p>Organize your CRM pages, vendor tools, project links, reports, and admin shortcuts in one clean menu.</p>
                            </div>
                            <a href="#" onclick="smartChoiceLinksModal(0); return false;" class="btn btn-primary scl-add-link-btn">
                                <i class="fa fa-plus"></i> <?php echo _l('smart_choice_links_add'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="panel_s scl-links-panel">
                    <div class="panel-body">
                        <div class="scl-table-note">
                            <i class="fa fa-arrows"></i> Drag rows up or down to change the link order. Position numbers will update automatically.
                        </div>

                        <div class="table-responsive scl-table-wrap">
                            <table class="table table-condensed scl-links-table">
                                <thead>
                                    <tr>
                                        <th class="scl-drag-col"><?php echo _l('smart_choice_links_drag'); ?></th>
                                        <th><?php echo _l('smart_choice_links_title'); ?></th>
                                        <th><?php echo _l('smart_choice_links_category'); ?></th>
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
                                        ?>
                                        <tr draggable="true" data-id="<?php echo (int) $link->id; ?>">
                                            <td class="scl-drag-handle"><i class="fa fa-grip-vertical"></i></td>
                                            <td class="scl-title-cell">
                                                <i class="<?php echo html_escape($link->icon); ?>"></i>
                                                <span style="<?php echo $titleStyle; ?>"><?php echo html_escape($link->title); ?></span>
                                            </td>
                                            <td><?php echo html_escape($link->category); ?></td>
                                            <td class="scl-url-cell"><a href="<?php echo html_escape($link->url); ?>" target="_blank"><?php echo html_escape($link->url); ?></a></td>
                                            <td class="scl-position-value"><?php echo (int) $link->position; ?></td>
                                            <td><?php echo $link->is_active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
                                            <td class="scl-actions-cell">
                                                <a href="#" onclick="smartChoiceLinksModal(<?php echo (int) $link->id; ?>); return false;" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>
                                                <a href="<?php echo admin_url('smart_choice_links/delete/' . (int) $link->id); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="smart-choice-links-modal-wrapper" class="modal fade" tabindex="-1" role="dialog"></div>
            </div>
        </div>
    </div>
</div>
<script>
    window.smartChoiceLinksReorderEndpoint = 'smart_choice_links/reorder';
</script>
<?php init_tail(); ?>
</body>
</html>
