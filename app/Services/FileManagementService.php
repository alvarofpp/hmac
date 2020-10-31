<?php
/**
 * Copyright (C) 2020 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

namespace App\Services;


use App\Classes\HMAC;
use DirectoryIterator;
use Illuminate\Support\Collection;

/**
 * This class performs operations for file management.
 */
class FileManagementService
{
    protected $dir;
    protected $file;
    protected $jsonData;
    protected $filesData;

    /**
     * Go through the files in directory and executes hmac for each one.
     *
     * @param HMAC $hmac HMAC class for apply function 'execute'
     * @param string $path Files path
     * @return \Illuminate\Support\Collection
     */
    public static function through(HMAC $hmac, $path = null)
    {
        $dir = new DirectoryIterator($path);
        $files = collect();

        foreach ($dir as $file) {
            $filePath = $path . $file->getFilename();

            if (!$file->isDot() && $file->isDir()) {
                $files->merge(FileManagementService::through($hmac, $filePath . '/'));

            } elseif (!$file->isDot()) {
                $absolutePath = explode('/', $file->getRealPath());
                unset($absolutePath[ count($absolutePath)-1 ]);
                $absolutePath = implode('/', $absolutePath);

                $files->push([
                    "filename" => $file->getFilename(),
                    "dir_absolute_path" => $absolutePath,
                    "dir" => $path,
                    "hmac" => $hmac->execute(md5_file($filePath)),
                ]);
            }
        }

        return $files;
    }

    /**
     * Performs tracking of directory files.
     *
     * @param Collection $files
     * @return void
     */
    public static function tracking(Collection $files)
    {
        foreach ($files as $data) {
            $key = $this->search(($data['dir']. $data['file']));

            if (!($key == -1)) {
                $fileData = $this->jsonData[$key];

                // Altered files
                if (!($fileData['hmac'] == $data['hmac'])) {
                    $display->show('File ' . ($fileData['dir'] . $fileData['file']) . ' has been altered!', 'alter');
                }

                unset($this->jsonData[$key]);

            } else {
                // New files
                $display->show('File ' . ($data['dir'] . $data['file']) . ' has been added!', 'add');
            }
        }

        // Files that were deleted
        if (!empty($this->jsonData)) {
            foreach ($this->jsonData as $data) {
                $display->show('File ' . ($data['dir'] . $data['file']) . ' has been deleted!', 'delete');
            }
        }

        $display->show('Tracking completed!', 'alert');
        $this->save();
    }
}
