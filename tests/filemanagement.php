<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/Message.php';
require dirname(__DIR__) . '/FileManagement.php';

echo FileManagement::dirValidate(dirname(__DIR__) . '/huehuehue');
echo FileManagement::dirValidate(dirname(__DIR__) . '/test')."\n";

$filemanagement = new FileManagement(dirname(__DIR__) . '/test');