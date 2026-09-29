<?php
$id = $this->uri->segment(4);

?>

<div class=" inline-block full-width simple-editor">
    <?php echo form_open_multipart(admin_url('opportunity/add_opportunity_comment'), ['id' => 'opportunity_comment_form', 'class' => 'dropzone dropzone-manual', 'style' => 'min-height:auto;background-color:#fff;']); ?>
    <input type="hidden" name="opportunityid" value="<?= $id ?>">
    <textarea name="comment" placeholder="<?php echo _l('opportunity_single_add_new_comment'); ?>" id="opportunity_comment" rows="3"
              class="form-control ays-ignore mbot20"></textarea>
    <div id="dropzoneopportunityComment" class="dropzoneDragArea dz-default dz-message  opportunity-comment-dropzone mtop20">
        <span><?php echo _l('drop_files_here_to_upload'); ?></span>
    </div>
    <div class="dropzone-opportunity-comment-previews dropzone-previews"></div>
    <button type="button" class="btn btn-primary mtop10 pull-right " id="addopportunityCommentBtn" autocomplete="off"
            data-loading-text="<?php echo _l('wait_text'); ?>" onclick="add_opportunity_comment(<?= $id ?>)"
            data-comment-opportunity-id="<?= $id ?>">
        <?php echo _l('opportunity_single_add_new_comment'); ?>
    </button>
    <?php echo form_close(); ?>
    <div class="clearfix"></div>
    <?php

    if (count($opportunity_details->comments) > 0) {
        echo '<hr />';
    } ?>
    <div id="opportunity-comments" class="mtop20">
        <?php

        $comments = '';
        $len = count($opportunity_details->comments);
        $i = 0;
        foreach ($opportunity_details->comments as $comment) {

            $comments .= '<div id="comment_' . $comment['id'] . '" data-commentid="' . $comment['id'] . '" data-opportunity-attachment-id="' . $comment['file_id'] . '" class="tc-content opportunity-comment mbot25 tw-p-3  ' . (strtotime($comment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : '') . '">';
            $comments .= '<a data-opportunity-comment-href-id="' . $comment['id'] . '" href="' . admin_url('opportunity/view/' . $opportunity_details->id) . '#comment_' . $comment['id'] . '" class="opportunity-date-as-comment-id"><span class="tw-text-sm"><span class="text-has-action inline-block" data-toggle="tooltip" data-title="' . _dt($comment['dateadded']) . '">' . time_ago($comment['dateadded']) . '</span></span></a>';
            if ($comment['staffid'] != 0) {
                $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . staff_profile_image($comment['staffid'], [
                        'staff-profile-image-small',
                        'media-object img-circle pull-left mright10',
                    ]) . '</a>';
            } elseif ($comment['contact_id'] != 0) {
                $comments .= '<img src="' . contact_profile_image_url($comment['contact_id']) . '" class="client-profile-image-small media-object img-circle pull-left mright10">';
            }
            if ($comment['staffid'] == get_staff_user_id() || is_admin()) {
                $comment_added = strtotime($comment['dateadded']);
                $minus_1_hour = strtotime('-1 hours');
                if (get_option('client_staff_add_edit_delete_opportunity_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_opportunity_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()) {
                    $comments .= '<span class="pull-right"><a href="#" onclick="remove_opportunity_comment(' . $comment['id'] . '); return false;"><i class="fa fa-times text-danger"></i></span></a>';
                    $comments .= '<span class="pull-right mright5"><a href="#" onclick="edit_opportunity_comment(' . $comment['id'] . '); return false;"><i class="fa-regular fa-pen-to-square"></i></span></a>';
                }
            }

            $comments .= '<div class="media-body comment-wrapper">';
            $comments .= '<div class="mleft40">';

            if ($comment['staffid'] != 0) {
                $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . $comment['staff_full_name'] . '</a> <br />';
            } elseif ($comment['contact_id'] != 0) {
                $comments .= '<span class="label label-info mtop5 mbot5 inline-block">' . _l('is_customer_indicator') . '</span><br /><a href="' . admin_url('clients/client/' . get_user_id_by_contact_id($comment['contact_id']) . '?contactid=' . $comment['contact_id']) . '" class="pull-left" target="_blank">' . get_contact_full_name($comment['contact_id']) . '</a> <br />';
            }

            $comments .= '<div data-edit-comment="' . $comment['id'] . '" class="hide edit-opportunity-comment"><textarea rows="5" id="opportunity_comment_' . $comment['id'] . '" class="ays-ignore form-control">' . str_replace('[opportunity_attachment]', '', $comment['content']) . '</textarea>
                  <div class="clearfix mtop20"></div>
                  <button type="button" class="btn btn-primary pull-right" onclick="save_edited_comment(' . $comment['id'] . ',' . $opportunity_details->id . ')">' . _l('submit') . '</button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment(' . $comment['id'] . ')">' . _l('cancel') . '</button>
                  </div>';
            if ($comment['file_id'] != 0) {
                $comment['content'] = str_replace('[opportunity_attachment]', '<div class="clearfix"></div>' . $attachments_data[$comment['file_id']], $comment['content']);
                // Replace lightbox to prevent loading the image twice
                $comment['content'] = str_replace('data-lightbox="opportunity-attachment"', 'data-lightbox="opportunity-attachment-comment-' . $comment['id'] . '"', $comment['content']);
            } elseif (count($comment['attachments']) > 0) {

                $comment_attachments_html = '';
                foreach ($comment['attachments'] as $attachment) {
                    $comment_attachments_html .= '<div data-num="' . $i . '" data-commentid="' . $attachment['comment_file_id'] . '" data-comment-attachment="' . $attachment['opportunity_comment_id'] . '" data-task-attachment-id="' . $attachment['id'] . '" class="task-attachment-col col-md-6 ">
                        <ul class="list-unstyled task-attachment-wrapper" data-placement="right" data-toggle="tooltip" data-title="' . $attachment['file_name'] . '">
                            <li class="mbot10 task-attachment' . (strtotime($attachment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : '') . '">';


                    $externalPreview = false;
                    $is_image = false;
                    $path = get_upload_path_for_opportunity() . $opportunity_details->id . '/' . $attachment['file_name'];
                    $href_url = site_url('admin/opportunity/file_download/opportunity_comments/' . $attachment['attachment_key']);
                    $isHtml5Video = is_html5_video($path);
                    if (empty($attachment['external'])) {
                        $is_image = is_image($path);
                        $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $attachment['filetype']);
                    } elseif ((!empty($attachment['thumbnail_link']) || !empty($attachment['external'])) && !empty($attachment['thumbnail_link'])) {
                        $is_image = true;
                        $img_url = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
                        $externalPreview = $img_url;
                        $href_url = $attachment['external_link'];
                    } elseif (!empty($attachment['external']) && empty($attachment['thumbnail_link'])) {
                        $href_url = $attachment['external_link'];
                    }
                    if (!empty($attachment['external']) && $attachment['external'] == 'dropbox' && $is_image) {
                        $comment_attachments_html .= '<a href="' . $href_url . '" target="_blank" class="" data-toggle="tooltip" data-title="' . _l('open_in_dropbox') . '"><i class="fa fa-dropbox" aria-hidden="true"></i></a>';
                    } elseif (!empty($attachment['external']) && $attachment['external'] == 'gdrive') {
                        $comment_attachments_html .= '<a href="' . $href_url . '" target="_blank" class="" data-toggle="tooltip" data-title="' . _l('open_in_google') . '"><i class="fa-brands fa-google" aria-hidden="true"></i></a>';
                    }


                    $comment_attachments_html .= '<div class="clearfix"></div>';
                    $comment_attachments_html .= '<div class="' . ($is_image ? 'preview-image' : (!$isHtml5Video ? 'task-attachment-no-preview' : '')) . '">';
                    if (!$isHtml5Video) {
                        $comment_attachments_html .= '<a href="' . (!$externalPreview ? $href_url : $externalPreview) . '" target="_blank"' . ($is_image ? ' data-lightbox="task-attachment"' : '') . ' class="' . ($isHtml5Video ? 'video-preview' : '') . '">';
                    }
                    if ($is_image) {
                        $comment_attachments_html .= '<img src="' . $img_url . '" class="img img-responsive">';
                    } elseif ($isHtml5Video) {
                        $comment_attachments_html .= '<video width="100%" height="100%" src="' . site_url('download/preview_video?path=' . protected_file_url_by_path($path) . '&type=' . $attachment['filetype']) . '" controls>Your browser does not support the video tag.</video>';
                    } else {
                        $comment_attachments_html .= '<i class="' . get_mime_class($attachment['filetype']) . '"></i>' . $attachment['file_name'];
                    }
                    if (!$isHtml5Video) {
                        $comment_attachments_html .= '</a>';
                    }
                    $comment_attachments_html .= '</div>';
                    $comment_attachments_html .= '<div class="clearfix"></div>';
                    $comment_attachments_html .= '</li>';
                    $comment_attachments_html .= '</ul>';
                    $comment_attachments_html .= '</div>';
                    $i++;
                }


                ?>

                <?php


                $comment['content'] = str_replace('[opportunity_attachment]', '<div class="clearfix"></div>' . $comment_attachments_html, $comment['content']);
                // Replace lightbox to prevent loading the image twice
                $comment['content'] = str_replace('data-lightbox="opportunity-attachment"', 'data-lightbox="opportunity-comment-files-' . $comment['id'] . '"', $comment['content']);
                $comment['content'] .= '<div class="clearfix"></div>';
                $comment['content'] .= '<div class="text-center download-all">
                   <hr class="hr-10" />
                   <a href="' . admin_url('opportunity/download_files/' . $opportunity_details->id . '/' . $comment['id']) . '" class="bold">' . _l('download_all') . ' (.zip)
                   </a>
                   </div>';
            }
            $comments .= '<div class="comment-content mtop10">' . app_happy_text(check_for_links($comment['content'])) . '</div>';
            $comments .= '</div>';
            if ($i >= 0 && $i != $len - 1) {
                $comments .= '<hr class="opportunity-info-separator" />';
            }
            $comments .= '</div>';
            $comments .= '</div>';
            $i++;
        }
        echo $comments;
        ?>
    </div>
</div>

<script type="text/javascript">
    if (typeof opportunityCommentAttachmentDropzone != "undefined") {
        opportunityCommentAttachmentDropzone.destroy();
    }

    opportunityCommentAttachmentDropzone = new Dropzone(
        "#opportunity_comment_form",
        appCreateDropzoneOptions({
            uploadMultiple: true,
            clickable: "#dropzoneopportunityComment",
            previewsContainer: ".dropzone-opportunity-comment-previews",
            autoProcessQueue: false,
            addRemoveLinks: true,
            parallelUploads: 20,
            maxFiles: 20,
            paramName: "file",
            sending: function (file, xhr, formData) {
                formData.append(
                    "opportunity_id",
                    $("#addopportunityCommentBtn").attr("data-comment-opportunity-id")
                );
                if (tinyMCE.activeEditor) {
                    formData.append("content", tinyMCE.activeEditor.getContent());
                } else {
                    formData.append("content", $("#opportunity_comment").val());
                }
            },
            success: function (files, response) {
                window.location.reload();
                if (
                    this.getUploadingFiles().length === 0 &&
                    this.getQueuedFiles().length === 0
                ) {
                    tinymce.remove("#opportunity_comment");
                }
            },
        })
    );

    function add_opportunity_comment(opportunity_id) {
        var data = {};

        if (opportunityCommentAttachmentDropzone.files.length > 0) {
            opportunityCommentAttachmentDropzone.processQueue(opportunity_id);
            return;
        }
        if (tinymce.activeEditor) {
            data.content = tinyMCE.activeEditor.getContent();
        } else {
            data.content = $("#opportunity_comment").val();
            data.no_editor = true;
        }
        data.opportunity_id = opportunity_id;
        $.post(admin_url + "opportunity/add_opportunity_comment", data).done(function (response) {

            tinymce.remove("#opportunity_comment");
            window.location.reload();
        });
    }

    // Deletes opportunity comment from database
    function remove_opportunity_comment(commentid) {
        if (confirm_delete()) {
            requestGetJSON("opportunity/remove_comment/" + commentid).done(function (
                response
            ) {
                if (response.success === true || response.success == "true") {
                    $('[data-commentid="' + commentid + '"]').remove();
                    $('[data-comment-attachment="' + commentid + '"]').remove();
                }
            });
        }
    }

    // Init opportunity edit comment
    function edit_opportunity_comment(id) {
        var edit_wrapper = $('[data-edit-comment="' + id + '"]');
        edit_wrapper.next().addClass("hide");
        edit_wrapper.removeClass("hide");

        if (!is_ios()) {
            tinymce.remove("#opportunity_comment_" + id);
            var editorConfig = _simple_editor_config();
            editorConfig.auto_focus = "opportunity_comment_" + id;
            editorConfig.setup = function (editor) {
                initStickyTinyMceToolbarInModal(
                    editor,
                    document.querySelector(".opportunity-modal-single")
                );
            };
            init_editor("#opportunity_comment_" + id, editorConfig);
            tinymce.triggerSave();
        }
    }

    // Cancel editing commment after clicked on edit href
    function cancel_edit_comment(id) {
        var edit_wrapper = $('[data-edit-comment="' + id + '"]');
        tinymce.remove('[data-edit-comment="' + id + '"] textarea');
        edit_wrapper.addClass("hide");
        edit_wrapper.next().removeClass("hide");
    }

    // Save opportunity edited comment
    function save_edited_comment(id, opportunity_id) {
        tinymce.triggerSave();
        var data = {};
        data.id = id;
        data.opportunity_id = opportunity_id;
        data.content = $('[data-edit-comment="' + id + '"]')
            .find("textarea")
            .val();
        if (is_ios()) {
            data.no_editor = true;
        }
        $.post(admin_url + "opportunity/edit_comment", data).done(function (response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == "true") {
                alert_float("success", response.message);
                window.location.reload();
            } else {
                cancel_edit_comment(id);
            }
            tinymce.remove('[data-edit-comment="' + id + '"] textarea');
        });
    }
</script>