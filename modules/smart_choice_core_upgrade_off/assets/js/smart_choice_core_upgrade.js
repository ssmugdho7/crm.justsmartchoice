
(function(){
  var cfg = window.smartChoiceCoreUpgradeSettings || {};
  if(!cfg.enabled){return;}
  document.body.classList.add('smart-choice-core-active');

  function addMenuSearch(selector, placeholder){
    var menu=document.querySelector(selector);
    if(!menu || menu.parentNode.querySelector('.sc-menu-search')) return;
    var box=document.createElement('div');box.className='sc-menu-search';
    box.innerHTML='<input type="text" placeholder="'+placeholder+'">';
    menu.parentNode.insertBefore(box, menu);
    var input=box.querySelector('input');
    input.addEventListener('input',function(){var q=this.value.toLowerCase();menu.querySelectorAll('li').forEach(function(li){li.style.display=li.textContent.toLowerCase().indexOf(q)>-1?'':'none';});});
  }
  if(cfg.mainMenuSearch){addMenuSearch('#side-menu','Search main menu');}
  if(cfg.setupMenuSearch){addMenuSearch('#setup-menu','Search setup menu');}

  // Display Smart Choice version on the native update/settings area without touching license files.
  if(location.href.indexOf('settings')>-1 || location.href.indexOf('modules')>-1){
    var panels=document.querySelectorAll('.content .panel_s .panel-body, .content .panel-body');
    if(panels.length){
      var pill=document.createElement('span');pill.className='sc-version-pill';pill.textContent='Smart Choice CRM '+(cfg.displayVersion||'3.1.2')+' · Quality ★★★★★';
      panels[0].insertBefore(pill, panels[0].firstChild);
    }
  }

  // Replace visible standalone Perfex version text in Settings > Update only; do not alter data values or licensing.
  if(location.href.indexOf('group=update')>-1){
    document.querySelectorAll('.alert p,.alert h3,.panel-body p').forEach(function(el){
      if((el.textContent||'').trim()==='3.4.1'){el.textContent='3.1.2';}
    });
  }

  // Safe table toolbar helper. Avoid modules page and sales item editor tables.
  if(cfg.tableTools && location.href.indexOf('/modules')===-1){
    setTimeout(function(){
      document.querySelectorAll('.dataTables_length').forEach(function(lengthBox){
        if(lengthBox.querySelector('.sc-table-tools')) return;
        var tableWrap=lengthBox.closest('.dataTables_wrapper');
        if(!tableWrap) return;
        if(tableWrap.closest('#sales_item_modal') || tableWrap.closest('.items-wrapper') || tableWrap.closest('.invoice-items-table')) return;
        var tools=document.createElement('span');tools.className='sc-table-tools';
        tools.innerHTML='<button type="button" class="btn btn-default btn-xs">Import</button><button type="button" class="btn btn-default btn-xs">Sample Header</button><button type="button" class="btn btn-default btn-xs">Export</button><button type="button" class="btn btn-default btn-xs" onclick="location.reload()">Refresh</button><button type="button" class="btn btn-danger btn-xs">Mass Delete</button>';
        lengthBox.appendChild(tools);
      });
    },800);
  }
})();
