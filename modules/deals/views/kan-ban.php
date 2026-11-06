<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_admin = is_admin();
$i = 0;
$total_value = 0;
foreach ($stages as $stage) {
    $kanBan = new \modules\deals\libraries\DealsKanban($stage->stage_id);
    $kanBan->search($this->input->get('search'))
        ->sortBy($this->input->get('sort_by'), $this->input->get('sort'));
    if ($this->input->get('refresh')) {
        $kanBan->refresh($this->input->get('refresh')[$stage->stage_id] ?? null);
    }
    $deals = $kanBan->get();
    // Full stage totals use the same ownership scope, not only the first paginated cards.
    $this->db->select('currency,SUM(deal_value) AS amount', false)->where('stage_id', $stage->stage_id)->group_by('currency');
    if (!has_permission('deals', '', 'view')) $this->db->where('default_deal_owner', get_staff_user_id());
    $stage_totals = $this->db->get('tbl_deals')->result_array();
    $total_value = 0;
    $total_deals = count($deals);
    $total_pages = $kanBan->totalPages();

    $settings = '';
    foreach (get_system_favourite_colors() as $color) {
        $color_selected_class = 'cpicker-small';
        $settings .= "<div class='kanban-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ';border:1px solid ' . $color . "'></div>";
    } ?>
    <ul class="kan-ban-col" data-col-status-id="<?php echo $stage->stage_id; ?>"
        data-total-pages="<?php echo $total_pages; ?>"
        data-total="<?php echo $total_deals; ?>">
        <li class="kan-ban-col-wrapper">
            <div class="border-right panel_s">
                <?php
                $status_color = '';
                if (!empty($status['color'])) {
                    $status_color = 'style="background:' . $status['color'] . ';border:1px solid ' . $status['color'] . '"';
                } ?>
                <div class="panel-heading tw-bg-neutral-700 tw-text-white"

                    <?php echo $status_color; ?> data-status-id="<?php echo $stage->stage_id; ?>">
                    <i class="fa fa-reorder pointer"></i>
                    <span class="heading pointer tw-ml-1" <?php if ($is_admin) { ?>
                        data-order="<?php echo $stage->stage_order; ?>"
                        data-name="<?php echo html_escape($stage->stage_name); ?>"
                    <?php } ?>><?php echo html_escape($stage->stage_name); ?>
                </span>
                    <?php foreach ($stage_totals as $stage_total) { echo html_escape(app_format_money($stage_total['amount'], $stage_total['currency'])) . '<br>'; } ?> - <small><?php echo $total_deals . ' ' . _l('deals'); ?></small>

                </div>
                <div class="kan-ban-content-wrapper">
                    <div class="kan-ban-content">
                        <ul class="status leads-status sortable" data-lead-status-id="<?php echo $stage->stage_id; ?>">
                            <?php
                            foreach ($deals as $deal) {
                                $this->load->view('_kan_ban_card', ['deal' => $deal, 'stage' => $stage]);
                            } ?>
                            <?php if ($total_deals > 0) { ?>
                                <li class="text-center not-sortable kanban-load-more"
                                    data-load-status="<?php echo $stage->stage_id; ?>">
                                    <a href="#"
                                       class="btn btn-default btn-block<?php if ($total_pages <= 1 || $kanBan->getPage() === $total_pages) {
                                           echo ' disabled';
                                       } ?>" data-page="<?php echo $kanBan->getPage(); ?>"
                                       onclick="kanban_load_more(<?php echo $stage->stage_id; ?>, this, 'deals/deals_kanban_load_more', 315, 360); return false;"
                                       ;>
                                        <?php echo _l('load_more'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="text-center not-sortable mtop30 kanban-empty<?php if ($total_deals > 0) {
                                echo ' hide';
                            } ?>">
                                <h4>
                                    <i class="fa-solid fa-circle-notch" aria-hidden="true"></i><br/><br/>
                                    <?php echo _l('no_leads_found'); ?>
                                </h4>
                            </li>
                        </ul>
                    </div>
                </div>
        </li>
    </ul>
    <?php $i++;
} ?>
