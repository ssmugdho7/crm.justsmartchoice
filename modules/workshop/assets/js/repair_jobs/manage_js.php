<script type="text/javascript">
    $(function(){
        'use strict';

        var repair_job_params = {
            "client_filter": "[name='client_filter']",
            "appointment_type_filter": "[name='appointment_type_filter']",
            "from_date_filter": "[name='from_date_filter']",
            "to_date_filter": "[name='to_date_filter']",
            "device_filter": "[name='device_filter']",
        };
        var repair_job_table = $('table.table-repair_job_table');
        var _table_api = initDataTable(repair_job_table, admin_url+'workshop/repair_job_table', [0], [0], repair_job_params, ['0', 'desc']);
        var hidden_columns = [0];
        $('.table-repair_job_table').DataTable().columns(hidden_columns).visible(false, false);

        $.each(repair_job_params, function(i, obj) {
            $('select' + obj).on('change', function() {  
                $('.table-repair_job_table').DataTable().ajax.reload();
            });
        });

        $('#from_date_filter').on('change', function() {
            repair_job_table.DataTable().ajax.reload();
        });
        $('#to_date_filter').on('change', function() {
            repair_job_table.DataTable().ajax.reload();
        });

    });

    function repair_job_modal(repair_job_id) {
        "use strict";

        $("#modal_wrapper").load("<?php echo admin_url('workshop/load_repair_job_modal'); ?>", {
          repair_job_id: repair_job_id,
      }, function() {
          $("body").find('#repair_jobModal').modal({ show: true, backdrop: 'static' });
          init_selectpicker();
          init_datepicker();

      });

    }

    function delete_repair_job(id) {
        "use strict";

        if (confirm_delete()) {
            $.post(admin_url + "workshop/delete_repair_job/" + id).done(function (response) {
                response = JSON.parse(response);

                if (response.success === true || response.success == "true") {
                    alert_float('success', response.message)
                    $('.table-repair_job_table').DataTable().ajax.reload();
                }
            });
        }
    }

    function delete_repair_job_attachment(wrapper, attachment_id) {
        "use strict";  

        if (confirm_delete()) {
            $.get(admin_url + 'workshop/delete_repair_job_attachment/' +attachment_id, function (response) {
                if (response.success == true) {
                    $(wrapper).parents('.dz-preview').remove();

                    var totalAttachmentsIndicator = $('.dz-preview'+attachment_id);
                    var totalAttachments = totalAttachmentsIndicator.text().trim();

                    if(totalAttachments == 1) {
                        totalAttachmentsIndicator.remove();
                    } else {
                        totalAttachmentsIndicator.text(totalAttachments-1);
                    }
                    alert_float('success', "<?php echo _l('wshop_deleted_repair_job_image_successfully') ?>");

                } else {
                    alert_float('danger', "<?php echo _l('wshop_deleted_repair_job_image_failed') ?>");
                }
            }, 'json');
        }
        return false;
    }

    function repair_job_status_mark_as(status, id, type) {
        "use strict"; 
        
        var url = 'workshop/repair_job_status_mark_as/' + status + '/' + id + '/' + type;
        var taskModalVisible = $('#task-modal').is(':visible');
        url += '?single_task=' + taskModalVisible;
        $("body").append('<div class="dt-loader"></div>');

        requestGetJSON(url).done(function (response) {
            $("body").find('.dt-loader').remove();
            if (response.success === true || response.success == 'true') {

                var av_tasks_tables = ['table.table-repair_job_table'];
                $.each(av_tasks_tables, function (i, selector) {
                    if ($.fn.DataTable.isDataTable(selector)) {
                        $(selector).DataTable().ajax.reload(null, false);
                    }
                });
                alert_float('success', response.message);
            }
        });
    }

</script>