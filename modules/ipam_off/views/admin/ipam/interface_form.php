<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title"><?php echo isset($interface) ? 'Edit Interface' : 'Add Interface'; ?></h1>
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="post" action="<?php echo admin_url('ipam/' . (isset($interface) ? 'edit_interface/' . $interface['id'] : 'add_interface')); ?>">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <div class="form-group">
                                <label for="interface_name">Interface Name:</label>
                                <input type="text" name="interface_name" id="interface_name" class="form-control" value="<?php echo isset($interface) ? $interface['interface_name'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="mac_address">MAC Address:</label>
                                <input type="text" name="mac_address" id="mac_address" class="form-control" value="<?php echo isset($interface) ? $interface['mac_address'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="network">Network:</label>
                                <select name="network" id="network" class="form-control" required>
                                    <?php foreach ($networks as $network) { ?>
                                    <option value="<?php echo $network['id']; ?>" <?php echo isset($interface) && $interface['network'] == $network['id'] ? 'selected' : ''; ?>><?php echo $network['network_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="vlan_id">VLAN:</label>
                                <select name="vlan_id" id="vlan_id" class="form-control">
                                    <?php foreach ($vlans as $vlan) { ?>
                                    <option value="<?php echo $vlan['id']; ?>" <?php echo isset($interface) && $interface['vlan_id'] == $vlan['id'] ? 'selected' : ''; ?>><?php echo $vlan['vlan_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- Parent Interface Selection -->
                            <div class="form-group">
                                <label for="parent_interface_id">Parent Interface:</label>
                                <select name="parent_interface_id" id="parent_interface_id" class="form-control">
                                    <option value="">Select Parent Interface</option>
                                    <?php if (!empty($interfaces)) { ?>
                                        <?php foreach ($interfaces as $intf) { ?>
                                            <option value="<?php echo $intf['id']; ?>" <?php echo isset($interface) && $interface['parent_interface_id'] == $intf['id'] ? 'selected' : ''; ?>>
                                                <?php echo $intf['interface_name']; ?>
                                            </option>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <option value="">No Interfaces Available</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- Virtual Checkbox -->
                            <div class="form-group">
                                <label for="virtual">Virtual:</label>
                                <input type="checkbox" class="form-control" id="virtual" name="virtual" <?php echo isset($interface) && $interface['virtual'] ? 'checked' : ''; ?>>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo isset($interface) ? 'Update Interface' : 'Add Interface'; ?></button>
                            <a href="<?php echo admin_url('ipam/interfaces'); ?>" class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
