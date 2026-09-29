<!-- / The Context Menu -->
<nav id="context-menu" class="context-menu" data-share="false">
	<ul class="context-menu__items">
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="edit"><i class="fa fa-cog"></i> <?php echo _l('edit') ?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="delete"><i class="fa fa-trash"></i> <?php echo _l('delete') ?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="share"><i class="fa fa-share"></i> <?php echo _l('share')?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="related"><i class="fa fa-user"></i> <?php echo _l('related')?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="d_file"><i class="fa fa-download"></i> <?php echo _l('download') ?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="create_file"><i class="fa fa-plus"></i> <?php echo _l('create_file') ?></a>
		</li>
		<li class="context-menu__item">
			<a href="#" class="context-menu__link" data-action="create_folder"><i class="fa fa-plus"></i> <?php echo _l('create_folder') ?></a>
		</li>
	</ul>
</nav>

<div class="popup-overlay">
	<div class="popup-content">
		<header role="banner">
			<nav class="nav-class" role="navigation">
				<ul class="nav__list button-group__mono-colors" data-share="false">
					<li class="select-option-choose" data-option="edit">
						<input id="group-1" type="checkbox" hidden />
						<label for="group-1"><span class="fa fa-angle-right"></span><i class="fa fa-crosshairs"></i> <?php echo _l('edit') ?></label>
					</li>
					<li class="select-option-choose" data-option="delete">
						<input id="group-2" type="checkbox" hidden />
						<label for="group-2"><span class="fa fa-angle-right"></span><i class="fa fa-trash-o"></i> <?php echo _l('delete') ?></label>
					</li>
					<li class="select-option-choose" data-option="share">
						<input id="group-3" type="checkbox" hidden />
						<label for="group-3"><span class="fa fa-angle-right"></span><i class="fa fa-user-plus" aria-hidden="true"></i>
							<?php echo _l('share')?></label>
					</li>
					<li class="select-option-choose" data-option="related">
						<input id="group-4" type="checkbox" hidden />
						<label for="group-4"><span class="fa fa-angle-right"></span> <i class="fa fa-user" aria-hidden="true"></i>
							<?php echo _l('related')?></label>
					</li>
					<li class="select-option-choose" data-option="d_file">
						<input id="group-5" type="checkbox" hidden />
						<label for="group-5"><span class="fa fa-angle-right"></span><i class="fa fa-download" aria-hidden="true"></i> <?php echo _l('download') ?></label>
					</li>
					<li class="select-option-choose" data-option="create_file"> 
						<input id="group-6" type="checkbox" hidden />
						<label for="group-6"><span class="fa fa-angle-right"></span><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _l('create_file') ?></label>
					</li>
					<li class="select-option-choose" data-option="create_folder">
						<input id="group-7" type="checkbox" hidden />
						<label for="group-7"><span class="fa fa-angle-right"></span><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _l('create_folder') ?></label>
					</li>
				</ul>

			</nav>
		</header>
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
			$rel_id =  [
				['id' => '', 'name' => ''],
			];
			?>
			<?php echo form_open_multipart(admin_url('spreadsheet_online/update_related_spreadsheet_online'),array('id'=>'related-form'));?>
			<?php echo form_hidden('id'); ?>
			<div class="modal-body">
					<div class="list_information_fields_review_related">
						<div id="item_information_fields_review_related">
							<div class="col-md-11">
								<div class="col-md-6">
									<?php 
									$selected = '';
									echo render_select('rel_type[0]',$task,array('rel_type',array('name')),'related_to',$selected,array()); 
									?>
								</div>
								<div class="col-md-6">
									<?php 
									$selected = '';
									echo render_select('rel_id[0]',$rel_id,array('id',array('name')),'value',$selected,array()); 
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

<div class="modal fade" id="relateDetailModal" role="dialog">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('related_detail') ?></h4>
			</div>
			<div class="modal-body">
				<h4>List name: </h4>
				<ul class="content-related"></ul>
			</div>
		</div>
	</div>
</div>

<a href="#" data-toggle="modal" class="btn add_file_button btn-info">
	<i class="fa fa-plus-circle"></i> <?php echo _l('add_file'); ?>
</a>

<a href="#AddFolderModal" data-toggle="modal" class="btn add_folder_button btn-info">
	<i class="fa fa-plus-square-o"></i> <?php echo _l('add_folder'); ?>
</a>

<a href="#ShareModal" data-toggle="modal" class="btn add_share_button btn-info">
	<i class="fa fa-share-square-o"></i> <?php echo _l('share'); ?>
