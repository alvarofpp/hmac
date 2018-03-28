<?php
/**
 * Created by PhpStorm.
 * User: roleta
 * Date: 27/03/18
 * Time: 23:09
 */

require dirname(__DIR__) . '/Message.php';


(new Message())->show('DELETE', 'delete');
(new Message())->show('ADD', 'add');
(new Message())->show('ALTER', 'alter');
(new Message())->show('WARNING', 'warning');
(new Message())->show('SUCCESS', 'success');
(new Message())->show('ALERT', 'alert');
(new Message())->show('NOT EXIST', 'hueragem');
