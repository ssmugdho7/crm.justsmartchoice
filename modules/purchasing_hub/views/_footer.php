      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<script>
(function($){
  'use strict';

  function smartChoicePlacePurchasingHubTableActions(){
    $('[data-ph-dt-actions="1"]').each(function(){
      var $actions = $(this);
      if ($actions.data('phPlaced') === 1) {
        return;
      }

      var $panel = $actions.closest('.panel-body, .panel_s, .content, body');
      var $table = $panel.find('table.dt-table, table.purchasing-hub-table').first();
      if (!$table.length) {
        return;
      }

      var $wrapper = $table.closest('.dataTables_wrapper');
      if (!$wrapper.length) {
        return;
      }

      var $buttons = $wrapper.find('.dt-buttons').first();
      if (!$buttons.length) {
        var $length = $wrapper.find('.dataTables_length').first();
        if ($length.length) {
          $buttons = $('<div class="dt-buttons btn-group ph-created-dt-buttons"></div>');
          $length.after($buttons);
        }
      }

      if (!$buttons.length) {
        return;
      }

      $actions.addClass('ph-dt-actions-inline').data('phPlaced', 1).appendTo($buttons).show();
    });
  }

  $(function(){
    smartChoicePlacePurchasingHubTableActions();
    setTimeout(smartChoicePlacePurchasingHubTableActions, 250);
    setTimeout(smartChoicePlacePurchasingHubTableActions, 750);
    $(document).on('draw.dt init.dt', function(){
      setTimeout(smartChoicePlacePurchasingHubTableActions, 50);
    });
  });
})(jQuery);
</script>

</body>
</html>
