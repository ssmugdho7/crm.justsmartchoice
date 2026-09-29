<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_customers_portal_head'); ?>

<div class="col-md-12">
	<h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 section-heading section-heading-invoices">
		<?php echo e($title); ?>
	</h4>
	<div class="panel_s">
		<div class="panel-body">
			<?php $this->load->view('clients/inspections/table_html'); ?>
		</div>
	</div>
</div>

<?php workshop_client_init_tail(); ?>