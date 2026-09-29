/* Smart Choice Links v1.2.8 */
(function ($) {
    'use strict';
    var observer = null, timer = null;

    function removeLegacyVisuals() {
        /* Remove duplicate stars, labels, tooltips and portals created by older
         * module builds or cached scripts. Keep only the two v1.2.8 live stars. */
        $('.smart-choice-links-nav').not('#smart-choice-links-source .smart-choice-links-nav')
            .not('#smart-choice-links-left-live,#smart-choice-links-right-live').remove();

        $('.scl-label-portal, #smart-choice-links-label-portal').not(':first').remove();
        $('.scl-panel-portal, #smart-choice-links-panel-portal').not(':first').remove();

        $('.tooltip').filter(function () {
            var text = ($(this).text() || '').toLowerCase();
            return text.indexOf('administration links') !== -1 ||
                   text.indexOf('admin links') !== -1 ||
                   text.indexOf('setup links') !== -1 ||
                   text.indexOf('smart choice quick links') !== -1 ||
                   text.indexOf('smart choice favorites') !== -1;
        }).remove();
    }

    function normalizeText(value) {
        var text = (value || '').toString().toLowerCase();
        try { text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch (e) {}
        return text.replace(/[_\-]+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function findTopSearchAnchor() {
        var selectors = [
            '#top_search_button',
            '#top_search',
            '#global_search',
            '#search_input',
            '#top-search',
            '.top-search-button',
            '.top-search',
            '.top_search',
            '[data-target="#search_modal"]',
            '[data-bs-target="#search_modal"]',
            'input[name="q"]',
            'input[type="search"][placeholder*="Search"]',
            'form[role="search"]',
            '.navbar-form'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var $candidate = $(selectors[i]).filter(':visible').first();
            if (!$candidate.length) { continue; }

            var $li = $candidate.closest('li');
            if ($li.length) { return $li; }

            var $form = $candidate.closest('form');
            if ($form.length) { return $form; }

            var $searchWrap = $candidate.closest('.tw-relative, .tw-flex, .input-group, .search-wrapper, .top-search-wrapper');
            if ($searchWrap.length) { return $searchWrap; }

            return $candidate;
        }

        /* Never fall back to the whole right navigation list. That old fallback
         * placed both stars on the same side whenever the CRM header changed. */
        return $();
    }

    function prepareLiveStar($star, id) {
        if (!$star.length) { return $(); }

        var side = $star.hasClass('smart-choice-links-left') ? 'left' : 'right';
        try { $star.tooltip('destroy'); } catch (e) {}
        try { $star.find('.smart-choice-links-trigger').tooltip('destroy'); } catch (e) {}

        $star.attr('id', id)
            .attr('data-scl-side', side)
            .removeClass('open dropdown scl-force-open')
            .removeAttr('title data-toggle data-placement data-original-title aria-describedby');

        $star.find('.smart-choice-links-trigger')
            .removeAttr('data-toggle data-target data-bs-toggle data-bs-target title data-original-title aria-describedby')
            .attr('aria-haspopup', 'true')
            .attr('aria-expanded', 'false');

        /* The visible list is always rendered in the body portal. The nested
         * fallback remains data-only and may never be opened by Bootstrap. */
        $star.children('ul.smart-choice-links-dropdown')
            .removeClass('dropdown-menu animated fadeIn show in')
            .attr('aria-hidden', 'true')
            .css('display', 'none');

        $star.find('.tooltip').remove();
        removeLegacyVisuals();
        return $star;
    }

    function cloneStar($source, id) {
        if (!$source.length) { return $(); }
        return prepareLiveStar($source.clone(false, false), id);
    }

    function placeStars() {
        removeLegacyVisuals();
        var $source = $('#smart-choice-links-source > ul');
        if (!$source.length) { return; }

        var $left = $('#smart-choice-links-left-live');
        var $right = $('#smart-choice-links-right-live');

        if (!$left.length) {
            $left = cloneStar($source.find('.smart-choice-links-left').first(), 'smart-choice-links-left-live');
        } else {
            $left = prepareLiveStar($left, 'smart-choice-links-left-live');
        }
        if (!$right.length) {
            $right = cloneStar($source.find('.smart-choice-links-right').first(), 'smart-choice-links-right-live');
        } else {
            $right = prepareLiveStar($right, 'smart-choice-links-right-live');
        }

        var $anchor = findTopSearchAnchor();
        if (!$anchor.length) { return; }

        /* Force one star on each side of the actual search control. Detaching
         * first also repairs stars left in the wrong location by older builds. */
        if ($left.length) { $left.detach().removeClass('scl-flex-host-item'); }
        if ($right.length) { $right.detach().removeClass('scl-flex-host-item'); }

        if ($anchor.is('li')) {
            if ($left.length) { $anchor.before($left); }
            if ($right.length) { $anchor.after($right); }
            return;
        }

        var $anchorLi = $anchor.closest('li');
        if ($anchorLi.length) {
            if ($left.length) { $anchorLi.before($left); }
            if ($right.length) { $anchorLi.after($right); }
            return;
        }

        if ($left.length) {
            $left.addClass('scl-flex-host-item');
            $anchor.before($left);
        }
        if ($right.length) {
            $right.addClass('scl-flex-host-item');
            $anchor.after($right);
        }
        removeLegacyVisuals();
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
        var selectors = [
            '#side-menu',
            '#setup-menu',
            '#setup-menu ul',
            '#setup-menu-wrapper ul',
            '.setup-menu-wrapper ul',
            '.settings-menu',
            '.settings-menu ul',
            '.sidebar-menu',
            '.admin-menu',
            '[data-menu="setup"] ul',
            '[data-menu-name="setup"] ul',
            '.setup-menu ul'
        ];

        selectors.forEach(function (selector) {
            $(selector).each(function () {
                var $candidate = $(this);
                if (!$candidate.is('ul')) {
                    var $inner = $candidate.find('ul').first();
                    if ($inner.length) { $candidate = $inner; }
                }
                if ($candidate.length && found.indexOf($candidate[0]) === -1) {
                    found.push($candidate[0]);
                }
            });
        });

        return $(found);
    }

    function addSearchBox($menu, index) {
        if (!$menu.length || $menu.children('.smart-choice-links-menu-search-wrap').length) { return; }
        var setup = $menu.is('#setup-menu') || $menu.closest('#setup-menu,#setup-menu-wrapper,.setup-menu-wrapper,.settings-menu,.setup-menu,[data-menu="setup"],[data-menu-name="setup"]').length || $menu.hasClass('settings-menu');
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

    var activePortalOwner = null;
    var $portalPanel = null;
    var $portalLabel = null;

    function ensurePortalElements() {
        /* Older builds could leave more than one portal in the page. Keep one
         * panel and one label only, so one star click can never show duplicates. */
        removeLegacyVisuals();
        $('#smart-choice-links-panel-portal').slice(1).remove();
        $('.scl-panel-portal').not('#smart-choice-links-panel-portal').remove();
        $('#smart-choice-links-label-portal').slice(1).remove();
        $('.scl-label-portal').not('#smart-choice-links-label-portal').remove();

        $portalPanel = $('#smart-choice-links-panel-portal').first();
        if (!$portalPanel.length) {
            $portalPanel = $('<div id="smart-choice-links-panel-portal" class="scl-panel-portal" aria-hidden="true"></div>').appendTo(document.body);
        }
        $portalLabel = $('#smart-choice-links-label-portal').first();
        if (!$portalLabel.length) {
            $portalLabel = $('<div id="smart-choice-links-label-portal" class="scl-label-portal" role="tooltip" aria-hidden="true"></div>').appendTo(document.body);
        }
    }

    function positionPortal($target, $portal, isPanel) {
        if (!$target || !$target.length || !$portal || !$portal.length) { return; }
        var rect = $target[0].getBoundingClientRect();
        var viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        var width = isPanel ? Math.max(240, parseInt(getComputedStyle(document.documentElement).getPropertyValue('--scl-panel-width'), 10) || 285) : $portal.outerWidth();
        var left = rect.left + (rect.width / 2) - (width / 2);
        left = Math.max(8, Math.min(left, viewportWidth - width - 8));
        $portal.css({
            position: 'fixed',
            top: Math.round(rect.bottom + (isPanel ? 8 : 6)) + 'px',
            left: Math.round(left) + 'px',
            width: isPanel ? width + 'px' : 'auto'
        });
    }

    function hidePortalLabel() {
        ensurePortalElements();
        $portalLabel.removeClass('is-visible').attr('aria-hidden', 'true').empty();
    }

    function showPortalLabel(nav) {
        ensurePortalElements();
        if (!nav || activePortalOwner === nav) { return; }
        var $nav = $(nav);
        var label = $nav.attr('data-scl-label') || $nav.find('.scl-dropdown-header strong').first().text() || 'Quick Links';
        $portalLabel.text(label).addClass('is-visible').attr('aria-hidden', 'false');
        positionPortal($nav.children('.smart-choice-links-trigger').first(), $portalLabel, false);
    }

    function closeAllStarMenus() {
        ensurePortalElements();
        activePortalOwner = null;
        $('.smart-choice-links-nav').removeClass('open scl-force-open scl-label-visible')
            .children('.smart-choice-links-trigger').attr('aria-expanded', 'false');
        $('.smart-choice-links-nav > ul.smart-choice-links-dropdown').attr('aria-hidden','true').css('display','none');
        $portalPanel.removeClass('is-open').attr('aria-hidden', 'true').empty();
        hidePortalLabel();
    }

    function openPortalMenu(nav) {
        ensurePortalElements();
        var $nav = $(nav);
        var side = $nav.attr('data-scl-side') || $nav.children('.smart-choice-links-trigger').attr('data-scl-side') || ($nav.hasClass('smart-choice-links-left') ? 'left' : 'right');
        var $sourcePanel = $('#smart-choice-links-panel-templates > #smart-choice-links-' + side + '-panel-template').first();
        var $trigger = $nav.children('.smart-choice-links-trigger').first();

        /* The server-rendered template is preferred.  The live star's nested
         * list is a safe fallback, so a CRM DOM change can never leave the
         * button with nothing to open. */
        if (!$sourcePanel.length) {
            $sourcePanel = $nav.children('ul.smart-choice-links-dropdown').first();
        }
        if (!$sourcePanel.length || !$trigger.length) {
            console.error('Smart Choice Links: no panel content found for side', side);
            return;
        }

        closeAllStarMenus();
        $('.scl-panel-portal').not($portalPanel).remove();
        activePortalOwner = nav;
        $nav.addClass('scl-force-open');
        $trigger.attr('aria-expanded', 'true');

        var $content = $sourcePanel.clone(false, false)
            .removeAttr('id style')
            .removeClass('smart-choice-links-panel-template smart-choice-links-dropdown smart-choice-links-dropdown-left smart-choice-links-dropdown-right dropdown-menu animated fadeIn show in')
            .addClass('scl-portal-content');
        $portalPanel.empty().append($content).addClass('is-open').attr('aria-hidden', 'false');
        positionPortal($trigger, $portalPanel, true);
    }

    function togglePortalMenu(nav) {
        if (activePortalOwner === nav && $portalPanel && $portalPanel.hasClass('is-open')) {
            closeAllStarMenus();
        } else {
            openPortalMenu(nav);
        }
    }

    function installCaptureClickHandler() {
        if (window.smartChoiceLinksCaptureInstalledV128) { return; }
        window.smartChoiceLinksCaptureInstalledV128 = true;

        function closestElement(target, selector) {
            while (target && target !== document) {
                if (target.matches && target.matches(selector)) { return target; }
                target = target.parentNode;
            }
            return null;
        }

        /* Use mousedown in capture phase.  This runs before Perfex/Bootstrap
         * click handlers and avoids the pointerup/click double-toggle that
         * affected earlier releases. */
        document.addEventListener('mousedown', function (event) {
            var trigger = closestElement(event.target, '.smart-choice-links-trigger');
            if (!trigger) { return; }
            var nav = closestElement(trigger, '.smart-choice-links-nav');
            if (!nav) { return; }

            event.preventDefault();
            event.stopPropagation();
            if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
            hidePortalLabel();
            togglePortalMenu(nav);
        }, true);

        /* Suppress the later Bootstrap click only for the star itself. */
        document.addEventListener('click', function (event) {
            var trigger = closestElement(event.target, '.smart-choice-links-trigger');
            if (trigger) {
                event.preventDefault();
                event.stopPropagation();
                if (event.stopImmediatePropagation) { event.stopImmediatePropagation(); }
                return;
            }

            if (closestElement(event.target, '#smart-choice-links-panel-portal')) {
                return;
            }
            closeAllStarMenus();
        }, true);
    }

    function initEvents() {
        removeLegacyVisuals();
        ensurePortalElements();
        installCaptureClickHandler();
        $(document).off('.smartChoiceLinks');

        $(document).on('keydown.smartChoiceLinks', function (e) {
            if (e.key === 'Escape') { closeAllStarMenus(); }
        });

        $(document).on('mouseenter.smartChoiceLinks focusin.smartChoiceLinks', '.smart-choice-links-nav', function () {
            showPortalLabel(this);
        });
        $(document).on('mouseleave.smartChoiceLinks focusout.smartChoiceLinks', '.smart-choice-links-nav', function () {
            hidePortalLabel();
        });

        $(window).off('.smartChoiceLinksPortal').on('resize.smartChoiceLinksPortal scroll.smartChoiceLinksPortal', function () {
            if (activePortalOwner && $portalPanel && $portalPanel.hasClass('is-open')) {
                positionPortal($(activePortalOwner).children('.smart-choice-links-trigger').first(), $portalPanel, true);
            }
        });

        $(document).on('click.smartChoiceLinks', '#smart-choice-links-panel-portal a[data-scl-open-behavior]', function (e) {
            var behavior = $(this).attr('data-scl-open-behavior') || '_self';
            var href = this.href;
            closeAllStarMenus();
            if (behavior === '_self') { return true; }
            e.preventDefault();
            if (behavior === '_blank') {
                window.open(href, '_blank', 'noopener');
                return false;
            }
            window.open(href, 'smart_choice_link_window', behavior === '_popup' ? 'width=980,height=720,scrollbars=yes,resizable=yes' : 'width=1280,height=850,scrollbars=yes,resizable=yes');
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
