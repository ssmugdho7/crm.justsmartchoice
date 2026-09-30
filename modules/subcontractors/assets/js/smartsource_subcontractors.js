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
            {value:'smartsource_subcontractors', label:'Subcontractors'},
            {value:'smartsource_subcontractor_contracts', label:'Subcontractor Contracts'}
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
    function injectStaffSubcontractorFields(){
        if(window.location.href.indexOf('/admin/staff/member') === -1){ return; }
        if(document.getElementById('smartsource-staff-subcontractor-box')){ return; }
        var form = document.querySelector('form.staff-form');
        if(!form){ return; }
        var target = form.querySelector('#tab_profile') || form;
        var box = document.createElement('div');
        box.id = 'smartsource-staff-subcontractor-box';
        box.className = 'form-group';
        box.innerHTML = '<div class="checkbox checkbox-primary"><input type="checkbox" id="is_subcontractor" name="is_subcontractor" value="1"><label for="is_subcontractor">Mark this staff member as a subcontractor</label></div><input type="hidden" name="smartsource_staff_fields_present" value="1"><input type="hidden" name="smartsource_subcontractor_id">';
        box.querySelector('[name="is_subcontractor"]').checked = form.getAttribute('data-is-subcontractor') === '1';
        box.querySelector('[name="smartsource_subcontractor_id"]').value = form.getAttribute('data-smartsource-subcontractor-id') || '0';
        target.insertBefore(box, target.firstChild);
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', injectStaffSubcontractorFields);
    } else { injectStaffSubcontractorFields(); }
})();


(function(){
    function initSmartsourceEditors(){
        var wrap = document.querySelector('.smartsource-template-editor-wrap');
        if(!wrap){ return; }
        wrap.classList.add('smartsource-editor-ready');
        if(typeof init_editor === 'function'){ init_editor(); }
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initSmartsourceEditors);
    } else { initSmartsourceEditors(); }
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

/* v2.7.0 persistent portal application alerts */
(function(){
    var cfg = window.smartsourceApplicationAlerts || null;
    if(!cfg || !cfg.enabled || !cfg.url){ return; }
    var lastAlertId = 0;
    var audioReady = false;

    function beep(){
        if(!cfg.sound){ return; }
        try{
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if(!AudioContext){ return; }
            var ctx = new AudioContext();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.value = 0.12;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            setTimeout(function(){ osc.stop(); ctx.close(); }, 420);
        }catch(e){}
    }

    function ensureBox(){
        var box = document.getElementById('smartsource-application-alert-box');
        if(box){ return box; }
        box = document.createElement('div');
        box.id = 'smartsource-application-alert-box';
        box.className = 'smartsource-application-alert-box';
        box.innerHTML = '<div class="smartsource-alert-head"><strong><i class="fa fa-bell"></i> New Subcontractor Application</strong><button type="button" class="smartsource-alert-close">×</button></div><div class="smartsource-alert-body"></div>';
        document.body.appendChild(box);
        box.addEventListener('click', function(e){
            if(e.target && e.target.classList.contains('smartsource-alert-close')){
                box.classList.remove('active');
            }
            var ack = e.target.closest ? e.target.closest('[data-smartsource-ack]') : null;
            if(ack){
                e.preventDefault();
                var id = ack.getAttribute('data-smartsource-ack');
                fetch((cfg.ackUrl || '').replace(/\/$/, '') + '/' + id, {credentials:'same-origin'}).then(function(){ box.classList.remove('active'); });
            }
        });
        return box;
    }

    function render(alerts){
        if(!alerts || !alerts.length){ return; }
        var box = ensureBox();
        var body = box.querySelector('.smartsource-alert-body');
        var html = '';
        alerts.forEach(function(a){
            var id = parseInt(a.id || 0, 10);
            if(id > lastAlertId){ lastAlertId = id; beep(); }
            html += '<div class="smartsource-alert-item">';
            html += '<div class="smartsource-alert-title">'+escapeHtml(a.title || 'Subcontractor Application')+'</div>';
            html += '<div class="smartsource-alert-message">'+escapeHtml(a.message || '')+'</div>';
            html += '<div class="smartsource-alert-actions"><a class="btn btn-success btn-xs" href="'+escapeAttr(a.open_url || '#')+'">Open</a> <a class="btn btn-default btn-xs" href="#" data-smartsource-ack="'+id+'">Mark Reviewed</a></div>';
            html += '</div>';
        });
        body.innerHTML = html;
        box.classList.add('active');
    }

    function escapeHtml(str){ return (str || '').toString().replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]; }); }
    function escapeAttr(str){ return escapeHtml(str).replace(/`/g,'&#096;'); }
    function poll(){
        fetch(cfg.url, {credentials:'same-origin'}).then(function(r){ return r.json(); }).then(function(data){ if(data && data.success){ render(data.alerts || []); } }).catch(function(){});
    }
    setTimeout(poll, 1500);
    setInterval(poll, Math.max(10, parseInt(cfg.pollSeconds || 20, 10)) * 1000);
})();
