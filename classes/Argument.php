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
        '-h' => [
            'description' => 'Show help.',
        ],
        '-i' => [
            'acceptValue' => true,
            'description' => 'Starts the guard of specified directory in [dir].',
        ],
        '-t' => [
            'acceptValue' => true,
            'description' => 'Tracking of the specified directory in [dir].',
        ],
        '-d' => [
            'acceptValue' => true,
            'description' => 'Disables the guard of specified directory in [dir].',
        ],
    ];

    protected $longArgs = [
        '--help' => [
            'argumentBase' => '-h',
        ],
    ];

    const SPACES = 12;

    const ARG_VALUE = 0;
    const SIMPLE_ARG = 1;
    const LONG_ARG = 2;

    public function __construct()
    {
        $this->display = new Display();
    }

    /**
     * Shows the help message in terminal.
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
     * Shows the arguments that are accepted by the program.
     *
     * @return void
     */
    private function showArgs()
    {
        foreach ($this->args as $arg => $values) {
            $arg = $this->treatmentArg($arg);
            $this->display->show($arg . $values['description']);
        }
    }

    /**
     * Treats the argument to be exhibited in the terminal.
     *
     * @return string $arg Argument to exhibition
     */
    private function treatmentArg($arg)
    {
        // LongArgs
        $longArgs = [];
        foreach ($this->longArgs as $key => $values) {
            if ($values['argumentBase'] == $arg) {
                array_push($longArgs, $key);
            }
        }

        $args = array_merge([$arg], $longArgs);

        // Spacing
        $args = ' '.implode(' ', $args);
        $args = str_pad($args, self::SPACES, ' ', STR_PAD_RIGHT);

        return $args;
    }

    private function acceptValue($arg)
    {
        $count = substr_count($arg, '-');

        if ($count == self::SIMPLE_ARG) {
            return isset($this->args[$arg]['acceptValue'])?$this->args[$arg]['acceptValue']:false;
        }

        $argument = $this->longArgs[$arg]['argumentBase'];
        return isset($this->args[$argument]['acceptValue'])?$this->args[$argument]['acceptValue']:false;
    }

    private function validateValue($arg)
    {
        $count = substr_count(substr($arg, 0, 2), '-');

        return ($count == self::ARG_VALUE);

    }

    /**
     * Validates arguments sent by the terminal.
     *
     * @return bool True if arguments are valid, false if arguments are not valid
     */
    public function validate($args)
    {
        $totalArgs = array_merge($this->args, $this->longArgs);

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if (!array_key_exists($arg, $totalArgs)) {
                $this->display->show('Invalid argument: "' . $arg . '"', 'error');
                return false;

            } elseif ($this->acceptValue($arg) && (! $this->validateValue($args[++$i]))) {
                $this->display->show('Invalid value for argument: "' . $arg . '" => "' . $args[$i] . '"', 'error');
                return false;

            }
        }

        return true;
    }
}