<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['feedbacks/feedback/(:num)/(:any)'] = 'participate/index/$1/$2';
