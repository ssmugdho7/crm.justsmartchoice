<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title"><?php echo isset($network) ? 'Edit Network' : 'Add Network'; ?></h1>
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="post" action="<?php echo admin_url('ipam/' . (isset($network) ? 'edit_network/' . $network['id'] : 'add_network')); ?>">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <div class="form-group">
                                <label for="network_name">Network Name:</label>
                                <input type="text" name="network_name" id="network_name" class="form-control" value="<?php echo isset($network) ? $network['network_name'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="netmask">Netmask:</label>
                                <input type="text" name="netmask" id="netmask" class="form-control" value="<?php echo isset($network) ? $network['netmask'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="subnet_ip">Subnet IP:</label>
                                <input type="text" name="subnet_ip" id="subnet_ip" class="form-control" value="<?php echo isset($network) ? $network['subnet_ip'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="client">Client:</label>
                                <select name="client" id="client" class="form-control" required>
                                    <?php foreach ($clients as $client) { ?>
                                    <option value="<?php echo $client['userid']; ?>" <?php echo isset($network) && $network['client'] == $client['userid'] ? 'selected' : ''; ?>><?php echo $client['company']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location">Location:</label>
                                <select name="location" id="location" class="form-control" required>
                                    <?php foreach ($locations as $location) { ?>
                                    <option value="<?php echo $location['id']; ?>" <?php echo isset($network) && $network['location'] == $location['id'] ? 'selected' : ''; ?>><?php echo $location['location']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="vlan_id">VLAN:</label>
                                <select name="vlan_id" id="vlan_id" class="form-control">
                                    <?php foreach ($vlans as $vlan) { ?>
                                    <option value="<?php echo $vlan['id']; ?>" <?php echo isset($network) && $network['vlan_id'] == $vlan['id'] ? 'selected' : ''; ?>><?php echo $vlan['vlan_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- Public/Private Dropdown -->
                            <div class="form-group">
                                <label for="public_private">Network Type:</label>
                                <select name="public_private" id="public_private" class="form-control" required>
                                    <option value="Public" <?php echo isset($network) && $network['public_private'] == 'Public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="Private" <?php echo isset($network) && $network['public_private'] == 'Private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo isset($network) ? 'Update Network' : 'Add Network'; ?></button>
                            <a href="<?php echo admin_url('ipam/networks'); ?>" class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
