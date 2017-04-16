<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
require __DIR__.'/../src/class/UserProvider.php';
require __DIR__.'/../src/class/UserRegister.php';
require __DIR__.'/../src/class/BookForms.php';
require __DIR__.'/../src/class/BookDisplay.php';
require __DIR__.'/../src/class/UserExtended.php';

$app->run();