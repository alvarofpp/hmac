<?php
/**
 * Created by PhpStorm.
 * User: roleta
 * Date: 27/03/18
 * Time: 23:09
 */

require dirname(__DIR__) . '/Message.php';
require dirname(__DIR__) . '/FileManagement.php';

echo FileManagement::dirValidate(dirname(__DIR__) . '/huehuehue');
echo FileManagement::dirValidate(dirname(__DIR__) . '/test')."\n";

$filemanagement = new FileManagement(dirname(__DIR__) . '/test');