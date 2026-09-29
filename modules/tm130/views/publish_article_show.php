<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <?php $favicon = get_option('favicon'); ?>
    <link rel="icon" href="<?php echo base_url('uploads/company/'.$favicon); ?>" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/articles_show/styles/jquery.tocify.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo site_url('assets/plugins/tinymce/plugins/codesample/css/prism.css'); ?>">
    <link href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/publish_article_show.css'); ?>" rel="stylesheet">

<style id="training-manual-rendered-style">
.training-manual-rendered-article{max-width:1120px;margin:24px auto;background:#fff;border-radius:16px;padding:30px;box-shadow:0 10px 32px rgba(15,23,42,.10);font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.6;}
.training-manual-rendered-article h1,.training-manual-rendered-article h2{color:#1f2937;border-left:5px solid #1f8f4d;padding-left:12px;}
.training-manual-rendered-article h3{color:#1f8f4d;}
.training-manual-rendered-article .sc-cover{border:1px solid #d9e2ec;border-radius:14px;padding:28px;background:linear-gradient(135deg,#f0fff4,#ffffff 45%,#fff7ed);margin-bottom:24px;}
.training-manual-rendered-article .sc-toc,.training-manual-rendered-article .sc-card,.training-manual-rendered-article .sc-ui-box{border:1px solid #d9e2ec;border-radius:12px;background:#fff;padding:16px;box-shadow:0 6px 20px rgba(15,23,42,.06);}
.training-manual-rendered-article .sc-badge{display:inline-block;border:1px solid #d9e2ec;border-radius:999px;padding:6px 12px;margin:3px;font-size:12px;font-weight:700;background:#fff;}
.training-manual-rendered-article .sc-badge.green{background:#e8f8ef;color:#166534;border-color:#bbf7d0;}
.training-manual-rendered-article .sc-badge.orange{background:#fff7ed;color:#9a3412;border-color:#fed7aa;}
.training-manual-rendered-article .sc-success{border-left:4px solid #1f8f4d;background:#f0fdf4;padding:12px 14px;border-radius:8px;margin:14px 0;}
.training-manual-rendered-article .sc-note{border-left:4px solid #2563eb;background:#eff6ff;padding:12px 14px;border-radius:8px;margin:14px 0;}
.training-manual-rendered-article .sc-warning{border-left:4px solid #f7941d;background:#fff7ed;padding:12px 14px;border-radius:8px;margin:14px 0;}
.training-manual-rendered-article .sc-danger{border-left:4px solid #dc2626;background:#fef2f2;padding:12px 14px;border-radius:8px;margin:14px 0;}
.training-manual-rendered-article table,.training-manual-rendered-article .sc-table{width:100%;border-collapse:collapse;margin:16px 0;}
.training-manual-rendered-article th{background:#f1f5f9;color:#111827;border:1px solid #d9e2ec;padding:9px;text-align:left;}
.training-manual-rendered-article td{border:1px solid #d9e2ec;padding:9px;}
.training-manual-rendered-article .sc-flow{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:18px 0;}
.training-manual-rendered-article .sc-flow-box{background:#fff;border:2px solid #1f8f4d;border-radius:12px;padding:12px 14px;font-weight:700;min-width:115px;text-align:center;}
.training-manual-rendered-article .sc-flow-arrow{font-size:24px;color:#f7941d;font-weight:900;}
.training-manual-rendered-article .sc-folder{background:#0f172a;color:#e5e7eb;padding:18px;border-radius:12px;font-family:Consolas,monospace;white-space:pre-wrap;}
</style>
</head>

<style>

</style>
</head>

<body>

    <div id="mySidebar" class="sidebar">
        <div class="header">
           <div id="logo">
              <?php get_company_logo(get_admin_uri().'/') ?>
           </div>
           
        </div>
        <div id="toc">
        </div>
    </div>

    <div id="main">
        <div id="header" style="">
            <div class="header-left">
              <a href="javascript:void(0)" id="closebtn" class="closebtn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="20" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                  </svg>
              </a>
              <span class="openbtn" id="openbtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
              </svg>
              </span>
              <span class=""> <?php echo $article['title']; ?></span>
            </div>
          
            
        </div>

        <div class="training-manual-translate-bar">
            <?php $tm_translate_url = 'https://translate.google.com/translate?sl=auto&tl=es&u=' . rawurlencode(current_url()); ?>
            <a class="training-manual-translate-btn" href="<?php echo $tm_translate_url; ?>" target="_blank" rel="noopener">
                <i class="fa fa-language"></i> <?php echo _l('translate_to_spanish'); ?>
            </a>
        </div>

        <div class="content-main training-manual-rendered-article">
            <?php 
                if($article['type'] == 'document'){
                    echo $article['content'];
                }else if($article['type'] == 'mindmap'){
                    echo '<img class="training_manual-mindmap-thumb-content" src="' . training_manual_get_mindmap_thumb($article['mindmap_thumb']) . '" />';
                };
            ?>
        </div>

    </div>

    <script>

    </script>

   
    <script src=" <?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/articles_show/javascripts/jquery/jquery-1.8.3.min.js'); ?>"></script>
    <script src=" <?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/articles_show/javascripts/jqueryui/jquery-ui-1.9.1.custom.min.js'); ?>"></script>
    <script src=" <?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/articles_show/javascripts/jquery.tocify.min.js'); ?>"></script>
    <script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/js/article_show.js'); ?>"></script>

</body>

</html>