<div id="profile">
     <div class="profile-bar">
          <div class="profile-bar-left">
               <div class="status-dropdown" id="status-dropdown-group">
                    <button type="button" class="profile-avatar-trigger" id="status-dropdown-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_status'); ?>" aria-haspopup="true" aria-expanded="false">
                         <span class="profile-bar-avatar">
                              <?php echo staff_profile_image($params['props']->staffid, ['img', 'img-responsive', 'profile-avatar'], 'small', ['id' => 'profile-img']); ?>
                         </span>
                    </button>
                    <ul class="dropdown-menu" id="status-dropdown-menu" role="menu">
                         <li data-status="online" class="status-option active" role="none">
                              <a href="#" role="menuitem"><span class="status-dot online"></span> <?= _l('chat_status_online'); ?></a>
                         </li>
                         <li data-status="away" class="status-option" role="none">
                              <a href="#" role="menuitem"><span class="status-dot away"></span> <?= _l('chat_status_away'); ?></a>
                         </li>
                         <li data-status="busy" class="status-option" role="none">
                              <a href="#" role="menuitem"><span class="status-dot busy"></span> <?= _l('chat_status_busy'); ?></a>
                         </li>
                         <li data-status="offline" class="status-option" role="none">
                              <a href="#" role="menuitem"><span class="status-dot offline"></span> <?= _l('chat_status_offline'); ?></a>
                         </li>
                    </ul>
               </div>
               <div class="profile-bar-info">
                    <span class="profile-bar-name"><?= get_staff_full_name(); ?></span>
               </div>
          </div>

          <div class="profile-bar-actions">
               <button type="button" class="btn-icon" id="chat-header-search-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('search'); ?>" aria-label="<?= _l('search'); ?>">
                    <i class="fa fa-search" aria-hidden="true"></i>
               </button>

               <button type="button" class="btn-icon prchat-theme-toggle" id="chat-theme-toggle-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_theme_options_dark'); ?>" aria-label="<?= _l('chat_dark_mode'); ?>">
                    <i class="fa fa-moon" id="chat-theme-toggle-icon" aria-hidden="true"></i>
               </button>

               <div class="btn-group" id="chat-add-dropdown-group">
                    <button type="button" class="btn-icon" id="chat-add-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_actions_menu'); ?>" aria-label="<?= _l('chat_actions_menu'); ?>">
                         <i class="fa fa-list-ul"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" id="chat-add-dropdown-menu">
                         <li class="add-tab-action add-tab-groups" style="display:none;">
                              <a href="#" id="chat-add-action-groups"><i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('chat_group_modal_title'); ?></a>
                         </li>
                         <?php if (is_admin()) : ?>
                              <li class="dropdown-header add-menu-admin-h-staff"><?= _l('chat_actions_menu'); ?></li>
                              <li class="add-menu-admin-staff">
                                   <a href="#" id="broadcastBtn"><i class="fa fa-bullhorn" style="color: #ec4899;"></i> <?= _l('chat_message_announcement_text'); ?></a>
                              </li>
                              <li class="add-menu-admin-staff">
                                   <a href="#" id="mentionsBtn"><i class="fa fa-at" style="color: #8b5cf6;"></i> <?= _l('chat_quick_mention_title'); ?></a>
                              </li>
                              <li class="dropdown-header add-menu-admin-h-client" style="display:none;"><?= _l('chat_actions_menu'); ?></li>
                              <li class="add-menu-admin-client" style="display:none;">
                                   <a href="#" id="clientBroadcastBtn"><i class="fa fa-bullhorn" style="color: #ec4899;"></i> <?= _l('chat_message_announcement_text'); ?></a>
                              </li>
                              <li class="add-menu-admin-client" style="display:none;">
                                   <a href="#" id="clientMentionsBtn"><i class="fa fa-at" style="color: #8b5cf6;"></i> <?= _l('chat_quick_mention_title'); ?></a>
                              </li>
                         <?php endif; ?>
                    </ul>
               </div>

               <div class="btn-group" id="options-dropdown-group">
                    <button type="button" class="btn-icon prchat-filters-btn" id="options-dropdown-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('filter_by'); ?>" aria-label="<?= _l('filter_by'); ?>">
                         <i class="fa fa-filter" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right chat-options-menu" id="options-dropdown-menu">
                         <li class="dropdown-header filter-header staff-filter"><?= _l('filter_by'); ?></li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="all">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="online">
                                   <i class="fa fa-circle" style="color: #22c55e;"></i> <?= _l('chat_online_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="offline">
                                   <i class="fa fa-circle" style="color: #9ca3af;"></i> <?= _l('chat_offline_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="unread">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>

                         <li class="dropdown-header filter-header client-filter" style="display:none;"><?= _l('filter_by'); ?></li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showAllClientsBtn">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showOnlineContactsBtn">
                                   <i class="fa fa-circle" style="color: #22c55e;"></i> <?= _l('chat_only_online_clients'); ?>
                              </a>
                         </li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showUnreadClientsBtn">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>

                         <li class="dropdown-header filter-header groups-filter" style="display:none;"><?= _l('filter_by'); ?></li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="all">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="unread">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="my_groups">
                                   <i class="fa fa-user" style="color: #22c55e;"></i> <?= _l('chat_my_groups'); ?>
                              </a>
                         </li>
                         <li class="divider groups-filter" style="display:none;"></li>
                         <li class="dropdown-header groups-filter" style="display:none;"><?= _l('chat_filter_by_association'); ?></li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="project">
                                   <i class="fa-solid fa-briefcase" style="color: #3b82f6;"></i> <?= _l('chat_project'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="ticket">
                                   <i class="fa-solid fa-life-ring" style="color: #ef4444;"></i> <?= _l('chat_ticket'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="invoice">
                                   <i class="fa-solid fa-file-invoice-dollar" style="color: #10b981;"></i> <?= _l('chat_invoice'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="task">
                                   <i class="fa-solid fa-list-check" style="color: #8b5cf6;"></i> <?= _l('chat_task'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="contract">
                                   <i class="fa-solid fa-file-signature" style="color: #f97316;"></i> <?= _l('chat_contract'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="lead">
                                   <i class="fa-solid fa-user-tie" style="color: #ec4899;"></i> <?= _l('chat_lead'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="estimate">
                                   <i class="fa-solid fa-file-invoice" style="color: #06b6d4;"></i> <?= _l('chat_estimate'); ?>
                              </a>
                         </li>
                    </ul>
               </div>
          </div>
     </div>
</div>

<script>
     document.addEventListener('DOMContentLoaded', function() {
          var initProfileDropdowns = function() {
               if (typeof jQuery === 'undefined') {
                    setTimeout(initProfileDropdowns, 100);
                    return;
               }

               var $ = jQuery;

               function closeAllDropdowns() {
                    $('#options-dropdown-group, #status-dropdown-group, #chat-add-dropdown-group').removeClass('open');
                    $('#status-dropdown-btn').attr('aria-expanded', 'false');
               }

               $(document).on('click', '#options-dropdown-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $('#options-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#options-dropdown-group').addClass('open');
                    }
               });

               $(document).on('click', '#status-dropdown-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $('#status-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#status-dropdown-group').addClass('open');
                         $('#status-dropdown-btn').attr('aria-expanded', 'true');
                    }
               });

               $(document).on('click', function(e) {
                    if (!$(e.target).closest('#profile .btn-group, #profile .status-dropdown').length) {
                         closeAllDropdowns();
                    }
               });

               $(document).on('click', '#chat-header-search-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeAllDropdowns();
                    var $searchWrap = $('#frame #sidepanel #search');
                    if (!$searchWrap.length) {
                         $searchWrap = $('#search');
                    }
                    var $f = $searchWrap.find('input[type="text"]').first();
                    if ($f.length) {
                         $f.focus();
                         try {
                              $f[0].select();
                         } catch (err) {}
                         $searchWrap.addClass('prchat-search-highlight');
                         setTimeout(function() {
                              $searchWrap.removeClass('prchat-search-highlight');
                         }, 1200);
                    }
               });

               $(document).on('click', '#chat-add-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var visibleLinks = $('#chat-add-dropdown-menu > li').filter(function() {
                         return $(this).css('display') !== 'none' && $(this).find('a').length;
                    }).length;
                    if (!visibleLinks) {
                         closeAllDropdowns();
                         return;
                    }
                    var isOpen = $('#chat-add-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#chat-add-dropdown-group').addClass('open');
                    }
               });

               $(document).on('click', '#chat-add-action-groups', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.chatGroups) {
                         $(".modal_container").load(prchatSettings.chatGroups, function() {
                              $("#chat_groups_custom_modal").modal('show');
                         });
                    } else {
                         $('.add_group_btn, #add_group_btn').trigger('click');
                    }
               });

               function updateChatThemeToggleUi(theme) {
                    var $icon = $('#chat-theme-toggle-icon');
                    var $btn = $('#chat-theme-toggle-btn');
                    if (!$icon.length || !$btn.length) {
                         return;
                    }
                    if (theme === 'dark') {
                         $icon.removeClass('fa-moon').addClass('fa-sun');
                         $btn.attr('title', "<?= addslashes(_l('chat_theme_options_light')); ?>");
                    } else {
                         $icon.removeClass('fa-sun').addClass('fa-moon');
                         $btn.attr('title', "<?= addslashes(_l('chat_theme_options_dark')); ?>");
                    }
                    if ($btn.data('bs.tooltip')) {
                         $btn.tooltip('fixTitle');
                    }
               }

               $(document).on('click', '#chat-theme-toggle-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeAllDropdowns();
                    var currentTheme = localStorage.getItem("prChatThemeMode") || "light";
                    var newTheme = currentTheme === "dark" ? "light" : "dark";
                    $("body").removeClass("chat_dark chat_light").addClass("chat_" + newTheme);
                    localStorage.setItem("prChatThemeMode", newTheme);
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.switchTheme) {
                         $.post(prchatSettings.switchTheme, {
                              theme_name: newTheme
                         });
                    }
                    updateChatThemeToggleUi(newTheme);
               });

               $(document).on('click', '#status-dropdown-menu .status-option a', function(e) {
                    e.preventDefault();
                    var $li = $(this).parent();
                    var status = $li.data('status');

                    $('#status-dropdown-menu .status-option').removeClass('active');
                    $li.addClass('active');
                    $('#profile-img').removeClass('online away busy offline').addClass(status);

                    if (typeof user_chat_status !== 'undefined') {
                         user_chat_status = status;
                    }

                    if (typeof prchatSettings !== 'undefined' && prchatSettings.handleChatStatus) {
                         $.post(prchatSettings.handleChatStatus, {
                              status: status
                         });
                    }

                    closeAllDropdowns();
               });

               function hasUnreadMessages(el) {
                    var $el = $(el);
                    var unreadBadge = $el.find('span.unread-notifications, span.badge, .unread-count');
                    if (unreadBadge.length === 0) return false;
                    var dataBadge = unreadBadge.attr('data-badge');
                    if (dataBadge && parseInt(dataBadge) > 0) return true;
                    var badgeText = unreadBadge.text().trim();
                    if (badgeText && parseInt(badgeText) > 0) return true;
                    return unreadBadge.is(':visible') && unreadBadge.text().trim() !== '';
               }

               $(document).on('click', '#options-dropdown-menu .filter-option.staff-filter a', function(e) {
                    e.preventDefault();
                    var filterName = $(this).data('filter');
                    var staffList = $("#staff .chat_contacts_list, #contacts .chat_contacts_list");

                    staffList.find("li").show();
                    staffList.find("li a").css('display', '');

                    if (filterName === 'all') {
                    } else if (filterName === 'online') {
                         staffList.find("li").each(function() {
                              var link = $(this).find('a');
                              var img = $(this).find('img.imgFriend');
                              var isOnline = link.hasClass('on') || img.hasClass('online') || img.hasClass('away') || img.hasClass('busy');
                              $(this).toggle(isOnline);
                         });
                    } else if (filterName === 'offline') {
                         staffList.find("li").each(function() {
                              var link = $(this).find('a');
                              var img = $(this).find('img.imgFriend');
                              var isOffline = link.hasClass('off') || img.hasClass('offline');
                              $(this).toggle(isOffline);
                         });
                    } else if (filterName === 'unread') {
                         staffList.find("li").each(function() {
                              $(this).toggle(hasUnreadMessages(this));
                         });
                    }
                    closeAllDropdowns();
               });

               $(document).on('click', '#showAllClientsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").show();
                    closeAllDropdowns();
               });

               $(document).on('click', '#showOnlineContactsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").each(function() {
                         var link = $(this).find('a, .contact_name');
                         var img = $(this).find('img');
                         var isOnline = link.hasClass('on') || img.hasClass('online');
                         $(this).toggle(isOnline);
                    });
                    closeAllDropdowns();
               });

               $(document).on('click', '#showUnreadClientsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").each(function() {
                         $(this).toggle(hasUnreadMessages(this));
                    });
                    closeAllDropdowns();
               });

               $(document).on('click', '#options-dropdown-menu [data-group-filter]', function(e) {
                    e.preventDefault();
                    var filter = $(this).data('group-filter');
                    var groupList = $("#groups .chat_groups_list");
                    var userId = typeof userSessionId !== 'undefined' ? userSessionId : '';

                    groupList.find("li.group_selector").each(function() {
                         var $li = $(this);
                         var show = true;

                         if (filter === 'unread') {
                              show = $li.find('.group-unread-badge').length > 0;
                         } else if (filter === 'my_groups') {
                              show = $li.attr('data-created-by') == userId;
                         } else if (filter !== 'all') {
                              show = $li.attr('data-related-type') === filter;
                         }

                         $li.toggle(show);
                    });
                    closeAllDropdowns();
               });

               $(document).on('click', '#broadcastBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    $("#chooseAnnouncement").modal('show');
               });

               $(document).on('click', '#mentionsBtn, #clientMentionsBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.quickMentions) {
                         $(".modal_container").load(prchatSettings.quickMentions, function() {
                              $("#quickMentionsModal").modal('show');
                         });
                    }
               });

               $(document).on('click', '#clientBroadcastBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    $("#chooseAnnouncement").modal('show');
               });

               function updateAddMenuForTab(activeTab) {
                    $('.add-tab-action').hide();
                    $('.add-menu-admin-divider, .add-menu-admin-h-staff, .add-menu-admin-staff, .add-menu-admin-h-client, .add-menu-admin-client').hide();
                    if (activeTab === '#staff') {
                         if ($('.add-menu-admin-staff').length) {
                              $('.add-menu-admin-divider').show();
                              $('.add-menu-admin-h-staff').show();
                              $('.add-menu-admin-staff').show();
                         }
                    } else if (activeTab === '#groups') {
                         $('.add-tab-groups').show();
                         if ($('.add-menu-admin-staff').length) {
                              $('.add-menu-admin-divider').show();
                              $('.add-menu-admin-h-staff').show();
                              $('.add-menu-admin-staff').show();
                         }
                    } else if (activeTab === '#crm_clients') {
                         if ($('.add-menu-admin-client').length) {
                              $('.add-menu-admin-divider').show();
                              $('.add-menu-admin-h-client').show();
                              $('.add-menu-admin-client').show();
                         }
                    }
                    var n = $('#chat-add-dropdown-menu > li').filter(function() {
                         return $(this).css('display') !== 'none' && $(this).find('a').length;
                    }).length;
                    $('#chat-add-dropdown-group').toggleClass('prchat-add-menu-empty', n === 0);
               }

               function updateFiltersForTab(activeTab) {
                    $('.staff-filter, .client-filter, .groups-filter').hide();

                    if (activeTab === '#crm_clients') {
                         $('.client-filter').show();
                    } else if (activeTab === '#staff') {
                         $('.staff-filter').show();
                    } else if (activeTab === '#groups') {
                         $('.groups-filter').show();
                    }
                    updateAddMenuForTab(activeTab);
               }

               $(document).on('shown.bs.tab', '.nav-tabs.chat_nav a', function(e) {
                    updateFiltersForTab($(e.target).attr('href'));
                    if (typeof updateOnlineCounter === 'function') {
                         updateOnlineCounter();
                    }
               });

               $(document).on('click', '.nav-tabs.chat_nav a', function() {
                    setTimeout(function() {
                         var activeTab = $('.nav-tabs.chat_nav li.active a').attr('href') || '#staff';
                         updateFiltersForTab(activeTab);
                    }, 100);
               });

               var savedTheme = localStorage.getItem("prChatThemeMode") || "light";
               updateChatThemeToggleUi(savedTheme);

               var activeTab = $('.nav-tabs.chat_nav li.active a').attr('href') || '#staff';
               updateFiltersForTab(activeTab);

               var userStatus = '<?= get_user_chat_status() ?>' || 'online';
               var $statusOption = $('#status-dropdown-menu .status-option[data-status="' + userStatus + '"]');
               if ($statusOption.length) {
                    $('#status-dropdown-menu .status-option').removeClass('active');
                    $statusOption.addClass('active');
                    $('#profile-img').addClass(userStatus);
               }
          };

          initProfileDropdowns();
     });
</script>
