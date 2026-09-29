(function(){
    'use strict';
    function addModuleButtons(){
        if(!window.smartModuleManagerAdminUrl){return;}
        var uri = window.location.href.toLowerCase();
        if(uri.indexOf('/admin/modules') === -1 && uri.indexOf('/admin/mods') === -1){return;}
        var links = document.querySelectorAll('a[href*="/admin/modules/"], a[href*="/admin/mods/"]');
        links.forEach(function(link){
            var href = link.getAttribute('href') || '';
            var match = href.match(/(?:module|activate|deactivate|upgrade_database)\/([a-zA-Z0-9_\-]+)/);
            if(!match || !match[1]){return;}
            var moduleName = match[1];
            var parent = link.parentNode;
            if(!parent || parent.querySelector('.smart-module-manager-download-inline[data-module="'+moduleName+'"]')){return;}
            var a = document.createElement('a');
            a.className = 'smart-module-manager-download-inline';
            a.setAttribute('data-module', moduleName);
            a.href = window.smartModuleManagerAdminUrl + '/download/' + moduleName;
            a.innerHTML = '<i class="fa fa-download"></i> Download';
            parent.appendChild(a);
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', addModuleButtons);
    }else{
        addModuleButtons();
    }
})();
