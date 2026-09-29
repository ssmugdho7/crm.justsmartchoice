<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$lang = $this->input->get('lang') === 'es' ? 'es' : 'en';
$company_logo = function_exists('get_option') ? get_option('company_logo') : '';
$logo_url = !empty($company_logo) ? base_url('uploads/company/' . $company_logo) : 'https://crm.justsmartchoice.com/media/public/smartchoice%20logo.png';
$home_url = 'https://justsmartchoice.com/';
$profile_url = !empty($token) && function_exists('sales_center_portal_profile_url') ? sales_center_portal_profile_url($token) : '';
$text = [
    'en' => [
        'title' => 'Thank You For Your Submission',
        'message' => 'Your salesperson profile information was saved successfully. Smart Choice Contractors USA has received your submission and our team will review your documents and company information.',
        'next' => 'You may safely close this page. If you need to update your profile or upload more documents, use the profile button below.',
        'profile' => 'Open Your Profile',
        'home' => 'Visit Smart Choice Website',
        'received' => 'Submission Received',
    ],
    'es' => [
        'title' => 'Gracias Por Su Envío',
        'message' => 'La información de su perfil de representante de ventas fue guardada correctamente. Smart Choice Contractors USA recibió su solicitud y nuestro equipo revisará sus documentos e información de compañía.',
        'next' => 'Puede cerrar esta página. Si necesita actualizar su perfil o cargar más documentos, use el botón de perfil abajo.',
        'profile' => 'Abrir Su Perfil',
        'home' => 'Visitar Sitio Web Smart Choice',
        'received' => 'Solicitud Recibida',
    ],
];
$L = $text[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($L['title']); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        body{background:#eef3f1;font-family:Arial,sans-serif;color:#1f2937;padding:25px;}
        .portal-wrap{max-width:900px;margin:0 auto;}
        .success-card{background:#fff;border-radius:18px;box-shadow:0 18px 45px rgba(0,0,0,.16);padding:38px;text-align:center;border-top:6px solid #169179;}
        .success-logo{max-height:82px;max-width:280px;object-fit:contain;background:#fff;border-radius:14px;padding:8px;margin-bottom:18px;}
        .success-icon{width:86px;height:86px;border-radius:50%;background:#169179;color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:42px;font-weight:900;box-shadow:0 12px 28px rgba(22,145,121,.30);}
        h1{font-weight:800;color:#111827;margin:0 0 12px;}
        p{font-size:16px;line-height:1.7;color:#4b5563;}
        .btn-smart{background:#169179;color:#fff;border:0;font-weight:700;border-radius:9px;padding:11px 18px;margin:6px;}
        .btn-smart:hover{background:#0f6f5d;color:#fff;}
        .btn-home{background:#ef8c25;color:#fff;border:0;font-weight:700;border-radius:9px;padding:11px 18px;margin:6px;}
        .btn-home:hover{background:#d87412;color:#fff;}
        .language-box{text-align:right;margin-bottom:12px;}
        @media(max-width:767px){body{padding:12px}.success-card{padding:24px}.language-box{text-align:center}}
    </style>
</head>
<body>
<div class="portal-wrap">
    <div class="language-box">
        <a href="?lang=en" class="btn btn-xs <?php echo $lang === 'en' ? 'btn-primary' : 'btn-default'; ?>">English</a>
        <a href="?lang=es" class="btn btn-xs <?php echo $lang === 'es' ? 'btn-primary' : 'btn-default'; ?>">Español</a>
    </div>
    <div class="success-card">
        <img src="<?php echo html_escape($logo_url); ?>" class="success-logo" alt="Smart Choice Contractors USA">
        <div class="success-icon">✓</div>
        <h1><?php echo html_escape($L['title']); ?></h1>
        <h4 class="text-success"><?php echo html_escape($L['received']); ?></h4>
        <p><?php echo html_escape($L['message']); ?></p>
        <p><?php echo html_escape($L['next']); ?></p>
        <hr>
        <?php if ($profile_url !== '') { ?>
            <a href="<?php echo html_escape($profile_url); ?>" class="btn btn-smart"><?php echo html_escape($L['profile']); ?></a>
        <?php } ?>
        <a href="<?php echo html_escape($home_url); ?>" target="_blank" class="btn btn-home"><?php echo html_escape($L['home']); ?></a>
    </div>
</div>
</body>
</html>
