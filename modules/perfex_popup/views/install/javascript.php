(function() {
  let get_scroll_percentage = () => {
    let h = document.documentElement;
    let b = document.body;
    let st = "scrollTop";
    let sh = "scrollHeight";
  
    return ((h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight)) * 100;
  };
  <?php $popup->settings = json_decode($popup->settings); ?>
  const options = {
    display_trigger: <?php echo  json_encode($popup->settings->display_trigger); ?>,
    display_trigger_value: <?php echo  json_encode($popup->settings->display_trigger_value); ?>,
  };
  // add iframe
  var iframe = document.createElement("iframe");
  iframe.style.position = "fixed";
  iframe.style.width = "100vw";
  iframe.style.height = "100vh";
  iframe.style.top = 0;
  iframe.style.left = 0;
  iframe.style.border = "none";
  iframe.style.zIndex = "-1";
  iframe.src = "<?php echo admin_url('perfex_popup/install/install_iframe/'). $popup->popup_key; ?>";
  document.body.appendChild(iframe);

  // listen on remove iframe
  function removeIframe(event) {
    if (event?.data !== "tfgremoveiframe"){
      return;
    }
    iframe.parentNode.removeChild(iframe);
  }
  window.addEventListener("message", removeIframe, false);

  // listen on redirect
  function fnRedirect(event) {
    if (event?.data?.name !== "tfgfnredirect" || !event?.data?.target_url){
      return;
    }
    window.location = event.data.target_url;
  }
  window.addEventListener("message", fnRedirect, false);

  // listen on iframe ready
  function iframeReady(event){
    if (event.data == "tfgiframeready"){
      iframe.contentWindow.postMessage({
        name: "tfginitpopup",
        parent_url: window.location.href,
        parent_inner_width: window.innerWidth,
        local_display_frequency: localStorage.getItem(`popup_display_frequency_<?php echo  $popup->id; ?>`),
        session_display_frequency: sessionStorage.getItem(`popup_display_frequency_<?php echo  $popup->id; ?>`),
        session_popup_hover: sessionStorage.getItem(`popup_hover_<?php echo  $popup->id; ?>`),
        local_should_show: localStorage.getItem(`popup_<?php echo  $popup->id; ?>_converted`),
      }, '*');
    }
  }
  window.addEventListener("message", iframeReady, false);

  // listen on set localstorage
  function setLocalstorage(event){
    if (event?.data?.name == "tfgsetlocalstorage"){
      localStorage.setItem(event?.data?.key, event?.data?.value);
    }
  }
  window.addEventListener("message", setLocalstorage, false);

  // listen on set sessionstorage
  function setSessionstorage(event){
    if (event?.data?.name == "tfgsetsessionstorage"){
      sessionStorage.setItem(event?.data?.key, event?.data?.value);
    }
  }
  window.addEventListener("message", setSessionstorage, false);

  // events on display
  function postDisplay(type) {
    console.log("postDisplay: " + type);
    iframe.contentWindow.postMessage({
      name: "tfgdisplaypopup",
    }, '*');
  }
  switch (options.display_trigger) {
    case "delay":
      // setTimeout(() => {
      //   postDisplay(options.display_trigger);
      // }, options.display_trigger_value * 1000);
      break;
    case "exit_intent":
      let exit_intent_triggered = false;
      document.addEventListener("mouseout", (event) => {
        let viewport_width = Math.max(
          document.documentElement.clientWidth,
          window.innerWidth || 0
        );
        if (event.clientX >= viewport_width - 50) return;
        if (event.clientY >= 50) return;
        let from = event.relatedTarget || event.toElement;
        if (!from && !exit_intent_triggered) {
          postDisplay(options.display_trigger);
          exit_intent_triggered = true;
        }
      });
      break;
    case "scroll":
      let scroll_triggered = false;
      document.addEventListener("scroll", (event) => {
        if (
          !scroll_triggered &&
          get_scroll_percentage() > options.display_trigger_value
        ) {
          postDisplay(options.display_trigger);
          scroll_triggered = true;
        }
      });
      break;
  }

  function displayIframe(event){
    if (event?.data == "tfgdisplay"){
      iframe.style.zIndex = "999999999999";
    }
  }
  window.addEventListener("message", displayIframe, false);
})();
