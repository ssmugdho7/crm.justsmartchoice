<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s smart-choice-office-settings">
    <div class="panel-body">
        <div class="smart-choice-settings-hero">
            <div>
                <h4 class="no-margin">Smart Choice Office Theme Health Check</h4>
                <p class="text-muted mtop10">Use this page to confirm that the module, assets, settings, and visual controls are loading correctly.</p>
            </div>
            <span class="smart-choice-badge">Health Ready</span>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <div class="smart-choice-health-card">
                    <h5>Module</h5>
                    <p><strong>Status:</strong> Active when this page loads.</p>
                    <p><strong>Version:</strong> 1.4.9</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="smart-choice-health-card">
                    <h5>CSS Assets</h5>
                    <p><strong>Admin CSS:</strong> modules/perfex_office_theme/assets/css/theme_styles.css</p>
                    <p><strong>Client CSS:</strong> modules/perfex_office_theme/assets/css/clients/clients.css</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="smart-choice-health-card">
                    <h5>Recommended Test</h5>
                    <p>Open the sidebar, expand nested menu items, then open a page with bottom save buttons.</p>
                </div>
            </div>
        </div>
        <hr>
        <a href="<?php echo admin_url('perfex_office_theme/settings'); ?>" class="btn btn-primary">Back To Theme Settings</a>
        <a href="<?php echo base_url('modules/perfex_office_theme/assets/css/theme_styles.css'); ?>" target="_blank" class="btn btn-default">Open Theme CSS File</a>
    </div>
</div>
<style>
.smart-choice-health-card{background:#fff;border:1px solid #e5edf2;border-radius:14px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06);min-height:165px;border-top:5px solid #0077CC}.smart-choice-health-card h5{color:#00A651;font-weight:700}.smart-choice-office-settings .panel-body{border-top:5px solid #0077CC;border-radius:14px;background:linear-gradient(180deg,#fff,#fbfdff)}.smart-choice-settings-hero{display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap}.smart-choice-badge{background:linear-gradient(135deg,#0077CC,#00A651);color:#fff;padding:8px 14px;border-radius:999px;font-weight:700;box-shadow:0 8px 22px rgba(22,145,121,.18)}
</style>
