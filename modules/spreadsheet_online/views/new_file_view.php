<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
		<?php echo form_hidden('role', $role); ?>
		<?php echo form_hidden('token', $this->security->get_csrf_hash()); ?>
		<div class="upload_file_class">
					<a href="#" id="file-link" class="btn btn-info"><i class="fa fa-upload" aria-hidden="true"></i> Upload</a>
          			<i class="fa fa-question-circle fa-lg " data-toggle="tooltip" title="" data-original-title="<?php echo _l('spreadsheet_online_warning'); ?>"></i>

					<input type="file" id="Luckyexcel-demo-file" name="Luckyexcel-demo-file" change="demoHandler">

					<?php if (isset($id)) {?>
					<a href="#" class="btn add_share_button btn-info new_file_margin" onclick="new_share(); return false;">
						<i class="fa fa-share-square-o"></i> <?php echo _l('share'); ?>
					</a>
					<a href="#RelatedModal" data-toggle="modal" class="btn add_related_button btn-info new_file_margin">
						<i class="fa fa-paw"></i> <?php echo _l('related'); ?>
					</a>
				<?php }?>
				
		</div>
			<?php echo form_open_multipart(admin_url('spreadsheet_online/new_file_view/' . $parent_id), array('id' => 'spreadsheet-test-form')); ?>
			<div class="row">
				
			<div id="luckysheet" class="luckysheet_padding_bottom"></div>
			</div>
			<?php echo form_hidden('csrf_token_name', $this->security->get_csrf_hash()); ?>
			<?php echo form_hidden('name'); ?>
			<?php echo form_hidden('parent_id', $parent_id); ?>
			<?php echo form_hidden('id', isset($id) ? $id : ""); ?>
			<?php echo form_hidden('type', 1); ?>
			<?php echo form_close(); ?>
</div>


<div class="modal fade" id="SaveAsModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('save_as') ?></h4>
			</div>

			<div class="modal-body">
				<label for="folder" class="control-label"><?php echo _l('leads_email_integration_folder') ?></label>
				<input type="text" id="folder" name="folder" class="selectpicker" placeholder="<?php echo _l('leads_email_integration_folder'); ?>" autocomplete="off">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="RelatedModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('add_new_related') ?></h4>
			</div>
			<?php $task = [
	['rel_type' => 'project', 'name' => _l('project')],
	['rel_type' => 'estimate', 'name' => _l('estimate')],
	['rel_type' => 'contract', 'name' => _l('contract')],
	['rel_type' => 'proposal', 'name' => _l('proposal')],
	['rel_type' => 'invoice', 'name' => _l('invoice')],
	['rel_type' => 'expense', 'name' => _l('expense')],
	['rel_type' => 'lead', 'name' => _l('lead')],
];
$rel_id = [
	['id' => '', 'name' => ''],
];
?>
			<?php echo form_open_multipart(admin_url('spreadsheet_online/update_related_spreadsheet_online'), array('id' => 'related-form')); ?>
			<?php echo form_hidden('id', $id); ?>
			<?php echo form_hidden('parent_id', $parent_id); ?>

			<div class="modal-body">
					<div class="list_information_fields_review_related">
						<div id="item_information_fields_review_related">
							<div class="col-md-11">
								<div class="col-md-6">
									<?php
$selected = '';
echo render_select('rel_type[0]', $task, array('rel_type', array('name')), 'related_to', $selected, array());
?>
								</div>
								<div class="col-md-6">
									<?php
$selected = '';
echo render_select('rel_id[0]', $rel_id, array('id', array('name')), 'value', $selected, array());
?>
								</div>

							</div>
							<div class="col-md-1">
								<span class="pull-bot">
									<button name="add" class="new-btn-clone1 btn new_box_information_review_related btn-info" data-ticket="true" type="button">
										<i class="fa fa-plus"></i>
									</button>
								</span>
							</div>
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<div class="modal fade" id="ShareModal" role="dialog">
	<?php echo form_hidden('value-hidden'); ?>

	<?php echo form_open_multipart(admin_url('spreadsheet_online/update_share_spreadsheet_online'),array('id'=>'share-form')) ?>
	<?php echo form_hidden('share_id');  ?>
	<?php echo form_hidden('id', $id); ?>

	
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close test_class" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('add_new_share') ?></h4>
			</div>
			<div class="modal-body">
				<div class="row mtop15 mbot15 fs-gr-radio">
					<div class="col-md-12">
						<label class="radio-inline">
							<h5><span><?php echo _l('what_do_you_want_to_choose') ?></span></h5>
						</label>
						<label class="radio-inline">
							<input type="radio" name="type" value="staff" checked>
							<span class="inline-mbot"><?php echo _l('staff'); ?></span>
						</label>
						<label class="radio-inline">
							<input type="radio" name="type" value="client">
							<span class="inline-mbot" ><?php echo _l('client'); ?></span>
						</label>
					</div>
				</div>

				<div id="div_staff">
					<?php 
					$selected_role = array();
					echo render_select('departments_share[]',$departments,array('departmentid',array('name')),'department_share',$selected_role,array('multiple'=>true, 'data-actions-box' => true),array(),'','',false); ?>
					<?php 
					$selected = array();
					echo render_select('staffs_share[]',$staffs,array('staffid',array('firstname','lastname')),'staff_share',$selected,array('multiple'=>true, 'data-actions-box' => true),array(),'','',false); ?>
				</div>
				<div id="div_client" class="hide">
					<?php 
					$selected_role = array();
					echo render_select('client_groups_share[]',$client_groups,array('id','name'),'client_groups_share',$selected_role,array('multiple'=>true, 'data-actions-box' => true),array(),'','',false); ?>
					<?php 
					$selected = array();
					echo render_select('clients_share[]',$clients,array('userid','company'),'client_share',$selected,array('multiple'=>true, 'data-actions-box' => true),array(),'','',false); ?>
				</div>
				<div class="row" id="div_permisstion">
					<div class="col-md-6">
						<h5 class="title mbot5"><?php echo _l('fs_permisstion') ?></h5>
					    <div class="row">
					        <div class="col-md-6 mtop10 border-right">
					            <span><?php echo _l('view'); ?></span>
					        </div>
					        <div class="col-md-6 mtop10">
					            <div class="onoffswitch">
					                <input type="checkbox" id="is_read" data-perm-id="5" class="onoffswitch-checkbox" value="1" name="is_read" checked>
					                <label class="onoffswitch-label" for="is_read"></label>
					            </div>
					        </div>
					        <div class="col-md-6 mtop10 border-right">
					            <span><?php echo _l('edit'); ?></span>
					        </div>
					        <div class="col-md-6 mtop10">
					            <div class="onoffswitch">
					                <input type="checkbox" id="is_write" data-perm-id="6" class="onoffswitch-checkbox"  value="1" name="is_write" checked>
					                <label class="onoffswitch-label" for="is_write"></label>
					            </div>
					        </div>
					    </div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" onclick="close_edit_sharing(); return false;"><?php echo _l('close'); ?></button>
					<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
				</div>
			</div>
		</div>
		<?php echo form_close(); ?>   
	</div>
</div>

<!-- box loading -->
<div id="box-loading"></div>
<?php init_tail();?>
<?php require 'modules/spreadsheet_online/assets/js/new_file_js.php';?>
</body>
</html>
