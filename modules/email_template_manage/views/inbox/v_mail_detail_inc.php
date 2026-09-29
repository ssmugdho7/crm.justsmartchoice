<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>




        <div class="panel_s">

            <div class="panel-heading">

                <div>

                    <strong style="font-size: 20px"> <?php echo $detail->subject?> </strong>


                    <a style="float: right" class="btn text-danger _delete" href="<?php echo admin_url('email_template_manage/inbox_delete_mail/'.$detail->id)?>" data-toggle="tooltip" title="" data-original-title="<?php echo _l('delete')?>">
                        <i class="fa fa-trash"></i>
                    </a>



                    <a style="float: right" class="btn text-default" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_email_forward')?>" onclick="email_template_manage_mail_response( <?php echo $detail->id ?> , 'forward' ); return false;">
                        <i class="fa fa-share"></i>
                    </a>


                    <a style="float: right" class="btn text-default" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_email_reply')?>" onclick="email_template_manage_mail_response( <?php echo $detail->id ?> , 'reply' ); return false;">
                        <i class="fa fa-reply"></i>
                    </a>


                    <a style="float: right" class="btn text-default btn-star-tag-0 <?php if ( $detail->is_stard == 1 ) { ?> hide <?php } ?>" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_add_star'); ?>" onclick="email_template_manage_update_star_inc( <?php echo $detail->id?> , 1 ); return false;">
                        <i class="fa fa-star "></i>
                    </a>

                    <a style="float: right" class="btn text-success btn-star-tag-1 <?php if ( $detail->is_stard == 0 ) { ?> hide <?php } ?>" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_remove_star'); ?>" onclick="email_template_manage_update_star_inc( <?php echo $detail->id?> , 0 ); return false;">
                        <i class="fa fa-star "></i>
                    </a>


                    <a style="float: right" class="btn text-warning"  href="<?php echo admin_url('email_template_manage/inbox_read_status/'.$detail->id)?>" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_mark_as_unread'); ?>" >
                        <i class="fa fa-envelope  "></i>
                    </a>




                </div>

            </div>


            <div class="panel-body">

                <p> <strong style="font-weight: bold"><?php echo _l('email_template_manage_from_name')?> : </strong> <?php echo email_template_decode_mime_header($detail->from_name)." ( $detail->from_email )" ?> </p>
                <p> <strong style="font-weight: bold"><?php echo _l('email_template_manage_log_table_head_date')?> : </strong> <?php echo _d($detail->mail_date) ?> </p>
                <p> <strong style="font-weight: bold"><?php echo _l('email_template_manage_to')?> : </strong> <?php echo $detail->to ?> </p>
                <p> <strong style="font-weight: bold"><?php echo _l('email_template_manage_cc')?> : </strong> <?php echo $detail->cc ?> </p>

                <hr />

                <div>
                    <?php //echo nl2br( $detail->message )?>
                    <?php echo ( $detail->message )?>
                </div>



                <?php if ( !empty( $attachments ) ) { ?>

                    <hr class="hr-panel-separator" />

                    <div class=" col-md-offset-1">


                        <?php foreach ($attachments as $attachment) {
                            $attachment_url = site_url($attachment['file_path'] . $attachment['file_name']);
                            ?>

                            <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">

                                <div class="col-md-8">
                                    <div class="pull-left">
                                        <i class="<?php echo get_mime_class($attachment['file_type']); ?>"></i>
                                    </div>
                                    <a href="<?php echo $attachment_url; ?>"
                                       target="_blank"><?php echo $attachment['file_name']; ?></a>
                                    <br/>
                                    <small class="text-muted"> <?php echo $attachment['file_type']; ?></small>
                                </div>


                            </div>
                            <?php
                        } ?>

                    </div>

                <?php } ?>

            </div>

            <div class="panel-footer">

                <a class="btn btn-info" onclick="email_template_manage_back_to_list(); return false;"> <i class="fa fa-arrow-left"></i> <?php echo _l('email_template_manage_back_to_list')?></a>

                <a class="btn btn-primary" onclick="email_template_manage_mail_response( <?php echo $detail->id ?> , 'reply' ); return false;"> <i class="fa fa-reply"></i> <?php echo _l('email_template_manage_email_reply')?></a>
                <a class="btn btn-primary" onclick="email_template_manage_mail_response( <?php echo $detail->id ?> , 'forward' ); return false;"> <i class="fa fa-share"></i> <?php echo _l('email_template_manage_email_forward')?></a>

            </div>


        </div>



        <?php if ( !empty( $email_response ) ) { ?>

            <?php foreach ( $email_response as $response ) { ?>

                <div class="panel_s">

                    <div class="panel-heading">
                        <div>


                            <?php if ( $response->send_rel_type == 'reply' ) { ?>

                                <strong style="font-size: 20px"> <?php echo _l('email_template_manage_replied')?> </strong>

                            <?php } else { ?>

                                <strong style="font-size: 20px"> <?php echo _l('email_template_manage_forwarded')?> </strong>

                            <?php } ?>

                            <?php echo get_staff_full_name( $response->added_staff_id ).' - '._dt($response->date)?>

                            <?php if ( $response->opened == 1 ) {  ?>
                                <span class="label label-success" style="float: right">
                                    <i class="fa-regular fa-clock text-has-action tw-mr-1" data-toggle="tooltip" data-title="<?php echo _dt( $aRow['date_opened'] )?>" data-original-title="" title=""></i>
                                    <?php echo _l('email_template_manage_opened') ?>
                                </span>
                            <?php } ?>

                        </div>
                    </div>

                    <div class="panel-body">


                        <div>
                            <?php echo $response->content?>
                        </div>

                    </div>

                </div>


            <?php } ?>

        <?php } ?>


<script>

    function email_template_manage_update_star_inc( email_id , star_status )
    {

        $.post(admin_url+'email_template_manage/inbox_star', { email_id:email_id , star_status:star_status } ).done(function (){

            alert_float('success' , '<?php echo _l('email_template_manage_success')?>');


            if ( star_status == 0 )
            {
                $('.btn-star-tag-0').removeClass('hide');
                $('.btn-star-tag-1').addClass('hide');
            }
            else
            {
                $('.btn-star-tag-0').addClass('hide');
                $('.btn-star-tag-1').removeClass('hide');
            }

        })

    }

    function email_template_manage_mail_response( mail_id , type )
    {

        tinymce.remove();
        requestGet('email_template_manage/response_mail_modal/' + mail_id +'/'+type ).done(function(response) {

            $('#email_template_manage_modal').html(response);

            $('#email_template_manage_modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });

            tinymce.init({
                selector: "textarea"
            });

        }).fail(function(data) {

            alert_float('danger', data.responseText);

        });


    }


</script>



<style>

    .text-default{
        color: #dae1e8;
    }

</style>
