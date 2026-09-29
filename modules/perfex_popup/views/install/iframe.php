<head>
  <link href="https://fonts.googleapis.com/css?family=<?php echo implode("|",PERFEX_POPUP_CONFIG['google_fonts']); ?>&display=swap" type="text/css" rel="stylesheet" media="screen,print" />
  <link href="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/install/css/pixel.css'); ?>" type="text/css" rel="stylesheet" media="screen,print" />
  <link href="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/install/css/general.css'); ?>" type="text/css" rel="stylesheet" media="screen,print" />
 <style>
    #tfg-popup-wrapper{
        background-color: #ffff;
        font-family: sans-serif;
    }
 </style>
<style id="popup-style">
    <?php echo $popup->css; ?>
</style>
 
 <style>
#tfg-popup-wrapper{
    width: <?php echo $popup->width; ?>px;
    height: <?php echo $popup->height; ?>px;
    max-width: 100%;
    max-height: 100%;
}
@media (max-width: 479px) {
    #tfg-popup-wrapper{
        height: auto;
    }
}
* {
    box-sizing: border-box;
}
</style>
  <script>
    
    let PARENT_URL = undefined;
    let PARENT_INNER_WIDTH = undefined;
    let LOCAL_DISPLAY_FREQUENCE = undefined;
    let SESSION_DISPLAY_FREQUENCE = undefined;
    let SESSION_POPUP_HOVER = undefined;
    let LOCAL_SHOULD_SHOW = undefined;

    function closeSelf() {
        parent.window.postMessage("tfgremoveiframe", "*");
    }

    function setLocalstorage(key, value) {
        parent.window.postMessage({
            name: "tfgsetlocalstorage",
            key, 
            value,
        }, "*");
    }

    function setSessionstorage(key, value) {
        parent.window.postMessage({
            name: "tfgsetsessionstorage",
            key, 
            value,
        }, "*");
    }

    const POPUPS_BUILDER_TRACKING_URL = "";
    const POPUPS_BUILDER_CSRF_TOKEN = "";
    const POPUPS_COLLECT_URL = "<?php echo base_url('perfex_popup/install/collect'); ?>";
    const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    const POPUPS_BUILDER_KEY = "<?php echo $popup->popup_key; ?>";
    const POPUPS_BUILDER_ANALYTICS = true;
    const POPUPS_BUILDER_CSS_LOADED = {
      pixel: false,
      general: false,
      main: false,
    };

    function initPopup(event){
        if (event?.data?.name !== "tfginitpopup" || !event?.data?.parent_url || !event?.data?.parent_inner_width){
            return;
        }
        PARENT_URL = event.data.parent_url;
        PARENT_INNER_WIDTH = event.data.parent_inner_width;
        LOCAL_DISPLAY_FREQUENCE = event?.data?.local_display_frequency;
        SESSION_DISPLAY_FREQUENCE = event?.data?.session_display_frequency;
        SESSION_POPUP_HOVER = event?.data?.session_popup_hover;
        LOCAL_SHOULD_SHOW = event?.data?.local_should_show;

        // start: init popup
        let script = document.createElement('script');
        script.src = '<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/install/js/pixel.js'); ?>';
        script.onload = function() { 
        
            new zillapageManager({
                should_show: !LOCAL_SHOULD_SHOW,
                content: `<?php echo  html_entity_decode($popup->html); ?>`,
                display_mobile: <?php echo  json_encode($popup->settings->display_mobile); ?>,
                display_desktop: <?php echo  json_encode($popup->settings->display_desktop); ?>,
                display_trigger: <?php echo  json_encode($popup->settings->display_trigger); ?>,
                display_trigger_value: <?php echo  json_encode($popup->settings->display_trigger_value); ?>,
                duration: <?php echo  $popup->settings->display_duration === -1 ? -1 : $popup->settings->display_duration * 1000; ?>,
                display_frequency: <?php echo  json_encode($popup->settings->display_frequency); ?>,
                position: <?php echo  json_encode($popup->settings->display_position); ?>,
                trigger_all_pages: <?php echo  json_encode($popup->settings->trigger_all_pages); ?>,
                triggers: <?php echo  json_encode($popup->settings->triggers); ?>,
                on_animation: <?php echo  json_encode($popup->settings->on_animation); ?>,
                off_animation: <?php echo  json_encode($popup->settings->off_animation); ?>,
                popup_id: <?php echo  $popup->id; ?>
            }).initiate({
                displayed: main_element => {
                    // add close button
                    const closeLink = document.createElement('a');
                    closeLink.href = "javascript:void(0);";
                    closeLink.innerHTML = "Close";
                    closeLink.classList.add("popup-close-link");
                    closeLink.addEventListener('click', function() {
                        zillapageManager.remove_popup(main_element);
                    });
                    main_element.appendChild(closeLink);


                    // on submit form
                    const form = main_element.querySelector('form');
                    if(!form) {
                        return;
                    }
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        let popup_id = main_element.getAttribute('data-popup-id');

                        const data = {};
                        form.querySelectorAll('[name]').forEach((input) => {
                            const name = input.getAttribute('name');
                            const val = input.value;
                            data[name] = val;
                        });
                        let response = await fetch(POPUPS_COLLECT_URL, {
                            method: "POST",
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                key: "<?php echo $popup->popup_key; ?>",
                                url: PARENT_URL,
                                data,
                            })
                        });
                        if (response.ok) { // if HTTP-status is 200-299
                            let res = await response.json();
                            if (res.error !== undefined) {
                                alert(res.error);
                            }else{
                                <?php if ( $popup->type_form_submit === 'thank_you_page' ) : ?>

                                    var styles = `<?php echo  $popup->thank_you_css; ?>`;
                                    var styleSheet = document.createElement("style");
                                    styleSheet.innerText = styles;
                                    document.head.appendChild(styleSheet);

                                    const popupElement = main_element.querySelector('#tfg-popup-wrapper');
                                    if(popupElement) {
                                        popupElement.remove();
                                    }
                                    document.head.querySelector("#popup-style").innerHTML = `<?php echo  $popup->thank_you_css; ?>`;
                                    var thankyouElement = document.createElement("div");
                                    thankyouElement.innerHTML = `<?php echo  html_entity_decode($popup->thank_you_html); ?>`;
                                    main_element.appendChild(thankyouElement);
                                <?php else : ?>

                                    zillapageManager.remove_popup(main_element);
                                    window.open("<?php echo $popup->redirect_url; ?>", '_blank');
                                <?php endif; ?>
                            }
                            
                        }else {
                            alert("HTTP-Error: " + response.status);
                        }
                        
                    });
                }
            });

        };
        document.head.appendChild(script);
        // end: init popup        
    }
    window.addEventListener("message", initPopup, false);

    parent.window.postMessage("tfgiframeready", "*");
  </script>
</head>
<body>

</body>