(function(){
    document.addEventListener('change', function(e){
        if(e.target && e.target.id === 'smartsource-template-select'){
            var option = e.target.options[e.target.selectedIndex];
            var content = option.getAttribute('data-content');
            if(!content){ return; }
            if(typeof tinymce !== 'undefined' && tinymce.get('content')){
                tinymce.get('content').setContent(content);
            } else {
                var textarea = document.getElementById('content');
                if(textarea){ textarea.value = content; }
            }
        }
    });

    if(window.smartsourceChartData && typeof Chart !== 'undefined'){
        var labels = window.smartsourceChartData.map(function(row){ return row.contract_type || 'No Type'; });
        var totals = window.smartsourceChartData.map(function(row){ return parseInt(row.total || 0, 10); });
        var values = window.smartsourceChartData.map(function(row){ return parseFloat(row.value || 0); });
        var typeCanvas = document.getElementById('smartsourceTypeChart');
        var valueCanvas = document.getElementById('smartsourceValueChart');
        if(typeCanvas){
            new Chart(typeCanvas.getContext('2d'), { type:'bar', data:{ labels:labels, datasets:[{ label:'Contracts By Type', data:totals, backgroundColor:'#169179' }] }, options:{ responsive:true, maintainAspectRatio:false } });
        }
        if(valueCanvas){
            new Chart(valueCanvas.getContext('2d'), { type:'bar', data:{ labels:labels, datasets:[{ label:'Contract Value By Type', data:values, backgroundColor:'#0057b8' }] }, options:{ responsive:true, maintainAspectRatio:false } });
        }
    }
})();

(function(){
    function addSmartSourceCustomFieldOptions(){
        var select = document.getElementById('fieldto');
        if(!select){ return; }
        var options = [
            {value:'sales_center', label:'Salespersons'},
            {value:'sales_center_contracts', label:'Salesperson Contracts'}
        ];
        options.forEach(function(opt){
            if(!select.querySelector('option[value="'+opt.value+'"]')){
                var option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                select.appendChild(option);
            }
        });
        if(window.jQuery && jQuery.fn.selectpicker){
            jQuery(select).selectpicker('refresh');
        }
    }

    function signaturePad(){
        var canvas = document.getElementById('smartsourceSignatureCanvas');
        if(!canvas){ return; }
        var input = document.getElementById('signature_data');
        var clearBtn = document.getElementById('smartsourceClearSignature');
        var saveBtn = document.getElementById('smartsourceSaveSignature');
        var ctx = canvas.getContext('2d');
        var drawing = false;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#111827';

        function getPos(e){
            var rect = canvas.getBoundingClientRect();
            var touch = e.touches && e.touches.length ? e.touches[0] : e;
            return {x: touch.clientX - rect.left, y: touch.clientY - rect.top};
        }
        function start(e){ drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); e.preventDefault(); }
        function move(e){ if(!drawing){return;} var p = getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); e.preventDefault(); }
        function end(){ drawing = false; if(input){ input.value = canvas.toDataURL('image/png'); } }
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, {passive:false});
        canvas.addEventListener('touchmove', move, {passive:false});
        canvas.addEventListener('touchend', end);
        if(clearBtn){ clearBtn.addEventListener('click', function(){ ctx.clearRect(0,0,canvas.width,canvas.height); if(input){input.value='';} }); }
        if(saveBtn){ saveBtn.addEventListener('click', function(){ if(input){ input.value = canvas.toDataURL('image/png'); } }); }
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', function(){ addSmartSourceCustomFieldOptions(); signaturePad(); });
    } else {
        addSmartSourceCustomFieldOptions(); signaturePad();
    }
})();

