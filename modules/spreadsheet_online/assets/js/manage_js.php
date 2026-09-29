<script type="text/javascript">
    var fnServerParams = {
        "file_id": '#ShareModal input[name="id"]',
      };
	(function(){
		"use strict";
       
		$("#spreadsheet-advanced").treetable({ expandable: true });
	    $("#spreadsheet-advanced tbody").on("mousedown", "tr", function() {
            $(".selected").not(this).removeClass("selected");
            $(this).toggleClass("selected");
        });

    	// Drag & Drop spreadsheet Code
    	$("#spreadsheet-advanced .file, #spreadsheet-advanced .folder").draggable({
    		helper: "clone",
    		opacity: .75,
    		refreshPositions: true,
    		revert: "invalid",
    		revertDuration: 300,
    		scroll: true
    	});

    	$("#spreadsheet-advanced .folder").each(function() {
    		$(this).parents("#spreadsheet-advanced tr").droppable({
    			accept: ".file, .folder",
    			drop: function(e, ui) {
    				var droppedEl = ui.draggable.parents("tr");
    				$("#spreadsheet-advanced").treetable("move", droppedEl.data("ttId"), $(this).data("ttId"));
                    $.post(admin_url + 'spreadsheet_online/droppable_event/' + droppedEl.data("ttId") + '/' + $(this).data("ttId")).done(function(response) {
                        response = JSON.parse(response);
                    })
    			},
    			hoverClass: "accept",
    			over: function(e, ui) {
    				var droppedEl = ui.draggable.parents("tr");
    				if(this != droppedEl[0] && !$(this).is(".expanded")) {

    					$("#spreadsheet-advanced").treetable("expandNode", $(this).data("ttId"));
    				}
    			}
    		});
    	});

        $('#ShareModal input[name="type"]').change(function(){
            var type = $(this).val();
            if(type == 'client'){
              $('#ShareModal #div_client').removeClass('hide');
              $('#ShareModal #div_staff').addClass('hide');
            }else{
              $('#ShareModal #div_client').addClass('hide');
              $('#ShareModal #div_staff').removeClass('hide');
            }
        });
        
  })(jQuery);

  function new_share(){
    "use strict";

    $('#ShareModal #staff').prop('checked',true);
    $('#ShareModal #div_client').addClass('hide');
    $('#ShareModal #div_staff').removeClass('hide');

    $('#ShareModal :radio(:checked)').attr('disabled', false);
    $('#ShareModal :radio:not(:checked)').attr('disabled', false);

    $('#ShareModal select[name="staffs_share[]"]').selectpicker('val', '');
    $('#ShareModal select[name="departments_share[]"]').selectpicker('val', '');
    $('#ShareModal select[name="clients_share[]"]').selectpicker('val', '');
    $('#ShareModal select[name="client_groups_share[]"]').selectpicker('val', '');

    $('#ShareModal #is_read').attr('checked', true);
    $('#ShareModal #is_write').attr('checked', true);

    $('#sharing-list-modal').modal('hide');
    $('#ShareModal').modal('show');
}


function edit_sharing(invoker){
  "use strict";
    $('#sharing-list-modal').modal('hide');

  $('#ShareModal').find('button[type="submit"]').prop('disabled', false);

  var id = $(invoker).data('id');
  var is_read = $(invoker).data('is_read') == 1 ? true : false;
  var is_write = $(invoker).data('is_write') == 1 ? true : false;
  var type = $(invoker).data('type');
  var staffs = $(invoker).data('staffs');
  var departments = $(invoker).data('departments');
  var customers = $(invoker).data('customers');
  var customer_groups = $(invoker).data('customer_groups');

  $('#ShareModal input[name="share_id"]').val(id);

  $('#ShareModal #is_read').attr('checked', is_read);
  $('#ShareModal #is_write').attr('checked', is_write);

  if(type == 'client'){
    $('#ShareModal #client').prop('checked',true);
    $('#ShareModal #div_client').removeClass('hide');
    $('#ShareModal #div_staff').addClass('hide');
  }else{
    $('#ShareModal #staff').prop('checked',true);
    $('#ShareModal #div_client').addClass('hide');
    $('#ShareModal #div_staff').removeClass('hide');
  }

  $('#ShareModal :radio(:checked)').attr('disabled', false);
  $('#ShareModal :radio:not(:checked)').attr('disabled', true);

  if (!empty(staffs)) {
    if(staffs.toString().indexOf(",") > 0){
      var selected = staffs.split(',');
    }else{
      var selected = [staffs.toString()];
    }
    selected = selected.map(function(e) {
        return e.trim();
    });
    $('#ShareModal select[name="staffs_share[]"]').selectpicker('val', selected);
  }else{
    $('#ShareModal select[name="staffs_share[]"]').selectpicker('val', '');
  }

  if (!empty(departments)) {
    if(departments.toString().indexOf(",") > 0){
      var selected = departments.split(',');
    }else{
      var selected = [departments.toString()];
    }
    selected = selected.map(function(e) {
        return e.trim();
    });
    $('#ShareModal select[name="departments_share[]"]').selectpicker('val', selected);
  }else{
    $('#ShareModal select[name="departments_share[]"]').selectpicker('val', '');
  }

  if (!empty(customers)) {
    if(customers.toString().indexOf(",") > 0){
      var selected = customers.split(',');
    }else{
      var selected = [customers.toString()];
    }
    selected = selected.map(function(e) {
        return e.trim();
    });
    $('#ShareModal select[name="clients_share[]"]').selectpicker('val', selected);
  }else{
    $('#ShareModal select[name="clients_share[]"]').selectpicker('val', '');
  }

  if (!empty(customer_groups)) {
    if(customer_groups.toString().indexOf(",") > 0){
      var selected = customer_groups.split(',');
    }else{
      var selected = [customer_groups.toString()];
    }
    selected = selected.map(function(e) {
        return e.trim();
    });
    $('#ShareModal select[name="client_groups_share[]"]').selectpicker('val', selected);
  }else{
    $('#ShareModal select[name="client_groups_share[]"]').selectpicker('val', '');
  }
  
  $('#ShareModal').modal('show');
}
function delete_sharing(id) {
  "use strict";
    if (confirm("Are you sure?")) {
      var url = admin_url + 'spreadsheet_online/delete_sharing/'+id;

      requestGet(url).done(function(response){
          response = JSON.parse(response);
          if (response.success === true || response.success == 'true') { 
            alert_float('success', response.message); 
            init_sharing_table();
          }else{
            alert_float('danger', response.message); 
          }
      });
    }
    return false;
}

function close_edit_sharing(){
  $('#ShareModal').modal('hide');
  $('#sharing-list-modal').modal('show');
}

function init_sharing_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-sharing')) {
    $('.table-sharing').DataTable().destroy();
  }
  initDataTable('.table-sharing', admin_url + 'spreadsheet_online/sharing_detail_table', false, false, fnServerParams);
}
</script>