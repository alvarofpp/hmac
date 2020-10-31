<?php
/**
 * Copyright (C) 2020 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */

namespace App\Services;


use App\Classes\HMAC;
use DirectoryIterator;

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
}
