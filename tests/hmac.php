<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/HMAC.php';
require dirname(__DIR__) . '/Message.php';
require dirname(__DIR__) . '/FileManagement.php';

$hmac = new HMAC(dirname(__DIR__) . '/test');
//$hmac->info();

$hmac->filesTracking();