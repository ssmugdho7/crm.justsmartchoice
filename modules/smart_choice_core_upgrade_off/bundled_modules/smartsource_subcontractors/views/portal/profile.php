<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$lang = $this->input->get('lang') === 'es' ? 'es' : 'en';
$is_register = !empty($is_register);
$company_logo = function_exists('get_option') ? get_option('company_logo') : '';
$logo_url = !empty($company_logo) ? base_url('uploads/company/' . $company_logo) : 'https://crm.justsmartchoice.com/media/public/smartchoice%20logo.png';
$home_url = 'https://justsmartchoice.com/';
$t = [
    'en' => [
        'title' => $is_register ? 'Smart Choice Subcontractor Registration' : 'Smart Choice Subcontractor Portal',
        'subtitle' => $is_register ? 'Create your subcontractor profile and upload your license, insurance, W-9, photos, and company documents for Smart Choice Contractors USA review.' : 'Update your company profile, license information, insurance date, contact information, and upload documents.',
        'language' => 'Language', 'company_info' => 'Company Information', 'company_name' => 'Company Name', 'contact_name' => 'Contact Name', 'email' => 'Email', 'phone' => 'Phone', 'trade' => 'Trade', 'license' => 'License Number', 'dbpr' => 'DBPR Verification Link', 'county' => 'County Verification Link', 'insurance' => 'Insurance Expiration', 'picture' => 'Profile Picture', 'address' => 'Address', 'city' => 'City', 'state' => 'State', 'zip' => 'ZIP', 'upload' => 'Upload Documents', 'upload_note' => 'Upload licenses, insurance certificates, W-9, photos, and other documents requested by Smart Choice Contractors USA.', 'files' => 'Files Already Uploaded', 'nofiles' => 'No files found.', 'save' => $is_register ? 'Create Subcontractor Profile' : 'Save Subcontractor Profile', 'website' => 'Visit Smart Choice Website'
    ],
    'es' => [
        'title' => $is_register ? 'Registro de Subcontratista Smart Choice' : 'Portal de Subcontratistas Smart Choice',
        'subtitle' => $is_register ? 'Cree su perfil de subcontratista y cargue su licencia, seguro, W-9, fotos y documentos de compañía para revisión de Smart Choice Contractors USA.' : 'Actualice su perfil de compañía, licencia, fecha de seguro, información de contacto y cargue documentos.',
        'language' => 'Idioma', 'company_info' => 'Información de la Compañía', 'company_name' => 'Nombre de la Compañía', 'contact_name' => 'Nombre de Contacto', 'email' => 'Correo Electrónico', 'phone' => 'Teléfono', 'trade' => 'Oficio', 'license' => 'Número de Licencia', 'dbpr' => 'Enlace de Verificación DBPR', 'county' => 'Enlace de Verificación del Condado', 'insurance' => 'Vencimiento del Seguro', 'picture' => 'Foto de Perfil', 'address' => 'Dirección', 'city' => 'Ciudad', 'state' => 'Estado', 'zip' => 'Código Postal', 'upload' => 'Cargar Documentos', 'upload_note' => 'Cargue licencias, certificados de seguro, W-9, fotos y otros documentos solicitados por Smart Choice Contractors USA.', 'files' => 'Archivos Ya Cargados', 'nofiles' => 'No hay archivos.', 'save' => $is_register ? 'Crear Perfil de Subcontratista' : 'Guardar Perfil del Subcontratista', 'website' => 'Visitar Sitio Web Smart Choice'
    ],
];
$L = $t[$lang];
$portal_error = $this->input->get('error');
$error_messages = [
    'security' => $lang === 'es' ? 'La verificación de seguridad falló. Actualice la página e intente nuevamente.' : 'Security verification failed. Please refresh the page and try again.',
    'save_failed' => $lang === 'es' ? 'No se pudo guardar la información. Revise los campos e intente nuevamente.' : 'The information could not be saved. Please review the form and try again.',
];
function sc_portal_value($subcontractor, $field) {
    if (!$subcontractor) { return ''; }
    return isset($subcontractor->$field) ? $subcontractor->$field : '';
}
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
        .portal-wrap{max-width:1040px;margin:0 auto;}
        .portal-header{background:linear-gradient(135deg,#0f172a,#169179);color:#fff;border-radius:18px;box-shadow:0 18px 45px rgba(0,0,0,.18);padding:28px;margin-bottom:22px;border-bottom:5px solid #ef8c25;}
        .portal-logo{max-height:78px;max-width:280px;object-fit:contain;background:#fff;border-radius:14px;padding:8px;margin-bottom:15px;}
        .portal-card{background:#fff;border-radius:16px;box-shadow:0 14px 38px rgba(0,0,0,.12);padding:28px;margin-bottom:22px;border-top:5px solid #169179;}
        .portal-title{font-weight:800;margin-top:0;color:#fff;}
        .portal-card h4{font-weight:800;color:#111827;margin-top:0;}
        .profile-image{width:118px;height:118px;border-radius:50%;object-fit:cover;border:4px solid #ef8c25;background:#fff;}
        .btn-smart{background:#169179;color:#fff;border:0;font-weight:700;}
        .btn-smart:hover{background:#0f6f5d;color:#fff;}
        .btn-home{background:#ef8c25;color:#fff;border:0;font-weight:700;}
        .btn-home:hover{background:#d87412;color:#fff;}
        .file-list li{margin-bottom:7px;}
        .language-box{text-align:right;margin-bottom:12px;}
        .smart-help-text{font-size:13px;color:#6b7280;margin-top:5px;}
        @media(max-width:767px){body{padding:12px}.portal-card,.portal-header{padding:18px}.language-box{text-align:center}.portal-logo{max-width:220px}}
    </style>
</head>
<body>
<div class="portal-wrap">
    <div class="language-box">
        <strong><?php echo $L['language']; ?>:</strong>
        <a href="?lang=en" class="btn btn-xs <?php echo $lang === 'en' ? 'btn-primary' : 'btn-default'; ?>">English</a>
        <a href="?lang=es" class="btn btn-xs <?php echo $lang === 'es' ? 'btn-primary' : 'btn-default'; ?>">Español</a>
    </div>

    <?php if (!empty($portal_error) && isset($error_messages[$portal_error])) { ?>
        <div class="alert alert-danger" style="border-radius:12px;font-weight:700;">
            <?php echo html_escape($error_messages[$portal_error]); ?>
        </div>
    <?php } ?>

    <div class="portal-header text-center">
        <img src="<?php echo html_escape($logo_url); ?>" class="portal-logo" alt="Smart Choice Contractors USA">
        <?php if (!$is_register && !empty(sc_portal_value($subcontractor, 'profile_image'))) { ?>
            <div><img src="<?php echo base_url(sc_portal_value($subcontractor, 'profile_image')); ?>" class="profile-image" alt="Subcontractor Profile Picture"></div>
        <?php } ?>
        <h2 class="portal-title"><?php echo $L['title']; ?></h2>
        <p><?php echo $L['subtitle']; ?></p>
        <a href="<?php echo $home_url; ?>" class="btn btn-home" target="_blank"><?php echo $L['website']; ?></a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="smartsource_website" value="" style="position:absolute;left:-9999px;top:-9999px" tabindex="-1" autocomplete="off">
        <?php
        $CI =& get_instance();
        if (isset($CI->security)) {
            echo '<input type="hidden" name="' . $CI->security->get_csrf_token_name() . '" value="' . $CI->security->get_csrf_hash() . '">';
        }
        ?>
        <div class="portal-card">
            <h4><?php echo $L['company_info']; ?></h4>
            <div class="row">
                <div class="col-md-6 form-group"><label><?php echo $L['company_name']; ?></label><input name="company" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'company')); ?>" required></div>
                <div class="col-md-6 form-group"><label><?php echo $L['contact_name']; ?></label><input name="contact_name" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'contact_name')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['email']; ?></label><input name="email" type="email" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'email')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['phone']; ?></label><input name="phone" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'phone')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['trade']; ?></label><input name="trade" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'trade')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['license']; ?></label><input name="license_number" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'license_number')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['dbpr']; ?></label><input name="dbpr_link" class="form-control" placeholder="www.myfloridalicense.com/..." value="<?php echo html_escape(sc_portal_value($subcontractor, 'dbpr_link')); ?>"><div class="smart-help-text">You can enter with or without https://</div></div>
                <div class="col-md-6 form-group"><label><?php echo $L['county']; ?></label><input name="county_license_link" class="form-control" placeholder="county website license verification link" value="<?php echo html_escape(sc_portal_value($subcontractor, 'county_license_link')); ?>"><div class="smart-help-text">You can enter with or without https://</div></div>
                <div class="col-md-6 form-group"><label><?php echo $L['insurance']; ?></label><input name="insurance_expiration" type="date" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'insurance_expiration')); ?>"></div>
                <div class="col-md-6 form-group"><label><?php echo $L['picture']; ?></label><input type="file" name="profile_image_file" class="form-control" accept="image/*"></div>
                <div class="col-md-12 form-group"><label><?php echo $L['address']; ?></label><textarea name="address" class="form-control" rows="2"><?php echo html_escape(sc_portal_value($subcontractor, 'address')); ?></textarea></div>
                <div class="col-md-4 form-group"><label><?php echo $L['city']; ?></label><input name="city" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'city')); ?>"></div>
                <div class="col-md-4 form-group"><label><?php echo $L['state']; ?></label><input name="state" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'state')); ?>"></div>
                <div class="col-md-4 form-group"><label><?php echo $L['zip']; ?></label><input name="zip" class="form-control" value="<?php echo html_escape(sc_portal_value($subcontractor, 'zip')); ?>"></div>
            </div>
        </div>
        <div class="portal-card">
            <h4><?php echo $L['upload']; ?></h4>
            <p class="text-muted"><?php echo $L['upload_note']; ?></p>
            <input type="file" name="file[]" class="form-control" multiple>
            <hr>
            <h5><?php echo $L['files']; ?></h5>
            <?php if (empty($files)) { ?><p class="text-muted"><?php echo $L['nofiles']; ?></p><?php } else { ?>
                <ul class="file-list">
                <?php foreach ($files as $file) { ?>
                    <li><?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?> <small class="text-muted"><?php echo html_escape($file['dateadded']); ?></small></li>
                <?php } ?>
                </ul>
            <?php } ?>
        </div>
        <?php if (function_exists('show_recaptcha') && show_recaptcha()) { ?>
        <div class="portal-card"><div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div></div>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php } ?>
        <button class="btn btn-smart btn-lg btn-block" type="submit"><?php echo $L['save']; ?></button>
    </form>
</div>
<script>
(function(){
    var form = document.querySelector('form');
    if(!form){ return; }
    var smartsourcePortalSubmitting = false;
    form.addEventListener('submit', function(e){
        if(smartsourcePortalSubmitting){
            e.preventDefault();
            return false;
        }
        smartsourcePortalSubmitting = true;
        var btn = form.querySelector('button[type="submit"]');
        if(btn){
            btn.disabled = true;
            btn.innerHTML = 'Saving...';
        }
    });
})();
</script>
</body>
</html>
