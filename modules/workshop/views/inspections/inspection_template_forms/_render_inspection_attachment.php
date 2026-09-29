<div id="images_old_preview" class="mbot20">
    <?php if( isset($inspection_attachments) && count($inspection_attachments) > 0){ ?>
        <?php foreach ($inspection_attachments as $product_attachment) { ?>

            <div class="pdf_attachment dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                <div class="dz-image">
                    <?php if(is_image(INSPECTION_QUESTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                        <?php if(file_exists(INSPECTION_QUESTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                            <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/inspection_questions/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                        <?php } ?>
                    <?php } ?>
                </div>
                <?php if(!is_image(INSPECTION_QUESTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                    <div class="dz-details dz-active"><div class="dz-size"><span data-dz-size=""></span></div>    <div class="dz-filename"><span data-dz-name=""><?php echo html_entity_decode($product_attachment['file_name']); ?></span></div>  </div>
                <?php } ?>

                <div class="dz-error-mark">
                    <a class="dz-remove" data-dz-remove>Remove file</a>
                </div>
                <div class="remove_file">
                    <a class="btn btn-sm" name="preview-btn" onclick="preview_file(this); return false;" rel_id = "<?php echo html_entity_decode($product_attachment['rel_id']); ?>" id = "<?php echo html_entity_decode($product_attachment['id']); ?>" data-toggle="tooltip" title data-original-title="<?php echo _l("preview_file") ?>"><i class="fa fa-eye"></i></a>
                    <?php if($is_delete){ ?>
                        <a href="#" class="text-danger" onclick="delete_inspection_question_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
                    <?php } ?>
                </div>
            </div>

        <?php } ?>
        <div id="pdf_file_data"></div>
    <?php } ?>
</div>