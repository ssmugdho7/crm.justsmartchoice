<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sc_core_option_checked($optionName)
{
    return get_option($optionName) === '1';
}
