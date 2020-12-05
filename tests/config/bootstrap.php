<?php

require dirname(__DIR__) . '/../vendor/autoload.php';
$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Tests\\", dirname(__DIR__), true);
$classLoader->register();