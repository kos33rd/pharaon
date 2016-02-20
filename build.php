<?php


require __DIR__ . '/vendor/autoload.php';
require 'src/Builder.php';
require_once 'extract_phar.php';

$builder = new \Pharaon\Builder();
$builder->build('delta.phar', 'delta', 'tests/from', 'tests/to');
print('Build complete.'.PHP_EOL);
extract_phar('./delta.phar', './phar_content');
