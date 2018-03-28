<?php
/**
 * User: alvarofpp
 * Date: 27/03/18
 * Time: 20:25
 */

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
        $this->filesData = [];

        if ($this->dirExist($dir)) {
            $this->loadData();
        }
    }

    /**
     * Load JSON file data of directory.
     *
     * @return void.
     */
    private function loadData()
    {
        $this->dirGuardExist();

        if (!$this->fileExist()) {
            return;
        }

        $jsonFile = file_get_contents($this->file);
        $this->jsonData = json_decode($jsonFile, true);
    }

    /**
     * Load JSON file data of directory.
     *
     * @return bool True if file exist, false if not exist.
     */
    private function fileExist()
    {
        return file_exists($this->file)?true:false;
    }

    /**
     * Verify that directory of program exist.
     *
     * @return bool True if there is, false if not there is.
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
     * Verify that directory exist.
     *
     * @param string $dir Directory that you want to check if it exists.
     * @return bool True if there is, false if not there is.
     */
    public function dirExist($dir)
    {
        if (!is_dir($dir)) {
            (new Message())->show('Directory does not exist.', 'error');
            return false;
        }

        $this->dir = substr($dir, -1)=='/'?$dir:$dir.'/';
        $this->file = $this->dir . self::JSON_PATH  . md5($this->dir) . ".json";

        return true;
    }

    /**
     * Verify that directory exist.
     *
     * @param string $dir Directory that you want to check if it exists.
     * @return bool True if there is, false if not there is.
     */
    public static function dirValidate($dir)
    {
        if (!is_dir($dir)) {
            (new Message())->show('Directory does not exist.', 'error');
            return false;
        }

        return true;
    }

    /**
     * Go through the files in the directory and execute hmac for each one.
     *
     * @param HMAC $hmac HMAC class for apply function execute.
     * @param string $path Paths of files.
     * @return void.
     */
    public function through(HMAC $hmac, $path = null)
    {
        if (!isset($dir)) {
            $path = $this->dir;
        }

        $dir = new DirectoryIterator($path);

        foreach ($dir as $file) {
            $filePath = $path . $file->getFilename();

            if (!$file->isDot() && $file->isDir()) {
                $this->through($hmac, $filePath . '/');

            } elseif (!$file->isDot()) {
                $hmac = $hmac->execute(md5_file($filePath));

                array_push($this->filesData, [
                    "file" => $file->getFilename(),
                    "dir" => $path,
                    "hmac" => $file->getFilename(),
                ]);
            }
        }
    }

    /**
     * Save HMAC of the files in JSON file.
     *
     * @return void.
     */
    public function save()
    {
        $this->dirGuardExist();

        $json = json_encode($this->filesData);

        $file = fopen($this->file, "wb");
        fwrite($file, $json);
        fclose($file);

        (new Message())->show('Files saved!', 'success');
    }

    /**
     * Search filename in jsonData.
     *
     * @param string $filename The filename you want to search.
     * @return array|int -1 if filename is not in jsonData or return array with values.
     */
    public function search($filename)
    {
        if (!isset($this->jsonData)) {
            return -1;
        }

        foreach ($this->jsonData as $key => $array) {
            if ($array['file'] == $filename) {
                return [
                    'key' => $key,
                    'hmac' => $array['hmac'],
                ];
            }
        }

        return -1;
    }

    /**
     * Remove guard of files of the directory.
     *
     * @return void.
     */
    public function remove()
    {
        if ($this->dirGuardExist() && $this->fileExist()) {
            unlink($this->file);
            (new Message())->show('HMAC files from the ' . $this->dir . ' directory are no longer being saved.', 'warning');
        } else {
            (new Message())->show($this->dir . ' directory was not saved by program.', 'alert');
        }
    }

    public function getVars()
    {
        return [
            'dir' => $this->dir,
            'file' => $this->file,
            'jsonData' => $this->jsonData,
            'filesData' => $this->filesData,
        ];
    }
}