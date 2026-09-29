<div class="widget" id="widget-<?php echo create_widget_id('flexiblekit'); ?>">
<div class="row">
<div class="col-md-12">
    <div class="widget" id="widget-flexblekit_activity" data-name="<?php echo _l('flexiblekit') ?>">
        <div class="flexiblekit-activity">
            <div class="panel_s" id="flexiblekit_activity">
                <div class="panel-body padding-10">
                    <div class="widget-dragger"></div>
                    <div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
                        <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                            <span class="tw-text-neutral-700 tw-font-semibold">
                                <?php echo _l('flexiblekit') ?>
                            </span>
                        </p>
                    </div>
                    <hr class="-tw-mx-3 tw-mt-2 tw-mb-4">
                    <!-- <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs" role="tablist">
                    <li class="active">
                        <a data-group="" href="<?php //echo $menu['href'] ?>">
                            <i class="<?php //echo $menu['icon'] ?> menu-icon" aria-hidden="true"></i>
                            <?php echo $menu['name']; ?>
                        </a>
                    </li>
                </ul> -->

                    <ul class="nav nav-tabs nav-justified">
                        <li role="presentation" class="active">
                            <a href="#my_activity" data-toggle="tab"><?php echo _l('flexiblekit_my') ?></a </li>
                        <li role="presentation">
                            <a href="#team_activity" data-toggle="tab"><?php echo _l('flexiblekit_team') ?></a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="my_activity" class="tab-pane active">
                            <div class="row tw-m-0">
                                <div class="col-md-12">
                                    <h4 class="text-center tw-font-semibold tw-bg-neutral-200 tw-py-5">
                                        <?php echo _l('flexiblekit_next_kit_activity') ?>
                                    </h4>
                                    <?php $next_schedule = flexiblekit_get_next_schedule(); ?>

                                    <?php if ($next_schedule) { ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <i class="fa <?php echo flexiblekit_get_type_icon($next_schedule['flexibleschedule_type']) ?> fa-6x text-center display-block tw-p-6"></i>
                                            </div>
                                            <div class="col-md-8">
                                                <h4 class="text-info">
                                                    <a href="<?php echo flexiblekit_schedules_url($next_schedule['id']).'?from=widget' ?>"><?php echo $next_schedule['flexibleschedule_subject'] ?> </a>
                                                </h4>
                                                <div>
                                                    <span class="badge bg-warning">
                                                        <?php echo flexiblekit_date_for_humans($next_schedule['flexibleschedule_start_datetime']) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="tw-font-semibold">
                                                        <?php echo _l('flexiblekit_due_date') ?>
                                                    </span>

                                                    <?php echo $next_schedule['flexibleschedule_start_datetime'] ?>
                                                </div>
                                                <div>
                                                    <span class="tw-font-semibold">
                                                        <?php echo _l('flexiblekit_status') ?>
                                                    </span>

                                                    <?php echo ucfirst($next_schedule['flexibleschedule_status']) ?>
                                                </div>
                                                <div>
                                                    <span class="tw-font-semibold">
                                                       <?php echo _l('flexiblekit_frequency') ?>
                                                    </span>

                                                    <span class="text-info">
                                                        <?php echo $next_schedule['flexibleschedule_name'] ?>
                                                    </span>
                                                    (<?php echo $next_schedule['flexibleschedule_interval_label'] ?>)
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="col-md-12">
                                            <?php echo _l('flexiblekit_no_next_activities') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-12">
                                    <h4 class="text-center tw-font-semibold tw-bg-neutral-200 tw-py-5">
                                        <?php echo _l('flexiblekit_previous_kit_activity') ?>
                                    </h4>
                                    <?php $previous_schedules = flexiblekit_get_previous_schedules(); ?>

                                    <?php if (count($previous_schedules) > 0) { ?>
                                        <?php foreach($previous_schedules as $previous_schedule){ ?>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <i class="fa <?php echo flexiblekit_get_type_icon($previous_schedule['flexibleschedule_type']) ?> fa-6x text-center display-block tw-p-6"></i>
                                                </div>
                                                <div class="col-md-8">
                                                    <h4 class="text-info">
                                                        <a href="<?php echo flexiblekit_schedules_url($previous_schedule['id']).'?from=widget' ?>"><?php echo $previous_schedule['flexibleschedule_subject'] ?></a>
                                                    </h4>
                                                    <div>
                                                        <span class="badge bg-warning">
                                                            <?php echo flexiblekit_date_for_humans($previous_schedule['flexibleschedule_start_datetime']) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                            <?php echo _l('flexiblekit_due_date') ?>
                                                        </span>
    
                                                        <?php echo $previous_schedule['flexibleschedule_start_datetime'] ?>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                            <?php echo _l('flexiblekit_status') ?>
                                                        </span>
    
                                                        <?php echo ucfirst($previous_schedule['flexibleschedule_status']) ?>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                           <?php echo _l('flexiblekit_frequency') ?>
                                                        </span>
    
                                                        <span class="text-info">
                                                            <?php echo $previous_schedule['flexibleschedule_name'] ?>
                                                        </span>
                                                        (<?php echo $previous_schedule['flexibleschedule_interval_label'] ?>)
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="col-md-12">
                                            <?php echo _l('flexiblekit_no_previous_activities') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div id="team_activity" class="tab-pane">
                            <div class="row">
                            <div class="col-md-12">
                                    <h4 class="text-center tw-font-semibold tw-bg-neutral-200 tw-py-5">
                                        <?php echo _l('flexiblekit_team_kit_activity') ?>
                                    </h4>
                                    <?php $team_schedules = flexiblekit_get_team_schedules(); ?>

                                    <?php if (count($team_schedules) > 0) { ?>
                                        <?php foreach($team_schedules as $team_schedule){ ?>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <i class="fa <?php echo flexiblekit_get_type_icon($team_schedule['flexibleschedule_type']) ?> fa-6x text-center display-block tw-p-6"></i>
                                                </div>
                                                <div class="col-md-8">
                                                    <h4 class="text-info">
                                                        <?php echo $team_schedule['flexibleschedule_subject'] ?>
                                                    </h4>
                                                    <div>
                                                        <span class="badge bg-warning">
                                                            <?php echo flexiblekit_date_for_humans($team_schedule['flexibleschedule_start_datetime']) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                            <?php echo _l('flexiblekit_due_date') ?>
                                                        </span>
    
                                                        <?php echo $team_schedule['flexibleschedule_start_datetime'] ?>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                            <?php echo _l('flexiblekit_status') ?>
                                                        </span>
    
                                                        <?php echo ucfirst($team_schedule['flexibleschedule_status']) ?>
                                                    </div>
                                                    <div>
                                                        <span class="tw-font-semibold">
                                                            <?php echo _l('flexiblekit_frequency') ?>
                                                        </span>
    
                                                        <span class="text-info">
                                                            <?php echo $team_schedule['flexibleschedule_name'] ?>
                                                        </span>
                                                        (<?php echo $team_schedule['flexibleschedule_interval_label'] ?>)
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="col-md-12">
                                            <?php echo _l('flexiblekit_no_team_activities') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>