<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Interfaces</h1>
                <a href="<?php echo admin_url('ipam/add_interface'); ?>" class="btn btn-primary mb-3">Add Interface</a>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Interface Name</th>
                                        <th>MAC Address</th>
                                        <th>Network</th>
                                        <th>VLAN</th>
                                        <th>Parent Interface</th>
                                        <th>Virtual</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($interfaces as $interface) { ?>
                                    <tr>
                                        <td><?php echo $interface['id']; ?></td>
                                        <td><?php echo $interface['interface_name']; ?></td>
                                        <td><?php echo $interface['mac_address']; ?></td>
                                        <td><?php echo $interface['network_name']; ?></td>
                                        <td><?php echo $interface['vlan_name']; ?></td>
                                        <td><?php echo $interface['parent_interface_name']; ?></td>
                                        <td><?php echo $interface['virtual'] ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('ipam/edit_interface/' . $interface['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('ipam/delete_interface/' . $interface['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this interface?');">Delete</a>
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
