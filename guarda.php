<?php
/**
 * Created by PhpStorm.
 * User: alvarofpp
 * Date: 13/10/16
 * Time: 19:12
 */

if ((!isset($argv[1]) || empty($argv[1])) || $argv[1] == '--help' || $argv[1] == '-h') {
    echo "Para executar o script corretamente digite:\n";
    echo "php guarda.php [option] [pasta] \n";
    echo "\n";
    echo "[option]:\n";
    echo "  --help -h Mostra a ajuda;\n";
    echo "  -i        inicia a guarda da pasta indicada em [pasta], ou seja, faz a leitura de todos os arquivos da pasta (recursivamente) registrando os dados e HMAC de cada um e armazenando numa estrutura própria (Ex: tabela hash em uma subpasta oculta ./guarda);\n";
    echo "  -t        faz o rastreio (tracking) da pasta indicada em [pasta], inserindo informações sobre novos arquivos e indicando alterações detectadas/exclusões;\n";
    echo "  -x        desativa a guarda e remove a estrutura alocada.\n";
    echo "[pasta]: \n";
    echo "            indica a pasta a ser \"guardada\".\n";
} else {
    if (!isset($argv[1]) || empty($argv[1]) || !($argv[1] == '-i' || $argv[1] == '-t' || $argv[1] == '-x')) {
        echo "Por favor, digite uma opção válida.\n";
        exit();
    }
    if (!isset($argv[2]) || empty($argv[2])) {
        echo "Por favor, passe o nome da pasta que deseja utilizar no script.\n";
        exit();
    }

    $hmac = new HMAC($argv[2]);
    if ($hmac->dir) {
        switch ($argv[1]) {
            case '-i':
                $hmac->percorrer_dir($argv[2]);
                $hmac->salvar_hmac($argv[2]);
                break;
            case '-t':
                $hmac->percorrer_dir($argv[2]);
                $pasta = substr($argv[2], 0, strlen($argv[2]) - 1);
                if (file_exists(".guarda/" . $pasta . ".json")) {
                    $hmac->tracking($argv[2]);
                } else {
                    echo "Pasta " . $pasta . " não esta guardo pelo programa.\n";
                    echo "Use o comando para rastrear a guarda de uma pasta usada.\n";
                }
                break;
            case '-x':
                $hmac->remover_hmac($argv[2]);
                break;
        }
    }
}


class HMAC
{
    var $dir;
    var $json;
    var $ipad, $opad;
    var $b;
    var $key;

    function __construct($pasta)
    {
        $this->verificar_pasta($pasta);
        $this->ipad = '00110110';
        $this->opad = '01011100';
        $this->b = 32;
        $this->key = 'segurancaEmR3d3s';
    }

    public function verificar_pasta($pasta)
    {
        if (is_dir($pasta)) {
            $this->dir = true;
        } else {
            $this->dir = false;
            echo "A pasta não existe.\n";
        }
    }

    public function percorrer_dir($ext)
    {
        $dir = new DirectoryIterator($ext);
        foreach ($dir as $file) {
            if (!$file->isDot() && $file->isDir()) {
                $this->percorrer_dir($ext . $file->getFilename() . '/');
            } else if (!$file->isDot()) {
                $hmac = $this->hmac($this->key, md5_file($ext . $file->getFilename()));
                $this->json .= ',{';
                $this->json .= '"file": "' . $file->getFilename() . '",';
                $this->json .= '"dir": "' . $ext . '",';
                $this->json .= '"hmac": "' . $hmac . '"';
                $this->json .= '}';
                //echo $ext . $file->getFilename() . '  -  ' . $hmac . "\n";
            }
        }
    }

