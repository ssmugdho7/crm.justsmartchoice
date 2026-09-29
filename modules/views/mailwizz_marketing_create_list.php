<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5 left-column">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('mailwizz_list_button'); ?></h4>
                        <hr class="hr-panel-heading">
                        <?php echo form_open('mailwizz_marketing/campaign/list_submit', array('id' => 'meeting-submit-form')); ?>
                        <div class="row">
                            <div class="col-md-12">

                                <?php

echo render_input('mailwizz_list_name', 'mailwizz_list_name', '', 'text', array('required' => 'true')); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">

                                <?php

echo render_input('mailwizz_list_displayname', 'mailwizz_list_displayname', '', 'text', array('required' => 'true')); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php

echo render_input('mailwizz_list_description', 'mailwizz_list_description', '', 'text', array('required' => 'true')); ?>
                            </div>

                        </div>
                        <h4>Defaults</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php

echo render_input('mailwizz_list_fromname', 'mailwizz_list_fromname', '', 'text', array('required' => 'true')); ?>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php

echo render_input('mailwizz_marketing_from_email', 'mailwizz_marketing_from_email', '', 'email', array('required' => 'true')); ?>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php

echo render_input('mailwizz_marketing_replyto', 'mailwizz_marketing_replyto', '', 'email', array('required' => 'true')); ?>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php

echo render_input('mailwizz_marketing_listsubject', 'mailwizz_marketing_listsubject', '', 'text', array('required' => 'true')); ?>
                            </div>

                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail();?>
</body>

</html>