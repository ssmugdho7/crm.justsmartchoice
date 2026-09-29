<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>modules/diagramy/assets/css/preview.css">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel_s" id="top-panel">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?><span class="close2" id="close">×</span></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo render_input('title', 'Title', (isset($diagramy) ? $diagramy->title : ''), '', ['disabled'=>'disabled']); ?>
                        <?php $mmgroup = ($diagramy_group) ? $diagramy_group->name : ''; echo render_input('diagramy_group_id', 'diagramy_group', $mmgroup, '', ['disabled'=>'disabled']); ?>
                        <?php echo render_textarea('description', 'Description', (isset($diagramy) ? $diagramy->description : ''), ['rows'=>4, 'disabled'=>'disabled'], []); ?>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('diagramy'); ?>
                            <span>
                                <button id="expand-button" type="button" class="collapsible btn btn-success">Properties</button>
                                <a href="<?php echo base_url(); ?>admin/diagramy/publicpreview/<?php echo $diagramy->diagramy_slug; ?>" target="_blank" class="btn btn-warning" style="float:right;margin-right:2px;"><i class="fa fa-share" style="padding-right:2px;"></i>Public URL</a>
                            </span>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <div class="row"><div class="col-md-12"><div id="map">
                            <img id="image" style="max-width:100%;cursor:pointer;" src="<?php echo htmlspecialchars((isset($diagramy) ? $diagramy->diagramy_content : ''), ENT_QUOTES, 'UTF-8'); ?>" />
                        </div></div></div>
                    </div>
                </div>
                <div class="btn-bottom-toolbar text-right"><a href="<?php echo admin_url('diagramy'); ?>" class="btn btn-info mindmap-btn"><?php echo _l('Go Back'); ?></a></div>
            </div>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>modules/diagramy/assets/js/preview.js"></script>
</body>
</html>
