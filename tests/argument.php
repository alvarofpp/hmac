<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/classes/Display.php';
require dirname(__DIR__) . '/classes/Argument.php';

use Classes\Argument;

$argument = new Argument();

// $argument->validate(['-h', '--help', '---help']);

$argument->help();