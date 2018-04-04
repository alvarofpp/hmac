<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
require 'classes/Argument.php';
require 'classes/HMAC.php';

use Classes\Argument;
use Classes\HMAC;

$argument = new Argument();

// Remove filename
$args = array_slice($argv, 1);

// Validate arguments
if (count($args) == 0) {
    $argument->help();
    exit();
} elseif (!$argument->validate($args)) {
    exit();
}

// Execute arguments
for ($i = 0; $i < count($args); $i++) {
    if ($argument->getArg($args[$i]) == '-h') {
        $argument->help();
        continue;
    }

    $hmac = new HMAC($args[++$i]);

    switch ($argument->getArg($args[$i-1])) {
        case '-i':
            $hmac->firstFilesTracking();
            break;
        case '-t':
            $hmac->filesTracking();
            break;
        case '-d':
            $hmac->disable();
            break;
    }
}