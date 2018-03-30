<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/Argument.php';

$argument = new Argument();

$argument->getTypeArg('-h');
$argument->getTypeArg('--help');
$argument->getTypeArg('h');
$argument->getTypeArg('---help');