</a>

<a href="#RelatedModal" data-toggle="modal" class="btn add_related_button btn-info">
	<i class="fa fa-paw"></i> <?php echo _l('related'); ?>
</a>

<div class="row">
	<div class="col-sm-12">
		<table id="spreadsheet-advanced">
			<caption>
				<a href="#" class="btn btn-info caption-a" onclick="jQuery('#spreadsheet-advanced').treetable('expandAll'); return false;"><span class="expand-all"></span><?php echo _l('expand_all')?></a>
				<a href="#" class="btn btn-info caption-a" onclick="jQuery('#spreadsheet-advanced').treetable('collapseAll'); return false;"><span class="collapse-all"></span><?php echo _l('collapse_all')?></a>
			</caption>
			<thead>
				<tr>
					<th><?php echo _l('name') ?></th>
					<th><?php echo _l('kind') ?></th>
					<th><?php echo _l('related_to') ?></th>
				</tr>
			</thead>
			<?php echo html_entity_decode($folder_my_tree); ?>
		</table>
	</div>
</div>

<div class="modal fade" id="AddFileModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('add_new_action') ?></h4>
				<h4 class="modal-title update-new hide"><?php echo _l('update_action') ?></h4>
			</div>
			<?php echo form_open_multipart(admin_url('hira/update_observations_action'),array('id'=>'observations-action-form'));?>
			<?php echo form_hidden('id'); ?>
			<?php echo form_hidden('type', 'observations'); ?>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
			<?php echo form_close(); ?>   
		</div>
	</div>
</div>

<div id="fsModal" class="modal animated bounceIn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<button class="btn btn-secondary" data-dismiss="modal">
					close
				</button>
				<button class="btn btn-default">
					Default
				</button>
				<button class="btn btn-primary">
					Primary
				</button>
			</div>
		</div>
	</div>
</div>



<div class="modal fade" id="sharedetailModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('share_detail') ?></h4>
			</div>
			<div class="modal-body">
				<?php echo form_hidden('data_share'); ?>
				<div class="row">
					<ul class="tabs">
						<li class="tab-link current" data-tab="tab-1"><?php echo _l('staff') ?></li>
						<li class="tab-link" data-tab="tab-2"><?php echo _l('clients') ?></li>
					</ul>

					<div id="tab-1" class="tab-content current">
						<table class="content-table">
							<thead>
								<tr>
									<th>NAME</th>
									<th>PERMISSION</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<div id="tab-2" class="tab-content">

						<table class="content-table">
							<thead>
								<tr>
									<th>NAME</th>
									<th>PERMISSION</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="AddFolderModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title add-new"><?php echo _l('add_new_folder') ?></h4>
				<h4 class="modal-title update-new hide"><?php echo _l('update_folder') ?></h4>
			</div>
			<?php echo form_open_multipart(admin_url('spreadsheet_online/add_edit_folder'),array('id'=>'add-edit-folder-form'));?>
			<?php echo form_hidden('id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12 col-sm-12">
						<?php echo render_input('name', 'name_folder');?>
					</div>
				</div>
				<?php echo form_hidden('parent_id'); ?>				
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
	<?php echo form_hidden('id'); ?>
	<?php echo form_hidden('check'); ?>
	<?php echo form_hidden('share_id');  ?>
	
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
							<input type="radio" name="type" value="staff" id="staff" checked>
							<span class="inline-mbot"><?php echo _l('staff'); ?></span>
						</label>
						<label class="radio-inline">
							<input type="radio" name="type" value="client" id="client">
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


<div class="modal fade" id="sharing-list-modal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title add-title"><?php echo _l('sharing')?></h4>
			</div>
			<div class="modal-body">
				<div>
					<a href="#" class="btn btn-info mbot15" onclick="new_share(); return false;"><?php echo _l('add'); ?></a>
				</div>
				<?php echo form_hidden('share_hash'); ?>
				<table class="table table-sharing scroll-responsive">
		           <thead>
		              <tr>
		                 <th><?php echo _l('hash_share'); ?></th>
		                 <th><?php echo _l('type'); ?></th>
		                 <th><?php echo _l('view'); ?></th>
		                 <th><?php echo _l('edit'); ?></th>
		                 <th><?php echo _l('datecreated'); ?></th>
		                 <th><?php echo _l('options'); ?></th>
		              </tr>
		           </thead>
		        </table>
			</div>
		</div>
	</div>
</div>