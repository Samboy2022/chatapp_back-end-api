#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

// Register The Auto Loader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the command...
$status = (require_once __DIR__.'/bootstrap/app.php')
    ->handleCommand(request());

if ($status) {
    exit($status);
}