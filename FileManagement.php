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
        if (!$this->dirExist($dir)) {
            exit();
        }

        $this->filesData = [];
        $this->loadData();
    }

    /**
     * Load JSON file data of directory.
     *
     * @return void
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
     * @return bool True if file exist, false if not exist
     */
    private function fileExist()
    {
        return file_exists($this->file)?true:false;
    }

    /**
     * Verify that directory of program exist.
     *
     * @return bool True if there is, false if not there is
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
     * @param string $dir Directory that you want to check if it exists
     * @return bool True if there is, false if not there is
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
     * @param string $dir Directory that you want to check if it exists
     * @return bool True if there is, false if not there is
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
     * @param HMAC $hmac HMAC class for apply function execute
     * @param string $path Paths of files
     * @return void
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

                array_push($this->filesData, [
                    "file" => $file->getFilename(),
                    "dir" => $path,
                    "hmac" => $hmac->execute(md5_file($filePath)),
                ]);
            }
        }
    }

    /**
     * Save HMAC of the files in JSON file.
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

        (new Message())->show('Files saved!', 'success');
    }

    /**
     * Search filename in jsonData.
     *
     * @param string $filename The filename you want to search
     * @return array|int -1 if filename is not in jsonData or return array with values
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
     * @return void
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

    /**
     * Realizes the tracking of files of directory.
     *
     * @return void
     */
    public function tracking()
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1); // Retira o "/" da pasta passada
        $this->json = '[' . (substr($this->json, 1)) . ']'; // Termina a estrutura do JSON
        $jsonAtual = json_decode($this->json, true); // Decofidica como JSON, retorna array

        $jsonFile = file_get_contents(".guarda/" . $pasta . ".json"); // Pega o JSON salvo
        $jsonSalvo = json_decode($jsonFile, true); // Decofidica como JSON, retorna array

        // $jsonAtual: conterá os novos valores obtidos
        // $jsonSalvo: conterá os valores já salvos em arquivo

        foreach ($jsonAtual as $array) {
            $valor = $this->procurarJson($jsonSalvo, $array['file']); // Verifica se campo já existia no JSON do arquivo já salvo
            // Verifica se está vazio
            if (!empty($valor)) {
                $valores = explode(' - ', $valor); // Transforma em um array de 2 posições
                // Verifica se o HMAC do arquivo foi modificado
                if (!($valores[1] == $array['hmac'])) {
                    echo "\e[1;36mArquivo \"" . $array['dir'] . $array['file'] . "\" foi alterado. \n"; // Exibe mensagem sobre arquivos alterados
                }
                unset($jsonSalvo[$valores[0]]); // Esvazia indice no array
            } else {
                echo "\e[1;32mArquivo \"" . $array['dir'] . $array['file'] . "\" foi adicionado! \n"; // Exibe mensagem sobre novos arquivos
            }
        }
        // Ao final do foreach anterior, $jsonSalvo só conterá os arquivos apagados
        foreach ($jsonSalvo as $array) {
            echo "\e[1;31mO arquivo \"" . $array['dir'] . $array['file'] . "\" foi excluído. \n"; // Exibe mensagem sobre arquivos excluídos
        }
        $file = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria o arquivo na pasta
        fwrite($file, json_encode($jsonAtual)); // Salva o JSON na pasta
        fclose($file); // Fecha o ponteiro
        echo "\e[1;33mRastreamento realizado com sucesso! \n";
        echo "\e[1;33mA guarda da pasta ".$pasta." foi atualizada. \n";
    }
}