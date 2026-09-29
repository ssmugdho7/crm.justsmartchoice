<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style type="text/css">
    .vl_video_link {
        position: relative;
        display: initial;
    }

    .vl_video_link a {
        border: 1px dotted #b3b3b3;
        display: inline-block;
        padding: 18px 54px;
        border-radius: 6px;
        position: relative;
        padding-bottom: 35px;
    }

    .d_l_btn {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        text-align: center;
        margin-bottom: 0;
        border-top: 1px solid #e1e1e1;
        padding-top: 5px;
        background-color: #e3e8ee;
        font-size: 11px;
    }

    .d_l_btn i {}

    .vl_video_link h5 {
        font-size: 20px;
    }

    .vl_video_link h5 i {}

    .vl_video_link span {
        position: absolute;
        right: 0;
        z-index: 999999;
        padding: 5px 9px;
        font-size: 10px;
        color: red;
        cursor: pointer;
    }

    .vl_video_link p {
        position: absolute;
        right: 0;
        z-index: 999999;
        padding: 5px 9px;
        font-size: 10px;
        color: #4f709b;
        cursor: pointer;
    }

    p._delete_thumb {
        display: inherit;
        color: red;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        echo form_open_multipart($this->uri->uri_string(), array('id' => 'upload_video_form'));
                        $value = isset($video) ? $video->title : '';
                        echo render_input('title', _l('vl_video_title'), $value);
                        $selected = isset($video) ? $video->category : '';
                        $data_category = isset($data_category) && !empty($data_category) ? $data_category : [];
                        echo render_select('category', $data_category, array('id', 'category'), _l('vl_video_cate'), $selected);
                        //$selected = isset($video->project_id) && !empty($video->project_id) ? $video->project_id : '';
                        // echo render_select('project_id', $projects, array('id', 'name'), _l('vl_projects'), $selected);
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rel_type"
                                        class="control-label"><?php echo _l('task_related_to'); ?></label>
                                    <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="project" <?php if (isset($video)) {
                                                                    if ($video->rel_type == 'project') {
                                                                        echo 'selected';
                                                                    }
                                                                } ?>><?php echo _l('project'); ?></option>
                                        <option value="customer" <?php if (isset($video)) {
                                                                        if ($video->rel_type == 'customer') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?>><?php echo _l('customer'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group<?= !isset($video) ? ' hide' : ''; ?>" id="rel_id_wrapper">
                                    <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="ajax-sesarch" data-width="100%"
                                            data-live-search="true"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <?php if (isset($video) && $video->rel_id != '' && $video->rel_type != '') {
                                                $rel_data = get_relation_data($video->rel_type, $video->rel_id);
                                                $rel_val  = get_relation_values($rel_data, $video->rel_type);
                                                echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $staff_members = isset($staff_members) && !empty($staff_members) ? $staff_members : [];
                        $selected_uploader = isset($video) && !empty($video->uploaded_by) ? $video->uploaded_by : get_staff_user_id();
                        echo render_select('uploaded_by', $staff_members, ['staffid', ['firstname', 'lastname']], _l('vl_uploaded_by'), $selected_uploader, [], [], '', '', false);
                        ?>
                        <div class="form-group">
                            <label for="upload_type" class="control-label clearfix">
                                <?php echo _l('vl_ask_for_upload_file'); ?> </label>
                            <div class="radio radio-primary radio-inline">
                                <?php $valuee = isset($video) ? $video->upload_type : ''; ?>
                                <input type="radio" class="upload_type" id="upload-type-file" name="upload_type" value="file" <?php if ($valuee == 'file') : ?>checked<?php endif; ?> checked>
                                <label for="upload-type-file">
                                    <?php echo _l('vl_input_option1'); ?> </label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <?php $checked = isset($video) && !empty($video->upload_type) && $video->upload_type != 'file' ? 'checked' : ''; ?>
                                <input type="radio" id="upload-type-link" class="upload_type" name="upload_type" value="link" <?echo $checked; ?>>
                                <label for="upload-type-link">
                                    <?php echo _l('vl_input_option2'); ?> </label>
                            </div>
                        </div>
                        <?php
                        echo render_input('upload_video_thumbnail', _l('vl_video_thumbnail'), '', 'file', [], [],);
                        if (isset($video) && !empty($video->upload_video_thumbnail)) {
                            echo "<div class='form-group  vl_video_link'><a href='" . base_url() . 'uploads/video_library/' . $video->upload_video_thumbnail . "' download> <h5><i class='fa fa-image'></i></h5> <p class='d_l_btn'><i class='fa fa-download'></i> Download</p> </a> <p class='_delete_thumb' ' data-id='" . $video->id . "'><i class='fa fa-times' data-id='" . $video->id . "'></i></p></div>";
                        }
                        $link = isset($video) && $video->upload_type != 'file' ? $video->upload_video : '';
                        $hidden = $link ? 'showf' : 'hidden showl';
                        echo render_input('link', _l('vl_link_url'), $link, '',  ['placeholder' => _l('vl_link_url_placeholder')], [], $hidden);
                        $hidden = $link ? 'hidden showl' : 'showf';
                        echo render_input('upload_video', _l('vl_video_file'), '', 'file', [], [], $hidden);
                        if (isset($video) && !empty($video->upload_video) && $video->upload_type == 'file') {
                            echo "<div class='form-group vl_video_link'><a href='" . base_url() . 'uploads/video_library/' . $video->upload_video . "' download> <h5><i class='fa fa-video-camera'></i></h5> <p class='d_l_btn'><i class='fa fa-download'></i> Download</p> </a> <span class='_delete' data-id='" . $video->id . "'><i class='fa fa-times' data-id='" . $video->id . "'></i></span></div>";
                        }
                        $value = isset($video) ? $video->description : '';
                        echo render_textarea('description', _l('vl_video_description'), $value); ?>
                        <button type="submit" class="btn btn-info pull-right save_vl_btn" data-><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
init_tail();
$vl_allowed_type = explode(',', get_option('vl_allowed_type'));
$dotlessArray = array_map(function ($item) {
    return str_replace('.', '', $item);
}, $vl_allowed_type);
$vl_conv_allowed_type = implode('|', $dotlessArray);
?>
<script>
    var _rel_id = $('#rel_id'),
        _rel_type = $('#rel_type'),
        _rel_id_wrapper = $('#rel_id_wrapper');

    function validate_form() {
        <?php if (!isset($video) && empty($video)) { ?>
            appValidateForm($('#upload_video_form'), {
                title: 'required',
                category: 'required',
                description: 'required',
                upload_video: {
                    required: {
                        depends: function(element) {
                            if ($('.upload_type') == 'file') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                    },
                    extension: "<?php echo "$vl_conv_allowed_type";  ?>",

                },
                upload_video_thumbnail: {
                    extension: "jpg|jpeg|gif|png|bmp",
                },
                link: {
                    required: {
                        depends: function(element) {
                            if ($('.upload_type') == 'link') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                    }
                }
            });
        <?php } else { ?>
            appValidateForm($('#upload_video_form'), {
                title: 'required',
                category: 'required',
                description: 'required',
                upload_video: {
                    required: {
                        depends: function(element) {
                            if ($('.upload_type') == 'file') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                    },
                    extension: "<?php echo "$vl_conv_allowed_type";  ?>",

                },
                upload_video_thumbnail: {
                    extension: "<?php echo "$vl_conv_allowed_type";  ?>",
                },
                link: {
                    required: {
                        depends: function(element) {
                            if ($('.upload_type') == 'link') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                    }
                }
            });
        <?php } ?>
    }
    $(function() {
        $('body').on('click', 'button.save_vl_btn', function() {
            validate_form();
            $('form#upload_video_form').submit();
        });
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
        _rel_type.on('change', function() {

            var clonedSelect = _rel_id.html('').clone();
            _rel_id.selectpicker('destroy').remove();
            _rel_id = clonedSelect;
            $('#rel_id_select').append(clonedSelect);
            $('.rel_id_label').html(_rel_type.find('option:selected').text());

            task_rel_select();
            if ($(this).val() != '') {
                _rel_id_wrapper.removeClass('hide');
            } else {
                _rel_id_wrapper.addClass('hide');
            }
        });

        init_datepicker();
        init_color_pickers();
        init_selectpicker();
        task_rel_select();
    })
    $("body").on('click', '._delete_thumb', function(e) {
        if (confirm_delete()) {
            return true;
        }
        return false;
    });
    $(document).on('click', '.vl_video_link span', function(event) {
        var video_id = $(event.currentTarget).data('id');
        $.post(admin_url + "video_library/delete_video/" + video_id, function(resp) {
            resp = JSON.parse(resp);
            if (resp.status == 'success') {
                location.reload();
            }
            alert_float(resp.status, resp.message);
        });
    });
    var jFoo = <?php echo json_encode($valuee); ?>;
    if (jFoo == 'link') {
        $('.showf').hide();
        $('.showl').removeClass("hidden");
    }
    $(document).on('change', '.upload_type', function() {
        if (this.value == 'link') {
            $('.showf').hide();
            $('.showl').removeClass("hidden");
            $(".showl").css('display', 'block');
        }
        if (this.value == 'file') {
            $('.showl').hide();
            $('.showf').show();
        }
    });

    $(document).on('click', '.vl_video_link p', function(event) {
        var video_id = $(event.currentTarget).data('id');
        $.post(admin_url + "video_library/delete_thumbnail_video/" + video_id, function(resp) {
            resp = JSON.parse(resp);
            if (resp.status == 'success') {
                location.reload();
            }
            // alert_float(resp.status, resp.message);
        });
    });

    function task_rel_select() {
        var serverData = {};
        serverData.rel_id = _rel_id.val();
        serverData.type = _rel_type.val();
        init_ajax_search(_rel_type.val(), _rel_id, serverData);
    }
</script>
</body>

</html>