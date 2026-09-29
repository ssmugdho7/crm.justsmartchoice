<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<head>
    <style>
        #sortable {
            list-style-type: none;
            margin: 0;
            padding: 0;
            width: 90%;
        }

        #sortable li {
            margin: 3px 3px 3px 0;
            padding: 1px;
            float: left;
            border: 0;
            background: none;
        }

        #sortable li img {
            width: 180px;
            height: 140px;
        }
    </style>
</head>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            $formUrl = 'projectspot/create_project';
            if (isset($project_data)) {
                $formUrl = 'projectspot/create_project/'.$project_data->id;
            }
            echo form_open_multipart(admin_url($formUrl), ['id' => 'quickshare-form', 'class' => 'dropzone dropzone-manual']);
            ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                            <?php echo $title; ?>
                        </h4>
                        <div class="col-md-6">
                            <?php echo render_input('project_name', 'projectspot_project_name', $project_data->project_name ?? ''); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_select('category_id', $project_categories, ['id', ['category_name']], 'projectspot_category', $project_data->category_id ?? '') ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('project_description', 'projectspot_project_description', $project_data->project_description ?? '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>


                        <div class="dropzone-previews"></div>
                        <hr class="hr-panel-separator"/>

                        <div class="col-md-<?php echo isset($project_data) ? '6' : '12'; ?>">
                            <div id="dropzoneDragArea" class="dz-default dz-message">
                                <span><?php echo _l('qs_attach_file_placeholder'); ?></span>
                            </div>
                        </div>

                        <?php
                        if (isset($project_data, $project_images)) {
                            ?>
                            <div class="col-md-6">
                                <hr>
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                                    <?php echo _l('projectspot_project_rearrange_images'); ?> <br></h4><p><?php echo _l('projectspot_project_p_text'); ?></p>
                                    <button type="button" id="updateOrder" class="btn btn-primary"><?php echo _l('projectspot_project_update_order'); ?></button>
                                <ul id="sortable">
                                    <?php
                                    foreach ($project_images as $image) {
                                        ?>
                                        <li class="ui-state-default" data-id="<?php echo $image['id']; ?>">
                                            <img src="<?php echo module_dir_url('projectspot', 'uploads/gallery_images/' . $project_data->id . '/' . $image['image_url']) ?>"
                                                 title="<?php echo $image['image_url'] ?>">
                                            <?php
                                            if (has_permission('projectspot', '', 'delete')) {
                                                ?>
                                                    <br>
                                                <button onclick="delete_image('<?php echo $image['project_id'] ?>','<?php echo $image['id'] ?>','<?php echo $image['image_url'] ?>')"
                                                   class="btn btn-danger _delete">X</button>
                                                <?php
                                            }
                                            ?>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>

    Dropzone.options.quickshareForm = false;
    var expenseDropzone;

    $(function () {

        // Initialize sortable
        $("#sortable").sortable();

        if ($('#dropzoneDragArea').length > 0) {
            expenseDropzone = new Dropzone("#quickshare-form", appCreateDropzoneOptions({
                autoProcessQueue: false,
                clickable: '#dropzoneDragArea',
                previewsContainer: '.dropzone-previews',
                maxFiles: 2,
                addRemoveLinks: true,
                success: function (file, response) {
                    response = JSON.parse(response);
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length ===
                        0) {
                        window.location.assign(response.url);
                    }
                },
            }));
        }

        appValidateForm($('#quickshare-form'), {
            'project_name': 'required',
            'project_description': 'required',
            'category_id': 'required'
        }, projectSpotSubmitHandler);

        $('#updateOrder').click(function () {
            var imageids_arr = [];

            $('#sortable li').each(function () {
                var id = $(this).data('id');
                imageids_arr.push(id);
            });

            $.post(admin_url + "projectspot/update_images_order", {imageids: imageids_arr}).done(function (response) {
                response = JSON.parse(response);
                if (response.error == 0) {
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
            });
        });

    });

    function projectSpotSubmitHandler(form) {

        $.post(form.action, $(form).serialize()).done(function (response) {
            response = JSON.parse(response);

            if (response.project_id) {
                if (typeof (expenseDropzone) !== 'undefined') {
                    if (expenseDropzone.getQueuedFiles().length > 0) {
                        expenseDropzone.options.url = admin_url + 'projectspot/queue_uploaded_file/' + response
                            .project_id;
                        expenseDropzone.processQueue();
                    } else {
                        window.location.assign(response.url);
                    }
                } else {
                    window.location.assign(response.url);
                }
            } else {
                window.location.assign(response.url);
            }

        });

    }

    function delete_image(project_id, image_id, image_path) {
        $.post(admin_url + "projectspot/delete_image", {project_id: project_id, image_id: image_id, image_path: image_path}).done(function (response) {
            location.reload();
        });
    }

</script>
</body>

</html>
