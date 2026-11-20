<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h1 class="tw-text-xl tw-font-semibold tw-mb-4">Trash &amp; Recovery</h1>
<p>Deleted records and uploaded files are retained privately. There is no automatic purge. Only administrators can access recovery copies.</p>
<?php if (!$ready): ?><div class="alert alert-warning">Recovery protection is not enabled yet. Complete the verified backup and installation before enabling it.</div>
<?php else: ?>
<?php if(!empty($maintenance['ok'])): ?><div class="alert alert-success">Last verified daily database and media backup: <?=html_escape($maintenance['completed_at'])?>.</div><?php else: ?><div class="alert alert-warning">A daily backup has not completed successfully yet. Administrator attention is required.</div><?php endif; ?>
<h2 class="tw-text-lg tw-mt-6">Files</h2>
<p>Restore puts a file back at its original location and keeps the archived copy. It will never replace an existing file. If its parent record was also deleted, recover that record separately before using the file in the CRM.</p>
<form method="get" class="tw-mb-4"><label for="recovery-search">Find file</label> <input class="form-control" style="max-width:420px;display:inline-block" id="recovery-search" name="search" value="<?=html_escape($search)?>" maxlength="150">
<label class="tw-ml-3"><input type="checkbox" name="all" value="1" <?=$all?'checked':''?>> Include saved uploads and previous versions</label> <button class="btn btn-default" type="submit">Search</button></form>
<div class="table-responsive"><table class="table"><thead><tr><th>File</th><th>Saved at (UTC)</th><th>Reason</th><th>Actions</th></tr></thead><tbody>
<?php foreach($files as $file): ?><tr><td><?=html_escape($file['name'])?><br><small><?=html_escape($file['path'])?></small></td><td><?=html_escape($file['created_at'])?></td><td><?=html_escape($file['event'])?></td><td><a class="btn btn-default btn-sm" href="<?=admin_url('recovery/download/'.(int)$file['id'])?>">Download copy</a>
<?php if($file['event']!=='incoming'): ?><?=form_open(admin_url('recovery/restore/'.(int)$file['id']),['style'=>'display:inline'])?><button type="submit" class="btn btn-success btn-sm">Restore file</button><?=form_close()?><?php else: ?><small>Reattach through the original CRM form.</small><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$files): ?><tr><td colspan="4">No matching recovery copies.</td></tr><?php endif; ?></tbody></table></div>
<h2 class="tw-text-lg tw-mt-6">Deleted records</h2><p>These copies preserve deleted records and their related rows for administrator-assisted recovery. Export a copy before recovery review. Financial and linked records need review so that recovery does not undo newer changes or repeat external transactions.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Deleted at (UTC)</th><th>Deleted by</th><th>Records</th><th>Actions</th></tr></thead><tbody>
<?php foreach($batches as $batch): ?><tr><td><?=html_escape($batch['deleted_at'])?></td><td><?=html_escape($batch['actor'])?></td><td><?=(int)$batch['records']?> records in <?=(int)$batch['tables']?> tables</td><td><a class="btn btn-default btn-sm" href="<?=admin_url('recovery/records/'.rawurlencode($batch['batch_id']))?>">Export recovery copy</a></td></tr><?php endforeach; ?>
<?php if(!$batches): ?><tr><td colspan="4">No deleted records have been archived yet.</td></tr><?php endif; ?></tbody></table></div>
<nav aria-label="Recovery pages"><?php if($page>1): ?><a class="btn btn-default" href="<?=admin_url('recovery?'.http_build_query(['page'=>$page-1,'search'=>$search,'all'=>$all?'1':'0']))?>">Previous</a><?php endif; ?> <a class="btn btn-default" href="<?=admin_url('recovery?'.http_build_query(['page'=>$page+1,'search'=>$search,'all'=>$all?'1':'0']))?>">Next</a></nav>
<?php endif; ?>
</div></div></div></div>
<?php init_tail(); ?>
