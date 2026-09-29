<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between">
                    <h4 class="tw-mb-2 tw-font-semibold tw-text-lg">
                        <?php echo tourGuideHelper()->translate('tour_guide'); ?></h4>
                    <div>
                        <a href="<?php echo tourGuideHelper()->adminUrl('form'); ?>" class="btn btn-primary mb-2">
                            <?php echo tourGuideHelper()->translate('tour_guide_create_tour'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php

                        // Let hide priority
                        \TourGuide\TourGuideHook::addFilter('tour_guide_table_columns', function ($columns) {
                            unset($columns['priority']);
                            return $columns;
                        });

                        // Use custom setup action
                        \TourGuide\TourGuideHook::addFilter('tour_guide_table_actions', function ($actions, $id) {
                            $setupUrl = tourGuideHelper()->adminUrl('setup/' . $id);
                            $actions['setup'] = sprintf(
                                '<div class="tour-guide-dropdown">
                                    <a href="#" data-title="%s" data-toggle="tooltip" class="tour-guide-dropbtn btn-icon"><i class="tour-guide-icon tour-guide-icon-cogs"></i></a>
                                    <div class="tour-guide-dropdown-content">
                                        <a href="%s">' . _l('tour_guide_admin_portal') . '</a>
                                        <a href="%s">' . _l('tour_guide_staff_portal') . '</a>
                                        <a href="%s">' . _l('tour_guide_client_portal') . '</a>
                                    </div>
                                </div>',
                                tourGuideHelper()->translate('tour_guide_setup'),
                                $setupUrl . '?portal=admin',
                                $setupUrl . '?portal=staff',
                                $setupUrl . '?portal=client',
                            );
                            return $actions;
                        });

                        echo \TourGuide\TourGuideUIHelper::renderTable($tour_guides, 0, 'desc', true);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
"use strict";
$(document).ready(function() {
    const $dropdown = $('.tour-guide-dropdown');
    const $tableResponsive = $('.table-responsive');
    // Bind hover to change overflow-x
    $dropdown.on('mouseenter', function() {
        $tableResponsive.css('overflow-x', 'visible'); // Change overflow to visible
    });

    $dropdown.on('mouseleave', function() {
        $tableResponsive.css('overflow-x', 'auto'); // Revert back to auto
    });
});
</script>