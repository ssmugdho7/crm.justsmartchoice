(function(window, document){
    'use strict';

    var cfg = window.SMART_CHOICE_TOAST_MASTER || {};
    var userInteracted = false;
    var audioCtx = null;
    var queuedSoundType = null;

    function ensureAudio(){
        try{
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if(!AudioContext) return null;
            if(!audioCtx) audioCtx = new AudioContext();
            if(audioCtx.state === 'suspended') { audioCtx.resume(); }
            return audioCtx;
        }catch(e){ return null; }
    }

    function onUserGesture(){
        userInteracted = true;
        ensureAudio();
        if(queuedSoundType){
            var t = queuedSoundType;
            queuedSoundType = null;
            setTimeout(function(){ makeSound(t, true); }, 60);
        }
    }

    document.addEventListener('click', onUserGesture, {passive:true});
    document.addEventListener('keydown', onUserGesture, {passive:true});
    document.addEventListener('touchstart', onUserGesture, {passive:true});

    function container(settings){
        settings = settings || cfg;
        var pos = settings.position || 'top-right';
        var id = 'sc-toast-container-' + pos;
        var el = document.getElementById(id);
        if(!el){
            el = document.createElement('div');
            el.id = id;
            el.className = 'sc-toast-container ' + pos;
            document.body.appendChild(el);
        }
        return el;
    }

    function iconFor(type){
        if(type === 'success') return '✓';
        if(type === 'warning') return '!';
        if(type === 'danger' || type === 'error') return '×';
        return 'i';
    }

    function titleFor(type){
        if(type === 'success') return 'Success';
        if(type === 'warning') return 'Warning';
        if(type === 'danger' || type === 'error') return 'Error';
        return 'Notice';
    }

    function tone(ctx, start, frequency, duration, type, volume){
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = type || 'sine';
        osc.frequency.setValueAtTime(frequency, start);
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.exponentialRampToValueAtTime(Math.max(0.0001, volume), start + 0.018);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(start);
        osc.stop(start + duration + 0.02);
    }

    function makeSound(type, forced, settings){
        settings = settings || cfg;
        if(!settings.soundEnabled || settings.soundName === 'none') return;
        if(!userInteracted && !forced){ queuedSoundType = type || 'info'; return; }
        try{
            var ctx = ensureAudio();
            if(!ctx) return;
            var now = ctx.currentTime + 0.015;
            var vol = Math.max(0, Math.min(1, (parseInt(settings.volume || 80, 10) / 100))) * 0.28;
            var sound = settings.soundName || 'soft_click';
            var base = 620;
            if(type === 'success') base = 820;
            if(type === 'warning') base = 520;
            if(type === 'danger' || type === 'error') base = 280;

            if(sound === 'mail'){
                tone(ctx, now, 740, 0.16, 'sine', vol);
                tone(ctx, now + 0.18, 980, 0.20, 'sine', vol * 0.9);
                return;
            }
            if(sound === 'message'){
                tone(ctx, now, 610, 0.10, 'triangle', vol);
                tone(ctx, now + 0.11, 790, 0.12, 'triangle', vol * 0.85);
                return;
            }
            if(sound === 'bell'){
                tone(ctx, now, 920, 0.28, 'sine', vol);
                tone(ctx, now + 0.03, 1220, 0.22, 'sine', vol * 0.45);
                return;
            }
            if(sound === 'error'){
                tone(ctx, now, 260, 0.18, 'sawtooth', vol * 0.8);
                tone(ctx, now + 0.19, 190, 0.18, 'sawtooth', vol * 0.65);
                return;
            }
            if(sound === 'warning'){
                tone(ctx, now, 440, 0.13, 'square', vol * 0.55);
                tone(ctx, now + 0.15, 440, 0.13, 'square', vol * 0.55);
                return;
            }
            if(sound === 'success'){
                tone(ctx, now, 660, 0.11, 'sine', vol);
                tone(ctx, now + 0.12, 880, 0.16, 'sine', vol);
                return;
            }
            if(sound === 'pop'){
                tone(ctx, now, base, 0.08, 'square', vol * 0.5);
                return;
            }
            if(sound === 'ding'){
                tone(ctx, now, 880, 0.18, 'sine', vol);
                return;
            }
            if(sound === 'glass'){
                tone(ctx, now, 1050, 0.13, 'triangle', vol * 0.9);
                tone(ctx, now + 0.08, 1320, 0.11, 'triangle', vol * 0.45);
                return;
            }
            tone(ctx, now, base, 0.12, 'sine', vol);
        }catch(e){}
    }

    function logNotification(type, message, title){
        if(!window.jQuery || !cfg.channels || !cfg.channels.ajax) return;
        try{
            var token = window.csrfData || {};
            var data = {type:type, message:message, title:title, source:'crm', url:window.location.href};
            if(token.token_name && token.hash){ data[token.token_name] = token.hash; }
            window.jQuery.post((window.admin_url || '/admin/') + 'toast_master/log', data);
        }catch(e){}
    }

    function cleanMessage(message){
        return String(message || '').replace(/<[^>]*>?/gm, '').replace(/\s+/g, ' ').trim();
    }

    function render(type, message, title, options){
        options = options || {};
        type = (type || 'info').replace('danger','error');
        message = cleanMessage(message);
        title = title || options.title || titleFor(type);
        var settings = {};
        Object.keys(cfg || {}).forEach(function(key){ settings[key] = cfg[key]; });
        Object.keys(options || {}).forEach(function(key){ settings[key] = options[key]; });
        var duration = parseInt(settings.duration || 4500, 10);
        var el = document.createElement('div');
        el.className = 'sc-toast ' + type + ' style-' + (settings.style || '1') + ' animation-' + (settings.animation || 'fade');
        el.innerHTML = '<div class="sc-toast-icon">'+iconFor(type)+'</div><div class="sc-toast-content"><div class="sc-toast-title"></div><div class="sc-toast-message"></div></div><button type="button" class="sc-toast-close" aria-label="Close">×</button>' + (settings.progressBar ? '<div class="sc-toast-progress"></div>' : '');
        el.querySelector('.sc-toast-title').textContent = title;
        el.querySelector('.sc-toast-message').textContent = message;
        var progress = el.querySelector('.sc-toast-progress');
        if(progress){ progress.style.animationDuration = duration + 'ms'; }
        var timeout = null;
        function close(){ el.classList.add('sc-toast-out'); setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 200); }
        el.querySelector('.sc-toast-close').addEventListener('click', close);
        if(duration > 0){ timeout = setTimeout(close, duration); }
        if(settings.pauseOnHover && timeout){
            el.addEventListener('mouseenter', function(){ clearTimeout(timeout); if(progress) progress.style.animationPlayState='paused'; });
            el.addEventListener('mouseleave', function(){ timeout = setTimeout(close, 1600); if(progress) progress.style.animationPlayState='running'; });
        }
        container(settings).appendChild(el);
        makeSound(type, false, settings);
        logNotification(type, message, title);
        return el;
    }

    function modalAlert(message, title, type){
        type = type || 'warning';
        title = title || titleFor(type);
        var old = document.getElementById('sc-notification-alert-modal');
        if(old && old.parentNode) old.parentNode.removeChild(old);
        var wrap = document.createElement('div');
        wrap.id = 'sc-notification-alert-modal';
        wrap.className = 'sc-notification-modal-wrap';
        wrap.innerHTML = '<div class="sc-notification-modal style-' + (cfg.style || '1') + ' ' + type + '" role="dialog" aria-modal="true"><div class="sc-notification-modal-icon">' + iconFor(type) + '</div><div class="sc-notification-modal-body"><div class="sc-notification-modal-title"></div><div class="sc-notification-modal-message"></div><button type="button" class="sc-notification-modal-ok">OK</button></div></div>';
        wrap.querySelector('.sc-notification-modal-title').textContent = title;
        wrap.querySelector('.sc-notification-modal-message').textContent = cleanMessage(message);
        function close(){ if(wrap.parentNode) wrap.parentNode.removeChild(wrap); }
        wrap.querySelector('.sc-notification-modal-ok').addEventListener('click', close);
        wrap.addEventListener('click', function(e){ if(e.target === wrap) close(); });
        document.body.appendChild(wrap);
        setTimeout(function(){ var b = wrap.querySelector('.sc-notification-modal-ok'); if(b) b.focus(); }, 30);
        makeSound(type);
        logNotification(type, message, title);
    }


    window.smartChoiceToastConfigure = function(next){
        next = next || {};
        Object.keys(next).forEach(function(key){ cfg[key] = next[key]; });
        window.SMART_CHOICE_TOAST_MASTER = cfg;
        return cfg;
    };

    window.showSuccessToast = function(msg, title, options){ return render('success', msg, title, options); };
    window.showErrorToast = function(msg, title, options){ return render('error', msg, title, options); };
    window.showWarningToast = function(msg, title, options){ return render('warning', msg, title, options); };
    window.showInfoToast = function(msg, title, options){ return render('info', msg, title, options); };
    window.smartChoiceNotify = function(type, msg, title, options){ return render(type, msg, title, options); };
    window.smartChoiceAlert = function(msg, title, type){ return modalAlert(msg, title, type || 'warning'); };
    window.smartChoiceTestSound = function(type, settings){ userInteracted = true; if(settings){ window.smartChoiceToastConfigure(settings); } makeSound(type || 'info', true, settings || cfg); };

    var originalAlertFloat = window.alert_float;
    if(!cfg.channels || cfg.channels.alertFloat !== false){
        window.alert_float = function(type, msg){
            var mapped = type === 'danger' ? 'error' : (type || 'info');
            render(mapped, msg, titleFor(mapped));
            if(typeof originalAlertFloat === 'function' && cfg.keepOriginalAlerts){ originalAlertFloat(type, msg); }
        };
    }

    if(!cfg.channels || cfg.channels.browserAlert !== false){
        var originalAlert = window.alert;
        window.alert = function(message){ modalAlert(message, 'CRM Notice', 'warning'); };
        window.smartChoiceOriginalAlert = originalAlert;
    }

    if(window.jQuery){
        window.jQuery(function(){
            var flashes = window.jQuery('.alert:not(.sc-toast-skip), .flashdata, .alert-success, .alert-danger, .alert-warning, .alert-info');
            flashes.each(function(){
                var $el = window.jQuery(this);
                if($el.data('scToastRead')) return;
                var text = cleanMessage($el.text());
                if(!text) return;
                var type = 'info';
                if($el.hasClass('alert-success')) type = 'success';
                if($el.hasClass('alert-danger')) type = 'error';
                if($el.hasClass('alert-warning')) type = 'warning';
                render(type, text, titleFor(type));
                $el.data('scToastRead', 1);
            });
        });
    }

})(window, document);
