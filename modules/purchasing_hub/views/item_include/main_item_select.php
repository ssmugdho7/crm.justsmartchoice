<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$crmSalesItems = [];
if (isset($CI->db) && $CI->db->table_exists(db_prefix().'items')) {
    $crmSalesItems = $CI->db->select('id, description, long_description, rate')
        ->from(db_prefix().'items')
        ->order_by('description', 'ASC')
        ->limit(500)
        ->get()->result_array();
}
?>
<div class="form-group mbot25 select-placeholder smart-choice-purchase-item-select">
    <select name="item_select" class="selectpicker no-margin<?php if(isset($ajaxItems) && $ajaxItems == true){echo ' ajax-search';} ?>" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('select_item'); ?>" data-live-search="true">
        <option value=""></option>
        <?php if (!empty($crmSalesItems)) { ?>
        <optgroup data-group-id="crm_sales_items" label="CRM Sales Items / Invoice Items">
            <?php foreach($crmSalesItems as $item){ ?>
            <option value="crm_item_<?php echo (int)$item['id']; ?>" data-subtext="<?php echo html_escape(strip_tags(mb_substr($item['long_description'] ?? '',0,200))).'...'; ?>">(<?php echo app_format_number($item['rate']); ?>) <?php echo html_escape($item['description']); ?></option>
            <?php } ?>
        </optgroup>
        <?php } ?>
        <?php if(isset($items) && is_array($items)) { foreach($items as $group_id=>$_items){ if(empty($_items)){continue;} ?>
        <optgroup data-group-id="<?php echo html_escape($group_id); ?>" label="<?php echo html_escape($_items[0]['group_name'] ?? 'Purchasing Items'); ?>">
            <?php foreach($_items as $item){ ?>
            <option value="<?php echo (int)$item['id']; ?>" data-subtext="<?php echo html_escape(strip_tags(mb_substr($item['long_description'] ?? '',0,200))).'...'; ?>">(<?php echo app_format_number($item['purchase_price'] ?? 0); ?>) <?php echo html_escape($item['description'] ?? ''); ?></option>
            <?php } ?>
        </optgroup>
        <?php }} ?>
    </select>
</div>
