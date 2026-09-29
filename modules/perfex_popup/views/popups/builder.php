<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#4188c9">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <?php $favicon = get_option('favicon'); ?>
    <link rel="icon" href="<?php echo base_url('uploads/company/'.$favicon); ?>" type="image/png">
    <link rel="shortcut icon" type="image/x-icon" href="" />
    <?php
      $template_param = "";
      $segment_type_page = $this->uri->segment('6');
      $segment_template = $this->uri->segment('7');
      $type_page = "main-content";
      if($segment_type_page) $type_page = $segment_type_page;

      $back_button_url = admin_url('perfex_popup/popups/index');
      $publish_button_url = admin_url('perfex_popup/popups/setting/'.$item->code);
      $title_page = _l('Popup - '). $item->name;
      
      if($segment_template == 'template'){
        $template_param = "/template";
        $back_button_url = admin_url('perfex_popup/templates');
        $publish_button_url = admin_url('perfex_popup/templates/template/'.$item->id);
        $title_page = _l('Template - '). $item->name;
      }
      ?>
    <title><?php echo html_escape($title_page); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,900" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/css/builder.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/css/customize-builder.css'); ?>">
    <script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/builder.js'); ?>"></script>
    <style>
       :root {
        --gjs-frame-width: <?php echo $item->width ?>px;
        --gjs-frame-height: <?php echo $item->height ?>px;
       }
      .gjs-frame-wrapper{
        margin-top: 80px;
        width: var(--gjs-frame-width);
        height: var(--gjs-frame-height);
      }
    </style>
    <script type="text/javascript">
      var config = {
        enable_edit_code: false,
        enable_slider: true,
        enable_custom_code_block: false,
        all_icons:  JSON.parse(JSON.stringify(<?php echo $all_icons; ?>)),
      };
    </script>
    <script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/grapeweb.js'); ?>"></script>
</head>

 <body>
    <div id="mobileAlert">
      <div class="message">
        <h3><?php echo _l('Builder not work on mobile'); ?></h3>
        <a href ="#"><?php echo _l('Back'); ?></a>
      </div>
    </div>
   
    <input type="text" name="code" value="<?php echo $item->id; ?>" hidden class="form-control">
    
    <div id="loadingMessage">
      <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
    </div>
    <div class="btn-page-group">
        <a href="<?php echo admin_url('perfex_popup/popups/builder/'.$item->code.'/main-content'.$template_param); ?>" class="btn btn-light <?php if($segment_type_page != 'thank-content') echo "active"; ?>" id="btn-main-content"><?php echo _l('Main Content'); ?></a>
        <a href="<?php echo admin_url('perfex_popup/popups/builder/'.$item->code.'/thank-content'.$template_param); ?>" class="btn btn-light <?php if($segment_type_page == 'thank-content') echo "active"; ?>" id="btn-thank-content"><?php echo _l('Thank Content'); ?></a>
    </div>
    <div id="modalResize" class="rsbr-modal">
        <div class="rsbr-modal-content">
        <div class="rsbr-modal-header">
            <h3><?php echo _l('Resize popup'); ?></h3>
            <span class="rsbr-modal-close" id="modalResizeClose" >&times;</span>
        </div>
            <form id="rsbr_form_resize_popup" action="">
                <div class="rsbr-modal-body">
                    <small><i>**<?php echo _l('You can change the size of the popup with pixels'); ?></i></small>
                    <br><small id="rsbr_message_error" class="rsbr_message_error"></small>
                    <div class="rsbr-form-group">
                        <label><?php echo _l('Width - pixel'); ?></label><br/>
                        <input type="number" name="width" required value="<?php echo $item->width; ?>"  placeholder="<?php echo _l('Width - pixel'); ?>" class="rsbr-form-control">
                    </div>
                    <div class="rsbr-form-group">
                        <label><?php echo _l('Height - pixel'); ?></label>
                        <input type="number" value="<?php echo $item->height; ?>" required  class="rsbr-form-control" name="height" placeholder="<?php echo _l('Height - pixel'); ?>"/>
                    </div>
                </div>
                <div class="rsbr-modal-footer">
                    <button class="btn btn-success" id="rsbr-button-save" type="submit"><?php echo _l('Save'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalTemplates" class="rsbr-modal">
        <div class="rsbr-modal-content rsbr-modal-large">
            <div class="rsbr-modal-header">
                <h3><?php echo _l('Choose a template'); ?></h3>
                <span class="rsbr-modal-close" id="modalTemplatesClose">&times;</span>
            </div>
            <div class="rsbr-modal-body">
                    <div class="modal-flex">
                        <?php foreach($all_templates as $template){ ?>
                            <div class="modal-column modal-col3">
                                <div class="card card-template modal-column-content" data-code="<?php echo $template->code; ?>" data-type-page="<?php echo $type_page; ?>">
                                    <img src="<?php echo base_url(PERFEX_POPUP_UPLOAD_PATH.'/popup_thumb_templates/'. $template->thumbnail); ?>" alt="<?php echo $template->name; ?>" data-was-processed="true" class="" />
                                    <div class="template-title">
                                        <h4><?php echo $template->name; ?> - (<?php echo $template->width."x".$template->height; ?>)</h4>
                                        <?php if ( $template->is_premium ) : ?>
                                            <span class="template-type premium"><i class="fas fa-star"></i> <?php echo _l("Premium"); ?></span>
                                        <?php else : ?>
                                            <span class="template-type free"><i class="fas fa-star"></i> <?php echo _l("Free"); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
            </div>
        </div>
    </div>
    <div id="gjs">
    </div>
    
    
    <script type="text/javascript">
      var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
      var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
      
      var urlStore = '<?php echo admin_url('perfex_popup/popups/updatebuilder/'.$item->code.'/'.$type_page.$template_param); ?>';
      var urlLoad = '<?php echo admin_url('perfex_popup/popups/loadbuilder/'.$item->code.'/'.$type_page.$template_param); ?>';

      var upload_Image = '<?php echo admin_url('perfex_popup/popups/uploadimage'); ?>';
      var url_delete_image = '<?php echo admin_url('perfex_popup/popups/deleteimage'); ?>';
      var back_button_url = '<?php echo $back_button_url; ?>';
      var publish_button_url = '<?php echo $publish_button_url; ?>';
    
      var resize_popup_url = '<?php echo admin_url('perfex_popup/popups/resize/'.$item->code.$template_param); ?>';
      var url_load_template = '<?php echo admin_url('perfex_popup/popups/loadtemplate'); ?>';

      
      var url_default_css_template = '<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/css/general.css'); ?>';
      var url_search_icon = '<?php echo admin_url('perfex_popup/popups/searchIcon'); ?>';

      var images_url = JSON.parse(JSON.stringify(<?php echo $images_url; ?>));
      var all_fonts = JSON.parse(JSON.stringify(<?php echo $all_fonts; ?>));

      var google_fonts_string = "<?php echo implode("|",PERFEX_POPUP_CONFIG['google_fonts']); ?>";

      var langs = {
          "fontFamily": "<?php echo _l('Fonts'); ?>",
          "resize_popup": "<?php echo _l('Resize popup'); ?>",
          "changeTemplates": "<?php echo _l('Popup Templates'); ?>",
      };
    </script>
    <script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/customize-builder.js'); ?>"></script>
    
  </body>

</html>