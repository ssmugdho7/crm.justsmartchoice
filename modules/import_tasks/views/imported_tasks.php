<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link href="<?php echo base_url('modules/import_tasks/assets/main.css'); ?>" rel="stylesheet" type="text/css" />
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="/admin/import_tasks/import" class="btn btn-info pull-left display-block"><?php echo _l('new_import'); ?></a>
                  </div>
                  <div class="clearfix"></div>
                  <hr class="hr-panel-heading" />
                  <div class="clearfix"></div>
                  <table class="apitable table dt-table">
                     <thead>
                        <th><?= _l('id'); ?></th>
                        <th><?= _l('filename'); ?></th>
                        <th><?= _l('tasks_imported_count'); ?></th>
                        <th><?= _l('created'); ?></th>
                        <th><?= _l('options'); ?></th>
                     </thead>
                     <tbody>
                        <?php foreach ($importedTasksHistory as $importedTask) { ?>
                           <tr>
                              <td><?= addslashes($importedTask['id']); ?></td>
                              <td><a href="/uploads/import_tasks/<?= $importedTask['id'] . '/' . $importedTask['filename'] ?>">
                                    <?= addslashes($importedTask['filename']); ?></a></td>
                              <td><?= addslashes($importedTask['tasks_count']); ?></td>
                              <td><?= addslashes($importedTask['created']); ?></td>
                              <td>
                                 <a href="<?= admin_url('import_tasks/delete/' . addslashes($importedTask['id'])); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                              </td>
                           </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>