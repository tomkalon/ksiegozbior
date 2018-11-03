<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\CsrfServiceProvider;

$app = new Application();
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'    => array(
    'driver'        => 'pdo_mysql',
    'dbname'        => 'booklist-old',
    'dbhost'        => 'localhost',
    'user'          => 'root',
    'password'      => '',
    'charset'       => 'utf8'   
    )
));

$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'pl',
    'locale_fallbacks' => array('en'),
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/login$',
            'anonymous' => true,
        ),
        'default' => array(
            'pattern' => '^/.*$',
            'anonymous' => true,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/user/login_check',
                'default_target_path' => '/logged',
            ),
            'logout' => array(
                'logout_path' => '/user/logout',
                'invalidate_session' => true,
            ),
            'remember_me' => array(
                'key'                => 'fjgkr5owscmaQplW$#OL',
                'always_remember_me' => true,
            ),
            'users' => function () use ($app) {
                return new UserProvider($app['db']);
            },
        ),
    ),
    'security.access_rules' => array(
        array('^/user', 'ROLE_USER')
    )
    
));
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\RememberMeServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new CsrfServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

return $app;
