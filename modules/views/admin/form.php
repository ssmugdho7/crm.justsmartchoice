<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
// Determine if we are editing an existing tour or creating a new one
$value_title = isset($tour_guide) ? $tour_guide->title : '';
$value_description = isset($tour_guide) ? $tour_guide->description : '';
$value_status = isset($tour_guide) ? $tour_guide->status : 'active';
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <h4
                    class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span>
                        <?= isset($tour_guide) ? tourGuideHelper()->translate('tour_guide_edit_tour') . ':' . $tour_guide->title : tourGuideHelper()->translate('tour_guide_create_tour'); ?>
                    </span>
                </h4>

                <?= validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
                <?php $this->load->view('authentication/includes/alerts'); ?>

                <?= form_open($this->uri->uri_string(), ['id' => 'tour-guide-form']); ?>

                <?php if (isset($tour_guide)) echo form_hidden('id', $tour_guide->id); ?>

                <!-- Row grid -->
                <div class="row">

                    <!-- Baseic toure info -->
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">

                                <?= \TourGuide\TourGuideUIHelper::renderForm(
                                    (array)($tour_guide ?? []),
                                    '</div></div><div class="tw-mt-4 tw-mb-4"><hr /></div><div class="panel_s"><div class="panel-body">'
                                ); ?>

                                <div class="text-right">
                                    <button type="submit"
                                        data-loading-text="<?= tourGuideHelper()->translate('tour_guide_save'); ?>..."
                                        data-form="#toures_form" class="btn btn-primary mtop15
                                    mbot15"><?= tourGuideHelper()->translate('tour_guide_save'); ?></button>
                                    <a href="<?= tourGuideHelper()->adminUrl(); ?>" class="btn btn-secondary">
                                        <?= tourGuideHelper()->translate('tour_guide_cancel'); ?>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row-->

                <?= form_close(); ?>

            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>