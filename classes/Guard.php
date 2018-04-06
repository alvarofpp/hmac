<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
namespace Classes;

require_once 'HMAC.php';
require_once 'Display.php';
require_once 'FileManagement.php';

/**
* This class performs the operations required to run the guard program.
*/
class Guard
{
    /**
     * @var HMAC
     */
    protected $hmac;

    /**
     * @var Display
     */
    protected $display;

    /**
     * @var FileManagement
     */
    protected $fileManagement;

    function __construct()
    {
        $this->display = new Display();
    }

    /**
     * Performs tracking for the first time.
     *
     * @return void
     */
    public function initGuard()
    {
        if ($this->fileManagement->fileGuardExist()) {
            $this->display->show('Directory is already guarded by the program.', 'warning');
            return;
        }

        $this->fileManagement->through($this);
        $this->fileManagement->firstTracking();
    }

    /**
     * Performs tracking.
     *
     * @return void
     */
    public function filesTracking()
    {
        if (! $this->fileManagement->fileGuardExist()) {
            $this->display->show('Directory is NOT already protected by the program.', 'warning');
            return;
        }

        $this->fileManagement->through($this->hmac);
        $this->fileManagement->tracking();
    }

    /**
     * Disables guard of directory files.
     *
     * @return void
     */
    public function disable()
    {
        if (! $this->fileManagement->fileGuardExist()) {
            $this->display->show('Directory is NOT already protected by the program.', 'warning');
            return;
        }

        $this->fileManagement->disable();
    }
}