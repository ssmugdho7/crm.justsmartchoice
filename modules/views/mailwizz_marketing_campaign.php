<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
		    <div class="row _buttons">
                      <div class="col-md-8">

                        <a href="#" onclick="open_link()" class="btn btn-info pull-left new"><?php echo _l('mailwizz_list_button'); ?></a>


                     </div>
            </div>
            <div class="clearfix"></div>
		    <hr />

            <h4 class="no-margin"><?php echo _l('mailwizz_marketing_campaign_list'); ?></h4>
             <hr class="hr-panel-heading" />
            <?php
$table_data = [
    _l('mailwizz_marketing_campaign_id'),
    _l('mailwizz_marketing_campaign_name'),
    _l('mailwizz_marketing_campaign_status'),
    _l('mailwizz_marketing_campaign_group'),
    _l('mailwizz_marketing_campaign_actions'),
];
render_datatable($table_data, ($class ?? 'mailwizz_marketing_campaign_list'));?>
          </div>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
<?php init_tail();?>
<script type="text/javascript">
  $(function(){
    initDataTable('.table-mailwizz_marketing_campaign_list', window.location.href,'undefined','undefined','');
  });
</script>

<script type="text/javascript">

      function open_link(){

        window.open("create_list","_self");
      }

</script>

</body>
</html>