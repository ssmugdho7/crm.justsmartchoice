<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Insert the item table once, supporting both editor placeholder spellings. */
function sc_proposal_items_content($content, $items)
{
    $inserted = false;
    $content = preg_replace_callback('/\{\{\s*proposal_items\s*\}\}|\{\s*proposal_items\s*\}/i', function () use (&$inserted, $items) {
        if ($inserted) {
            return '';
        }
        $inserted = true;
        return $items;
    }, (string) $content);

    return $inserted ? $content : $content . '<br /><br />' . $items;
}
