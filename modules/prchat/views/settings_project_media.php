<?php defined('BASEPATH') or exit('No direct script access allowed');
if (!is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER)) { @mkdir(PR_CHAT_MEDIA_PROJECTS_FOLDER, 0755, true); }
$folders = is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER) ? array_values(array_filter(scandir(PR_CHAT_MEDIA_PROJECTS_FOLDER), function ($item) { return $item !== '.' && $item !== '..' && is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER . '/' . $item); })) : [];
?>
<div class="prchat-settings-panel">
  <h4><i class="fa fa-folder-open"></i> <?php echo _l('chat_project_media'); ?></h4>
  <p class="text-muted"><?php echo _l('chat_project_media_description'); ?></p>
  <div class="alert alert-info"><strong><?php echo _l('chat_project_media_path'); ?>:</strong> <?php echo html_escape(PR_CHAT_MEDIA_PROJECTS_FOLDER); ?></div>
  <table class="table table-bordered table-striped prchat-settings-table"><thead><tr><th><?php echo _l('chat_project_media_folder'); ?></th></tr></thead><tbody>
  <?php if ($folders) { foreach ($folders as $folder) { ?><tr><td><?php echo html_escape($folder); ?></td></tr><?php } } else { ?><tr><td><?php echo _l('chat_project_media_empty'); ?></td></tr><?php } ?>
  </tbody></table>
</div>
<style>.prchat-settings-panel{max-width:100%;}.prchat-settings-table{table-layout:auto!important;width:100%!important}.prchat-settings-table th,.prchat-settings-table td{white-space:normal!important;vertical-align:middle!important}</style>
