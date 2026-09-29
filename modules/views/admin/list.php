<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <h4><?php echo _l('field_signer_list'); ?></h4>
        <table class="table dt-table">
            <thead>
                <tr>
                    <th><?php echo _l('id'); ?></th>
                    <th><?php echo _l('project'); ?></th>
                    <th><?php echo _l('staff'); ?></th>
                    <th><?php echo _l('title'); ?></th>
                    <th><?php echo _l('date_created'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($signs as $s){ ?>
                <tr>
                    <td><?php echo $s['id']; ?></td>
                    <td><?php echo $s['project_id']; ?></td>
                    <td><?php echo $s['staff_id']; ?></td>
                    <td><?php echo $s['title']; ?></td>
                    <td><?php echo $s['date_created']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
