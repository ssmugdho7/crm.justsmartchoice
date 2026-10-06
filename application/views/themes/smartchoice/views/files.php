<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 section-heading section-heading-files">
    <?= _l('customer_profile_files'); ?>
</h4>
<?php hooks()->do_action('after_customers_area_files_heading'); ?>
<div class="panel_s sc-files-page">
    <div class="panel-body">
        <?= form_open_multipart(site_url('clients/upload_files'), ['class' => 'dropzone', 'id' => 'files-upload']); ?>
        <input type="file" name="file" multiple class="hide" />
        <?= form_close(); ?>
        <?php hooks()->do_action('after_customers_area_files_dropzone'); ?>
        <div class="tw-mt-4 tw-flex tw-justify-end tw-items-center tw-space-x-2 tw-mb-5">
            <button class="gpicker" data-on-pick="customerFileGoogleDriveSave">
                <i class="fa-brands fa-google" aria-hidden="true"></i>
                <?= _l('choose_from_google_drive'); ?>
            </button>
            <?php if (get_option('dropbox_app_key') != '') { ?>
            <div id="dropbox-chooser-files"></div>
            <?php } ?>
        </div>
        <?php if (count($files) == 0) { ?>
        <hr class="hr-panel-heading" />
        <p class="tw-text-neutral-500">
            <?= _l('no_files_found'); ?> Upload a document using the area above. Files shared by the team will also appear here.
        </p>
        <?php } else { ?>
        <table class="table dt-table mtop15 table-files" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th class="th-files-file">
                        <?= _l('customer_attachments_file'); ?>
                    </th>
                    <th class="th-files-date-uploaded">
                        <?= _l('file_date_uploaded'); ?>
                    </th>
                    <?php if (get_option('allow_contact_to_delete_files') == 1) { ?>
                    <th class="th-files-option">
                        <?= _l('options'); ?>
                    </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file) { ?>
                <tr>
                    <td>
                        <?php
                      $url    = site_url() . 'download/file/client/';
                    $path     = get_upload_path_by_type('customer') . $file['rel_id'] . '/' . $file['file_name'];
                    $is_image = false;
                    $is_external = !empty($file['external']);
                    $can_preview = false;
                    if (!$is_external) {
                        $attachment_url = $url . $file['attachment_key'];
                        $extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                        $is_image = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                        $can_preview = $is_image || $extension === 'pdf';
                        $view_url = $attachment_url . '?preview=1';
                        $img_url = $view_url;
                    } elseif (isset($file['external']) && ! empty($file['external'])) {
                        if (! empty($file['thumbnail_link'])) {
                            $is_image = true;
                            $img_url  = optimize_dropbox_thumbnail($file['thumbnail_link']);
                        }
                        $attachment_url = $file['external_link'];
                        $view_url = $attachment_url;
                        $can_preview = true;
                    }
                    if ($is_image) {
                        echo '<div class="sc-file-photo">';
                    }
                    ?>
                        <a href="<?= e($can_preview ? $view_url : $attachment_url); ?>"
                            <?= $can_preview ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
                            class="display-block mbot5">
                            <?php if ($is_image) { ?>
                            <div class="sc-file-thumbnail">
                                <img src="<?= e($img_url); ?>" alt="<?= e($file['file_name']); ?>" loading="lazy" decoding="async">
                            </div>
                            <?php } else { ?>
                            <i
                                class="<?= get_mime_class($file['filetype']); ?>"></i>
                            <?= e($file['file_name']); ?>
                            <?php } ?>
                        </a>
                        <?php if ($is_image) {
                            echo '</div>';
                        } ?>
                        <?php if ($is_image) { ?><strong class="sc-file-name"><?= e($file['file_name']); ?></strong><?php } ?>
                        <div class="sc-file-actions">
                            <?php if ($can_preview) { ?>
                            <a class="btn sc-file-view" href="<?= e($view_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="View <?= e($file['file_name']); ?>"><i class="fa fa-eye" aria-hidden="true"></i> View</a>
                            <?php } ?>
                            <a class="btn btn-default" href="<?= e($attachment_url); ?>" <?= $is_external ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> aria-label="Download <?= e($file['file_name']); ?>"><i class="fa fa-download" aria-hidden="true"></i> Download</a>
                        </div>
                    </td>
                    <td
                        data-order="<?= e($file['dateadded']); ?>">
                        <?= e(_dt($file['dateadded'])); ?>
                    </td>
                    <?php if (get_option('allow_contact_to_delete_files') == 1) { ?>
                    <td>
                        <?php if ($file['contact_id'] == get_contact_user_id()) { ?>
                        <a href="<?= site_url('clients/delete_file/' . $file['id'] . '/general'); ?>"
                            class="btn btn-danger btn-icon _delete file-delete" aria-label="Delete <?= e($file['file_name']); ?>"><i class="fa fa-remove"></i></a>
                        <?php } ?>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
        <?php hooks()->do_action('after_customers_area_files'); ?>
    </div>
</div>
