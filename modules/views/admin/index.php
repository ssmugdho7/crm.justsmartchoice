<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('project_signatures'); ?></h4>
            <hr />
            <table class="table dt-table">
              <thead>
                <tr>
                  <th><?php echo _l('id'); ?></th>
                  <th><?php echo _l('project'); ?></th>
                  <th><?php echo _l('signed_at'); ?></th>
                  <th><?php echo _l('signed_by'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($signatures as $s): ?>
                <tr>
                  <td><?php echo $s['id']; ?></td>
                  <td><?php echo html_escape($s['project_name']); ?></td>
                  <td><?php echo _dt($s['signed_at']); ?></td>
                  <td>
                    <?php
                      if ((int)$s['signed_by_staff_id'] > 0) {
                          echo _l('staff') . ' #' . $s['signed_by_staff_id'];
                      } elseif ((int)$s['signed_by_contact_id'] > 0) {
                          echo _l('client') . ' #' . $s['signed_by_contact_id'];
                      } else {
                          echo _l('unknown');
                      }
                    ?>
                  </td>
                  <td>
                    <a href="<?php echo admin_url('project_signatures/export_pdf/' . $s['id']); ?>" class="btn btn-default btn-sm"><?php echo _l('export_pdf'); ?></a>
                    <a href="<?php echo admin_url('project_signatures/delete/' . $s['id']); ?>" class="btn btn-danger btn-sm _delete"><?php echo _l('delete'); ?></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
