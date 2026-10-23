<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/App_Debug_context.php';

/** Include language/module log_message errors as well as PHP exceptions in page diagnostics. */
class App_Log extends CI_Log
{
    public function write_log($level, $msg)
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && strtoupper($level) === 'ERROR') {
            if (strpos($msg, '[visit=') === false) { $msg .= ' [visit=' . App_Debug_context::visit_id() . ']'; }
            if (strpos($msg, '[route=') === false) { $msg .= ' [route=' . App_Debug_context::route() . ']'; }
        }
        return parent::write_log($level, $msg);
    }
}
