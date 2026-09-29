<?php defined('BASEPATH') or exit('No direct script access allowed');
$route['prchat/Prchat_ClientsController/pusherCustomersAuth'] = 'prchat_ClientsController/pusherCustomersAuth';
$route['prchat/client/pusher-auth'] = 'prchat_ClientsController/pusherCustomersAuth';



// Smart Choice stable AI endpoints (Perfex HMVC compatibility aliases).
$route['admin/prchat/improve-message'] = 'prchat/Prchat_Controller/improve_message';
$route['prchat/improve-message'] = 'Prchat_Controller/improve_message';

// Smart Choice v2.1.8 compatibility routes.
$route['prchat/Prchat_ClientsController/getClientUnreadMessages'] = 'Prchat_Controller/getUnreadCounts';
$route['prchat/Prchat_Controller/improve_message'] = 'Prchat_Controller/improve_message';

$route['prchat/Prchat_ClientsController/getClientContactPreviews'] = 'Prchat_Controller/getClientContactPreviews';
