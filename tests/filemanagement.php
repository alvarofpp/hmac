<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/classes/Display.php';
require dirname(__DIR__) . '/classes/FileManagement.php';

use Classes\FileManagement;

echo FileManagement::dirValidate(dirname(__DIR__) . '/huehuehue');
echo FileManagement::dirValidate(dirname(__DIR__) . '/test')."\n";

$filemanagement = new FileManagement(dirname(__DIR__) . '/test');