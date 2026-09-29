<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['training_manual/customer-books'] = 'Customer_books/index';
$route['training_manual/customer-books/article/(:num)'] = 'Customer_books/article/$1';

$route['training_manual/(:any)'] = 'Training_manual/index/$1';
