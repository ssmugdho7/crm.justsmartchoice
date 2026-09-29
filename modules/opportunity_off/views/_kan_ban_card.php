<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<?php if ($opportunity['stage_id'] == $stage->stage_id) { ?>
    <li data-lead-id="<?php echo $opportunity['id']; ?>"
        class="lead-kan-ban<?php if ($opportunity['default_opportunity_owner'] == get_staff_user_id()) {
            echo ' current-user-lead';
        } ?>">
        <div class="panel-body lead-body">
            <div class="row">
                <div class="col-md-12 lead-name">
                    <?php if ($opportunity['default_opportunity_owner'] != 0) { ?>
                        <a href="<?php echo admin_url('profile/' . $opportunity['default_opportunity_owner']); ?>"
                           data-placement="right"
                           data-toggle="tooltip" title="<?php echo get_staff_full_name($opportunity['default_opportunity_owner']); ?>"
                           class="pull-left mtop8 mright5">
                            <?php echo staff_profile_image($opportunity['default_opportunity_owner'], [
                                'staff-profile-image-xs',
                            ]); ?></a>
                    <?php } ?>
                    <a href="<?php echo admin_url('opportunity/details/' . $opportunity['id']); ?>"
                       class="pull-left">
                    <span
                            class="inline-block mtop10 mbot10">#<?php echo $opportunity['id'] . ' - ' . $opportunity['title']; ?></span>
                    </a>
                </div>
                <div class="col-md-12">
                    <div class="tw-flex">
                        <div class="tw-grow tw-mr-2">
                            <p class="tw-text-sm tw-mb-0">
                                <?php echo _l('leads_canban_source', $opportunity['source_name']); ?>
                            </p>
                            <p class="tw-text-sm tw-mb-0">
                                <?php echo _l('status') . ' : ' . _l($opportunity['status']); ?>
                            </p>
                            <?php $lead_value = $opportunity['opportunity_value'] != 0 ? app_format_money($opportunity['opportunity_value'], $base_currency->symbol) : '--'; ?>
                            <p class="tw-text-sm tw-mb-0">
                                <?php echo _l('leads_canban_lead_value', $lead_value); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <small class="text-dark"><?php echo _l('lead_created'); ?>: <span class="bold">
                                <span class="text-has-action" data-toggle="tooltip"
                                      data-title="<?php echo _dt($opportunity['created_at']); ?>">
                                    <?php echo time_ago($opportunity['created_at']); ?>
                                </span>
                            </span>
                            </small><br/>

                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('leads_canban_tasks', $opportunity['total_tasks']); ?>">
                                    <i class="fa-regular fa-circle-check menu-icon"></i> <?php echo $opportunity['total_tasks']; ?>
                            </span>
                            <span class="mtop5 inline-block text-muted" data-placement="left" data-toggle="tooltip"
                                  data-title="<?php echo _l('lead_kan_ban_attachments', $opportunity['total_files']); ?>">
                            <i class="fa fa-paperclip"></i>
                            <?php echo $opportunity['total_files']; ?>
                        </span>
                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('calls', $opportunity['total_calls']); ?>">
                                    <i class="fa-regular fa fa-tty"></i> <?php echo $opportunity['total_calls']; ?>
                            </span>
                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('mettings', $opportunity['total_mettings']); ?>">
                                    <i class="fa-regular fa fa-calendar-check"></i>
                                <?php echo $opportunity['total_mettings']; ?>
                            </span>
                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('comments', $opportunity['total_comments']); ?>">
                                    <i class="fa-regular fa fa-comments"></i>
                                <?php echo $opportunity['total_comments']; ?>
                            </span>
                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('emails', $opportunity['total_emails']); ?>">
                                    <i class="fa-regular fa fa-envelope"></i>
                                <?php echo $opportunity['total_emails']; ?>
                            </span>
                            <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip"
                                  data-placement="left"
                                  data-title="<?php echo _l('products', $opportunity['total_items']); ?>">
                                    <i class="fa-regular fa fa-shopping-cart"></i>
                                <?php echo $opportunity['total_items']; ?>
                            </span>

                        </div>
                    </div>
                </div>

                <?php if ($opportunity['tags']) { ?>
                    <div class="col-md-12">
                        <div class="kanban-tags tw-text-sm tw-inline-flex">
                            <?php echo render_tags($opportunity['tags']); ?>
                        </div>
                    </div>
                <?php } ?>
                <a href="#" class="pull-right text-muted kan-ban-expand-top"
                   onclick="slideToggle('#kan-ban-expand-<?php echo $opportunity['id']; ?>'); return false;">
                    <i class="fa fa-expand" aria-hidden="true"></i>
                </a>
                <div class="clearfix no-margin"></div>
                <div id="kan-ban-expand-<?php echo $opportunity['id']; ?>" class="padding-10" style="display:none;">
                    <div class="clearfix"></div>
                    <hr class="hr-10"/>
                    <p class="text-muted lead-field-heading"><?php echo _l('title'); ?></p>
                    <p class="bold tw-text-sm"><?php echo($opportunity['title'] != '' ? $opportunity['title'] : '-') ?></p>

                    <p class="text-muted lead-field-heading"><?php echo _l('close') . ' ' . _l('date'); ?></p>
                    <p class="bold tw-text-sm"><?php echo($opportunity['days_to_close'] != '' ? _d($opportunity['days_to_close']) : '-') ?></p>

                    <p class="text-muted lead-field-heading"><?php echo _l('opportunity_owner'); ?></p>
                    <p class="bold tw-text-sm"><?php echo($opportunity['default_opportunity_owner'] != '' ? get_staff_full_name($opportunity['default_opportunity_owner']) : '-') ?></p>

                    <p class="text-muted lead-field-heading"><?php echo _l('clients'); ?></p>
                    <p class="bold tw-text-sm"> <?php
                        $client_name = json_decode($opportunity['client_id']);
                        if (!empty($client_name)) {
                            foreach ($client_name as $sl => $clientId) {
                                echo '<a class="tw-block tw-text-sm tw-text-gray-600 tw-underline hover:tw-no-underline"
                                 href="' . admin_url('clients/client/' . $clientId) . '">' . opportunity_client_name($clientId) . '</a>';
                            }
                        }
                        ?>
                    </p>
                    <p class="text-muted lead-field-heading"><?php echo _l('assigne'); ?></p>
                    <p class="bold tw-text-sm"> <?php
                        $user_id = json_decode($opportunity['user_id']);
                        if (!empty($user_id)) {
                            foreach ($user_id as $sl => $userId) {
                                echo '<a class="tw-block tw-text-sm tw-text-gray-600 tw-underline hover:tw-no-underline"
                                 href="' . admin_url('profile/' . $userId) . '">' . get_staff_full_name($userId) . '</a>';
                            }
                        }
                        ?>
                    </p>

                </div>
            </div>
        </div>
    </li>
<?php } ?>
