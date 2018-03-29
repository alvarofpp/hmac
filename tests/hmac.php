<?php
/**
 * Created by PhpStorm.
 * User: roleta
 * Date: 27/03/18
 * Time: 23:09
 */

require dirname(__DIR__) . '/HMAC.php';
require dirname(__DIR__) . '/Message.php';
require dirname(__DIR__) . '/FileManagement.php';

$hmac = new HMAC(dirname(__DIR__) . '/test');
//$hmac->info();

$hmac->filesTracking();