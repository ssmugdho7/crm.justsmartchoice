<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_customers_portal_head'); ?>

<div class="col-md-12">
	<h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 section-heading section-heading-invoices">
		<?php echo e($title); ?>
	</h4>
	<div class="panel_s">
		<div class="panel-body">
			<table class="table dt-table table-requests" data-order-col="1" data-order-type="desc">
				<thead>
					<tr>
						<th class="th-invoice-number"><?php echo _l('wshop_image'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_code'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_name'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_serial_no'); ?></th>
						<th class="th-invoice-number"><?php echo _l('client'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_model'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_category'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_manufacturer'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_purchase_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_last_maintenance_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_next_maintenance_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_warranty_period_months'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_warranty_expiry_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_warranty_status'); ?></th>
						<th class="th-invoice-number"><?php echo _l('options'); ?></th>
						
					</tr>
				</thead>
				<tbody>
					<?php foreach($devices as $device){ ?>
						<?php 
						$CI = &get_instance();

						if ($device['primary_profile_image'] != '' && file_exists(MAIN_IMAGE_DEVICES_IMAGES_FOLDER.$device['id'].'/'.$device['primary_profile_image'])) {
							$device_image = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/uploads/main_image_devices/'.$device['id'].'/'.$device['primary_profile_image']) . '" alt="'.$device['primary_profile_image'].'" >';
						}else{
							$device_image = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/assets/images/upload-image-icon.png') . '" >';
						}
						$category_name = '';
						$manufacturer_name = '';
						$model = wshop_get_model($device['model_id']);
						if($model){
							$category_name = wshop_get_category_name($model->category_id);
							$manufacturer_name = wshop_get_manufacturer_name($model->manufacturer_id);
						}

						$warranty_status = '---';
						if($device['warranty_expiry_date'] != null){
							if(strtotime($device['warranty_expiry_date']) > strtotime(date('Y-m-d'))){
								$warranty_status = '<span class="label label-success">'._l('wshop_being_under_warranty').'</span>';
							}else{
								$warranty_status = '<span class="label label-warning">'._l('wshop_out_of_warranty').'</span>';
							}
						}
						?>
						<tr>
							<td data-order="<?php echo new_html_entity_decode($device['id']); ?>"><?php echo html_entity_decode($device_image) ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['code']); ?>"><?php echo new_html_entity_decode(($device['code'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['name']); ?>"><a href="<?php echo site_url('workshop/client/device_detail/'.$device['id']) ?>"><?php echo new_html_entity_decode(($device['name'])); ?></a></td>
							<td data-order="<?php echo new_html_entity_decode($device['serial_no']); ?>"><?php echo new_html_entity_decode(($device['serial_no'])); ?></td>
							<td data-order="<?php echo get_company_name($device['client_id']); ?>"><?php echo new_html_entity_decode(get_company_name($device['client_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['model_id']); ?>"><?php echo new_html_entity_decode(wshop_get_model_name($device['model_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['model_id']); ?>"><?php echo new_html_entity_decode($category_name); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['model_id']); ?>"><?php echo new_html_entity_decode($manufacturer_name); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['purchase_date']); ?>"><?php echo  _dt($device['purchase_date']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['purchase_date']); ?>"><?php echo html_entity_decode(_d($device['last_maintenance'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['purchase_date']); ?>"><?php echo html_entity_decode(_d($device['next_maintenance'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['warranty_period_months']); ?>"><?php echo new_html_entity_decode($device['warranty_period_months']).' '._l('wshop_months') ; ?></td>
							<td data-order="<?php echo new_html_entity_decode($device['warranty_expiry_date']); ?>"><?php echo _d($device['warranty_expiry_date']); ?></td>

							<td data-order="<?php echo new_html_entity_decode($device['warranty_expiry_date']); ?>" ><?php echo new_html_entity_decode($warranty_status); ?></td>

							<?php 
							$option = '';
							$option .='<a href="'. site_url('workshop/client/device_detail/'.$device['id']).'" class="btn btn-default btn-icon mright5" data-toggle="tooltip" data-title="'._l('view').'" data-placement="bottom"><i class="fa-regular fa-eye"></i></a>';

							$_data = $option;
							?>
							<td data-order="<?php echo new_html_entity_decode($device['id']); ?>"><?php echo new_html_entity_decode($option); ?></td>

						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php workshop_client_init_tail(); ?>