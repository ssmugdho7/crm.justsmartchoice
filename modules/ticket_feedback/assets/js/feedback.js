$(document).ready(function () {
    $('#customer_id').change(function () {
        var customer_id = $(this).val();
        var projectDropdown = $('#project_id');
       
        if (customer_id) {
            $.ajax({
                url: baseUrl, // ✅ Use predefined URL
                type: "POST",
                data: { customer_id: customer_id },
                dataType: "json",
                success: function (response) {
                    console.log("Projects received:", response);

                    projectDropdown.empty().append('<option value="">Select a Project</option>');

                    if (response.length > 0) {
                        $.each(response, function (index, project) {
                            projectDropdown.append('<option value="' + project.id + '">' + project.name + '</option>');
                        });

                        projectDropdown.prop('disabled', false).selectpicker('refresh');
                    } else {
                        projectDropdown.prop('disabled', true).selectpicker('refresh');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        } else {
            projectDropdown.prop('disabled', true).empty().append('<option value="">Select a Project</option>').selectpicker('refresh');
        }
    });
});
