<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="">
    
	<br/>
	<div class="panel_s">
   <div class="panel-body">
      <div class="row mbot15">
         <div class="col-md-12">
            <h3 class="text-success projects-summary-heading no-mtop mbot15">Questionnaires</h3>
         </div>
        
      </div>
      <hr />
         <table class="table dt-table table-projects"  >
            <thead>
               <tr>
                  <th class="th-project-name"><?php echo _l('feedback_subject')?></th>
                  <th class="th-project-name"><?php echo _l('feedback_description')?></th>
				  <th class="th-project-name"><?php echo _l('feedback_date')?></th>
				  <th class="th-project-name"><?php echo _l('feedback_link')?></th>
				 
                  
               </tr>
            </thead>
            <tbody>
               <?php foreach($questions_list as $cm){  ?>
               <tr>
                 <td ><?php echo $cm['subject'];?></td>
                  <td ><?php echo $cm['description'];?></td>
                   <td ><?php echo $cm['datecreated'];?></td>
				 
				  <td ><a  href="<?php echo base_url();echo"feedbacks/feedback/".$cm['feedbackid'];echo"/"?><?php echo $cm['hash'];?>">Join</a></td>
				 
               </tr>
               <?php } ?>
            </tbody>
         </table>
   </div>
</div>
	
	
	
</div>
