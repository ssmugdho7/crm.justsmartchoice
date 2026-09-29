<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="snm-settings-wrap">
  <h4><i class="fa fa-network-wired"></i> Smart Network Manager Settings</h4>
  <p class="text-muted">Configure the local Windows Server agent, router notes, phpMyAdmin shortcut, and safety controls.</p>
  <?php echo form_open(admin_url('smart_network_manager/save_settings')); ?>
  <div class="row">
    <div class="col-md-6">
      <?php echo render_input('agent_url', 'Windows Agent URL', get_option('smart_network_manager_agent_url'), 'text', ['placeholder'=>'http://192.168.1.10:8099']); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('agent_token', 'Windows Agent Token', get_option('smart_network_manager_agent_token')); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('agent_ip_whitelist', 'Allowed Agent IPs / Notes', get_option('smart_network_manager_agent_ip_whitelist'), 'text', ['placeholder'=>'Example: 192.168.1.10, VPN only']); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('router_type', 'Router Type', get_option('smart_network_manager_router_type'), 'text', ['placeholder'=>'manual, pfsense, mikrotik, ubiquiti, tplink']); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('router_url', 'Router Admin URL', get_option('smart_network_manager_router_url'), 'text', ['placeholder'=>'http://192.168.1.1']); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('phpmyadmin_url', 'Bluehost / phpMyAdmin Link', get_option('smart_network_manager_phpmyadmin_url')); ?>
    </div>
    <div class="col-md-4">
      <?php echo render_input('theme_color', 'Theme Color', get_option('smart_network_manager_theme_color') ?: '#169179'); ?>
    </div>
    <div class="col-md-8">
      <div class="checkbox checkbox-primary"><input type="checkbox" name="enable_controls" id="enable_controls" value="1" <?php checked(get_option('smart_network_manager_enable_controls'), '1'); ?>><label for="enable_controls">Enable device control actions</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_cache_cleanup" id="allow_cache_cleanup" value="1" <?php checked(get_option('smart_network_manager_allow_cache_cleanup'), '1'); ?>><label for="allow_cache_cleanup">Allow safe CRM cache cleanup</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_database_tools" id="allow_database_tools" value="1" <?php checked(get_option('smart_network_manager_allow_database_tools'), '1'); ?>><label for="allow_database_tools">Allow safe database tools for module tables</label></div>
    </div>
  </div>
  <div class="alert alert-warning"><strong>Security:</strong> Do not expose the Windows Agent to the public internet. Use LAN, VPN, firewall whitelist, and a strong token.</div>
  <button type="submit" class="btn btn-primary">Save Settings</button>
  <a href="<?php echo admin_url('smart_network_manager/health'); ?>" class="btn btn-default">Health Checker</a>
  <a href="<?php echo admin_url('smart_network_manager/help'); ?>" class="btn btn-default">Help Guide</a>
  <?php echo form_close(); ?>
</div>
