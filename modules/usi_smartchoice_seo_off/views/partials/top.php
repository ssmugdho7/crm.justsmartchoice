<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="sc-ai-topbar sc-ai-nav-grid" id="scSammyNav">
  <div class="sc-nav-group sc-nav-group-estimating">
    <button type="button" class="sc-nav-heading" data-toggle="collapse" data-target="#scNavEstimating" aria-expanded="true"><i class="fa fa-calculator"></i> Estimating &amp; Operations</button>
    <div id="scNavEstimating" class="sc-nav-links collapse in">
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_dashboard'); ?>"><i class="fa fa-dashboard"></i> AI Dashboard</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/voice_assistant'); ?>"><i class="fa fa-microphone"></i> Voice Assistant</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/camera_intake'); ?>"><i class="fa fa-camera"></i> Camera Intake</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>"><i class="fa fa-calculator"></i> AI Estimates</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/estimate_review_queue'); ?>"><i class="fa fa-check-square-o"></i> Estimate Review</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/pricing_engine'); ?>"><i class="fa fa-database"></i> Pricing Engine</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>"><i class="fa fa-cubes"></i> Material Takeoff</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>"><i class="fa fa-shopping-cart"></i> AI Purchasing</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/scheduling'); ?>"><i class="fa fa-calendar"></i> AI Scheduling</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/jobsite_assistant'); ?>"><i class="fa fa-clipboard"></i> Jobsite Assistant</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty'); ?>"><i class="fa fa-shield"></i> Closeout &amp; Warranty</a>
    </div>
  </div>
  <div class="sc-nav-group sc-nav-group-ai">
    <button type="button" class="sc-nav-heading" data-toggle="collapse" data-target="#scNavAiCore" aria-expanded="true"><i class="fa fa-microchip"></i> AI Core</button>
    <div id="scNavAiCore" class="sc-nav-links collapse in">
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/executive_dashboard'); ?>"><i class="fa fa-line-chart"></i> Executive Dashboard</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/business_intelligence'); ?>"><i class="fa fa-lightbulb-o"></i> Business Intelligence</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/automation_center'); ?>"><i class="fa fa-bell"></i> Alerts &amp; Automation</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/workflow_engine'); ?>"><i class="fa fa-sitemap"></i> Workflow Engine</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/chat_engine'); ?>"><i class="fa fa-comments-o"></i> Conversation &amp; Chat</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator'); ?>"><i class="fa fa-podcast"></i> Voice Orchestrator</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/vision'); ?>"><i class="fa fa-eye"></i> Vision Engine</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/advanced_vision'); ?>"><i class="fa fa-camera-retro"></i> Advanced Vision</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/memory_engine'); ?>"><i class="fa fa-database"></i> Memory Engine</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/document_intelligence'); ?>"><i class="fa fa-file-pdf-o"></i> Document Intelligence</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_core'); ?>"><i class="fa fa-cogs"></i> AI Core</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/intelligence_engine'); ?>"><i class="fa fa-bolt"></i> Intelligence Engine</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/multi_agent_ai'); ?>"><i class="fa fa-users"></i> Multi-Agent AI</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/command_center'); ?>"><i class="fa fa-th-large"></i> Command Center</a>
    </div>
  </div>
  <div class="sc-nav-group sc-nav-group-video">
    <button type="button" class="sc-nav-heading" data-toggle="collapse" data-target="#scNavVideo" aria-expanded="true"><i class="fa fa-video-camera"></i> Video &amp; Avatar Studio</button>
    <div id="scNavVideo" class="sc-nav-links collapse in">
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_studio'); ?>"><i class="fa fa-video-camera"></i> AI Video Studio</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_avatars'); ?>"><i class="fa fa-user-circle"></i> AI Avatars</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/video_voices'); ?>"><i class="fa fa-volume-up"></i> AI Voices</a>
    </div>
  </div>
  <div class="sc-nav-group sc-nav-group-admin">
    <button type="button" class="sc-nav-heading" data-toggle="collapse" data-target="#scNavContent" aria-expanded="true"><i class="fa fa-folder-open-o"></i> Content, Reports &amp; Setup</button>
    <div id="scNavContent" class="sc-nav-links collapse in">
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/customer_packages'); ?>"><i class="fa fa-folder-open-o"></i> Customer Packages</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/field_verifications'); ?>"><i class="fa fa-check-circle"></i> Field Verification</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/project_handoffs'); ?>"><i class="fa fa-tasks"></i> Project Handoff</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/communications'); ?>"><i class="fa fa-comments"></i> Customer Communications</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/pages'); ?>"><i class="fa fa-file-text-o"></i> Website Pages</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/keywords'); ?>"><i class="fa fa-search"></i> SEO Keywords</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_training'); ?>"><i class="fa fa-graduation-cap"></i> AI Training</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/reports'); ?>"><i class="fa fa-bar-chart"></i> Reports</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/settings'); ?>"><i class="fa fa-cog"></i> Settings</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/health'); ?>"><i class="fa fa-heartbeat"></i> Health</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/help'); ?>"><i class="fa fa-question-circle"></i> Help</a>
    </div>
  </div>
</div>
