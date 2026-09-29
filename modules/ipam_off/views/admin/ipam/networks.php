<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Networks</h1>
                <a href="<?php echo admin_url('ipam/add_network'); ?>" class="btn btn-primary mb-3">Add Network</a>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Network Name</th>
                                        <th>Client</th>
                                        <th>Location</th>
                                        <th>Subnet IP</th>
                                        <th>Netmask</th>
                                        <th>VLAN</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($networks as $network) { ?>
                                    <tr>
                                        <td><?php echo $network['id']; ?></td>
                                        <td><?php echo $network['network_name']; ?></td>
                                        <td><?php echo $network['client_name']; ?></td>
                                        <td><?php echo $network['location_name']; ?></td>
                                        <td><?php echo $network['subnet_ip']; ?></td>
                                        <td><?php echo $network['netmask']; ?></td>
                                        <td><?php echo $network['vlan_name']; ?></td>
                                        <td><?php echo $network['public_private']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('ipam/edit_network/' . $network['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('ipam/delete_network/' . $network['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this network?');">Delete</a>
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
