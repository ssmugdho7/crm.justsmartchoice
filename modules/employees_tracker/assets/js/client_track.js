
(function(){
    var map = null;
    var projectMarker = null;
    var installerMarkers = {};
    var bounds = null;

    function hasMaps(){ return typeof google !== 'undefined' && google.maps; }
    function escapeHtml(str){ return String(str || '').replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];}); }
    function escapeAttr(str){ return escapeHtml(str).replace(/"/g, '&quot;'); }

    function initMap(){
        var el = document.getElementById('project-map');
        if (el && hasMaps() && !map) {
            el.innerHTML = '';
            map = new google.maps.Map(el, { zoom: 11, center: {lat:28.4769, lng:-82.5255}, mapTypeControl:false, streetViewControl:false });
            projectMarker = new google.maps.Marker({ map: map, label:'P', title:'Project' });
        } else if (el && !hasMaps()) {
            el.innerHTML = '<div class="et-map-fallback">Loading map. If it does not load, Google Maps is not configured yet.</div>';
        }
    }

    function card(i){
        var d = i.distance_text || 'Distance not available';
        var eta = i.eta_text || 'ETA not available';
        var last = i.last_time ? i.last_time : 'Waiting for installer location';
        var img = i.staff_image || '';
        var service = i.service_type || 'Scheduled service';
        var notes = i.job_notes ? '<div class="et-muted">'+escapeHtml(i.job_notes)+'</div>' : '';
        return '<div class="et-installer-card">' +
            '<img src="'+escapeAttr(img)+'" alt="Installer">' +
            '<div>' +
                '<strong>'+escapeHtml(i.staff_name)+'</strong><br>' +
                '<span class="et-badge">'+escapeHtml(eta)+'</span>' +
                '<span class="et-badge orange">'+escapeHtml(d)+'</span>' +
                '<div>'+escapeHtml(service)+'</div>' + notes +
                '<small class="et-muted">Last update: '+escapeHtml(last)+'</small>' +
            '</div>' +
        '</div>';
    }

    function poll(){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', ET_STATUS_URL, true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4){
                var list = document.getElementById('installer-list');
                if (xhr.status !== 200) {
                    if (list) list.innerHTML = '<div class="alert alert-warning">Tracking page is available, but the project status endpoint returned an error. Please verify the customer is assigned to this project.</div>';
                    return;
                }
                var resp;
                try { resp = JSON.parse(xhr.responseText); } catch(e){ return; }
                if (!resp.success) return;

                initMap();
                var data = resp.data || {};
                bounds = hasMaps() ? new google.maps.LatLngBounds() : null;
                if (map && data.project_location && data.project_location.lat && data.project_location.lng){
                    var pos = {lat: parseFloat(data.project_location.lat), lng: parseFloat(data.project_location.lng)};
                    projectMarker.setPosition(pos);
                    bounds.extend(pos);
                    map.setCenter(pos);
                }

                if (!list) return;
                list.innerHTML = '';
                if (!data.installers || !data.installers.length) {
                    list.innerHTML = '<div class="et-stat">No installer has been assigned to this project yet.</div>';
                    return;
                }

                data.installers.forEach(function(i){
                    var id = i.staff_id;
                    if (map && i.last_lat && i.last_lng){
                        if (!installerMarkers[id]) installerMarkers[id] = new google.maps.Marker({ map: map, label: 'I', title: i.staff_name });
                        var mpos = {lat: parseFloat(i.last_lat), lng: parseFloat(i.last_lng)};
                        installerMarkers[id].setPosition(mpos);
                        if (bounds) bounds.extend(mpos);
                    }
                    list.innerHTML += card(i);
                });
                if (map && bounds && !bounds.isEmpty()) map.fitBounds(bounds);
            }
        };
        xhr.send();
    }

    window.employeesTrackerClientGoogleReady = function(){ initMap(); poll(); };
    document.addEventListener('DOMContentLoaded', function(){
        initMap(); poll(); setInterval(poll, Math.max(10, ET_POLL)*1000);
    });
})();
