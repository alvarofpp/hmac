<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
namespace Classes;

use DirectoryIterator;

/**
* This class performs operations for file management.
*/
class FileManagement
{
    protected $dir;
    protected $file;
    protected $jsonData;
    protected $filesData;

    const JSON_PATH = ".guard/";

    function __construct($dir)
    {
        if (!$this->dirExist($dir)) {
            exit();
        }

        $this->filesData = [];
        $this->loadData();
    }

    /**
     * Loads JSON file with directory data.
     *
     * @return void
     */
    private function loadData()
    {
        $this->dirGuardExist();

        if (!file_exists($this->file)) {
            return;
        }

        $jsonFile = file_get_contents($this->file);
        $this->jsonData = json_decode($jsonFile, true);
    }

    /**
     * Verifies that program directory exists.
     *
     * @return bool True if exists, false if not exists
     */
    private function dirGuardExist()
    {
        if (!file_exists(self::JSON_PATH)) {
            mkdir(self::JSON_PATH);
            return false;
        }

        return true;
    }

    /**
     * Verifies if the directory exists.
     *
     * @param string $dir Directory you want to check if exists
     * @return bool True if exists, false if not exists
     */
    public function dirExist($dir)
    {
        if (!is_dir($dir)) {
            (new Display())->show('Directory does not exist!', 'error');
            return false;
        }

        $this->dir = substr($dir, -1)=='/'?$dir:$dir.'/';
        $this->file = dirname(dirname(__FILE__)) . '/' . self::JSON_PATH  . md5($this->dir) . ".json";

        return true;
    }

    /**
     * Verifies if the directory exists.
     *
     * @param string $dir Directory you want to check if exists
     * @return bool True if exists, false if not exists
     */
    public static function dirValidate($dir)
    {
        if (!is_dir($dir)) {
            (new Display())->show('Directory does not exist!', 'error');
            return false;
        }

        return true;
    }

    /**
     * Go through the files in directory and executes hmac for each one.
     *
     * @param HMAC $hmac HMAC class for apply function 'execute'
     * @param string $path Files path
     * @return void
     */
    public function through(HMAC $hmac, $path = null)
    {
        if (!isset($path)) {
            $path = $this->dir;
        }

        $dir = new DirectoryIterator($path);

        foreach ($dir as $file) {
            $filePath = $path . $file->getFilename();

            if (!$file->isDot() && $file->isDir()) {
                $this->through($hmac, $filePath . '/');

            } elseif (!$file->isDot()) {
                array_push($this->filesData, [
                    "file" => $file->getFilename(),
                    "dir" => $path,
                    "hmac" => $hmac->execute(md5_file($filePath)),
                ]);
            }
        }
    }

    /**
     * Saves HMAC of the files in JSON file.
     *
     * @return void
     */
    public function save()
    {
        $this->dirGuardExist();

        $json = json_encode($this->filesData);

        $file = fopen($this->file, "wb");
        fwrite($file, $json);
        fclose($file);

        (new Display())->show('Files saved!', 'success');
    }

    /**
     * Searches the filename in jsonData.
     *
     * @param string $filename The filename you want to search
     * @return int -1 if the filename is not in jsonData or return array key if exists in jsonData
     */
    public function search($filename)
    {
        if (!isset($this->jsonData)) {
            return -1;
        }

        foreach ($this->jsonData as $key => $array) {
            if ($array['file'] == $filename) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * Disables guard of directory files.
     *
     * @return void
     */
    public function disable()
    {
        if ($this->dirGuardExist() && file_exists($this->file)) {
            unlink($this->file);
            (new Display())->show('HMAC files from the ' . $this->dir . ' directory is no longer being saved.', 'warning');
        } else {
            (new Display())->show($this->dir . ' directory was not saved by program.', 'alert');
        }
    }

    /**
     * Performs tracking of directory files.
     *
     * @return void
     */
    public function tracking()
    {
        $display = new Display();

        foreach ($this->filesData as $data) {
            $key = $this->search($data['file']);

            if (!($key == -1)) {
                $fileData = $this->jsonData[$key];

                // Altered files
                if (!($fileData['hmac'] == $data['hmac'])) {
                    $display->show('File ' . $fileData['file'] . ' has been altered!', 'alter');
                }

                unset($this->jsonData[$key]);

            } else {
                // New files
                $display->show('File ' . $data['file'] . ' has been added!', 'add');
            }
        }

        // Files that were deleted
        if (!empty($this->jsonData)) {
            foreach ($this->jsonData as $data) {
                $display->show('File ' . $data['file'] . ' has been deleted!', 'delete');
            }
        }

        $this->save();

        $display->show('Tracking completed!', 'alert');
    }
}