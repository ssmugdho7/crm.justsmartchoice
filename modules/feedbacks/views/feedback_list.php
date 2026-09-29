<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="">
   
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
                  <th class="th-project-name"><?php echo _l('zoom')?></th>
                  <th class="th-project-name"><?php echo _l('zoom_meeting_id')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_timezone')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_start_time')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_duration')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_agenda')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_join_url')?></th>
				  <th class="th-project-name"><?php echo _l('zoom_delete_url')?></th>
                  
               </tr>
            </thead>
            <tbody>
               <?php foreach($client_meetings as $cm){  ?>
               <tr>
                 <td ><?php echo $cm['subject'];?></td>
                  <td ><?php echo $cm['meeting_id'];?></td>
                   <td ><?php echo $cm['timezone'];?></td>
				   <td ><?php echo $cm['start_time'];?></td>
				  <td ><?php echo $cm['duration'];?></td>
				   <td ><?php echo $cm['agenda'];?></td>
				  <td ><a  href="<?php echo $cm['join_url'];?>">Join</a></td>
				  <td ><a href="delete_meeting/<?php echo $cm['meeting_id'];?>">Delete</a></td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
   </div>
</div>
	
	
	
</div>