(function(){
    function copyLinkFocus(){
        document.addEventListener('click', function(e){
            if(e.target && e.target.classList.contains('smartsource-copy-link')){
                e.target.select();
                try{ document.execCommand('copy'); }catch(err){}
            }
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', copyLinkFocus);
    } else { copyLinkFocus(); }
})();

(function(){
    function injectStaffSalespersonFields(){
        if(window.location.href.indexOf('/admin/staff/member') === -1){ return; }
        if(document.getElementById('smartsource-staff-salesperson-box')){ return; }
        var form = document.querySelector('form');
        if(!form){ return; }
        var target = form.querySelector('.panel-body') || form;
        var box = document.createElement('div');
        box.id = 'smartsource-staff-salesperson-box';
        box.className = 'alert alert-info';
        box.innerHTML = '<strong>Smart Choice Salesperson Staff Type</strong><br><label style="margin-top:8px;"><input type="checkbox" name="is_salesperson" value="1"> Mark This Staff Member As A Salesperson</label><br><small>This field is stored by the SmartSource Salespersons module when the staff profile is saved.</small>';
        target.insertBefore(box, target.firstChild);
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', injectStaffSalespersonFields);
    } else { injectStaffSalespersonFields(); }
})();


/* SmartSource v13 client-side table filters and editor helpers */
(function(){
    function norm(v){ return (v || '').toString().toLowerCase().trim(); }
    function applyFilters(scope){
        var filters = scope.querySelectorAll('[data-smartsource-filter]');
        var table = scope.querySelector('table.smartsource-table');
        if(!table){ return; }
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row){
            var show = true;
            filters.forEach(function(input){
                var key = input.getAttribute('data-smartsource-filter');
                var val = norm(input.value);
                if(!val){ return; }
                var rowVal = norm(row.getAttribute('data-' + key));
                if(rowVal.indexOf(val) === -1){ show = false; }
            });
            row.style.display = show ? '' : 'none';
        });
    }
    document.addEventListener('input', function(e){
        if(e.target && e.target.hasAttribute('data-smartsource-filter')){
            var scope = e.target.closest('.smartsource-filter-scope');
            if(scope){ applyFilters(scope); }
        }
    });
    document.addEventListener('change', function(e){
        if(e.target && e.target.hasAttribute('data-smartsource-filter')){
            var scope = e.target.closest('.smartsource-filter-scope');
            if(scope){ applyFilters(scope); }
        }
    });
    document.addEventListener('click', function(e){
        var btn = e.target.closest ? e.target.closest('.smartsource-clear-filters') : null;
        if(!btn){ return; }
        var scope = btn.closest('.smartsource-filter-scope');
        if(!scope){ return; }
        scope.querySelectorAll('[data-smartsource-filter]').forEach(function(input){ input.value = ''; });
        applyFilters(scope);
    });
})();

/* Sales Center v1.1.2: decode safe custom-field link text when a CRM custom URL field is displayed as raw HTML. */
(function(){
    function decodeSafeCustomLinks(){
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        var nodes = [];
        while(walker.nextNode()){
            var text = (walker.currentNode.nodeValue || '').trim();
            if(text.indexOf('<a ') === 0 && text.indexOf('</a>') !== -1 && text.length < 800){
                nodes.push(walker.currentNode);
            }
        }
        nodes.forEach(function(node){
            var holder = document.createElement('div');
            holder.innerHTML = node.nodeValue;
            var a = holder.querySelector('a');
            if(!a){ return; }
            var href = a.getAttribute('href') || '';
            if(href && href.indexOf('javascript:') !== 0){
                if(href.indexOf('http://') !== 0 && href.indexOf('https://') !== 0 && href.indexOf('mailto:') !== 0 && href.indexOf('tel:') !== 0){
                    href = 'https://' + href.replace(/^\/+/, '');
                }
                var safe = document.createElement('a');
                safe.href = href;
                safe.target = '_blank';
                safe.rel = 'noopener';
                safe.textContent = a.textContent || href;
                node.parentNode.replaceChild(safe, node);
            }
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(decodeSafeCustomLinks, 600); });
    } else {
        setTimeout(decodeSafeCustomLinks, 600);
    }
})();


