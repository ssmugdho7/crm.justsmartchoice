<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="sccc-card sccc-employee-directory" aria-labelledby="scccDirectoryTitle">
 <h4 id="scccDirectoryTitle"><?= _l('sccc_employee_directory') ?></h4>
 <p class="text-muted"><?= _l('sccc_employee_directory_help') ?></p>
 <?php if(empty($employees)){ ?>
 <p class="text-muted"><?= _l('sccc_no_employee_numbers') ?></p>
 <?php }else{ ?>
 <ul class="sccc-directory-list">
 <?php foreach($employees as $employee){$self=(int)$employee['staff_id']===(int)get_staff_user_id(); ?>
  <li class="sccc-directory-row">
   <div class="sccc-directory-person"><strong><?= html_escape($employee['name']) ?></strong><span><?= html_escape(sccc_phone_display($employee['phone'])) ?></span></div>
   <?php if($self){ ?><span class="label label-default"><?= _l('sccc_your_number') ?></span>
   <?php }elseif(sccc_can('create')){ ?><button type="button" class="btn btn-default sccc-directory-call" data-phone="<?= html_escape($employee['phone']) ?>" aria-label="<?= html_escape(_l('sccc_call_employee',$employee['name'])) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= _l('sccc_call') ?></button><?php } ?>
  </li>
 <?php } ?>
 </ul>
 <?php } ?>
</section>
