<?php
/**
 * Author: Alexander Zharinov <zharinovalex88@gmail.com>
 */

require_once __DIR__ . '/core/Autoloader.php';

$loader = new \Core\Autoloader();

$loader
    ->register()
    ->addNamespace('Core', 'app/core')
    ->addNamespace('Src', 'app/src');

