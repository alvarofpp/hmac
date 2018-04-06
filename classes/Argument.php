<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
namespace Classes;

require_once 'Display.php';

/**
* This class is used to handle the arguments sent by the terminal.
*/
class Argument
{
    /**
     * Arguments accepted by the program.
     * @var array
     */
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

    /**
     * Long arguments accepted by the program.
     * @var array
     */
    protected $longArgs = [
        '--help' => [
            'argumentBase' => '-h',
        ],
    ];

    /**
     * @var Display
     */
    protected $display;

    /**
     * Spacing between arguments and their own descriptions.
     * @var int
     */
    const SPACES = 12;

    /**
     * Classification codes to strings passed by the terminal.
     * @var int
     */
    const ARG_VALUE = 0;
    const ARG_SIMPLE = 1;
    const ARG_LONG = 2;
    const ARG_OPTION = 3;

    /**
     * Argument constructor.
     */
    public function __construct()
    {
        $this->display = new Display();
    }

    /**
     * Shows the help message in terminal.
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
     * @param string $arg Argument that will be process
     * @return string $argShow Argument to exhibition
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

        $argsShow = array_merge([$arg], $longArgs);

        // Spacing
        $argShow = ' '.implode(' ', $argsShow);
        $argShow = str_pad($argShow, self::SPACES, ' ', STR_PAD_RIGHT);

        return $argShow;
    }

    /**
     * Verifies if argument accept value.
     *
     * @param string $arg Argument to be verified
     * @return boolean True if argument accepts value, False if argument not accepts value
     */
    private function acceptValue($arg)
    {
        $count = substr_count($arg, '-');

        if ($count == self::ARG_SIMPLE) {
            return isset($this->args[$arg]['acceptValue'])?$this->args[$arg]['acceptValue']:false;
        }

        $argument = $this->longArgs[$arg]['argumentBase'];
        return isset($this->args[$argument]['acceptValue'])?$this->args[$argument]['acceptValue']:false;
    }

    /**
     * Validates argument, verifying if argument is value or flag.
     *
     * @param string $arg Argument to be validated
     * @return boolean True if is value, False if is flag
     */
    private function validateValue($arg)
    {
        $count = substr_count(substr($arg, 0, 2), '-');

        return ($count == self::ARG_VALUE);
    }

    /**
     * Verifies if the next argument exists.
     *
     * @param array string $args Arguments
     * @param int $i Counter
     * @return boolean True if the next argument exists, False if the next argument not exists
     */
    private function nextArgExist($args, $i)
    {
        if (!(($i+1) < count($args))) {
            $this->display->show('The amount of arguments is incorrect for the correct execution of the program!', 'error');
            $this->display->show('Missing value of argument "' . $args[$i] . '".', 'error');
            return false;
        }

        return true;
    }

    /**
     * Validates arguments sent by the terminal.
     *
     * @param array string $args Arguments
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

            } elseif ($this->acceptValue($arg)) {
                if (!$this->nextArgExist($args, $i)) {
                    return false;
                }

                if (! $this->validateValue($args[++$i])) {
                    $this->display->show('Invalid value for argument: "' . $arg . '" => "' . $args[$i] . '"', 'error');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get argument.
     *
     * @param string $arg Argument
     * @return string The argument. If argument simple, returns himself, if argument long
     * verify if exists argument base: if exist returns argument base, if not exist returns argument long
     */
    public function getArg($arg)
    {
        $count = substr_count(substr($arg, 0, 2), '-');

        if ($count == self::ARG_SIMPLE) {
            return $arg;
        }

        return isset($this->longArgs[$arg]['argumentBase'])?$this->longArgs[$arg]['argumentBase']:$arg;
    }
}