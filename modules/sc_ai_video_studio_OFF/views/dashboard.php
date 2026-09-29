<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body">
<h4 class="no-margin">Smart Choice AI Video Studio</h4>
<p class="text-muted">Create AI-ready avatar videos, manage voices and avatars, store scripts, control logo watermark options, and keep generated video records inside the CRM.</p>
<div class="scv-grid">
<a href="<?php echo admin_url('sc_ai_video_studio/video'); ?>" class="scv-tile"><i class="fa fa-plus-circle"></i><strong>New Video</strong><span>Create script, avatar, voice, overlays.</span></a>
<a href="<?php echo admin_url('sc_ai_video_studio/videos'); ?>" class="scv-tile"><i class="fa fa-video-camera"></i><strong>Videos</strong><span><?php echo (int)$counts['videos']; ?> records</span></a>
<a href="<?php echo admin_url('sc_ai_video_studio/voices'); ?>" class="scv-tile"><i class="fa fa-microphone"></i><strong>Voices</strong><span><?php echo (int)$counts['voices']; ?> voices</span></a>
<a href="<?php echo admin_url('sc_ai_video_studio/avatars'); ?>" class="scv-tile"><i class="fa fa-user-circle"></i><strong>Avatars</strong><span><?php echo (int)$counts['avatars']; ?> avatars</span></a>
<a href="<?php echo admin_url('sc_ai_video_studio/settings'); ?>" class="scv-tile"><i class="fa fa-cog"></i><strong>Settings</strong><span>API key and branding</span></a>
<a href="<?php echo admin_url('sc_ai_video_studio/health'); ?>" class="scv-tile"><i class="fa fa-heartbeat"></i><strong>Health</strong><span>Check install status</span></a>
</div>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>
