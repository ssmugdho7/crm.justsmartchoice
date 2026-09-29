<table class="table dt-table table-requests" data-order-col="1" data-order-type="desc">
	<thead>
		<tr>
			<th class="th-invoice-number"><?php echo _l('wshop_name'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_repair_job'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_Report_Type'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_Report_Status'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_mechanic'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_from_date'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_to_date'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_parts_information'); ?></th>
			<th class="th-invoice-number"><?php echo _l('wshop_notes'); ?></th>
			
		</tr>
	</thead>
	<tbody>
		<?php foreach($workshops as $workshop){ ?>
			<?php 
			$CI = &get_instance();
			
			?>
			<tr>
				<td data-order="<?php echo new_html_entity_decode($workshop['id']); ?>"><a href="<?php echo site_url('workshop/client/repair_job_detail/'.$workshop['repair_job_id'].'?tab=workshop') ?>"><?php echo new_html_entity_decode($workshop['name']); ?></a></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['repair_job_id']); ?>"><?php echo get_repair_job_name(($workshop['repair_job_id'])); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['report_type_id']); ?>" ><?php echo wshop_get_category_name($workshop['report_type_id']); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['report_status_id']); ?>" ><?php echo wshop_get_category_name($workshop['report_status_id']); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['sale_agent']); ?>"><?php echo new_html_entity_decode(get_staff_full_name($workshop['sale_agent'])); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['from_date']); ?>"><?php echo  _dt($workshop['from_date']); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['to_date']); ?>"><?php echo  _dt($workshop['to_date']); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['id']); ?>"><?php echo new_html_entity_decode($workshop['parts_information']); ?></td>
				<td data-order="<?php echo new_html_entity_decode($workshop['id']); ?>"><?php echo new_html_entity_decode($workshop['description']); ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>