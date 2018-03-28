<?php
/**
 * User: alvarofpp
 * Date: 27/03/18
 * Time: 20:18
 */

/**
* This class performs the operations required to run the Hash-based Message Authentication Code (HMAC).
*/
class HMAC
{
    protected $ipadKey, $opadKey;
    protected $ipad, $opad;
    protected $sizeB;
    protected $key;

    /**
     * @var FileManagement
     */
    protected $fileManagement;

    function __construct($dir)
    {
        $this->ipad = '00110110';
        $this->opad = '01011100';
        $this->sizeB = 32;
        $this->key = 'segurancaEmR3d3s';

        $this->fileManagement = new FileManagement($dir);
        $this->createKeys();
    }

    /**
     * Check the key lengths and perform the required procedure.
     *
     * @return void.
     */
    private function lengthKeys()
    {
        $len = strlen($this->key);

        if ($len < $this->sizeB) {
            $this->key = str_pad($this->key, 64, 0, STR_PAD_LEFT);
        } elseif ($len > $this->sizeB) {
            $this->key = md5($this->key);
        }
    }

    /**
     * Create keys that will be used by HMAC in manipulation of hash.
     *
     * @return void.
     */
    private function createKeys()
    {
        $this->lengthKeys();
        $key = $this->key;

        $array0 = str_split($key);
        $ipadArray = str_split($this->ipad);
        $opadArray = str_split($this->opad);

        $ipadKey = [];
        $opadKey = [];
        $ipadKeyTemp = [];
        $opadKeyTemp = [];

        // XOR
        for ($i = 0; $i < strlen($key); $i++) {
            $letterBinArray = str_pad(decbin(ord($array0[$i])), 8, 0, STR_PAD_LEFT);

            for ($c = 0; $c < 8; $c++) {
                $ipadKeyTemp[$c] = ($letterBinArray[$c] xor $ipadArray[$c]) ? '1' : '0';
                $opadKeyTemp[$c] = ($letterBinArray[$c] xor $opadArray[$c]) ? '1' : '0';
            }

            $ipadKey[$i] = implode("", $ipadKeyTemp);
            $opadKey[$i] = implode("", $opadKeyTemp);
        }

        // Convert to binary and after to char
        for ($i = 0; $i < sizeof($ipadKey); $i++) {
            $ipadKey[$i] = chr(bindec($ipadKey[$i]));
            $opadKey[$i] = chr(bindec($opadKey[$i]));
        }

        $this->ipadKey = implode("", $ipadKey);
        $this->opadKey = implode("", $opadKey);
    }

    /**
     * Executes the HMAC in the hash.
     *
     * @param string $hash
     * @return string
     */
    public function execute($hash)
    {
        $hash = md5($hash . $this->ipadKey);
        $hashFinal = md5($this->opadKey . $hash);

        return $hashFinal;
    }

    // Realiza o Tracking da pasta passada como parâmetro
    public function tracking()
    {
        $pasta = $this->fileManagement->getVars()['dir'];
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