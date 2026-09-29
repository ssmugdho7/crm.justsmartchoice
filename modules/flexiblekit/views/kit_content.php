<div class="row">
    <div class="col-md-12">
        <?php if($contact_type == FLEXIBLEKIT_CUSTOMER_CONTACT_TYPE){ ?>
            <img src="<?php echo contact_profile_image_url($contact->id) ?>" class="client-profile-image-small mright5">
            <span class='tw-font-semibold'>
                <?php 
                  echo $contact->firstname . ' ' . $contact->lastname
                ?>
            </span>
        <?php } ?>
        <button id='flexiblekit_toggle_form' class="btn btn-primary pull-right">
            <?php echo _l('flexiblekit_new_schedule'); ?>
        </button>
    </div>
</div>
<?php
$attributes = ['id' => 'flexiblekit_form'];
?>
<?php echo form_open(admin_url('flexiblekit/add/'), $attributes); ?>
<?php echo render_input('flexiblekit_contact_type', '', $contact_type, 'hidden') ?>
<div class="row">
    <!-- <hr> -->
    <div class="col-md-6">
        <?php echo render_input('flexiblekit_name', 'flexiblekit_name', '', 'text', ['required' => 'required', 'placeholder' => _l('flexiblekit_name_placeholder')]); ?>
    </div>
    <div class="col-md-6">
        <?php $types = flexiblekit_get_types() ?>
        <?php echo render_select('flexiblekit_type', $types, ['id', 'label'], 'flexiblekit_type', 'call'); ?>
    </div>
    <div class="col-md-6">
        <?php $interval_types = flexiblekit_get_interval_types() ?>
        <?php echo render_select('flexiblekit_interval_type', $interval_types, ['id', 'label'], 'flexiblekit_interval_type', 'day'); ?>
    </div>
    <div id="day-container" class="col-md-6 intervals-container">
        <?php $intervals = flexiblekit_get_intervals() ?>
        <?php echo render_select('flexiblekit_interval', $intervals, ['id', 'label'], 'flexiblekit_interval', '1'); ?>
    </div>
    <div id="week-container" class="col-md-6 intervals-container hidden">
        <?php $intervals = flexiblekit_get_intervals('week') ?>
        <?php echo render_select('flexiblekit_interval', $intervals, ['id', 'label'], 'flexiblekit_interval', '1'); ?>
    </div>
    <div id="month-container" class="col-md-6 intervals-container hidden">
        <?php $intervals = flexiblekit_get_intervals('month') ?>
        <?php echo render_select('flexiblekit_interval', $intervals, ['id', 'label'], 'flexiblekit_interval', '1'); ?>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="flexiblekit_active" id="flexiblekit_active" value="1">
                <label for="flexiblekit_active">
                    <?php echo _l('flexiblekit_active'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="flexiblekit_skip_weekends" id="flexiblekit_skip_weekends" value="1">
                <label for="flexiblekit_skip_weekends">
                    <?php echo _l('flexiblekit_skip_weekends'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <?php echo render_input('flexiblekit_start_datetime', 'flexiblekit_start_datetime', '', 'datetime-local', ['placeholder' => _l('flexiblekit_start_datetime_placeholder')]); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('flexiblekit_end_datetime', 'flexiblekit_end_datetime', '', 'time', ['placeholder' => _l('flexiblekit_end_datetime_placeholder')]); ?>
    </div>
    <div class="col-md-6">
        <?php $contact_name = $contact_type == FLEXIBLEKIT_LEAD_CONTACT_TYPE
            ? $contact->name
            : $contact->firstname . ' ' . $contact->lastname;
        ?>
        <?php echo render_input('flexiblekit_contact_name', 'flexiblekit_contact', $contact_name, 'text'); ?>
        <?php echo render_input('flexiblekit_contact_id', '', $contact->id, 'hidden') ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('flexiblekit_tags', 'flexiblekit_tags', '', 'text'); ?>
    </div>
    <div class="col-md-12">
        <?php echo render_textarea('flexiblekit_description', 'flexiblekit_description', '', [], [], '', ''); ?>
    </div>
    <div class="col-md-6">
        <button type="submit" class="btn btn-primary pull-right">
            <?php echo _l('flexiblekit_submit'); ?>
        </button>
    </div>
</div>
<?php echo form_close(); ?>
<div class="clearfix"></div>
<hr />

<div class="panel_s">
    <div class="panel-body table-responsive">
        <div class="panel-table-full">
            <h4>
                <i class="fab fa-windows"></i>
                <?php echo _l('flexiblekit_configurations') ?>
            </h4>
            <table id="flexiblekit-table" class="table dt-table">
                <thead>
                    <tr>
                        <th>
                            <?php
                            echo _l('flexiblekit_name');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_contact');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_type');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_interval');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_active');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_skip_weekends');
                            ?>
                        </th>
                        <th>
                            <?php
                            echo _l('flexiblekit_start_datetime');
                            ?>
                        </th>
                        <th>
                            <?php echo _l('flexiblekit_options'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody id="flexiblekit_table_content">
                    <?php if (count($flexiblekits) > 0) { ?>
                        <?php foreach ($flexiblekits as $flexiblekit) { ?>
                            <tr>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_name'] ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_contact_name'] ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_type'] ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_interval_label'] ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_active'] == 1 ? 'Yes' : 'No' ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_skip_weekends'] == 1 ? 'Yes' : 'No' ?>
                                </td>
                                <td>
                                    <?php echo $flexiblekit['flexiblekit_start_datetime'] ?>
                                </td>
                                <td>
                                    <button
                                        class="flexiblekit_edit_btn btn btn-primay tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"
                                        data-id="<?php echo $flexiblekit['id'] ?>"
                                        data-name="<?php echo $flexiblekit['flexiblekit_name'] ?>"
                                        data-contact-name="<?php echo $flexiblekit['flexiblekit_contact_name'] ?>"
                                        data-contact-id="<?php echo $flexiblekit['flexiblekit_contact_id'] ?>"
                                        data-contact-type="<?php echo $flexiblekit['flexiblekit_contact_type'] ?>"
                                        data-type="<?php echo $flexiblekit['flexiblekit_type'] ?>"
                                        data-interval-type="<?php echo $flexiblekit['flexiblekit_interval_type'] ?>"
                                        data-interval="<?php echo $flexiblekit['flexiblekit_interval'] ?>"
                                        data-active="<?php echo $flexiblekit['flexiblekit_active'] ?>"
                                        data-skip-weekends="<?php echo $flexiblekit['flexiblekit_skip_weekends'] ?>"
                                        data-start-datetime="<?php echo $flexiblekit['flexiblekit_start_datetime'] ?>"
                                        data-end-datetime="<?php echo explode(' ', $flexiblekit['flexiblekit_end_datetime'])[1] //We need only the time part of the end datetime ?>"
                                        data-tags="<?php echo $flexiblekit['flexiblekit_tags'] ?>"'
                                                        data-description="<?php echo $flexiblekit['flexiblekit_description'] ?>">
                                                        <i class="fa-regular fa-edit fa-lg"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if (count($callschedules) > 0) { ?>
    <div class="panel_s">
        <div class="panel-body table-responsive">
            <div class="panel-table-full">
                <h4>
                    <i class="fa <?php echo flexiblekit_get_type_icon('call') ?>"></i>
                    <?php echo _l('flexiblekit_calls') ?>
                </h4>
                <table id="calls" class="table dt-table flexibleschedules-table">
                    <thead>
                        <tr>
                            <th>
                                <?php
                                echo _l('flexiblekit_subject');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_status');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_start_datetime');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_end_datetime');
                                ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        
                                    <?php foreach ($callschedules as $callschedule) { ?>
                                                <tr>
                                                    <td>
                                                    <a href="<?php echo flexiblekit_schedules_url($callschedule['id']) ?>">
                                                            <?php echo $callschedule['flexibleschedule_subject'] ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="<?php echo flexiblekit_get_status_text_color($callschedule['flexibleschedule_status']) ?>">
                                                            <?php echo $callschedule['flexibleschedule_status'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php echo $callschedule['flexibleschedule_start_datetime'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $callschedule['flexibleschedule_end_datetime'] ?>
                                                    </td>
                                                </tr>
                                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>
<?php if (count($meetingschedules) > 0) { ?>
    <div class="panel_s">
        <div class="panel-body table-responsive">
            <div class="panel-table-full">
                <h4>
                    <i class="fa <?php echo flexiblekit_get_type_icon('meeting') ?>"></i>
                    <?php echo _l('flexiblekit_meetings') ?>
                </h4>
                <table id="meetings" class="table dt-table flexibleschedules-table">
                    <thead>
                        <tr>
                            <th>
                                <?php
                                echo _l('flexiblekit_subject');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_status');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_start_datetime');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_end_datetime');
                                ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($meetingschedules as $meetingschedule) { ?>
                                        <tr>
                                            <td>
                                            <a href="<?php echo flexiblekit_schedules_url($meetingschedule['id']) ?>">
                                                    <?php echo $meetingschedule['flexibleschedule_subject'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="<?php echo flexiblekit_get_status_text_color($meetingschedule['flexibleschedule_status']) ?>">
                                                    <?php echo $meetingschedule['flexibleschedule_status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $meetingschedule['flexibleschedule_start_datetime'] ?>
                                            </td>
                                            <td>
                                                <?php echo $meetingschedule['flexibleschedule_end_datetime'] ?>
                                            </td>
                                        </tr>
                            <?php } ?>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (count($taskschedules) > 0) { ?>
    <div class="panel_s">
        <div class="panel-body table-responsive">
            <div class="panel-table-full">
                <h4>
                    <i class="fa <?php echo flexiblekit_get_type_icon('task') ?>"></i>
                    <?php echo _l('flexiblekit_tasks') ?>
                </h4>
                <table id="tasks" class="table dt-table flexibleschedules-table">
                    <thead>
                        <tr>
                            <th>
                                <?php
                                echo _l('flexiblekit_subject');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_status');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_start_datetime');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_end_datetime');
                                ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($taskschedules as $taskschedule) { ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo flexiblekit_schedules_url($taskschedule['id']) ?>">
                                                    <?php echo $taskschedule['flexibleschedule_subject'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="<?php echo flexiblekit_get_status_text_color($taskschedule['flexibleschedule_status']) ?>">
                                                    <?php echo $taskschedule['flexibleschedule_status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $taskschedule['flexibleschedule_start_datetime'] ?>
                                            </td>
                                            <td>
                                                <?php echo $taskschedule['flexibleschedule_end_datetime'] ?>
                                            </td>
                                        </tr>
                            <?php } ?>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    const PAGE_SCRIPT = function(){
        //console.log('jquery', $);
        <?php if($contact_type == FLEXIBLEKIT_LEAD_CONTACT_TYPE){ ?>
            initDataTableInline($(' #flexiblekit-table')); 
            initDataTableInline($('.flexibleschedules-table'));
        <?php } ?>
        $('#flexiblekit_table_content').on('click', '.flexiblekit_edit_btn', function (e) {
            e.preventDefault() 
            let fields = {
                name: $(this).data('name'), type:
                    $(this).data('type'), interval_type: $(this).data('interval-type'), active:
                    $(this).data('active'), skip_weekends: $(this).data('skip-weekends'), contact_id:
                    $(this).data('contact-id'), contact_name: $(this).data('contact-name'),
                start_datetime: $(this).data('start-datetime'), end_datetime:
                    $(this).data('end-datetime'), tags: $(this).data('tags'), description:
                    $(this).data('description'), interval: $(this).data('interval')
            } 
            resetForm(fields)
            let flexiblekitId = $('#flexiblekit_id') 
            let dataId = $(this).data('id') 
            let idInput = ''
            // update the hidden flexiblekit id input value if the input exists 
            // else, append a hidden flexiblekit id input to the form 
            if (flexiblekitId.length > 0) {
                flexiblekitId.val(dataId)
            } else {
                idInput = `<input id='flexiblekit_id' type='hidden' name='flexiblekit_id' value='${dataId}'>`
            }

            $('#flexiblekit_form')
                .append(idInput)
                .slideDown()
        });

        function resetForm(data = null) {
            const DEFAULTS = {
                name: '',
                type: 'call',
                interval_type: 'day',
                active: 0,
                skip_weekends: 0,
                contact_id: $('#flexiblekit_contact_id').val(),
                contact_name: $('#flexiblekit_contact_name').val(),
                start_datetime: '',
                end_datetime: '',
                tags: '',
                description: '',
                interval: 1
            }

            const SELECTPICKERS = [
                'type',
                'interval_type',
                'interval',
            ]

            let fields = data || DEFAULTS


            // Loop through fields and populate the
            // form
            for (property in fields) {
                let element = $('#flexiblekit_' + property)

                if (property == 'active' || property == 'skip_weekends') {
                    selected = element.val() == fields[property]

                    if (selected) {
                        element.prop('checked', true)
                    } else {
                        element.prop('checked', false)
                    }
                } else if (SELECTPICKERS.includes(property)) {
                    if (property == 'interval') {
                        // set the value of the correct interval select picker
                        let type = $('#flexiblekit_interval_type').val()
                        $(`#${type}-container`)
                            .find('select')
                            .selectpicker('val', fields[property])
                    } else {
                        // Select cannot be updated without using selectpicker
                        element.selectpicker('val', fields[property])
                            .trigger('change')
                    }
                } else {
                    if (property == 'contact_name') {
                        element.prop('readonly', false)
                            .val(fields[property])
                            .prop('readonly', true)
                    } else {
                        element.val(fields[property])
                    }
                }
            }
        }

        // Toggle flexible kit form visibility
        $('#flexiblekit_toggle_form').on('click', function (e) {
            e.preventDefault()
            let opened = false;

            $('#flexiblekit_form').slideToggle()
            opened = !opened

            if (opened) {
                // remove the hidden flexiblekit id input
                $('#flexiblekit_id').remove()
                resetForm()
            }
        })

        // Prevent default form submission action.
        // Add form validation rules
        // Hide form visibility
        $('#flexiblekit_form').submit(function (e) {
            e.preventDefault()
        }).appFormValidator({
            rules: {
                flexiblekit_name: "required",
                flexiblekit_type: "required",
                flexiblekit_interval_type: "required",
                flexiblekit_interval: "required",
                flexiblekit_start_datetime: "required",
                flexiblekit_end_datetime: "required",
            },
            submitHandler: submitForm
        }).hide()

        // Handle form submission
        function submitForm(form) {
            let url = form.action
            let data = $(form).serialize()

            $.post(url, data,
                function (response, textStatus, jqXHR) {
                    if (response.success == true) {
                        // resetForm()
                        $(form).hide()
                        alert_float("success", response.message)
                        window.location.reload()
                        // populate_flexiblekit_table()
                    } else {
                        alert_float('danger', response.message)
                    }
                },
                "json"
            );
        }

        // Update flexiblekit interval options when the interval type is changed
        $('#flexiblekit_interval_type').on('change', function (e) {
            e.preventDefault()
            let input = $(this)
            let type = input.val()

            $('.intervals-container')
                .addClass('hidden')
                .find('select')
                .prop('disabled', true)
                .selectpicker('refresh');
            $(`#${type}-container`)
                .removeClass('hidden')
                .find('select')
                .prop('disabled', false)
                .selectpicker('refresh');
        })
    }

    <?php if($contact_type == FLEXIBLEKIT_LEAD_CONTACT_TYPE){ ?>
        $(document).ready(PAGE_SCRIPT())
    <?php }else{ ?>
        window.onload = PAGE_SCRIPT;
    <?php } ?>
</script>