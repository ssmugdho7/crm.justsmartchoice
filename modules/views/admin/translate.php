<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <h4
                    class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span>
                        <?= tourGuideHelper()->translate('tour_guide_steps_translations') . ': ' . $tour_guide->title; ?>
                    </span>
                </h4>

                <?= validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
                <?php $this->load->view('authentication/includes/alerts'); ?>

                <?= form_open($this->uri->uri_string(), ['id' => 'tours_form']); ?>

                <?php if (isset($tour_guide)) echo form_hidden('id', $tour_guide->id); ?>

                <!-- Row grid -->
                <div class="row">

                    <!-- Baseic toure info -->
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">

                                <?php \TourGuide\TourGuideUtils::renderView('translate', $handler['data']); ?>

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