<?php defined('BASEPATH') or exit('No direct script access allowed');
$comments = isset($comments) && is_array($comments) ? $comments : [];
$files = isset($files) && is_array($files) ? $files : [];
$public_url = isset($public_url) ? $public_url : site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($contract->public_token ?? ''));
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo html_escape($contract->subject); ?></title>
<style>
body{margin:0;background:#f5f2ea;font-family:Arial,Helvetica,sans-serif;color:#1f2937}.top{background:#173c2e;color:#fff;padding:16px 28px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.top strong{font-size:17px}.top .badge{background:#fff;color:#173c2e;border-radius:20px;padding:5px 10px;font-weight:700}.wrap{max-width:1220px;margin:24px auto;background:#fff;box-shadow:0 8px 28px rgba(0,0,0,.12);border-radius:12px;overflow:hidden}.toolbar{background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:14px 18px;text-align:right}.btn{display:inline-block;padding:8px 13px;background:#245f46;color:#fff;text-decoration:none;border-radius:5px;margin:3px;border:0;cursor:pointer}.btn-light{background:#e5e7eb;color:#111827}.grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:0}.doc{padding:28px 38px}.side{background:#fbfaf7;border-left:1px solid #e5e7eb;padding:22px}.summary{border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fff;margin-bottom:18px}.summary h3,.comments h3,.files h3{margin-top:0;color:#173c2e}.summary p{margin:6px 0;font-size:13px}.cover{margin-bottom:16px}.content p{text-align:justify;line-height:1.42;margin:7px 0}.files,.comments{margin-top:20px;border-top:1px solid #ddd;padding-top:15px}.comment{border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px;background:#fff}.comment-form input,.comment-form textarea{width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px;margin-bottom:8px}.comment-form button{width:100%;border:0;background:#245f46;color:#fff;border-radius:6px;padding:10px;font-weight:700}.ssc-cover-page{min-height:auto!important;padding:14px 20px!important;margin-bottom:10px!important}.ssc-cover-card{padding:12px 18px!important}.ssc-cover-card h1{font-size:22px!important;margin:0 0 4px!important}.ssc-cover-card h2{font-size:18px!important;margin:0 0 6px!important}.ssc-cover-card p{margin:2px 0!important;line-height:1.25!important}.ssc-cover-logo{width:180px!important;max-width:180px!important;max-height:180px!important;height:auto!important;object-fit:contain!important;padding:5px!important;margin:0 auto 10px!important;display:block!important}@media(max-width:900px){.grid{grid-template-columns:1fr}.side{border-left:0;border-top:1px solid #e5e7eb}.toolbar{text-align:left}.doc{padding:22px 18px}}
</style>
</head>
<body>
<div class="top"><strong>Smart Choice Contractors USA</strong><span class="badge"><?php echo html_escape(ucwords(str_replace('_', ' ', $contract->status ?? 'Draft'))); ?></span></div>
<div class="wrap">
    <div class="toolbar">
        <?php if (function_exists('is_staff_logged_in') && is_staff_logged_in()) { ?><a class="btn btn-light" href="<?php echo admin_url('subcontractors/contract_view/' . (int) $contract->id); ?>">&larr; Back</a><?php } ?>
        <a class="btn" href="<?php echo admin_url('subcontractors/contract_pdf/' . $contract->id); ?>" target="_blank">Download PDF</a>
        <a class="btn btn-light" href="<?php echo $public_url; ?>" target="_blank">Digital Link</a>
    </div>
    <div class="grid">
        <main class="doc">
            <div class="cover"><?php echo $cover; ?></div>
            <div class="content"><?php echo $content; ?></div>
        </main>
        <aside class="side">
            <div class="summary">
                <h3>Summary</h3>
                <p><strong>Contract:</strong> #<?php echo (int)$contract->id; ?></p>
                <p><strong>Subject:</strong> <?php echo html_escape($contract->subject ?? ''); ?></p>
                <p><strong>Subcontractor:</strong> <?php echo html_escape($contract->subcontractor_company ?? ''); ?></p>
                <p><strong>Type:</strong> <?php echo html_escape($contract->contract_type ?? ''); ?></p>
                <p><strong>Value:</strong> <?php echo function_exists('app_format_money') ? app_format_money($contract->contract_value ?? 0, get_base_currency()) : html_escape($contract->contract_value ?? ''); ?></p>
                <p><strong>Start:</strong> <?php echo html_escape($contract->start_date ?? ''); ?></p>
                <p><strong>End:</strong> <?php echo html_escape($contract->end_date ?? ''); ?></p>
            </div>
            <?php if (!empty($files)) { ?><div class="files"><h3>Attachments</h3><?php foreach($files as $file){ if(!empty($file['visible_to_customer']) || !isset($file['visible_to_customer'])){ ?><p><a target="_blank" href="<?php echo smartsource_subcontractors_file_url($file); ?>"><?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?></a></p><?php }} ?></div><?php } ?>
            <div class="summary" id="contract-signature">
                <h3>Electronic Signature</h3>
                <?php if (!empty($contract->subcontractor_signature)) { ?>
                    <p><strong>Signed:</strong> <?php echo html_escape($contract->signed_date ?? ''); ?></p>
                    <p><strong>Initials:</strong> <?php echo html_escape($contract->subcontractor_initials ?? ''); ?></p>
                    <div style="background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:8px;"><?php echo smartsource_subcontractors_signature_img($contract->subcontractor_signature, 'Subcontractor Signature'); ?></div>
                <?php } elseif (!empty($contract->public_token)) { ?>
                <form method="post" action="<?php echo site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($contract->public_token)); ?>" id="subcontractor-sign-form">
                    <input type="hidden" name="action" value="sign_contract">
                    <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                    <label><strong>First Name</strong></label>
                    <input name="acceptance_firstname" required value="<?php echo html_escape($contract->contact_name ?? ''); ?>" style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px;margin:6px 0 10px;">
                    <label><strong>Last Name</strong></label>
                    <input name="acceptance_lastname" required style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px;margin:6px 0 10px;">
                    <label><strong>Email</strong></label>
                    <input type="email" name="acceptance_email" required value="<?php echo html_escape($contract->email ?? ''); ?>" style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px;margin:6px 0 10px;">
                    <label><strong>Typed Initials</strong></label>
                    <input name="initials" maxlength="12" required style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:9px;margin:6px 0 10px;text-transform:uppercase;">
                    <label><strong>Draw Initials</strong></label>
                    <canvas id="subcontractor-initials-pad" width="286" height="90" style="width:100%;height:90px;border:1px solid #d1d5db;border-radius:6px;background:#fff;touch-action:none;"></canvas>
                    <input type="hidden" name="initials_signature_data" id="subcontractor-initials-signature-data">
                    <button type="button" class="btn btn-light" id="subcontractor-initials-clear">Clear Initials</button>
                    <label><strong>Full Signature</strong></label>
                    <canvas id="subcontractor-signature-pad" width="286" height="150" style="width:100%;height:150px;border:1px solid #d1d5db;border-radius:6px;background:#fff;touch-action:none;"></canvas>
                    <input type="hidden" name="signature_data" id="subcontractor-signature-data">
                    <button type="button" class="btn btn-light" id="subcontractor-signature-clear">Clear Signature</button>
                    <label style="display:block;margin:10px 0;"><input type="checkbox" name="accept_terms" value="1" required> I agree that my electronic signature and initials are legally binding and may be stored with the date, time, and IP address.</label>
                    <button type="submit" class="btn" style="width:100%;">Sign Contract</button>
                </form>
                <script>
                (function(){
                    function makePad(id,clearId){var c=document.getElementById(id);if(!c)return null;var x=c.getContext('2d'),d=false,drawn=false;function p(e){var r=c.getBoundingClientRect(),t=e.touches?e.touches[0]:e;return{x:(t.clientX-r.left)*(c.width/r.width),y:(t.clientY-r.top)*(c.height/r.height)}}function st(e){e.preventDefault();d=true;drawn=true;var q=p(e);x.beginPath();x.moveTo(q.x,q.y)}function mv(e){if(!d)return;e.preventDefault();var q=p(e);x.lineWidth=2;x.lineCap='round';x.lineTo(q.x,q.y);x.stroke()}function up(){d=false}['mousedown','touchstart'].forEach(function(n){c.addEventListener(n,st,{passive:false})});['mousemove','touchmove'].forEach(function(n){c.addEventListener(n,mv,{passive:false})});['mouseup','mouseleave','touchend'].forEach(function(n){c.addEventListener(n,up)});document.getElementById(clearId).onclick=function(){x.clearRect(0,0,c.width,c.height);drawn=false};return{data:function(){return drawn?c.toDataURL('image/png'):''},isDrawn:function(){return drawn}}}
                    var sig=makePad('subcontractor-signature-pad','subcontractor-signature-clear');
                    var ini=makePad('subcontractor-initials-pad','subcontractor-initials-clear');
                    document.getElementById('subcontractor-sign-form').onsubmit=function(e){
                        var sd=sig.data(),id=ini.data();
                        if(!sd||!id){e.preventDefault();alert('Please draw both your initials and full signature.');return false;}
                        document.getElementById('subcontractor-signature-data').value=sd;
                        document.getElementById('subcontractor-initials-signature-data').value=id;
                    };
                })();
                </script>
                <?php } ?>
            </div>
            <div class="comments" id="contract-comments">
                <h3>Discussion</h3>
                <?php if (empty($comments)) { ?><p>No customer-visible comments yet.</p><?php } ?>
                <?php foreach ($comments as $comment) { ?><div class="comment"><strong><?php echo html_escape(($comment['customer_name'] ?? '') ?: trim(($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')) ?: 'Customer'); ?></strong><br><small><?php echo html_escape($comment['dateadded'] ?? ''); ?></small><p><?php echo nl2br(html_escape($comment['comment'] ?? '')); ?></p></div><?php } ?>
                <?php if (!empty($contract->public_token)) { ?>
                <form class="comment-form" method="post" action="<?php echo site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($contract->public_token)); ?>">
                    <input type="hidden" name="action" value="contract_comment">
                    <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                    <input name="customer_name" placeholder="Your Name">
                    <input name="customer_email" placeholder="Your Email">
                    <textarea name="comment" rows="4" placeholder="Write a comment for this contract"></textarea>
                    <button type="submit">Add Comment</button>
                </form>
                <?php } ?>
            </div>
        </aside>
    </div>
</div>
</body>
</html>
