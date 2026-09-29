<link rel="stylesheet" id="color-opt" href="<?= module_dir_url(OPPORTUNITY_MODULE) ?>assets/css/style.css">
<?php


$propability = 0;

$all_stages = get_opportunity_order_by(db_prefix() .'opportunity_stages', array('pipeline_id' => $opportunity_details->pipeline_id), 'stage_order', 'asc');

// total stages
if (!empty($all_stages)) {
    $total_stages = count($all_stages);
    foreach ($all_stages as $stage) {
        $res = round(100 / $total_stages);
        $propability += $res;
        if ($stage->stage_id == $opportunity_details->stage_id) {
            break;
        }
    }
}
if ($opportunity_details->status === 'won') {
    $propability = 100;
}
if ($opportunity_details->status === 'lost') {
    $propability = 0;
}


$outputStatus = '';

$outputStatus .= '<span class="" task-status-table="' . $opportunity_details->full_name . '">';

$outputStatus .= $opportunity_details->full_name;


$outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
$outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskStatus-' . $opportunity_details->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
$outputStatus .= '<span data-toggle="tooltip" title="' . _l('change_opportunity_owner') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
$outputStatus .= '</a>';

$outputStatus .= '<ul class="dropdown-menu dropdown-menu-right">';
foreach ($staff as $assignee) {
    if ($opportunity_details->default_opportunity_owner != $assignee['staffid']) {
        $outputStatus .= '<li>
                  <a href="#" onclick="change_opportunity_owner(' . $assignee['staffid'] . ',' . $opportunity_details->id . '); return false;">
                     ' . $assignee['full_name'] . '
                  </a>
               </li>';
    }
}
$outputStatus .= '<li>
                  <a href="#" onclick="change_opportunity_owner(0,' . $opportunity_details->id . '); return false;">
                     ' . _l('no_owner') . '
                  </a>
               </li>';

$outputStatus .= '</ul>';
$outputStatus .= '</div>';


$outputStatus .= '</span>';


$stage = '';

$stage .= '<span class="">';

$stage .= (!empty($opportunity_details->pipeline_name) ? $opportunity_details->pipeline_name : '-') . ' <i class="fa fa-angle-right"></i> ' . (!empty($opportunity_details->stage_name) ? $opportunity_details->stage_name : '');


$stage .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
$stage .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskStatus-' . $opportunity_details->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
$stage .= '<span data-toggle="tooltip" title="' . _l('change_opportunity_owner') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
$stage .= '</a>';

$stage .= '<ul class="dropdown-menu width300" >';
if (!empty($all_stages)) {
    foreach ($all_stages as $key => $vstage) {
        $stage .= '<li
                    ' . ($opportunity_details->stage_id == $vstage->stage_id ? 'class="active"' : '') . '
>
                  <a href="' . base_url('admin/opportunity/changeStage/' . $opportunity_details->id . '/' . $vstage->stage_id) . '">
                     ' . $vstage->stage_name . '
                  </a>
               </li>';

    }
}
$stage .= '</ul>';
$stage .= '</div>';


$stage .= '</span>';

?>