    public function hmac($key, $message)
    {
        // 1.
        if (strlen($key) < $this->b) {
            $key = str_pad($key, 64, 0, STR_PAD_LEFT);
        } else if (strlen($key) > $this->b) {
            $key = md5($key);
        }

        // 2 e 5. XOR
        $array0 = str_split($key);
        $array_ipad = str_split($this->ipad);
        $array_opad = str_split($this->opad);
        $k_ipad = array();
        $k_opad = array();
        $k_ipad_temp = array();
        $k_opad_temp = array();
        for ($i = 0; $i < strlen($key); $i++) {
            $array_letrabin = str_pad(decbin(ord($array0[$i])), 8, 0, STR_PAD_LEFT);
            for ($c = 0; $c < 8; $c++) {
                $k_ipad_temp[$c] = ($array_letrabin[$c] xor $array_ipad[$c]) ? '1' : '0'; // IPAD
                $k_opad_temp[$c] = ($array_letrabin[$c] xor $array_opad[$c]) ? '1' : '0'; // OPAD
            }
            $k_ipad[$i] = implode("", $k_ipad_temp);
            $k_opad[$i] = implode("", $k_opad_temp);
            $k_ipad_temp = null;
            $k_opad_temp = null;
        }

        for ($i = 0; $i < sizeof($k_ipad); $i++) {
            $k_ipad[$i] = chr(bindec($k_ipad[$i]));
            $k_opad[$i] = chr(bindec($k_opad[$i]));
        }
        $Si = implode("", $k_ipad);
        $So = implode("", $k_opad);

        // 3 e 4. Aplicar hash a junção de $mensagem e Si
        $hash = md5($message . $Si);

        // 6.
        $hash_final = md5($So . $hash);

        return $hash_final;
    }

    public function salvar_hmac($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1);
        $this->json = '[' . (substr($this->json, 1)) . ']';

        if (!file_exists(".guarda/")) {
            mkdir(".guarda");
        }

        $th = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria o arquivo na pasta
        fwrite($th, $this->json); // Salva o texto na pasta
        fclose($th);
        echo "Pasta e arquivos salvos. \n";
    }


    public function tracking($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1);
        $this->json = '[' . (substr($this->json, 1)) . ']';
        $json_atual = json_decode($this->json, true);

        $json_file = file_get_contents(".guarda/" . $pasta . ".json");
        $json_salvo = json_decode($json_file, true);

        foreach ($json_atual as $array) {
            $valor = $this->procurar_json($json_salvo, $array['file']);
            if (!empty($valor)) {
                $valores = explode(' - ', $valor);
                if (!($valores[1] == $array['hmac'])) {
                    echo "\e[1;36mArquivo \"" . $array['dir'] . $array['file'] . "\" foi alterado. \n";
                }
                unset($json_salvo[$valores[0]]);
            } else {
                echo "\e[1;32mArquivo \"" . $array['dir'] . $array['file'] . "\" foi adicionado! \n";
            }
        }
        foreach ($json_salvo as $array) {
            echo "\e[1;31mO arquivo \"" . $array['dir'] . $array['file'] . "\" foi excluído. \n";
        }
        $th = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria o arquivo na pasta
        fwrite($th, json_encode($json_atual)); // Salva o texto na pasta
        fclose($th);
        echo "\e[1;33mRastreamento realizado com sucesso! \n";
        echo "\e[1;33mA guarda da pasta ".$pasta." foi atualizada. \n";
    }

    public function procurar_json($json, $valor)
    {
        foreach ($json as $key => $array) {
            if ($array['file'] == $valor) {
                return $key . ' - ' . $array['hmac'];
            }
        }
        return null;
    }

    public function remover_hmac($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1);
        if (file_exists(".guarda/" . $pasta . ".json")) {
            $return = unlink(".guarda/" . $pasta . ".json");
            echo "Guarda da pasta " . $pasta . "/ desativado.\n";
        } else {
            echo "Pasta " . $pasta . " não estava guardada pelo programa.\n";
            echo "Use o comando para desativar a guarda de uma pasta usada.\n";
        }
    }
}