<?php
/**
 * User: alvarofpp
 * Date: 27/03/18
 * Time: 20:30
 */

/**
* This class shows messages in the terminal of different ways.
*/
class Message
{
    protected $colors = [
        'delete' => "\e[0;31m",
        'add' => "\e[0;32m",
        'warning' => "\e[0;33m",
        'alter' => "\e[0;36m",
        'error' => "\e[1;31m",
        'success' => "\e[1;32m",
        'alert' => "\e[1;33m",
    ];

    /**
     * Show the message in terminal.
     *
     * @param string $msg Message you want to display in the terminal
     * @param string null $cod Code of colors that you want the message to have
     * @return void
     */
    public function show($msg, $cod = null)
    {
        if (isset($cod) && array_key_exists($cod, $this->colors)) {
            echo $this->colors[$cod];
        }

        echo $msg . "\033[0m\n";
    }
}