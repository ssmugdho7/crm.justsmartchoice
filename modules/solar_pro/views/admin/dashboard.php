<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content solar-pro-shell"><?php $this->load->view('admin/_module_nav'); ?>
  <div class="solar-hero">
    <div><span class="solar-kicker"><?php echo _l('solar_pro_smart_choice_solar'); ?></span><h1><?php echo _l('solar_pro_dashboard'); ?></h1><p><?php echo _l('solar_pro_dashboard_subtitle'); ?></p></div>
    <a href="<?php echo admin_url('solar_pro/health'); ?>" class="btn btn-default"><i class="fa fa-heartbeat"></i> <?php echo _l('solar_pro_health'); ?></a> <a href="<?php echo admin_url('solar_pro/analysis'); ?>" class="btn solar-btn-primary"><i class="fa fa-plus"></i> <?php echo _l('solar_pro_new_analysis'); ?></a>
  </div>
  <div class="row solar-stat-grid">
    <?php $cards=[['fa-file-text-o','solar_pro_analyses',$stats['analyses']],['fa-bolt','solar_pro_total_kw',number_format($stats['kw'],2).' kW'],['fa-th','solar_pro_panels_sold',number_format($stats['panels'])],['fa-line-chart','solar_pro_pipeline_value',app_format_money($stats['revenue'],get_base_currency())],['fa-sun-o','solar_pro_annual_generation',number_format($stats['production']).' kWh']]; foreach($cards as $card): ?>
    <div class="col-md solar-stat-card"><i class="fa <?php echo $card[0]; ?>"></i><span><?php echo _l($card[1]); ?></span><strong><?php echo $card[2]; ?></strong></div>
    <?php endforeach; ?>
  </div>
  <div class="panel_s solar-card"><div class="panel-body"><div class="solar-card-head"><h4><?php echo _l('solar_pro_recent_analyses'); ?></h4><a href="<?php echo admin_url('solar_pro/analyses'); ?>"><?php echo _l('solar_pro_view_all'); ?></a></div>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th><?php echo _l('solar_pro_customer'); ?></th><th><?php echo _l('solar_pro_address'); ?></th><th><?php echo _l('solar_pro_system_size'); ?></th><th><?php echo _l('solar_pro_production'); ?></th><th><?php echo _l('solar_pro_price'); ?></th></tr></thead><tbody>
    <?php foreach($recent as $r): ?><tr><td><a href="<?php echo admin_url('solar_pro/analysis/'.$r['id']); ?>"><?php echo html_escape(trim($r['first_name'].' '.$r['last_name']) ?: '#'.$r['id']); ?></a></td><td><?php echo html_escape($r['address']); ?></td><td><?php echo number_format($r['system_kw'],3); ?> kW</td><td><?php echo number_format($r['annual_production_kwh']); ?> kWh</td><td><?php echo app_format_money($r['system_price'],get_base_currency()); ?></td></tr><?php endforeach; ?>
    <?php if(!$recent): ?><tr><td colspan="5" class="text-muted text-center"><?php echo _l('solar_pro_no_analyses'); ?></td></tr><?php endif; ?>
    </tbody></table></div>
  </div></div>
</div></div>
<?php init_tail(); ?>
