<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
namespace Classes;

/**
* This class is used to handle the arguments sent by the terminal.
*/
class Argument
{
    /**
     * @var Display
     */
    protected $display;

    protected $args = [
        '-h' => "Show help.",
        '-i' => "Starts to save the directory specified in [dir].",
        '-t' => "Tracking of the directory specific in [dir].",
        '-d' => "Disable the guard of directory specific in [dir].",
    ];

    protected $longArgs = [
        '--help' => '-h',
    ];

    const SPACES = 12;

    public function __construct()
    {
        $this->display = new Display();
    }

    /**
     * Show the help message on the terminal.
     *
     * @return void
     */
    public function help()
    {
        $this->display->show('php guard.php [option] [dir]');
        $this->display->show('[option]:');
        $this->showArgs();
        $this->display->show('[dir]:');
        $this->display->show(str_repeat(' ', self::SPACES) . 'Directory to be guarded.');
    }

    /**
     * Show the arguments that are accepted by the program.
     *
     * @return void
     */
    private function showArgs()
    {
        foreach ($this->args as $arg => $description) {
            $arg = $this->treatmentArg($arg);
            $this->display->show($arg . $description);
        }
    }

    /**
     * Treatment of the argument to be exhibited in the terminal.
     *
     * @return string $arg Argument to exhibition
     */
    private function treatmentArg($arg)
    {
        // LongArgs
        $longArgs = array_keys($this->longArgs, $arg);
        $args = array_merge([$arg], $longArgs);

        // Spacing
        $args = ' '.implode(' ', $args);
        $args = str_pad($args, self::SPACES, ' ', STR_PAD_RIGHT);

        return $args;
    }

    /**
     * Show the help message on the terminal.
     *
     * @return boolean True if arguments are validated, false if arguments are not validated
     */
    public function validate($args)
    {
        $totalArgs = array_merge($this->args, $this->longArgs);

        foreach ($args as $arg) {
            if (!array_key_exists($arg, $totalArgs)) {
                $this->display->show('Invalid argument: "' . $arg . '"', 'error');
                return false;
            }
        }

        return true;
    }
}