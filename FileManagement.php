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
        $this->dirGuardaExist();

        if (!file_exists($this->file)) {
            return;
        }

        $jsonFile = file_get_contents($this->file);
        $this->jsonData = json_decode($jsonFile, true);
    }

    /**
     * Verify that directory of program exist.
     *
     * @return void.
     */
    private function dirGuardaExist()
    {
        if (!file_exists(self::JSON_PATH)) {
            mkdir(self::JSON_PATH);
        }
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

        $this->dir = $dir;
        $this->file = self::JSON_PATH . $dir . ".json";

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
        $this->dirGuardaExist();

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
}