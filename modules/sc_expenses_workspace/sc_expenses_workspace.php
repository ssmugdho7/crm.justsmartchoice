<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Expenses Workspace
Description: Emerald expense directory, native row actions, tax-inclusive summaries and secure receipt previews.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/

register_language_files('sc_expenses_workspace', ['sc_expenses_workspace']);
// Uses the five native Expenses capabilities (View Own, View, Create, Edit, Delete),
// already registered by Perfex. No separate permission grants or schema are needed.
require_once __DIR__ . '/includes/expenses.php';
