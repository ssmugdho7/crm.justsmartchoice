<script type="text/javascript">
  (function(){
    "use strict";
    $('#luckysheet').parents('.row').css({'position': 'fixed', 'left':'13px', 'right':'0', 'bottom' : '2px', 'top' : '92px'});
    $('#luckysheet').parents('.container').css({'width': 'unset', 'padding':'0'});
    if((<?php echo $tree_save ?>).length > 0){
      var tree = $('input[name="folder"]').comboTree({
        source : <?php echo $tree_save ?>
      });
    }

    $('input[name="folder"]').on('change', function(){
      var id = tree.getSelectedItemsId();
      $("input[name='parent_id']").val(id.replace( /^\D+/g, ''));
    })
    if((<?php echo isset($data_form) ? "true" : "false" ?>)){
      var html = '';
          html += '<div class="Box">';
          html += '<span>';
          html += '<span></span>';
          html += '</span>';
          html += '</div>';
          $('#box-loading').html(html);

      $.ajax({
            url : site_url + "modules/spreadsheet_online/uploads<?php echo $realpath_data; ?>",
            success : function (data_txt) {

                var dataSheet = JSON.parse(data_txt);
                var title = "<?php echo isset($name) ? $name : "" ?>";

                var options = {
                  container: 'luckysheet',
                  lang: 'en',
                  allowEdit:true,
                  forceCalculation:true,
                  plugins: ['chart'],
                  data: dataSheet,
                  title: title,
                }

                luckysheet.create(options);
                $('#box-loading').html('');

                var role = $("input[name='role']").val();
                if(role == 1){
                  $('.luckysheet_info_detail_save_as').remove();
                  $('.luckysheet_info_detail_save').remove();
                }

            }
        });

    }else{
      var dataSheet = [{
        name: "Sheet1",
        status: "1",
        order: "0",
        data: [],
        config: {},
        index: 0
      }, {
        name: "Sheet2",
        status: "0",
        order: "1",
        data: [],
        config: {},
        index: 1
      }, {
        name: "Sheet3",
        status: "0",
        order: "2",
        data: [],
        config: {},
        index: 2
      }];

      var title = "Spreadsheet Online New";
      var options = {
        container: 'luckysheet',
        lang: 'en',
        allowEdit:true,
        forceCalculation:true,
        plugins: ['chart'],
        data: dataSheet,
        title: title,
      }
      luckysheet.create(options);
    }
    var type_screen = $("input[name='type']").val();
    var role = $("input[name='role']").val();

    if(type_screen == 3){
      $('.luckysheet_info_detail_save_as').remove();
    }
    if(type_screen == 2){
      $('.luckysheet_info_detail_save').remove();
    }

    if(role == 1){
      $('.luckysheet_info_detail_save_as').remove();
      $('.luckysheet_info_detail_save').remove();
    }


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

</script>

