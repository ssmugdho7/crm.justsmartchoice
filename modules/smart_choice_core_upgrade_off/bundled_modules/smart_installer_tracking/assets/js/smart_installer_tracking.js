(function(){
  'use strict';

  function qs(sel){ return document.querySelector(sel); }
  function setText(sel, text){ var el=qs(sel); if(el){ el.textContent=text; } }
  function csrfAppend(form){ if (typeof csrfData !== 'undefined') { form.append(csrfData.token_name, csrfData.hash); } }

  function openMapUrl(){
    var lat = qs('[name="destination_lat"]');
    var lng = qs('[name="destination_lng"]');
    var address = qs('[name="destination_address"]');
    var link = qs('#sit-open-google-map');
    if(!link){ return; }
    var query = lat && lng && lat.value && lng.value ? (lat.value + ',' + lng.value) : (address ? address.value : '');
    link.href = query ? ('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(query)) : 'https://www.google.com/maps';
  }

  document.addEventListener('change', function(e){
    if(e.target && e.target.id === 'client_id'){
      var option = e.target.options[e.target.selectedIndex];
      if(option){
        var name = qs('[name="client_name"]');
        var phone = qs('[name="client_phone"]');
        var address = qs('[name="destination_address"]');
        if(name){ name.value = option.getAttribute('data-name') || ''; }
        if(phone){ phone.value = option.getAttribute('data-phone') || ''; }
        if(address && !address.value){ address.value = option.getAttribute('data-address') || ''; }
        openMapUrl();
      }
    }
    if(e.target && e.target.id === 'project_id'){
      var p = e.target.options[e.target.selectedIndex];
      var clientId = p ? p.getAttribute('data-client-id') : '';
      var client = qs('#client_id');
      if(client && clientId){ client.value = clientId; client.dispatchEvent(new Event('change')); if(window.jQuery){ window.jQuery(client).selectpicker('refresh'); } }
    }
    if(e.target && e.target.id === 'appointment_id'){
      var a = e.target.options[e.target.selectedIndex];
      var cid = a ? a.getAttribute('data-client-id') : '';
      var pid = a ? a.getAttribute('data-project-id') : '';
      var clientSel = qs('#client_id');
      var projectSel = qs('#project_id');
      if(projectSel && pid && pid !== '0'){ projectSel.value = pid; projectSel.dispatchEvent(new Event('change')); if(window.jQuery){ window.jQuery(projectSel).selectpicker('refresh'); } }
      if(clientSel && cid && cid !== '0'){ clientSel.value = cid; clientSel.dispatchEvent(new Event('change')); if(window.jQuery){ window.jQuery(clientSel).selectpicker('refresh'); } }
    }
    if(e.target && (e.target.name === 'destination_lat' || e.target.name === 'destination_lng' || e.target.name === 'destination_address')){ openMapUrl(); }
  });

  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'sit-geocode-address'){
      var address = qs('[name="destination_address"]');
      if(!address || !address.value){ setText('#sit-geocode-status','Enter a destination address first.'); return; }
      setText('#sit-geocode-status','Getting coordinates from Google Maps...');
      var form = new FormData(); form.append('address', address.value); csrfAppend(form);
      fetch(admin_url + 'smart_installer_tracking/geocode_address', {method:'POST', body:form, credentials:'same-origin'})
        .then(function(r){return r.json();})
        .then(function(data){
          if(!data.success){ setText('#sit-geocode-status', data.message || 'Coordinates were not found.'); return; }
          var lat = qs('[name="destination_lat"]'); var lng = qs('[name="destination_lng"]');
          if(lat){ lat.value = data.lat; } if(lng){ lng.value = data.lng; }
          if(address && data.formatted_address){ address.value = data.formatted_address; }
          setText('#sit-geocode-status','Coordinates added successfully.'); openMapUrl();
        })
        .catch(function(){ setText('#sit-geocode-status','Google Maps request failed.'); });
    }
    if(e.target && e.target.id==='sit-start-location'){
      var panel=qs('#sit-location-panel'); if(!panel || !navigator.geolocation){ setText('.sit-location-status','GPS is not available on this device.'); return; }
      var tripId=panel.getAttribute('data-trip-id');
      navigator.geolocation.watchPosition(function(pos){ postLocation(tripId,pos); }, function(){ setText('.sit-location-status','Location permission is required.'); }, {enableHighAccuracy:true, maximumAge:15000, timeout:20000});
      setText('.sit-location-status','Live location sharing started. Keep this page open.');
    }
  });

  function postLocation(tripId, pos){
    var form = new FormData();
    form.append('trip_id', tripId);
    form.append('latitude', pos.coords.latitude);
    form.append('longitude', pos.coords.longitude);
    form.append('accuracy', pos.coords.accuracy || '');
    form.append('speed', pos.coords.speed || '');
    form.append('heading', pos.coords.heading || '');
    csrfAppend(form);
    fetch(admin_url + 'smart_installer_tracking/save_location', {method:'POST', body:form, credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
      setText('.sit-location-status', data.success ? 'Location shared successfully.' : 'Location could not be shared.');
    }).catch(function(){ setText('.sit-location-status','Location request failed.'); });
  }

  function initAdminMap(){
    var el=qs('#sit-admin-live-map');
    if(!el || typeof google === 'undefined' || !google.maps){ return; }
    var trips=[]; try{ trips=JSON.parse(el.getAttribute('data-trips') || '[]'); }catch(e){ trips=[]; }
    var center={lat:27.9506,lng:-82.4572};
    for(var i=0;i<trips.length;i++){ if(trips[i].last_lat && trips[i].last_lng){ center={lat:parseFloat(trips[i].last_lat), lng:parseFloat(trips[i].last_lng)}; break; } }
    var map=new google.maps.Map(el,{zoom:11,center:center});
    trips.forEach(function(t){ if(!t.last_lat || !t.last_lng){ return; } var name=((t.firstname||'')+' '+(t.lastname||'')).trim() || 'Installer'; new google.maps.Marker({position:{lat:parseFloat(t.last_lat),lng:parseFloat(t.last_lng)},map:map,title:name}); });
  }

  function initSingleMap(){
    var el=qs('#sit-single-live-map');
    if(!el || typeof google === 'undefined' || !google.maps){ return; }
    var lat=parseFloat(el.getAttribute('data-lat') || ''); var lng=parseFloat(el.getAttribute('data-lng') || '');
    if(!lat || !lng){ el.innerHTML='<div class="sit-map-placeholder">Live location is pending. The installer must press Start Live Location from the phone.</div>'; return; }
    var pos={lat:lat,lng:lng}; var map=new google.maps.Map(el,{zoom:14,center:pos}); new google.maps.Marker({position:pos,map:map,title:el.getAttribute('data-title') || 'Installer'});
  }

  function refreshClient(){
    var map=qs('#sit-client-map'); if(!map){return;}
    var token=map.getAttribute('data-token');
    fetch('/smart-installer-tracking/client-data/'+encodeURIComponent(token), {credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
      if(!data.success){return;}
      setText('#sit-client-status-text', data.status);
      setText('#sit-client-eta', data.eta_text || 'ETA pending');
      setText('#sit-client-last-update', data.last_location_at || 'Pending');
      map.textContent = data.lat && data.lng ? ('Installer Location: '+data.lat+', '+data.lng) : 'Installer location is pending.';
    }).catch(function(){});
  }

  openMapUrl();
  if(document.readyState === 'complete'){ initAdminMap(); initSingleMap(); } else { window.addEventListener('load', function(){ initAdminMap(); initSingleMap(); }); }
  var clientMap=qs('#sit-client-map'); if(clientMap){refreshClient(); setInterval(refreshClient, Math.max(15, parseInt(clientMap.getAttribute('data-refresh') || '45',10))*1000);}
})();
