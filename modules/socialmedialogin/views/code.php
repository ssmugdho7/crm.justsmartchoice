<?php if(get_option('socialmedialogin_module_status') == "Active"){ ?>
   <div class="social_icon">
      <?php if(get_option('google_btn_status') == "Active") { ?>
         <a href="<?php echo site_url('socialmedialogin/google_login'); ?>" class="border border-secondary p-1 rounded"><img class="" src="<?php echo module_dir_url('socialmedialogin','/assets/images/google.svg');?>">
         </a>
      <?php } ?>
      <?php if(get_option('facebook_btn_status') == "Active") { ?>
         <a href="<?php echo site_url('socialmedialogin/facebook_login'); ?>" class="border border-secondary mr-3 p-1 rounded"> <img class="" src="<?php echo module_dir_url('socialmedialogin','/assets/images/facebook.svg');?>">
         </a>
      <?php } ?>
      <?php if(get_option('linkedin_btn_status') == "Active") { ?>
         <a href="<?php echo site_url('socialmedialogin/linkedin_login'); ?>" class="border border-secondary mr-3 p-1 rounded"><img class="" src="<?php echo module_dir_url('socialmedialogin','/assets/images/linkedin.svg');?>">
         </a>
      <?php } ?>
      <?php if(get_option('twitter_btn_status') == "Active") { ?>
         <a href="<?php echo site_url('socialmedialogin/twitter_login'); ?>" class="border border-secondary mr-3 p-1 rounded"><img class="" src="<?php echo module_dir_url('socialmedialogin','/assets/images/twitter.svg');?>">
         </a>
      <?php } ?>
   </div>
<?php } ?>