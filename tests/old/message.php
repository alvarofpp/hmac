<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/classes/Display.php';

use Classes\Display;

(new Display())->show('DELETE', 'delete');
(new Display())->show('ADD', 'add');
(new Display())->show('ALTER', 'alter');
(new Display())->show('WARNING', 'warning');

(new Display())->show('ERROR', 'error');
(new Display())->show('SUCCESS', 'success');
(new Display())->show('ALERT', 'alert');

(new Display())->show('NOT EXIST', 'hueragem');