<div class="tw-relative">
    <div class="tw-rounded-lg tw-border tw-border-neutral-200 tw-bg-white tw-shadow-sm tw-dark:border-neutral-700 tw-dark:bg-neutral-900">
        <div class="tw-bg-white tw-px-3 tw-py-4 dark:tw-bg-neutral-900 sm:tw-p-6">
            <div class="tw-flex tw-grow tw-flex-col ">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="left-side">
                            <div class="distribution-content">
                                <h3 class="distribution mb-0"><?= $opportunity_details->title ?>
                                    <span class="products"><?=
                                        total_rows(db_prefix() .'opportunity_items', array('opportunity_id' => $id)) . ' ' . _l('products') ?></span>
                                </h3>
                                <?= $stage ?>
                                <p><?= _l('created') . ' ' . _l('at') . ':' . date('F j, Y', strtotime($opportunity_details->created_at)) . ' ' . opportunity_display_time($opportunity_details->created_at); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="right-side">
                            <div class="inside-con">
                                <div class="inline ">
                                    <div class="easypiechart text-success" data-percent="<?= $propability ?>"
                                         data-line-width="5"
                                         data-track-Color="#f0f0f0" data-bar-color="#<?php
                                    if ($propability == 100) {
                                        echo '8ec165';
                                    } elseif ($propability >= 40 && $propability <= 50) {
                                        echo '5d9cec';
                                    } elseif ($propability >= 51 && $propability <= 99) {
                                        echo '7266ba';
                                    } else {
                                        echo 'fb6b5b';
                                    }
                                    ?>" data-rotate="270" data-scale-Color="false" data-size="50" data-animate="2000">
                                        <span class="small "><?= $propability ?>%</span>
                                    </div>
                                </div>


                                <?php
                                echo $outputStatus;
                                ?>
                                <div>
                                    <?php
                                    if ($opportunity_details->status == 'won' || $opportunity_details->status == 'lost') {
                                        ?>
                                        <a href="<?= base_url('admin/opportunity/changedStatus/' . $id . '/open') ?>"
                                           class="btn btn-warning btn-sm rounded px-5 mr-2 ">
                                            <i class="fa fa-repeat"></i>
                                            <?= _l('reopen') ?></a>

                                        <?php
                                    }
                                    if ($opportunity_details->status == 'open' || $opportunity_details->status == 'lost') {
                                        ?>
                                        <a data-toggle="modal" data-target="#myModal"
                                           href="<?= base_url('admin/opportunity/changeStatus/' . $id . '/won') ?>"
                                           class="btn btn-success btn-sm rounded px-5 mr-2 ">
                                            <i class="fa fa-check"></i>
                                            <?= _l('won') ?></a>
                                        <?php
                                    }
                                    if ($opportunity_details->status == 'open' || $opportunity_details->status == 'won') {
                                        ?>
                                        <a data-toggle="modal" data-target="#myModal"
                                           href="<?= base_url('admin/opportunity/changeStatus/' . $id . '/lost') ?>"
                                           class="btn btn-danger btn-sm rounded px-5 mr-2">
                                            <i class="fa fa-times"></i>
                                            <?= _l('lost') ?></a>
                                    <?php } ?>
                                    <?php if (has_permission('tasks', '', 'create')) { ?>
                                        <a href="#"
                                           onclick="new_task_from_relation(undefined,'opportunity',<?php echo $opportunity_details->id; ?>); return false;"
                                           class="btn btn-primary btn-sm">
                                            <i class="fa-regular fa-plus tw-mr-1"></i>
                                            <?php echo _l('new_task'); ?>
                                        </a>
                                    <?php } ?>
                                </div>


                            </div>


                        </div>
                    </div>
                </div>
            </div>
            <div class="tw-mt-5">
                <ul class="progress-custom">
                    <?php
                    // bg-none-color color-other
                    $active_stage = 0;
                    $class = '';
                    if (!empty($all_stages)) {
                        $active_stage_key = array_search($opportunity_details->stage_id, array_column($all_stages, 'stage_id'));

                        for ($skey = 0; $skey <= $active_stage_key; $skey++) {
                            $all_stages[$skey]->active = true;
                        }
                        $nextStage = $active_stage_key + 1;
                        if (!empty($all_stages[$nextStage])) {
                            $all_stages[$nextStage]->next = true;
                        }
                        // if status == 'won'

                        if ($opportunity_details->status == 'won') {
                            $dstatus = 'active';
                        } elseif ($opportunity_details->status == 'lost') {
                            $dstatus = 'lost';
                        }
                        if (!empty($dstatus)) {
                            foreach ($all_stages as $stage) {
                                $stage->$dstatus = true;
                            }
                        }
                    }
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg"
                                                                           fill="none"
                                                                           viewBox="0 0 24 24" width="1.75rem"
                                                                           height="1.75rem" stroke-width="1.5"
                                                                           stroke="currentColor" aria-hidden="true"
                                                                           class="h-6 w-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4.5 12.75l6 6 9-13.5"></path>
                                    
                                </svg>';
                    if (!empty($all_stages)) {
                        foreach ($all_stages as $key => $stage) {
                            if (!empty($stage->lost)) {
                                $class = 'bg-lost-color';
                                $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-6 w-6 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>';
                            } else if (!empty($stage->active)) {
                                $class = '';
                            } elseif (!empty($stage->next)) {
                                $class = 'bg-none-color';
                            } else {
                                $class = 'bg-none-color color-other';
                                $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-5 w-5 text-neutral-500 dark:text-neutral-100"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>';
                            }
                            ?>
                            <li>
                                <a href="<?= base_url('admin/opportunity/changeStage/' . $id . '/' . $stage->stage_id) ?>"
                                   class="process-svg <?= $class ?>">

                                <span><span class="rounded-svg rounded-full ">
                                        <?= $icon ?>
                                    </span></span>
                                    <span class="svg-title tw-mt-2"><?= $stage->stage_name ?></span>
                                </a>
                                <div class="svg-stock" aria-hidden="true">
                                    <svg class="h-full w-full text-neutral-300 dark:text-neutral-600" width="1.75rem"
                                         height="3.3rem" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                                        <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke"
                                              stroke="currentcolor"
                                              stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    ?>

                </ul>
            </div>
        </div>
    </div>
</div>
<?php
if (!empty($opportunity_details->rel_id) && !empty($opportunity_details->rel_type)) {
    $task_rel_data = get_relation_data($opportunity_details->rel_type, $opportunity_details->rel_id);
    $task_rel_value = get_relation_values($task_rel_data, $opportunity_details->rel_type);
    echo '<br />' . _l('task_related_to') . ' ' . _l($opportunity_details->rel_type) . ': <a class="text-muted" href="' . $task_rel_value['link'] . '">' . $task_rel_value['name'] . '</a>';
}
?>
<script src="<?= module_dir_url(OPPORTUNITY_MODULE) ?>assets/easypiechart/jquery.easy-pie-chart.js"></script>
<script type="text/javascript">
    'use strict';

    function change_opportunity_owner(staffId, opportunityId) {
        $("body").append('<div class="dt-loader"></div>');
        requestGetJSON('<?= admin_url('opportunity/change_opportunity_owner') ?>' + '/' + staffId + '/' + opportunityId).done(function (response) {
            $("body").find(".dt-loader").remove();
            if (response.success === true || response.success == "true" || response.success == 1) {
                alert_float('success', response.message);
                window.location.reload();
            } else {
                alert_float('danger', response.message);
            }
        });
    }

    $('.easypiechart').each(function () {
        var $this = $(this), $data = $this.data(), $step = $this.find('.step'),
            $target_value = parseInt($($data.target).text()), $value = 0;
        $data.barColor || ($data.barColor = function ($percent) {
            $percent /= 100;
            return "rgb(" + Math.round(200 * $percent) + ", 200, " + Math.round(200 * (1 - $percent)) + ")";
        });
        $data.onStep = function (value) {
            $value = value;
            $step.text(parseInt(value));
            $data.target && $($data.target).text(parseInt(value) + $target_value);
        }
        $data.onStop = function () {
            $target_value = parseInt($($data.target).text());
            $data.update && setTimeout(function () {
                $this.data('easyPieChart').update(100 - $value);
            }, $data.update);
        }
        $(this).easyPieChart($data);
    });
</script>