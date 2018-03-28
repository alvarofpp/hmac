<?php
/**
 * User: alvarofpp
 * Date: 27/03/18
 * Time: 20:30
 */

namespace Hmac;

/**
* This class shows messages in the terminal of different ways.
*/
class Message
{
    protected $colors = [
        'delete' => '\e[1;31m',
        'add' => '\e[1;32m',
        'alter' => '\e[1;36m',
        'alert' => '\e[1;33m',
    ];

    /**
     * Show the message in terminal.
     *
     * @param string $msg Message you want to display in the terminal.
     * @param string null $cod Code of colors that you want the message to have.
     * @return void.
     */
    public function show($msg, $cod = null)
    {
        if (isset($cod)) {
            echo $this->colors[$cod];
        }
        echo $msg."\n";
    }
}