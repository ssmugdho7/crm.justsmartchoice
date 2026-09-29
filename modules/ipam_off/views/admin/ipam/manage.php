<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php if (has_permission('ipam', '', 'create')) { ?>
                            <a href="#" class="btn btn-info pull-left" onclick="newRecord(); return false;">New <?php echo ucfirst($type); ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <!-- Add other headers dynamically based on type -->
                                        <?php if (!empty($records) && isset($records[0])) { ?>
                                            <?php foreach (array_keys($records[0]) as $header) { ?>
                                                <th><?php echo ucfirst(str_replace('_', ' ', $header)); ?></th>
                                            <?php } ?>
                                        <?php } ?>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($records)) { ?>
                                        <?php foreach ($records as $record) { ?>
                                            <tr>
                                                <td><?php echo $record['id']; ?></td>
                                                <!-- Add other fields dynamically based on type -->
                                                <?php foreach ($record as $field) { ?>
                                                    <td><?php echo $field; ?></td>
                                                <?php } ?>
                                                <td>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="editRecord(<?php echo $record['id']; ?>); return false;"><i class="fa fa-pencil"></i></a>
                                                    <a href="#" class="btn btn-danger btn-icon" onclick="deleteRecord(<?php echo $record['id']; ?>); return false;"><i class="fa fa-remove"></i></a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="100%" class="text-center">No records found.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal for Adding/Editing Records -->
<div class="modal fade" id="recordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="recordForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="recordModalLabel">Add New <?php echo ucfirst($type); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Dynamically create form fields based on type -->
                    <?php if (!empty($records) && isset($records[0])) { ?>
                        <?php foreach (array_keys($records[0]) as $field) { ?>
                            <div class="form-group">
                                <label for="<?php echo $field; ?>"><?php echo ucfirst(str_replace('_', ' ', $field)); ?></label>
                                <input type="text" class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>">
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <input type="hidden" name="id" id="recordId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var type = '<?php echo $type; ?>';
    function newRecord() {
        $('#recordForm')[0].reset();
        $('#recordId').val('');
        $('#recordModalLabel').text('Add New ' + type.charAt(0).toUpperCase() + type.slice(1));
        $('#recordModal').modal('show');
    }

    function editRecord(id) {
        $.post(admin_url + 'ipam/manage_record/' + type, { id: id }, function(response) {
            if (response.success) {
                var data = response.data;
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        $('#' + key).val(data[key]);
                    }
                }
                $('#recordModalLabel').text('Edit ' + type.charAt(0).toUpperCase() + type.slice(1));
                $('#recordModal').modal('show');
            } else {
                alert(response.message);
            }
        }, 'json');
    }

    function deleteRecord(id) {
        if (confirm('Are you sure you want to delete this record?')) {
            $.post(admin_url + 'ipam/delete_record/' + type + '/' + id, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }, 'json');
        }
    }

    $('#recordForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post(admin_url + 'ipam/manage_record/' + type, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        }, 'json');
    });
</script>
