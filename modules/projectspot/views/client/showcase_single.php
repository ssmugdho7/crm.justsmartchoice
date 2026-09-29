<link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"
/>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>

<div class="panel_s">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div class="slick">
                    <?php
                    foreach ($project_data->images as $image) {
                        ?>
                        <a data-fancybox="gallery"
                           data-src="<?php echo module_dir_url('projectspot', 'uploads/gallery_images/' . $project_data->id . '/' . $image['image_url']) ?>">
                            <img style="width: 100%; max-height: 35rem"
                                 src="<?php echo module_dir_url('projectspot', 'uploads/gallery_images/' . $project_data->id . '/' . $image['image_url']) ?>"/>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <br>
                <h1><?php echo $project_data->project_name; ?></h1>
                <hr>
                <p><?php echo $project_data->project_description; ?></p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script src="
https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js
"></script>

<script>
    Fancybox.bind('[data-fancybox="gallery"]', {
        //
    });
    (function ($) {
        $(function () {


            $('.slick').slick({
                dots: true,
                infinite: true,
                speed: 150,
                slidesToShow: 1
            });


        });
    })(jQuery);
</script>