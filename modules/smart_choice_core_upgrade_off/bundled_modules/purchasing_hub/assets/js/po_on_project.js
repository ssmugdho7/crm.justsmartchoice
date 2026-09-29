(function($) {
  "use strict";

  	var _project_id = $('input[name="_project_id"]').val();
	initDataTable('.table-table_pur_order', admin_url+'purchasing_hub/purchase/table_project_pur_order/'+_project_id);

})(jQuery);
