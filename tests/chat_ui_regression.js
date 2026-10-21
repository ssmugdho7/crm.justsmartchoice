'use strict';
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const jquery = require('jquery');
const moment = require('moment');
const view = fs.readFileSync('modules/prchat/views/chat_full_view.php', 'utf8');
const client = fs.readFileSync('modules/prchat/assets/module_includes/crm_clients.php', 'utf8');
const groups = fs.readFileSync('modules/prchat/assets/module_includes/groups.php', 'utf8');
const stripPhp = source => source.replace(/<\?(?:php|=)[\s\S]*?\?>/g, '');
const between = (source, start, end) => source.slice(source.indexOf(start), source.indexOf(end, source.indexOf(start)));
const tick = () => new Promise(resolve => setTimeout(resolve, 15));
let checks = 0;
function check(ok, label) { assert.ok(ok, label); checks++; }
function harness() {
  const dom = new JSDOM(`<div id="frame"><div id="contacts"><ul>
    <li class="contact" id="2"><a href="#2" id="2"><img><div class="meta"><p>Fixture Two</p></div></a></li>
    <li class="contact" id="3"><a href="#3" id="3"><img><div class="meta"><p>Fixture Three</p></div></a></li></ul></div>
    <div class="content"><div class="chat_group_options"></div><div class="contact-profile"><div class="profile-wrapper"><img><p class="staff-name-dropdown"></p></div></div>
    <div class="messages"><div class="messages-skeleton"></div><ul></ul></div>
    <div class="client_messages"><div class="messages-skeleton"></div><div class="chat_client_messages"><ul></ul></div></div>
    <form id="pusherMessagesForm"><input class="to"><input class="from"></form>
    <ul class="chat_clients_list"><li class="contact_name selected" id="33"></li></ul></div></div>`, {runScripts:'outside-only', url:'https://fixture.test/chat'});
  const w = dom.window, $ = jquery(w), requests = [];
  w.$ = w.jQuery = $; w.moment = moment; w.userSessionId = '7'; w.site_url = '/'; w.chat_user_statuses = {};
  w.prchatSettings = {getMessages:'/messages',getMutualMessages:'/client-messages',timeFormat:'h:mm A'};
  w.scrollToBottom = w.getSharedFiles = () => {};
  w.generateMessageSkeleton = selector => $(selector).removeClass('hidden');
  w.formatDateSeparatorLabel = time => moment(time).format('YYYY-MM-DD');
  w.createModernMessage = data => `<div class="modern-message-sent modern-message-sending" data-message-id="${data.id}"><i class="modern-status-sending fa-clock"></i>${data.message}</div>`;
  w.PrchatSafeRenderer = {displayForModernMessageRow:value=>value};
  w.matchMedia = () => ({matches:false}); $.fn.hasScrollBar = () => false; $.fn.tooltip = function(){return this;};
  $.get = (url, data) => {const d = $.Deferred(); d.abort = () => d.reject({},'abort'); requests.push({url,data,d}); return d;};
  return {dom,w,$,requests};
}
(async () => {
  const h = harness(), {w,$,requests} = h;
  w.localStorage.setItem('prChatThemeMode','dark');
  w.eval(between(view,'<script>','</script>').replace('<script>',''));
  check(w.document.body.classList.contains('staff-tab') && w.document.body.classList.contains('chat_dark'), 'Theme and tab layout apply before shell markup');
  w.eval(stripPhp(between(view,'    var createChatBoxRequest = null;','    /*--------------------  * send message')));
  w.createChatBox($('#contacts a[id="2"]'));
  requests[0].d.resolve(JSON.stringify([{id:11,sender_id:2,message:'Earlier',time_sent:'2026-10-01T12:00:00-04:00'}]));
  w.createChatBox($('#contacts a[id="3"]'));
  requests[1].d.resolve('[]'); await tick();
  check($('.messages').attr('id') === 'id_3' && $('.messages [data-message-id]').length === 0, 'Old response cannot overwrite a newer empty conversation');
  check($('.messages-skeleton').first().hasClass('hidden'), 'Empty conversation releases skeleton');
  w.createChatBox($('#contacts a[id="2"]')); requests[2].d.resolve(JSON.stringify([{id:12,sender_id:2,message:'Incoming',time_sent:'2026-10-02T12:00:00-04:00'}])); await tick();
  check($('.messages [data-message-id="12"]').length === 1 && $('.messages .modern-date-separator').length === 1, 'Incoming-only days receive a date separator');
  w.createChatBox($('#contacts a[id="3"]')); requests[3].d.reject({},'error'); await tick();
  check($('.messages [role="alert"]').length === 1 && $('.messages-skeleton').first().hasClass('hidden'), 'Request failure displays an error instead of an endless or empty loading state');
  w.eval(stripPhp(between(client,'    var createChatBoxClientRequest = null;','    /*---------------* Check for messages history')));
  w.getClientMessages(7,33,$('.contact_name')); w.getClientMessages(7,34,$('.contact_name'));
  check(requests.length === 6 && requests[4].d.state() === 'rejected' && requests[5].data.sender_id === 'client_34', 'Rapid client switching starts the new request after aborting the previous one');
  requests[5].d.resolve([]); await tick();
  check($('.client_messages .messages-skeleton').hasClass('hidden'), 'Empty client conversation completes');
  let receipt;
  w.user_messages_events = {bind:(event,fn)=>{receipt=fn;}}; w.formatSeenTooltip = raw => 'Seen '+raw;
  w.eval(between(view,'    user_messages_events.bind("message_seen"','    // Create new task'));
  $('.messages').attr('id','id_2').html('<div class="modern-message-sent" data-message-id="101"><i class="modern-status-delivered fa-check"></i></div><div class="modern-message-sent" data-message-id="102"><i class="modern-status-delivered fa-check"></i></div>');
  $('#contacts a[id="2"]').addClass('active_chat');
  receipt([{msg_id:101,sender_id:7,reciever_id:2,viewed_at:'2026-10-02T12:00:00-04:00'}]);
  check($('.messages [data-message-id="101"] .modern-status-read').length === 1 && $('.messages [data-message-id="102"] .modern-status-delivered').length === 1, 'Numeric staff receipt marks only the acknowledged message');
  receipt([{msg_id:102,sender_id:7,reciever_id:3}]);
  check($('.messages [data-message-id="102"] .modern-status-delivered').length === 1, 'Other conversation receipt cannot alter the active conversation');
  $('.client_messages').html('<div class="modern-message-sent" data-message-id="101"><i class="modern-status-delivered"></i></div><div class="modern-message-sent" data-message-id="102"><i class="modern-status-delivered"></i></div>');
  receipt([{msg_id:102,sender_id:'staff_7',reciever_id:'client_33',viewed_at:'2026-10-02T12:01:00-04:00'}]);
  check($('.client_messages [data-message-id="102"] .modern-status-read').length === 1 && $('.client_messages [data-message-id="101"] .modern-status-delivered').length === 1, 'Client receipts are isolated by message ID and conversation');
  // Exercise the actual staff composer success/error handling with controlled responses.
  $('.messages').html('<ul><div class="modern-messages-container"></div></ul>');
  $('#pusherMessagesForm').append('<textarea class="chatbox" name="msg"></textarea>');
  $('#contacts li#2').addClass('active');
  w.internetConnectionCheck=()=>true; w.appendMemberToTop=()=>{};w.sendTypingEvent=()=>{};
  w.typingTimer=null;w.isTyping=false;w.TYPING_TIMER_LENGTH=1500;
  w.PrchatSafeRenderer.renderFromText=value=>({display:value,sidebar:value});
  w.prchatSettings.serverPath='/send';
  const sends=[];$.post=(url,data)=>{const d=$.Deferred();sends.push(d);return d;};
  w.eval(stripPhp(between(view,'    $("#frame").on("keypress", "textarea.chatbox"','    // Also handle keyup')));
  for(const response of ['<html>Login</html>',{success:false,error:'Failed'},{success:true,id:201}]) {
    $('.chatbox').val('Fixture message').trigger($.Event('keypress',{which:13}));
    sends.at(-1).resolve(response);
    const row=$('.messages .modern-message-sent').last();
    check(response.id ? row.attr('data-message-id')==='201'&&row.find('.modern-status-delivered').length===1 : row.hasClass('modern-message-failed'), 'Composer accepts only a persisted message ID, and visibly fails invalid/rejected responses');
  }
  // Presence is resolved against contacts the staff session already loaded.
  let added=[];w.myCustomers={customers:[{contact_id:33}]};w.addClientMember=member=>added.push(member.id);
  let onJoin;w.clientsChannel={bind:(event,fn)=>{onJoin=fn;}};
  w.eval(between(client,'    clientsChannel.bind("pusher:member_added"','    /*---------------* New staff member activity'));
  onJoin({id:'client_33',info:{contact_id:33}});onJoin({id:'client_34',info:{contact_id:34}});
  check(added.join(',')==='client_33','Presence updates only an authorized contact, without requiring broadcast customer details');
  const helper=fs.readFileSync('modules/prchat/assets/module_includes/mutual_and_helper_functions.php','utf8');
  const uploadHelper=between(helper,'  function prchatAppendUploadCsrf','  /**\n   * Main Settings').replace(/<\?=([\s\S]*?)\?>/g,(_,expression)=>JSON.stringify(expression.includes('get_csrf_token_name')?'fixture_csrf':'fallback'));
  w.eval(uploadHelper);$('body').append('<input type="hidden" name="fixture_csrf" value="current-token">');
  const multipart=new w.FormData();w.prchatAppendUploadCsrf(multipart);
  check(multipart.get('fixture_csrf')==='current-token'&&!multipart.has('csrf_token_name'),'Multipart uploads use the configured CSRF name and current value');
  // Loading group subscriptions in the background must never replace the Staff UI.
  $('#frame').prepend('<div id="sidepanel"><ul class="chat_nav"><li class="staff active"></li><li class="groups"></li></ul></div><ul class="chat_groups_list"></ul>');
  $('#frame .content').append('<div class="group_messages" style="display:none"></div><form name="groupMessagesForm" style="display:none"></form>');
  const groupRequests=[];let selectedGroups=0,emptyGroups=0;
  w.appendCurrentGroups=data=>$('.chat_groups_list').append($('<li class="group_selector"><a></a></li>').attr('id',data.id).on('click',()=>selectedGroups++));
  w.renderChatGroupMessages=()=>{};w.loadGroupPreviews=()=>{};w.showEmptyGroupsMessage=()=>emptyGroups++;
  $.ajax=()=>{const d=$.Deferred();groupRequests.push(d);return d;};
  w.eval(stripPhp(between(groups,'    function fetchMyGroups()','    /*---------------* Click event for adding new member')));
  groupRequests[0].resolve({groups:[{id:10}]});await tick();
  check(selectedGroups===0&&$('.group_messages')[0].style.display==='none'&&$('form[name="groupMessagesForm"]')[0].style.display==='none','Background group loading cannot select a group or show a second conversation/composer');
  w._groupsLoaded=false;w.fetchMyGroups();groupRequests[1].resolve({noChannels:true});await tick();
  check(emptyGroups===0,'Background empty-group result cannot replace the staff conversation');
  // Bootstrap updates nav classes after the direct tab handler selects the first group.
  $('body').addClass('groups-tab');$('.chat_nav li.staff').addClass('active');$('.chat_nav li.groups').removeClass('active');
  $('.group_messages').append('<div class="chat_group_messages"></div>');
  w.chat_group_messages=$('.chat_group_messages');w.chat_social_media=$();w.chat_contact_profile_img=$();
  let groupLoads=0;w.getGroupMessages=()=>groupLoads++;
  w.eval(stripPhp(between(groups,'  function renderChatGroupMessages(data)','  /*---------------* Main function for fetching messages')));
  w.renderChatGroupMessages($('.group_selector').first()[0]);
  check(groupLoads===1,'First group selection works before Bootstrap updates the nav active class');
  h.dom.window.close();
  // Parse all inline scripts after substituting PHP expression values; runtime behavior is checked above and in the real browser.
  for (const file of ['modules/prchat/views/chat_full_view.php','modules/prchat/views/includes/groups_modal.php','modules/prchat/assets/module_includes/crm_clients.php','modules/prchat/assets/module_includes/groups.php','modules/prchat/assets/module_includes/chat_drag_drop.php','modules/prchat/assets/module_includes/mutual_and_helper_functions.php','modules/prchat/views/chat_clients_view.php']) {
    let n=0;
    const rendered=fs.readFileSync(file,'utf8').replace(/<\?=([\s\S]*?)\?>/g,'null').replace(/<\?php\s+echo[\s\S]*?\?>/g,'null').replace(/<\?php[\s\S]*?\?>/g,'');
    for (const match of rendered.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)) {
      const code=match[1];
      new vm.Script(code,{filename:file+':'+(++n)});
    }
  }
  console.log(`PASS ${checks} chat layout, empty/error, switching, date separator and receipt checks; inline scripts parse`);
})().catch(error=>{console.error(error);process.exitCode=1;});
