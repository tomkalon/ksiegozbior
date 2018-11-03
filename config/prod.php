<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
//$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app ['debug'] = true;

require __DIR__.'/mailer.php';

//$app['swiftmailer.options'] = array(
//    'host' => '',
//    'port' => '587',
//    'username' => '',
//    'password' => '',
//    'encryption' => 'tls',
//);
//$app['swiftmailer.use_spool'] = false;
