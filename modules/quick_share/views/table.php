<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'file_key',
    'file_path',
    'file_size',
    'password',
    'share_type',
    'auto_destroy',
    'status',
    'created_at',
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'quick_share_downloads WHERE download_id=' . db_prefix() . 'quick_share_uploads.id) as totalDownloads',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'quick_share_uploads';

$join = [];
$where = [];

array_push($where, 'AND user_id ='. get_staff_user_id());

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['file_path'];
        $row[] = bytesToSize('', $aRow['file_size']);

        $status = '';
        if ($aRow['status'] == 0) {
            $status = '<span class="label project-status-2" style="color:#2563eb;border:1px solid #a8c1f7;background: #f6f9fe;">Available</span>';
        }
        if ($aRow['status'] == 1) {
            $status = '<span class="label project-status-5" style="color:#94a3b8;border:1px solid #d4dae3;background: #fbfcfc;">Not Available</span>';
        }

        $row[] = $status;

        $autoDestroy = '';
        if ($aRow['auto_destroy'] == 0) {
            $autoDestroy = _l('no');
        }
        if ($aRow['auto_destroy'] == 1) {
            $autoDestroy = _l('yes');
        }
        $row[] = $autoDestroy;

        $shareType = '';
        if ($aRow['share_type'] == 0) {
            $shareType = _l('qs_file_share_type_email');
        }
        if ($aRow['share_type'] == 1) {
            $shareType = _l('qs_file_share_type_link');
        }
        $row[] = $shareType;

        $isPassword = '';
        if (!empty($aRow['password'])) {
            $isPassword = _l('yes');
        } else {
            $isPassword = _l('no');
        }
        $row[] = $isPassword;

        $row[] = $aRow['totalDownloads'];

        $row[] = $aRow['created_at'];

        $downloadLink = base_url('quick_share/download/file/'.$aRow['file_key']);

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" onclick="copyFileLink(this,`'.$downloadLink.'`); return false;">
                    <i class="fa-regular fa-copy fa-lg"></i>
                </a>';

        $options .= '<a href="' . admin_url('quick_share/delete_file/' . $aRow['id']) . '"
                class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                    <i class="fa-regular fa-trash-can fa-lg"></i>
                </a>';
        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
