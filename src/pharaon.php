<?php

$baseDir = dirname(dirname(__FILE__));
require $baseDir . '/vendor/autoload.php';
use Pharaon\Application as App;

$app = new App('Pharaon', '0.0.1');
$app->run();
