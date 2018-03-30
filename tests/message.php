<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

require dirname(__DIR__) . '/classes/Message.php';

use Classes\Message;

(new Message())->show('DELETE', 'delete');
(new Message())->show('ADD', 'add');
(new Message())->show('ALTER', 'alter');
(new Message())->show('WARNING', 'warning');

(new Message())->show('ERROR', 'error');
(new Message())->show('SUCCESS', 'success');
(new Message())->show('ALERT', 'alert');

(new Message())->show('NOT EXIST', 'hueragem');
