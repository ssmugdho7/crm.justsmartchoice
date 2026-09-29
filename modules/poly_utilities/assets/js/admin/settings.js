(function($) {
    "use strict";
    PolyOperationFunctions.DisableReload();
    $('.btn-submit-poly-utilities-settings, .poly_utilities_settings input[type="checkbox"]').on('click', function() {
      "use strict";
      var config = {};
      config.is_sticky = $('#poly_utilities_topbar_is_sticky').prop('checked');
      config.is_search_menu = $('#poly_utilities_is_search_menu').prop('checked');
      config.is_quick_access_menu = $('#poly_utilities_is_quick_access_menu').prop('checked');
      config.is_quick_access_menu_icons = $('#poly_utilities_is_quick_access_menu_icons').prop('checked');
      config.is_table_of_content = $('#poly_utilities_is_table_of_content').prop('checked');
      config.is_active_scripts = $('#poly_utilities_enable_scripts').prop('checked');
      config.is_active_styles = $('#poly_utilities_enable_styles').prop('checked');
      config.is_note_confirm_delete = $('#poly_utilities_enable_note_confirm_delete').prop('checked');
      config.is_operation_functions = $('#poly_utilities_enable_operation_functions').prop('checked');
      config.is_scroll_to_top = $('#poly_utilities_enable_scroll_to_top').prop('checked');
      config.is_toggle_sidebar_menu = $('#poly_utilities_is_toggle_sidebar_menu').prop('checked');
      $.post(admin_url + 'poly_utilities/update_settings', {
        data: config,
      }).done(function(response) {
        PolyMessage.displayNotification('success', 'Successfully');
      });
    });
  })(jQuery);