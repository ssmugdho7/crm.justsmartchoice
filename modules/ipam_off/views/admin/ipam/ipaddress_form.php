<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title"><?php echo isset($ipaddress) ? 'Edit IP Address' : 'Add IP Address'; ?></h1>
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="post" action="<?php echo admin_url('ipam/' . (isset($ipaddress) ? 'edit_ipaddress/' . $ipaddress['id'] : 'add_ipaddress')); ?>">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <!-- DNS Name -->
                            <div class="form-group">
                                <label for="dns_name">DNS Name:</label>
                                <input type="text" class="form-control" id="dns_name" name="dns_name" value="<?php echo isset($ipaddress) ? $ipaddress['dns_name'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="ip_address">IP Address:</label>
                                <input type="text" name="ip_address" id="ip_address" class="form-control" value="<?php echo isset($ipaddress) ? $ipaddress['ip_address'] : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="client">Client:</label>
                                <select name="client" id="client" class="form-control" required>
                                    <?php foreach ($clients as $client) { ?>
                                    <option value="<?php echo $client['userid']; ?>" <?php echo isset($ipaddress) && $ipaddress['client'] == $client['userid'] ? 'selected' : ''; ?>><?php echo $client['company']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location">Location:</label>
                                <select name="location" id="location" class="form-control" required>
                                    <?php foreach ($locations as $location) { ?>
                                    <option value="<?php echo $location['id']; ?>" <?php echo isset($ipaddress) && $ipaddress['location'] == $location['id'] ? 'selected' : ''; ?>><?php echo $location['location']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="network">Network:</label>
                                <select name="network" id="network" class="form-control" required>
                                    <?php foreach ($networks as $network) { ?>
                                    <option value="<?php echo $network['id']; ?>" <?php echo isset($ipaddress) && $ipaddress['network'] == $network['id'] ? 'selected' : ''; ?>><?php echo $network['network_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="vlan_id">VLAN:</label>
                                <select name="vlan_id" id="vlan_id" class="form-control">
                                    <?php foreach ($vlans as $vlan) { ?>
                                    <option value="<?php echo $vlan['id']; ?>" <?php echo isset($ipaddress) && $ipaddress['vlan_id'] == $vlan['id'] ? 'selected' : ''; ?>><?php echo $vlan['vlan_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="interface">Interface:</label>
                                <select name="interface" id="interface" class="form-control">
                                    <?php foreach ($interfaces as $interface) { ?>
                                    <option value="<?php echo $interface['id']; ?>" <?php echo isset($ipaddress) && $ipaddress['interface'] == $interface['id'] ? 'selected' : ''; ?>><?php echo $interface['interface_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary"><?php echo isset($ipaddress) ? 'Update IP Address' : 'Add IP Address'; ?></button>
                            <a href="<?php echo admin_url('ipam/ipaddresses'); ?>" class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
