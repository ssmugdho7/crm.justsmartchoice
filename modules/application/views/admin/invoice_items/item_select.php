<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div
    class="form-group mbot25 items-wrapper select-placeholder<?= staff_can('create', 'items') ? ' input-group-select' : ''; ?>">
    <div
        class="<?= staff_can('create', 'items') ? 'input-group input-group-select' : ''; ?>">
        <div class="items-select-wrapper">
            <select name="item_select"
                class="selectpicker no-margin<?= $ajaxItems == true ? ' ajax-search' : ''; ?><?= staff_can('create', 'items') ? ' _select_input_group' : ''; ?>"
                data-width="false" id="item_select"
                data-none-selected-text="Select item or start typing"
                data-live-search="true">
                
                <?php foreach ($items as $group_id => $_items) { ?>
                <optgroup data-group-id="<?= e($group_id); ?>"
                    label="<?= $_items[0]['group_name']; ?>">
                    <?php foreach ($_items as $item) { ?>
                    <?php $sc_item_group = strtolower($_items[0]['group_name'] ?? ''); $sc_item_badge = (strpos($sc_item_group, 'service') !== false) ? 'Service' : 'Product'; $sc_item_color = $sc_item_badge === 'Service' ? '#fff2e8' : '#eaf7ff'; ?>
                    <option class="smart-choice-item-option"
                        value="<?= e($item['id']); ?>"
                        data-content="<span style='display:inline-block;padding:2px 6px;border-radius:10px;background:<?= $sc_item_color; ?>;margin-right:6px;'><?= $sc_item_badge; ?></span> (<?= e(app_format_number($item['rate'])); ?>) <?= e($item['description']); ?>"
                        data-subtext="<?= strip_tags(mb_substr($item['long_description'], 0, 200)) . '...'; ?>">
                        (<?= e(app_format_number($item['rate'])); ?>)
                        <?= e($item['description']); ?>
                    </option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>
        </div>
        <?php if (staff_can('create', 'items')) { ?>
        <div class="input-group-btn">
            <a href="#" data-toggle="modal" class="btn btn-default" data-target="#sales_item_modal">
                <i class="fa fa-plus"></i>
            </a>
        </div>
        <?php } ?>
    </div>
</div>