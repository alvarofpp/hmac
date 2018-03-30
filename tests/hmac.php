<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/classes/HMAC.php';
require dirname(__DIR__) . '/classes/Message.php';
require dirname(__DIR__) . '/classes/FileManagement.php';

use Classes\HMAC;

$hmac = new HMAC(dirname(__DIR__) . '/test');
//$hmac->info();

$hmac->filesTracking();