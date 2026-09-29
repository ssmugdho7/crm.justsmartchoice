<?php

exit('COMMENT ME TO TEST THE EXAMPLES!');

require_once __DIR__ . '/../vendor/autoload.php';

// configuration object
        // configuration object
$config = new \EmsApi\Config([
    'apiUrl'    => $apiUrl,
    'apiKey'    => $settings['public_key'],

    // components
    'components' => [
        'cache' => [
            'class'     => 'MailWizzApi_Cache_File',
            'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
        ]
    ],
]);

// now inject the configuration and we are ready to make api calls
\EmsApi\Base::setConfig($config);

// start UTC
date_default_timezone_set('UTC');
