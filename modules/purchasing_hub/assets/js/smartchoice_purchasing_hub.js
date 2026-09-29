
(function($){
  "use strict";
  if (typeof $ === 'undefined') { return; }
  function buildQuickNav(){
    var path = window.location.pathname || '';
    if (path.indexOf('/admin/purchasing_hub/purchase') === -1) { return; }
    if ($('.ph-quick-nav').length) { return; }
    var base = (typeof admin_url !== 'undefined' ? admin_url : '/admin/') + 'purchasing_hub/purchase/';
    var links = [
      ['Items', 'items', 'fa fa-cubes'],
      ['Vendors', 'vendors', 'fa fa-users'],
      ['Vendor Items', 'vendor_items', 'fa fa-list'],
      ['Purchase Requests', 'purchase_request', 'fa fa-shopping-basket'],
      ['Quotations', 'quotations', 'fa fa-file-text-o'],
      ['Purchase Orders', 'purchase_order', 'fa fa-cart-plus'],
      ['Return Orders', 'order_returns', 'fa fa-reply-all'],
      ['Invoices', 'invoices', 'fa fa-clipboard'],
      ['Reports', 'reports', 'fa fa-bar-chart'],
      ['Settings', 'setting', 'fa fa-gears'],
      ['Health Check', 'health_check', 'fa fa-heartbeat'],
      ['Help / Training', 'help_training', 'fa fa-life-ring']
    ];
    var html = '<div class="ph-quick-nav"><div class="ph-quick-nav-title">Purchasing Hub Quick Navigation</div><div class="ph-quick-nav-links">';
    links.forEach(function(l){
      var url = base + l[1];
      var active = path.indexOf('/'+l[1]) !== -1 ? ' active' : '';
      html += '<a class="'+active+'" href="'+url+'"><i class="'+l[2]+'"></i>'+l[0]+'</a>';
    });
    html += '</div></div>';
    var target = $('#wrapper .content, #wrapper .content-wrapper, .content').first();
    if (target.length) { target.prepend(html); }
  }
  $(function(){ buildQuickNav(); });
})(jQuery);


/* Purchasing Hub visible label cleanup and Smart Choice UI repair */
(function($){
  "use strict";
  if (typeof $ === 'undefined') { return; }
  var labels = {
    'item_list':'Item List','commodity_code':'Item Code','commodity_name':'Item Name','commodity_barcode':'Barcode','sku_code':'SKU Code','sku_name':'SKU Name','commodity_group':'Item Group','sub_group':'Sub Group','purchase_price':'Purchase Cost','unit_id':'Unit','item_add_edit_attach_image':'Attach Image','vendor_item':'Vendor Item','vendor_items':'Vendor Items','purchase_request':'Purchase Request','purchase_order':'Purchase Order','pur_order':'Purchase Order','pur_return_orders':'Return Orders','purchase_reports':'Purchasing Reports','pur_debit_note':'Vendor Credit','pur_faf_requests':'Funding Approval Requests','pur_invoice':'Accounts Payable','invoices':'Accounts Payable','purchase_invoices':'Accounts Payable','vendor':'Vendor','vendors':'Vendors','setting':'Settings','purchase':'Purchasing Hub','pur_approval_infor':'Approval Information','pur_order_name':'Purchase Order Name','pur_request_name':'Purchase Request Name','pur_estimate':'Vendor Quote','estimate':'Quote','quotations':'Vendor Quotes'
  };
  function humanizeText(t){
    if (!t) return t;
    var clean = String(t).replace(/\s+/g,' ').trim();
    var key = clean.toLowerCase();
    if (labels[key]) return labels[key];
    if (clean.indexOf('_') !== -1 && /^[a-z0-9_\-*\s]+$/i.test(clean)) {
      return clean.replace(/^\*\s*/,'* ').replace(/_/g,' ').replace(/\b\w/g,function(c){return c.toUpperCase();});
    }
    return t;
  }
  function cleanVisibleLabels(){
    var path = window.location.pathname || '';
    if (path.indexOf('/purchasing_hub/') === -1 && path.indexOf('/admin/purchasing_hub/') === -1) { return; }
    $('th,label,.control-label,.panel-title,.modal-title,h1,h2,h3,h4,h5,button,a.btn,.nav-tabs a,.menu-text,.dataTables_empty,option').each(function(){
      var $el=$(this);
      if ($el.children().length && !$el.is('option')) return;
      var old=$el.text();
      var fixed=humanizeText(old);
      if (fixed !== old) $el.text(fixed);
    });
    $('input[placeholder], textarea[placeholder]').each(function(){
      var old=$(this).attr('placeholder');
      var fixed=humanizeText(old);
      if (fixed !== old) $(this).attr('placeholder', fixed);
    });
  }
  $(function(){ cleanVisibleLabels(); setTimeout(cleanVisibleLabels, 600); setTimeout(cleanVisibleLabels, 1500); });
  $(document).ajaxComplete(function(){ setTimeout(cleanVisibleLabels, 100); });
})(jQuery);
