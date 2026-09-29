$(function(){
   $('#flexiblewa_action_sequence #section_id, #flexiblewa_action_sequence #when').on('change', function(){
       //get the value
      const id = $(this).val();
      if(!id) return;
      const container = $('.flexiblewa_action_sequence_container');
      const url = $('#flexiblewa_ajax_url').val();
      const action_type = $('#flexiblewa_action_type').val() ;
      const data = {
         action: 'get_list_of_actions_for_section',
         id: id,
         action_type: action_type,
      }
      //make get request
      $.get(url, data, function(response) {
         if(response.success){
            $(container).html(response.html);
            flexiblewa_initialize_sortable_action();
         }
      });
   });

   //initialiaze the sortable list
   function flexiblewa_initialize_sortable_action(){
       const container = $('#flexiblewa-action-list-item');
       if($(container).length) {
           $(container).sortable({
               placeholder: "ui-state-highlight-flexiblewa",
               update: function (event, ui) {
                   // Update actions order
                   flexiblewa_update_actions_order();
               }
           });
       }
   }

    //update actions order
    function flexiblewa_update_actions_order() {
        const actions = [];
        $('#flexiblewa-action-list-item .flexiblewa-action-item').each(function() {
            actions.push($(this).data('id'));
        });
        const url = $('#flexiblewa_ajax_url').val();
        const data = {
           action: 'update_actions_order',
           actions: actions,
           action_type: $('#flexiblewa_action_type').val(),
        };
        $.post(url, data);
        //show success
        alert_float('success', $('#flexiblewa-action-list-item').data('success'));
    }

    //get form for action
    $('.flexiblewa-action-btn').on('click', function(){
        const id = $(this).data('id');
        const url = $('#flexiblewa_ajax_url').val();
        const data = {
            action: 'get_form_for_action',
            id: id,
            action_type: $('#flexiblewa_action_type').val(),
        };
        $.get(url, data, function(response) {
            if(response.success){
                $('.flexiblewa-rule-lhs').html(response.data.html);
                //init select picker
                $('.selectpicker').selectpicker();
                appDatepicker();
                appTagsInput();
                init_editor('.tinymce-manual');
            }
        });
    });
});