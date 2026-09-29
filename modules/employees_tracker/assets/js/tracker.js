
(function(){
    var watchId = null;
    var map = null;
    var meMarker = null;
    var staffMarkers = {};
    var livePoller = null;
    var mapsWaiter = null;

    function hasMaps(){ return typeof google !== 'undefined' && google.maps; }

    function notify(message, type){
        type = type || 'success';
        if (typeof alert_float === 'function') {
            alert_float(type, message);
            return;
        }
        var toast = document.createElement('div');
        toast.className = 'et-toast et-toast-' + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function(){ toast.classList.add('show'); }, 30);
        setTimeout(function(){ toast.classList.remove('show'); setTimeout(function(){ toast.remove(); }, 300); }, 2600);
    }

    function setStatus(html){
        var s = document.getElementById('et-status');
        if (s) s.innerHTML = html;
    }

    function initMap(){
        var el = document.getElementById('map');
        if (!el) return;
        if (!hasMaps()) {
            el.innerHTML = '<div class="et-map-fallback">Loading Google map... If it stays here, check the Google Maps API key in Setup → Settings → Installer ETA.</div>';
            return;
        }
        if (map) return;
        el.innerHTML = '';
        map = new google.maps.Map(el, { zoom: 13, center: {lat: 28.4769, lng: -82.5255}, mapTypeControl: false, streetViewControl: false });
        meMarker = new google.maps.Marker({ map: map, title: 'My location', label: 'Me' });
        pollLiveLocations();
        if (!livePoller) { livePoller = setInterval(pollLiveLocations, Math.max(10, (window.ET_POLL || 30)) * 1000); }
    }

    function waitForMap(){
        initMap();
        if (map || mapsWaiter) return;
        var tries = 0;
        mapsWaiter = setInterval(function(){
            tries++;
            initMap();
            if (map || tries > 20) {
                clearInterval(mapsWaiter);
                mapsWaiter = null;
            }
        }, 500);
    }

    window.employeesTrackerGoogleReady = function(){ waitForMap(); };

    function buildParams(paramsObj) {
        var parts = [];
        for (var k in paramsObj) {
            if (Object.prototype.hasOwnProperty.call(paramsObj, k)) {
                parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(paramsObj[k]));
            }
        }
        return parts.join('&');
    }


    function pollLiveLocations(){
        if (!map || typeof ET_LIVE_LOCATIONS_URL === 'undefined') return;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', ET_LIVE_LOCATIONS_URL, true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            if (xhr.status !== 200) return;
            var resp;
            try { resp = JSON.parse(xhr.responseText); } catch(e) { return; }
            if (!resp.success || !resp.data) return;

            var bounds = new google.maps.LatLngBounds();
            var hasAny = false;
            resp.data.forEach(function(item){
                if (!item.lat || !item.lng) return;
                var sid = item.staff_id;
                var pos = {lat: parseFloat(item.lat), lng: parseFloat(item.lng)};
                if (!staffMarkers[sid]) {
                    staffMarkers[sid] = new google.maps.Marker({
                        map: map,
                        title: item.staff_name || 'Employee',
                        label: 'E'
                    });
                }
                staffMarkers[sid].setPosition(pos);
                staffMarkers[sid].setTitle((item.staff_name || 'Employee') + ' - ' + (item.last_time || ''));
                bounds.extend(pos);
                hasAny = true;
            });
            if (hasAny && !bounds.isEmpty()) {
                map.fitBounds(bounds);
            }
        };
        xhr.send();
    }

    function onPos(position){
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var acc = position.coords.accuracy || '';
        var battery = '';
        waitForMap();
        if (meMarker && map) {
            meMarker.setPosition({lat: lat, lng: lng});
            map.setCenter({lat: lat, lng: lng});
        }

        var data = {lat: lat, lng: lng, accuracy: acc, battery: battery};
        if (typeof window.csrfData !== 'undefined') {
            for (var k in window.csrfData) {
                if (Object.prototype.hasOwnProperty.call(window.csrfData, k)) data[k] = window.csrfData[k];
            }
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ET_SAVE_URL, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            if (xhr.status >= 400) {
                notify('Location was detected, but CRM did not save it. Check permissions or login session.', 'warning');
                return;
            }
            var resp = null;
            try { resp = JSON.parse(xhr.responseText); } catch(e) {}
            if (resp && resp.success === false) {
                notify(resp.message || 'CRM did not save the location.', 'danger');
                setStatus('<strong>Save error:</strong> ' + (resp.message || 'CRM did not save the location.'));
            } else {
                var t = new Date().toLocaleTimeString();
                setStatus('<strong>Live sharing active.</strong><br>Last sent at '+t+' ('+lat.toFixed(5)+', '+lng.toFixed(5)+')');
                pollLiveLocations();
            }
        };
        xhr.send(buildParams(data));
    }

    function onError(err){
        var msg = (err && (err.message||err.code)) ? (err.message||err.code) : 'Unknown error';
        setStatus('<strong>Location error:</strong> '+msg+'. Use HTTPS and allow browser location permissions.');
        notify('Location sharing could not start: ' + msg, 'danger');
    }

    function startShare(){
        waitForMap();
        if (!navigator.geolocation) {
            notify('Geolocation is not supported by this browser.', 'danger');
            return;
        }
        if (watchId !== null) {
            notify('Location sharing is already active.', 'info');
            return;
        }
        watchId = navigator.geolocation.watchPosition(onPos, onError, {
            enableHighAccuracy:true,
            maximumAge:5000,
            timeout:15000
        });
        setStatus('Starting secure location sharing...');
        notify('You started sharing your location.', 'success');
    }

    function stopShare(){
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
            setStatus('Location sharing stopped.');
            notify('Location sharing stopped.', 'warning');
        } else {
            notify('Location sharing is already stopped.', 'info');
        }
    }

    function saveTestLocation(){
        waitForMap();
        var data = {};
        if (typeof window.csrfData !== 'undefined') {
            for (var k in window.csrfData) {
                if (Object.prototype.hasOwnProperty.call(window.csrfData, k)) data[k] = window.csrfData[k];
            }
        }
        var url = (typeof ET_TEST_LOCATION_URL !== 'undefined') ? ET_TEST_LOCATION_URL : ET_SAVE_URL;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            var resp = null;
            try { resp = JSON.parse(xhr.responseText); } catch(e) {}
            if (xhr.status === 200 && resp && resp.success) {
                notify('Test location saved. The map should show a marker now.', 'success');
                setStatus('<strong>Test location saved.</strong><br>If this marker appears, the database and map polling are working.');
                pollLiveLocations();
            } else {
                notify((resp && resp.message) ? resp.message : 'Test location failed.', 'danger');
            }
        };
        xhr.send(buildParams(data));
    }

    function keepMenuOpen(){
        try {
            localStorage.setItem('employees_tracker_menu_open','1');
            var parent = document.querySelector('li.menu-item-employees-tracker, li[data-slug="employees-tracker"], #side-menu li a[href*="employees_tracker"]');
            var li = parent ? parent.closest('li') : null;
            if (li) {
                li.classList.add('active');
                li.classList.add('menu-open');
                var ul = li.querySelector('ul.nav-second-level, ul.collapse');
                if (ul) { ul.style.display = 'block'; ul.classList.add('in'); }
            }
            var links = document.querySelectorAll('#side-menu a[href*="employees_tracker"]');
            links.forEach(function(a){
                if (window.location.href.indexOf(a.getAttribute('href')) === 0 || window.location.href === a.href) {
                    var x = a.closest('li'); if (x) x.classList.add('active');
                }
            });
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', function(){
        keepMenuOpen();
        waitForMap();
        var start = document.getElementById('et-start');
        var stop = document.getElementById('et-stop');
        var test = document.getElementById('et-test');
        pollLiveLocations();
        if (start) start.addEventListener('click', startShare);
        if (stop) stop.addEventListener('click', stopShare);
        if (test) test.addEventListener('click', saveTestLocation);
    });
})();