/* Sales Hub v1.3.1 CSV export helper for visible table rows. */
(function(){
    function cleanCell(text){
        return '"' + (text || '').replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"';
    }
    function exportTable(btn){
        var scope = btn.closest('.panel_s') || document;
        var table = scope.querySelector('table.smartsource-table');
        if(!table){ return; }
        var rows = [];
        table.querySelectorAll('tr').forEach(function(row){
            if(row.style.display === 'none'){ return; }
            var cells = Array.prototype.slice.call(row.querySelectorAll('th,td'));
            rows.push(cells.map(function(cell){ return cleanCell(cell.innerText); }).join(','));
        });
        var blob = new Blob([rows.join('\n')], {type:'text/csv;charset=utf-8;'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = btn.getAttribute('data-filename') || 'sales-hub-export.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    document.addEventListener('click', function(e){
        var btn = e.target.closest ? e.target.closest('.smartsource-export-csv') : null;
        if(btn){ e.preventDefault(); exportTable(btn); }
    });
})();


/* Sales Hub v1.3.7 scoped table action toolbar.
 * Important: this toolbar must not touch the CRM Modules page or sales document edit forms.
 * It is intentionally scoped to Sales Hub list pages and native CRM sales list pages only.
 */
(function(){
    function cleanPath(){
        var a = document.createElement('a');
        a.href = window.location.href;
        return (a.pathname || '').toLowerCase().replace(/\/+$/, '');
    }
    function targetFromPath(){
        var path = cleanPath();
        if(path.indexOf('/admin/sales_center/reports') !== -1){ return 'reports'; }
        if(path.indexOf('/admin/sales_center/contracts') !== -1){ return 'contracts'; }
        if(path.indexOf('/admin/sales_center/templates') !== -1){ return 'templates'; }
        if(path.indexOf('/admin/sales_center/documents') !== -1){ return 'documents'; }
        if(path === '/admin/sales_center' || path.match(/\/admin\/sales_center\/index$/)){ return 'salespersons'; }
        if(path === '/admin/proposals'){ return 'proposals'; }
        if(path === '/admin/estimates'){ return 'estimates'; }
        if(path === '/admin/invoices'){ return 'invoices'; }
        if(path === '/admin/credit_notes'){ return 'credit_notes'; }
        if(path === '/admin/payments'){ return 'payments'; }
        return null;
    }
    function adminBase(){
        var marker = '/admin/';
        var href = window.location.href;
        var idx = href.indexOf(marker);
        if(idx === -1){ return window.location.origin + '/admin/'; }
        return href.substring(0, idx + marker.length);
    }
    function addCheckboxes(table){
        if(!table.classList.contains('smartsource-table')){ return; }
        if(table.getAttribute('data-sales-center-checks') === '1'){ return; }
        table.setAttribute('data-sales-center-checks','1');
        var headRow = table.querySelector('thead tr');
        if(headRow && !headRow.querySelector('.sales-center-row-check')){
            var th = document.createElement('th');
            th.className = 'sales-center-row-check';
            th.innerHTML = '<input type="checkbox" class="sales-center-check-all">';
            headRow.insertBefore(th, headRow.firstChild);
        }
        table.querySelectorAll('tbody tr').forEach(function(row){
            if(row.querySelector('.sales-center-row-check')){ return; }
            var id = 0;
            var del = row.querySelector('a[href*="delete_document_link/"],a[href*="delete_commission/"],a[href*="permanent_delete_contract/"],a[href*="contract_delete/"],a[href*="delete_template/"],a[href*="delete/"]');
            if(del){
                var match = (del.getAttribute('href') || '').match(/(delete_document_link|delete_commission|permanent_delete_contract|contract_delete|delete_template|delete)\/(\d+)/);
                if(match){ id = match[2]; }
            }
            var td = document.createElement('td');
            td.className = 'sales-center-row-check';
            td.innerHTML = id ? '<input type="checkbox" class="sales-center-row-checkbox" value="'+id+'">' : '';
            row.insertBefore(td, row.firstChild);
        });
    }
    function toolbar(target){
        var base = adminBase() + 'sales_center/';
        var wrap = document.createElement('div');
        wrap.className = 'sales-center-generated-toolbar btn-group';
        wrap.setAttribute('role','group');
        wrap.innerHTML = '<a class="btn btn-default btn-xs" href="'+base+'import/'+target+'"><i class="fa fa-upload"></i> Import</a>'+
            '<a class="btn btn-default btn-xs" href="'+base+'sample_file/'+target+'"><i class="fa fa-table"></i> Sample</a>'+
            '<a class="btn btn-success btn-xs" href="'+base+'table_export/'+target+'"><i class="fa fa-download"></i> Export</a>'+
            '<button type="button" class="btn btn-warning btn-xs sales-center-refresh"><i class="fa fa-refresh"></i> Refresh</button>'+
            '<button type="button" class="btn btn-danger btn-xs sales-center-mass-delete"><i class="fa fa-trash"></i> Mass Delete</button>';
        return wrap;
    }
    function selectedIds(scope){
        var ids = [];
        scope.querySelectorAll('.sales-center-row-checkbox:checked,input[type="checkbox"][name="ids[]"]:checked,input[type="checkbox"][name="id[]"]:checked').forEach(function(cb){ if(cb.value){ ids.push(cb.value); } });
        return ids;
    }
    function submitMassDelete(target, ids){
        if(!ids.length){ alert('Select at least one record first.'); return; }
        if(!confirm('Delete selected records?')){ return; }
        var form = document.createElement('form');
        form.method = 'post';
        form.action = adminBase() + 'sales_center/mass_delete/' + target;
        ids.forEach(function(id){
            var input = document.createElement('input'); input.type = 'hidden'; input.name = 'ids[]'; input.value = id; form.appendChild(input);
        });
        var csrfName = (window.csrfData && window.csrfData.token_name) ? window.csrfData.token_name : (typeof csrfData !== 'undefined' ? csrfData.token_name : '');
        var csrfHash = (window.csrfData && window.csrfData.hash) ? window.csrfData.hash : (typeof csrfData !== 'undefined' ? csrfData.hash : '');
        if(csrfName && csrfHash){ var csrf = document.createElement('input'); csrf.type='hidden'; csrf.name=csrfName; csrf.value=csrfHash; form.appendChild(csrf); }
        document.body.appendChild(form); form.submit();
    }
    function placeToolbar(scope, tb){
        var length = scope.querySelector('.dataTables_length');
        if(length && length.parentNode){
            var holder = document.createElement('div');
            holder.className = 'sales-center-toolbar-holder';
            holder.appendChild(tb);
            length.parentNode.insertBefore(holder, length.nextSibling);
            return;
        }
        scope.insertBefore(tb, scope.firstChild);
    }
    function init(){
        var target = targetFromPath();
        if(!target){ return; }
        var selector = (cleanPath().indexOf('/admin/sales_center') !== -1) ? 'table.smartsource-table' : 'table.table, table.dt-table';
        document.querySelectorAll(selector).forEach(function(table){
            if(table.closest('.sales-center-no-toolbar')){ return; }
            if(table.closest('form') && cleanPath().indexOf('/admin/sales_center') === -1){ return; }
            if(table.querySelector('input[name="items[]"], textarea[name*="description"]')){ return; }
            addCheckboxes(table);
            var scope = table.closest('.dataTables_wrapper') || table.closest('.panel-body') || table.parentNode;
            if(!scope || scope.querySelector('.sales-center-generated-toolbar')){ return; }
            var tb = toolbar(target);
            placeToolbar(scope, tb);
            tb.addEventListener('click', function(e){
                if(e.target.closest('.sales-center-refresh')){ window.location.reload(); }
                if(e.target.closest('.sales-center-mass-delete')){ submitMassDelete(target, selectedIds(scope)); }
            });
        });
        document.addEventListener('change', function(e){
            if(e.target.classList && e.target.classList.contains('sales-center-check-all')){
                var table = e.target.closest('table');
                if(table){ table.querySelectorAll('.sales-center-row-checkbox').forEach(function(cb){ cb.checked = e.target.checked; }); }
            }
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
