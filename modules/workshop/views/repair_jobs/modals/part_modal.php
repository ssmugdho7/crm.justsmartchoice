<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="part_detail" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">
                  <span class="add-title"><?php echo _l('wshop_select_parts'); ?></span>
              </h4>
          </div>
          <div class="modal-body">

              <div class="row">

                 <div class="col-md-12">

                    <?php 
                    render_datatable(
                        array(
                            _l('id'),
                            _l('wshop_name'),
                            _l('wshop_description'),
                            _l('wshop_category'),
                            _l('wshop_unit_price'),
                            _l('wshop_tax1'),
                            _l('wshop_tax2'),
                            _l('wshop_quantity'),
                            _l('options'),
                            _l('options'),
                        ),'part_table'
                    );
                    ?>

                </div>
            </div>
        </div>
    </div>
<div id="box-loading"></div>
</div>

</div>

