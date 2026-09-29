<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">IP Addresses</h1>
                <a href="<?php echo admin_url('ipam/add_ipaddress'); ?>" class="btn btn-primary mb-3">Add IP Address</a>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Location</th>
                                        <th>Network</th>
                                        <th>VLAN</th>
                                        <th>IP Address</th>
                                        <th>Interface</th>
                                        <th>DNS Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ipaddresses as $ipaddress) { ?>
                                    <tr>
                                        <td><?php echo $ipaddress['id']; ?></td>
                                        <td><?php echo $ipaddress['client_name']; ?></td>
                                        <td><?php echo $ipaddress['location_name']; ?></td>
                                        <td><?php echo $ipaddress['network_name']; ?></td>
                                        <td><?php echo $ipaddress['vlan_name']; ?></td>
                                        <td><?php echo $ipaddress['ip_address']; ?></td>
                                        <td><?php echo $ipaddress['interface_name']; ?></td>
                                        <td><?php echo $ipaddress['dns_name']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('ipam/edit_ipaddress/' . $ipaddress['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('ipam/delete_ipaddress/' . $ipaddress['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this IP address?');">Delete</a>
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
