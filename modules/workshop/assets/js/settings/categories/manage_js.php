<script type="text/javascript">
    $(function(){
        'use strict';

        var category_params = {
            "user_for_filter": "[name='user_for_filter']",
        };
        var category_table = $('table.table-category_table');
        var _table_api = initDataTable(category_table, admin_url+'workshop/category_table', [0], [0], category_params, ['2', 'asc']);
        var hidden_columns = [0];
        $('.table-category_table').DataTable().columns(hidden_columns).visible(false, false);

        $.each(category_params, function(i, obj) {
            $('select' + obj).on('change', function() {  
                $('.table-category_table').DataTable().ajax.reload();
            });
        });
        
        $( document ).ready(function() {

            $('#add_edit_category').appFormValidator({
                rules: {
                    code:'required',
                    name:'required',
                    use_for:'required',
                    days_off: {
                        required: true,
                        remote: {
                            url: admin_url + "workshop/category_days_off_exists",
                            type: 'post',
                            data: {
                                days_off: function() {
                                    return $('input[name="days_off"]').val();
                                },
                                id: function() {
                                    return $('input[name="id"]').val();
                                }
                            }
                        }
                    }
                },
                onSubmit: manage_category,
                messages: {
                    days_off: '<?php echo _l("wshop_category_already_exists"); ?>',
                },
            });

          });

        $('#category').on('hidden.bs.modal', function(event) {
            'use strict';
            
            $('#category input[name="code"]').val('');
            $('#category input[name="name"]').val('');
            $('#category select[name="use_for"]').val('').change();
            $('#category textarea[name="description"]').val('');

            $('#category #category_additional').html('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });

    });

    function manage_category(form) {
        'use strict';

        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);

            if (response.success) {
                $('.table-category_table').DataTable().ajax.reload();
                alert_float('success', response.message);
            }

            $(form).trigger('reinitialize.areYouSure');
            $('#category').modal('hide');
        });
        return false;
    }

    function new_category(){
        'use strict';

        $('#category').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_category(invoker,id){
        'use strict';

        var code = $(invoker).data('code');
        var name = $(invoker).data('name');
        var use_for = $(invoker).data('use_for');
        var description = $(invoker).data('description');

        $('#category_additional').append(hidden_input('id',id));
        $('#category input[name="code"]').val(code);
        $('#category input[name="name"]').val(name);
        $('#category select[name="use_for"]').val(use_for).change();
        $('#category textarea[name="description"]').val(description);

        $('#category').modal('show');
        $('.add-title').addClass('hide');
    }
</script>