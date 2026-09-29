

<div class="modal-dialog">

    <div class="modal-content ">

        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title">

                <span class="edit-title"><?php echo _l('perfex_menu_link_text')?></span>

            </h4>

        </div>

        <?php echo form_open_multipart('favorite_links/save/'.$menu_id ); ?>


            <div class="modal-body">

                <div class="row">

                    <div class="col-lg-12">

                        <?php


                        $hot_keys = [];
                        foreach (range('A', 'Z') as $letter) {
                            $hot_keys[] = [ 'value' => $letter, 'text' => $letter ];
                        }

                        $hot_key_number = [];
                        foreach (range('1', '9') as $letter) {
                            $hot_key_number[] = [ 'value' => $letter, 'text' => $letter ];
                        }

                        $rels = []; $target = [];

                        foreach ([
                                     'alternate', 'author', 'bookmark', 'external',
                                     'help', 'license', 'next', 'nofollow',
                                     'noreferrer', 'noopener', 'prev', 'search', 'tag'
                                 ] as $val) {
                            $rels[] = [ 'value' => $val, 'text' => $val ];
                        }

                        foreach ([
                                     '_self', '_blank', '_parent', '_top'
                                 ] as $val) {
                            $target[] = [ 'value' => $val, 'text' => $val ];
                        }



                        echo render_input('pml_title' , 'pml_title' , $data->pml_title , 'text' , [ 'required' => true ] );

                        echo render_input('pml_link' , 'pml_link' , $data->pml_link , 'text' , [ 'required' => true, 'placeholder' => 'admin/modules or https://example.com' ] );

                        ?>

                    </div>

                    <div class="col-md-6">

                        <?php

                        echo render_select('pml_rels' , $rels, [ 'value' , 'text' ] ,'pml_rels' , $data->pml_rels );

                        echo render_select('pml_hotkeys' , $hot_keys, [ 'value' , 'text' ] ,'pml_hotkeys' , $data->pml_hotkeys );

                        ?>

                    </div>

                    <div class="col-md-6">

                        <?php

                        echo render_select('pml_target' , $target , [ 'value' , 'text' ] ,'pml_target' , $data->pml_target );

                        echo render_select('pml_hotkey_numbers' , $hot_key_number, [ 'value' , 'text' ] ,'pml_hotkey_numbers' , $data->pml_hotkey_numbers );

                        ?>

                    </div>

                    <div class="col-md-12">
                        <div class="alert alert-info" style="margin-top:10px;">Use <strong>admin/modules</strong> for CRM internal links. The module will automatically correct common mistakes like <strong>adminmodules</strong> or <strong>ttps://</strong>.</div>
                    </div>

                    <div class="col-md-6">

                        <?php

                        echo render_input('pml_order' , 'pml_order' , $data->pml_order , 'number' , [ 'step' => '1' ]  );

                         ?>

                    </div>


                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

            </div>

        <?php echo form_close(); ?>

    </div>

</div>



