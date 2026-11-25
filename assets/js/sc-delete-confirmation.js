(function (window, document, $) {
  'use strict';

  var config = JSON.parse(document.getElementById('sc-delete-config').textContent);
  var namePattern = new RegExp(config.actionPattern, 'i');
  var operations = ['delete', 'remove', 'destroy', 'purge', 'trash'];
  var header = 'X-CRM-Delete-Confirmation';
  var approved = false;
  var legacyApproved = false;
  var activeDialog = null;
  var waiting = new WeakSet();

  function withApproval(action) {
    var previous = approved;
    approved = true;
    try {
      return action();
    } finally {
      approved = previous;
    }
  }

  function actionUrl(value) {
    if (!value || value.charAt(0) === '#' || /^javascript:/i.test(value)) return null;
    var url;
    try {
      url = new URL(value, window.location.href);
    } catch (_) {
      return null;
    }
    return url.origin === window.location.origin && url.pathname.startsWith(config.basePath) ? url : null;
  }

  function destructiveUrl(url) {
    if (!url) return false;
    return url.pathname.slice(config.basePath.length).split('/').some(function (segment) {
      return namePattern.test(segment.replace(/([a-z0-9])([A-Z])/g, '$1_$2').replace(/-/g, '_'));
    });
  }

  function requestValue(data, name) {
    if (typeof data === 'string') return new URLSearchParams(data).get(name);
    if (data && typeof data.get === 'function') return data.get(name);
    return data && data[name];
  }

  function destructiveData(data) {
    var bulk = requestValue(data, 'mass_delete');
    return (bulk && bulk !== '0') || operations.includes(requestValue(data, 'action'));
  }

  function promptLegacy() {
    if (approved || legacyApproved) return true;
    if (window.prompt(config.instruction, '') !== 'DELETE') return false;

    // Existing synchronous handlers can make their request in the same event.
    legacyApproved = true;
    queueMicrotask(function () { legacyApproved = false; });
    return true;
  }

  function confirmDelete(message) {
    if (activeDialog) return Promise.resolve(false);

    var dialog = document.createElement('dialog');
    if (typeof dialog.showModal !== 'function') return Promise.resolve(promptLegacy());
    dialog.className = 'sc-delete-dialog';
    dialog.style.cssText = 'width:min(92vw,460px);padding:22px;border:1px solid #ddd;border-radius:8px;color:#202832;background:#fff';
    dialog.setAttribute('aria-labelledby', 'sc-delete-title');
    dialog.innerHTML = '<form method="dialog"><h2 id="sc-delete-title" class="tw-mt-0"></h2><p></p>' +
      '<label for="sc-delete-word"></label><input id="sc-delete-word" class="form-control" autocomplete="off" spellcheck="false">' +
      '<div class="tw-flex tw-justify-end tw-gap-2 tw-mt-4">' +
      '<button type="submit" value="cancel" class="btn btn-default"></button>' +
      '<button type="submit" value="delete" class="btn btn-danger" disabled></button></div></form>';

    var input = dialog.querySelector('input');
    var cancel = dialog.querySelector('[value="cancel"]');
    var accept = dialog.querySelector('[value="delete"]');
    dialog.querySelector('h2').textContent = config.title;
    dialog.querySelector('p').textContent = message || '';
    dialog.querySelector('label').textContent = config.instruction;
    cancel.textContent = config.cancel;
    accept.textContent = config.title;
    input.addEventListener('input', function () { accept.disabled = input.value !== 'DELETE'; });
    dialog.querySelector('form').addEventListener('submit', function (event) {
      if (event.submitter === accept && input.value !== 'DELETE') event.preventDefault();
    });
    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' && !accept.disabled) {
        event.preventDefault();
        accept.click();
      }
    });

    var focus = document.activeElement;
    activeDialog = dialog;
    document.body.appendChild(dialog);
    dialog.showModal();
    input.focus();

    return new Promise(function (resolve) {
      dialog.addEventListener('close', function () {
        var confirmed = dialog.returnValue === 'delete' && input.value === 'DELETE';
        dialog.remove();
        activeDialog = null;
        if (focus && focus.isConnected) focus.focus();
        resolve(confirmed);
      }, {once: true});
    });
  }

  function afterConfirmation(control, action) {
    if (waiting.has(control)) return;
    waiting.add(control);
    confirmDelete(control.getAttribute('data-confirm')).then(function (confirmed) {
      if (confirmed && control.isConnected) withApproval(action);
    }).finally(function () { waiting.delete(control); });
  }

  function inputField(form, name, value) {
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  function postLink(url, target) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = url.href;
    form.target = target;
    inputField(form, 'delete_confirmation', 'DELETE');
    if (window.csrfData && window.csrfData.token_name) {
      inputField(form, window.csrfData.token_name, window.csrfData.hash);
    }
    document.body.appendChild(form);
    form.submit();
  }

  window.SCDelete = {confirm: confirmDelete, withApproval: withApproval};

  var nativeConfirm = window.confirm.bind(window);
  window.confirm = function (message) {
    if (approved) return true;
    return /\b(?:delet(?:e|ed|ion|ing)|permanently remove|elimin\w*|borrar)\b/i.test(String(message))
      ? promptLegacy() : nativeConfirm(message);
  };
  window.confirm_delete = function () {
    // Native bulk editors also use this legacy function for non-delete updates.
    var editor = document.activeElement && document.activeElement.closest('.bulk_actions');
    var deleteOption = editor && editor.querySelector('[name="mass_delete"]');
    if (deleteOption && !deleteOption.checked) {
      return nativeConfirm(window.app?.lang?.confirm_action_prompt || 'Confirm action?');
    }
    return promptLegacy();
  };

  document.addEventListener('click', function (event) {
    if (approved || event.defaultPrevented || event.button !== 0) return;
    var control = event.target.closest('a,button,input[type="submit"]');
    if (!control) return;
    var url = actionUrl(control.getAttribute('href'));
    if (!destructiveUrl(url) && !control.matches('._delete,.delete-reminder,.task-single-delete-timesheet,[data-confirm-delete]')) return;
    // Form submits retain their native submitter and validation.
    if (control.form && control.type === 'submit') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    afterConfirmation(control, function () { control.click(); });
  }, true);

  window.addEventListener('click', function (event) {
    if ((!approved && !legacyApproved) || event.defaultPrevented) return;
    var link = event.target.closest('a[href]');
    var url = link && actionUrl(link.getAttribute('href'));
    if (!destructiveUrl(url)) return;
    event.preventDefault();
    postLink(url, link.target);
  });

  document.addEventListener('submit', function (event) {
    var form = event.target;
    var submitter = event.submitter;
    var url = actionUrl(submitter?.getAttribute('formaction') || form.action);
    if (!url) return;
    var data = new FormData(form);
    var named = destructiveUrl(url)
      || (submitter && (submitter.matches('._delete,[data-confirm-delete]') || (submitter.name === 'action' && operations.includes(submitter.value))))
      || destructiveData(data);
    if (!named || data.get('delete_confirmation') === 'DELETE') return;
    if (approved || legacyApproved) {
      inputField(form, 'delete_confirmation', 'DELETE');
      // Some submit handlers prevent navigation and keep using the same form.
      setTimeout(function () { form.querySelector('input[type="hidden"][name="delete_confirmation"]')?.remove(); }, 0);
      return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    afterConfirmation(form, function () { form.requestSubmit(submitter || undefined); });
  }, true);

  if ($ && $.ajaxPrefilter) {
    $.ajaxPrefilter(function (options, original, request) {
      var url = actionUrl(options.url);
      if (!url || (!destructiveUrl(url) && !destructiveData(options.data) && String(options.type).toUpperCase() !== 'DELETE')) return;
      if (requestValue(options.data, 'delete_confirmation') === 'DELETE') return;
      if (!promptLegacy()) {
        request.abort('cancelled');
        return;
      }
      request.setRequestHeader(header, 'DELETE');
    });
  }

  if (window.fetch) {
    var nativeFetch = window.fetch.bind(window);
    window.fetch = function (request, options) {
      var url = actionUrl(typeof request === 'string' ? request : request.url || request.href);
      var method = options?.method || request.method || 'GET';
      if (!url || (!destructiveUrl(url) && !destructiveData(options?.body) && method.toUpperCase() !== 'DELETE')) {
        return nativeFetch(request, options);
      }
      if (requestValue(options?.body, 'delete_confirmation') === 'DELETE') return nativeFetch(request, options);
      if (!promptLegacy()) return Promise.reject(new DOMException(config.cancel, 'AbortError'));

      var headers = new Headers(options?.headers || request.headers);
      headers.set(header, 'DELETE');
      return nativeFetch(request, Object.assign({}, options, {headers: headers}));
    };
  }
})(window, document, window.jQuery);
