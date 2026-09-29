(function ($) {
    'use strict';
    var observer = null, timer = null;

    function normalizeText(value) {
        var text = (value || '').toString().toLowerCase();
        try { text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch (e) {}
        return text.replace(/[_\-]+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function findTopSearchAnchor() {
        var selectors = ['#top_search_button','#top_search','#global_search','#search_input','input[name="q"]','[data-target="#search_modal"]','[data-bs-target="#search_modal"]','.top-search','.top_search','form[role="search"]','.navbar-form'];
        for (var i = 0; i < selectors.length; i++) {
            var $candidate = $(selectors[i]).first();
            if (!$candidate.length) { continue; }
            var $li = $candidate.closest('li');
            if ($li.length) { return $li; }
            var $form = $candidate.closest('form');
            if ($form.length) { return $form; }
            return $candidate;
        }
        var $nav = $('.navbar-nav.navbar-right, .navbar-right').first();
        if ($nav.length) { return $nav; }
        return $('header .tw-flex, #header .tw-flex, .navbar .tw-flex').last();
    }

    function cloneStar($source, id) {
        if (!$source.length) { return $(); }
        return $source.clone(false, false).attr('id', id).removeClass('open');
    }

    function placeStars() {
        var $source = $('#smart-choice-links-source > ul');
        if (!$source.length) { return; }
        var $left = $('#smart-choice-links-left-live');
        var $right = $('#smart-choice-links-right-live');
        if (!$left.length) { $left = cloneStar($source.find('.smart-choice-links-left').first(), 'smart-choice-links-left-live'); }
        if (!$right.length) { $right = cloneStar($source.find('.smart-choice-links-right').first(), 'smart-choice-links-right-live'); }
        var $anchor = findTopSearchAnchor();
        if (!$anchor.length) { return; }
        if ($anchor.is('ul')) {
            if ($left.length && !$left.parent().is($anchor)) { $anchor.prepend($left); }
            if ($right.length && !$right.parent().is($anchor)) { $anchor.append($right); }
            return;
        }
        var $parent = $anchor.parent();
        if ($parent.is('ul')) {
            if ($left.length && !$left.parent().is($parent)) { $anchor.before($left); }
            if ($right.length && !$right.parent().is($parent)) { $anchor.after($right); }
            return;
        }
        if ($left.length) { $left.addClass('scl-flex-host-item'); if (!$left.parent().is($parent)) { $anchor.before($left); } }
        if ($right.length) { $right.addClass('scl-flex-host-item'); if (!$right.parent().is($parent)) { $anchor.after($right); } }
    }

    function topItems($menu) {
        var $items = $menu.children('li').not('.smart-choice-links-menu-search-wrap,.smart-choice-links-nav');
        if (!$items.length) { $items = $menu.find('> ul > li, > .panel-body > ul > li').not('.smart-choice-links-menu-search-wrap,.smart-choice-links-nav'); }
        return $items;
    }

    function clearSearch($menu) {
        $menu.find('.scl-filtered-out').removeClass('scl-filtered-out').show();
        $menu.find('.scl-search-parent-match').removeClass('scl-search-parent-match');
        $menu.find('.scl-search-force-open').removeClass('scl-search-force-open in show').css('display','');
    }

    function applyMenuSearch($menu, value) {
        var query = normalizeText(value);
        clearSearch($menu);
        if (!query) { return; }
        topItems($menu).each(function () {
            var $top = $(this);
            var match = normalizeText($top.text()).indexOf(query) !== -1;
            if (match) {
                $top.show().addClass('scl-search-parent-match');
                $top.find('li').show();
                $top.find('ul,.collapse,.nav-second-level,.nav-third-level').addClass('scl-search-force-open in show').css('display','block');
            } else {
                $top.addClass('scl-filtered-out').hide();
            }
        });
    }

    function menuCandidates() {
        var found = [];
        ['#side-menu','#setup-menu','.settings-menu','#setup-menu-wrapper ul','.sidebar-menu','.admin-menu'].forEach(function (selector) {
            $(selector).each(function () { if (found.indexOf(this) === -1) { found.push(this); } });
        });
        return $(found);
    }

    function addSearchBox($menu, index) {
        if (!$menu.length || $menu.children('.smart-choice-links-menu-search-wrap').length) { return; }
        var setup = $menu.is('#setup-menu') || $menu.closest('#setup-menu-wrapper').length || $menu.hasClass('settings-menu');
        var label = setup ? (window.smartChoiceLinksSearchSetupLabel || 'Search Setup Menu') : (window.smartChoiceLinksSearchMainLabel || 'Search Main Menu');
        var $wrap = $('<li class="smart-choice-links-menu-search-wrap" role="presentation"></li>');
        var $input = $('<input type="search" class="form-control input-sm smart-choice-links-menu-search" autocomplete="off">').attr('placeholder', label).attr('aria-label', label);
        $wrap.append($input); $menu.prepend($wrap);
        var wait = null;
        $input.on('input change keyup', function () { var el = this; clearTimeout(wait); wait = setTimeout(function () { applyMenuSearch($menu, el.value); }, 90); });
    }

    function initMenuSearch() {
        if (String(window.smartChoiceLinksEnableMenuSearch || '1') === '0') { return; }
        menuCandidates().each(function (i) { addSearchBox($(this), i); });
    }

    function initEvents() {
        $(document).off('.smartChoiceLinks');
        $(document).on('click.smartChoiceLinks', '.smart-choice-links-trigger', function (e) {
            e.preventDefault(); e.stopPropagation();
            var $nav = $(this).closest('.smart-choice-links-nav'), open = $nav.hasClass('open');
            $('.smart-choice-links-nav').removeClass('open'); if (!open) { $nav.addClass('open'); }
            return false;
        });
        $(document).on('click.smartChoiceLinks', '.smart-choice-links-dropdown', function (e) { e.stopPropagation(); });
        $(document).on('click.smartChoiceLinks', function (e) { if (!$(e.target).closest('.smart-choice-links-nav').length) { $('.smart-choice-links-nav').removeClass('open'); } });
        $(document).on('keydown.smartChoiceLinks', function (e) { if (e.key === 'Escape') { $('.smart-choice-links-nav').removeClass('open'); } });
        $(document).on('click.smartChoiceLinks', 'a[data-scl-open-behavior]', function (e) {
            var behavior = $(this).attr('data-scl-open-behavior') || '_self';
            if (behavior === '_self') { return true; }
            e.preventDefault();
            if (behavior === '_blank') { window.open(this.href, '_blank', 'noopener'); return false; }
            window.open(this.href, 'smart_choice_link_window', behavior === '_popup' ? 'width=980,height=720,scrollbars=yes,resizable=yes' : 'width=1280,height=850,scrollbars=yes,resizable=yes');
            return false;
        });
    }

    function initTableTools() {
        var $tbody = $('#smart-choice-links-sortable');
        if (!$tbody.length) { return; }
        $tbody.find('tr').attr('draggable','true');
        var dragged = null;
        $tbody.off('.sclSort').on('dragstart.sclSort','tr[data-id]',function(e){ dragged=this; e.originalEvent.dataTransfer.effectAllowed='move'; })
            .on('dragover.sclSort','tr[data-id]',function(e){ e.preventDefault(); if(!dragged||dragged===this){return;} var r=this.getBoundingClientRect(); this.parentNode.insertBefore(dragged,e.originalEvent.clientY<r.top+r.height/2?this:this.nextSibling); })
            .on('dragend.sclSort','tr[data-id]',function(){ var order=$tbody.find('tr[data-id]').map(function(){return $(this).data('id');}).get(); $.post(admin_url+'smart_choice_links/reorder',{order:JSON.stringify(order)}); dragged=null; });
        $('#smart-choice-links-table-search').off('.sclTable').on('input.sclTable',function(){ var q=normalizeText(this.value); $tbody.find('tr').each(function(){ $(this).toggle(!q||normalizeText($(this).text()).indexOf(q)!==-1); }); });
    }

    function boot() { placeStars(); initMenuSearch(); initEvents(); initTableTools(); }
    function observe() {
        if (!window.MutationObserver || observer) { return; }
        observer = new MutationObserver(function(){ clearTimeout(timer); timer=setTimeout(function(){ placeStars(); initMenuSearch(); },100); });
        observer.observe(document.body,{childList:true,subtree:true});
    }
    $(function(){ boot(); observe(); setTimeout(boot,300); setTimeout(boot,1000); setTimeout(boot,2500); });
})(jQuery);
