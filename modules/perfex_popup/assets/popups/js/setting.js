$(function(){
	"use strict"; // Start of use strict

    $('#notify_lead_imported').on('change',function(){
          $('.select-notification-settings').toggleClass('hide');
    });

    appValidateForm($('#form_setting'), {
        name: 'required',
        redirect_url: {
            required: {
                depends:function(element) {
                    return $('select[name="type_form_submit"]').val() == 'url';
                }
            }
        },
        responsible: {
         required: {
            depends:function(element){
             var isRequiredByNotifyType = ($('input[name="notify_type"]:checked').val() == 'assigned') ? true : false;
             var isRequired = isRequiredByNotifyType;
             if(isRequired) {
                $('[for="responsible"]').find('.req').removeClass('hide');
             } else {
                $(element).next('p.text-danger').remove();
                $('[for="responsible"]').find('.req').addClass('hide');
             }
             return isRequired;
           }
         }
       }
    });
    var $notifyTypeInput = $('input[name="notify_type"]');
    $notifyTypeInput.on('change',function(){
        $('#form_setting').validate().checkForm()
    });
    $notifyTypeInput.trigger('change');

    $('#type_form_submit').on('change', function (e) {
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;

        if (valueSelected) {
            // subdomain
            if (valueSelected == 'thank_you_page') {
              $("#form_redirect_url").addClass("d-none");
            }
            // custom_domain
            else if(valueSelected == 'url'){
              $("#form_redirect_url").removeClass("d-none");
             
            }
        }
    });

    // display trigger
    /* Display Trigger Handler */
    let display_trigger_status_handler = () => {
      let display_trigger = $('select[name="display_trigger"] option:selected');
      switch (display_trigger.val()) {
        case "delay":
        case "scroll":
          /* Make sure to show the input field */
          $('input[name="display_trigger_value"]').show();
          /* Add the proper placeholder */
          $('input[name="display_trigger_value"]').attr(
            "placeholder",
            $(display_trigger).data("placeholder")
          );
          break;
        case "exit_intent":
          /* Hide the display trigger value for this option */
          $('input[name="display_trigger_value"]').hide();
          break;
      }
    };

    /* Trigger it for the first initial load */
    display_trigger_status_handler();

    /* Trigger on select change */
    $('select[name="display_trigger"]').on("change", () => {
      display_trigger_status_handler();

      /* Clear the input from previous values */
      $('input[name="display_trigger_value"]').val("");
    });

    /* Triggers Handler */
    let triggers_status_handler = () => {
      if ($("#trigger_all_pages").is(":checked")) {
        /* Disable the container visually */
        $("#triggers").addClass("container-disabled");

        /* Remove the new trigger add button */
        $("#trigger_add").hide();
      } else {
        /* Remove disabled container if depending on the status of the trigger checkbox */
        $("#triggers").removeClass("container-disabled");

        /* Bring back the new trigger add button */
        $("#trigger_add").show();
      }

      $('select[name="trigger_type[]"]')
        .off()
        .on("change", (event) => {
          let input = $(event.currentTarget).closest("div").find("input");
          let placeholder = $(event.currentTarget)
            .find(":checked")
            .data("placeholder");

          /* Add the proper placeholder */
          input.attr("placeholder", placeholder);
        })
        .trigger("change");
    };

    /* Trigger on status change live of the checkbox */
    $("#trigger_all_pages").on("change", triggers_status_handler);

    /* Delete trigger handler */
    let triggers_delete_handler = () => {
      /* Delete button handler */
      $(".trigger-delete")
        .off()
        .on("click", (event) => {
          let trigger = $(event.currentTarget).closest(".input-group");

          trigger.remove();

          triggers_count_handler();
        });
    };

    let triggers_add_sample = () => {
      let trigger_rule_sample = $("#trigger_rule_sample").html();

      $("#triggers").append(trigger_rule_sample);
    };

    let triggers_count_handler = () => {
      let total_triggers = $("#triggers > .input-group").length;

      /* Make sure we at least have two input groups to show the delete button */
      if (total_triggers > 1) {
        $("#triggers .trigger-delete").show();

        /* Make sure to set a limit to these triggers */
        if (total_triggers > 10) {
          $("#trigger_add").hide();
        } else {
          $("#trigger_add").show();
        }
      } else {
        if (total_triggers == 0) {
          triggers_add_sample();
        }

        $("#triggers .trigger-delete").hide();
      }
    };

    /* Add new trigger rule handler */
    $("#trigger_add").on("click", () => {
      triggers_add_sample();
      triggers_delete_handler();
      triggers_count_handler();
      triggers_status_handler();
    });

    /* Trigger functions for the first initial load */
    triggers_status_handler();
    triggers_delete_handler();
    triggers_count_handler();

    /* Border radius preview */
    $('select[name="border_radius"]').on("change", (event) => {
      let border_radius = $(event.currentTarget).find(":checked").val();

      let notification_preview_wrapper = $(
        "#notification_preview .zillapage-wrapper"
      );

      notification_preview_wrapper
        .removeClass(
          "zillapage-wrapper-round zillapage-wrapper-straight zillapage-wrapper-rounded"
        )
        .addClass(`zillapage-wrapper-${border_radius}`);
    });
    // ennd display trigger
});    
