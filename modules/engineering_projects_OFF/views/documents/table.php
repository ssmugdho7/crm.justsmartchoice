<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
  'name',
  'noc',
  'eng_letter',
  'site_insp',
  'permit'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'eng_documents';

$join = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
  $row = [];
  for ($i = 0; $i < count($aColumns); $i++) {
    $_data = $aRow[$aColumns[$i]];
    if ($aColumns[$i] == 'noc') {
      $_data = document_file($aRow['noc'], $aRow['folder_path'] ?? '');
    }
    if ($aColumns[$i] == 'eng_letter') {
      $_data = document_file($aRow['eng_letter'], $aRow['folder_path'] ?? '');
    }
    if ($aColumns[$i] == 'site_insp') {
      $_data = document_file($aRow['site_insp'], $aRow['folder_path'] ?? '');
    }
    if ($aColumns[$i] == 'permit') {
      $_data = document_file($aRow['permit'], $aRow['folder_path'] ?? '');
    }
    $row[] = $_data;
  }
  $options = icon_btn('engineering_projects/documents/document/' . $aRow['id'], 'pencil-square-o', 'btn-default', [
    'data-id' => $aRow['id'],

  ]);
  $row[] = $options .= icon_btn('engineering_projects/documents/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

  //$row['DT_RowClass'] = 'has-row-options';
  $output['aaData'][] = $row;
}
