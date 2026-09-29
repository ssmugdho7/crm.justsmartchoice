<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">VLANs</h1>
                <a href="<?php echo admin_url('ipam/add_vlan'); ?>" class="btn btn-primary mb-3">Add VLAN</a>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>VLAN Name</th>
                                        <th>VLAN ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vlans as $vlan) { ?>
                                    <tr>
                                        <td><?php echo $vlan['id']; ?></td>
                                        <td><?php echo $vlan['vlan_name']; ?></td>
                                        <td><?php echo $vlan['vlan_id']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('ipam/edit_vlan/' . $vlan['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('ipam/delete_vlan/' . $vlan['id']); ?>" class="btn btn-danger btn-sm">Delete</a>
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
</div>
<?php init_tail(); ?>
