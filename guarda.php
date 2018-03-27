<?php
/**
 * Created by PhpStorm.
 * User: alvarofpp
 * Date: 13/10/16
 * Time: 19:12
 */

// Verifica se solicitou ajuda ou não passou o primeiro parâmetro
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
    // Verifica se é uma opção válida
    if (!isset($argv[1]) || empty($argv[1]) || !($argv[1] == '-i' || $argv[1] == '-t' || $argv[1] == '-x')) {
        echo "Por favor, digite uma opção válida.\n";
        exit();
    }
    // Verifica se o segundo parâmetro foi passado
    if (!isset($argv[2]) || empty($argv[2])) {
        echo "Por favor, passe o nome da pasta que deseja utilizar no script.\n";
        exit();
    }

    $hmac = new HMAC($argv[2]); // Cria um objeto
    // Verifica se o passado no segundo parâmetro é válido (é uma pasta)
    if ($hmac->dir) {
        switch ($argv[1]) {
            case '-i':
                $hmac->percorrerDir($argv[2]); // Percorre o diretório
                $hmac->salvarHmac($argv[2]); // Salva os dados coletados
                break;
            case '-t':
                $hmac->percorrerDir($argv[2]); // Percorre o diretório
                $pasta = substr($argv[2], 0, strlen($argv[2]) - 1); // Retira o "/" do segundo parâmetro
                // Verifica se o arquivo existe
                if (file_exists(".guarda/" . $pasta . ".json")) {
                    $hmac->tracking($argv[2]); // Realiza a varredura da pasta
                } else {
                    echo "Pasta " . $pasta . " não esta guardo pelo programa.\n";
                    echo "Use o comando para rastrear a guarda de uma pasta usada.\n";
                }
                break;
            case '-x':
                $hmac->removerHmac($argv[2]); // Remove o arquivo JSON com os dados da pasta
                break;
        }
    }
}


class HMAC
{
    var $dir; // Usado para informar se o diretório passado é válido ou não
    var $json; // JSON usado para armazenar os dados
    var $ipad, $opad; // Valores usados para os passos do HMAC
    var $sizeB; // Tamanho
    var $key; // Chave

    // Construtor. Inicia as variáveis e verifica se a pasta passada existe ou não
    function __construct($pasta)
    {
        $this->verificarPasta($pasta);
        $this->ipad = '00110110';
        $this->opad = '01011100';
        $this->sizeB = 32;
        $this->key = 'segurancaEmR3d3s';
    }

    // Essa função serve para verificar se a pasta existe ou não
    public function verificarPasta($pasta)
    {
        $this->dir = true;

        if (!is_dir($pasta)) {
            $this->dir = false;
            echo "A pasta não existe.\n";
        }
    }

    // Essa função percorre a pasta informada, coletando os dados e percorrendo subpastas
    public function percorrerDir($ext)
    {
        $dir = new DirectoryIterator($ext); // Usado para interagir com o diretório
        // Percorre os arquivos no diretório
        foreach ($dir as $file) {
            // Verifica se não é /. ou /.. e se é um diretório
            if (!$file->isDot() && $file->isDir()) {
                $this->percorrerDir($ext . $file->getFilename() . '/'); // Percorre o diretório
            } else if (!$file->isDot()) { // Verifica se não é /. ou /..
                // Realiza o HMAC do arquivo
                $hmac = $this->realizarHmac($this->key, md5_file($ext . $file->getFilename())); // Passa a chave e o HASH do arquivo (MD5)
                // Arruma as informações para o JSON
                $this->json .= ',{';
                $this->json .= '"file": "' . $file->getFilename() . '",'; // Nome do arquivo
                $this->json .= '"dir": "' . $ext . '",'; // Diretório
                $this->json .= '"hmac": "' . $hmac . '"'; // HMAC
                $this->json .= '}';
            }
        }
    }

    // Realiza o HMAC. Recebe a chave e o HASH passado nos parâmetro
    public function realizarHmac($key, $message)
    {
        // Verifica o tamanho da chave
        if (strlen($key) < $this->sizeB) { // Se é menor que o tamanho exigido
            $key = str_pad($key, 64, 0, STR_PAD_LEFT); // Preenche com 0 a esquerda até possuir o tamanho necessário
        } else if (strlen($key) > $this->sizeB) { // Verifica se é maior que o tamanho exigido
            $key = md5($key); // Realiza o MD5 da chave
        }

        // Realiza o XOR
        $array0 = str_split($key); // Array com os caracteres da chave
        $ipadArray = str_split($this->ipad); // Array com os caracteres do ipad
        $opadArray = str_split($this->opad); // Array com os caracteres do opad
        $ipadKey = array(); // Array com as chaves do ipad
        $opadKey = array(); // Array com as chaves do opad
        $ipadKeyTemp = array(); // Array auxiliar do ipad
        $opadKeyTemp = array(); // Array auxiliar do opad
        for ($i = 0; $i < strlen($key); $i++) {
            // Pega o caractere da chave, transforma em número ASCII e depois em binário
            // Aumenta com 0 a esquerda até possuir 8 caracteres
            $letraBinArray = str_pad(decbin(ord($array0[$i])), 8, 0, STR_PAD_LEFT);
            for ($c = 0; $c < 8; $c++) {
                $ipadKeyTemp[$c] = ($letraBinArray[$c] xor $ipadArray[$c]) ? '1' : '0'; // XOR IPAD para stringI
                $opadKeyTemp[$c] = ($letraBinArray[$c] xor $opadArray[$c]) ? '1' : '0'; // XOR OPAD para stringO
            }
            $ipadKey[$i] = implode("", $ipadKeyTemp); // Transforma em uma string e armazena nas chaves do ipad
            $opadKey[$i] = implode("", $opadKeyTemp); // Transforma em uma string e armazena nas chaves do opad
            $ipadKeyTemp = null; // Esvazia o array temporário do ipad
            $opadKeyTemp = null; // Esvazia o array temporário do opad
        }

        // Converte todos os valores armazenados no array em binário e depois em caractere
        for ($i = 0; $i < sizeof($ipadKey); $i++) {
            $ipadKey[$i] = chr(bindec($ipadKey[$i]));
            $opadKey[$i] = chr(bindec($opadKey[$i]));
        }
        $stringI = implode("", $ipadKey); // Transforma em uma string
        $stringO = implode("", $opadKey); // Transforma em uma string

        // Aplicar hash a junção de $mensagem e $stringI
        $hash = md5($message . $stringI);

        // Aplicar hash a junção do hash gerado anteriormente e $stringO
        $hashFinal = md5($stringO . $hash);

        return $hashFinal; // Retorna o HMAC
    }

    // Salva o HMAC gerado até agora
    public function salvarHmac($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1); // Retira o "/" da pasta passada
        $this->json = '[' . (substr($this->json, 1)) . ']'; // Termina a estrutura JSON

        // Verifica se a pasta existe
        if (!file_exists(".guarda/")) {
            mkdir(".guarda"); // Cria a pasta
        }

        $file = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria/ler o arquivo na pasta
        fwrite($file, $this->json); // Salva o JSON na pasta
        fclose($file); // Fecha o ponteiro
        echo "Pasta e arquivos salvos. \n";
    }

    // Realiza o Tracking da pasta passada como parâmetro
    public function tracking($pasta)
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

    // Procura valores referentes ao arquivo
    // Recebe JSON e valor que deve ser procurado (nome do arquivo)
    public function procurarJson($json, $valor)
    {
        // Percorre JSON
        foreach ($json as $key => $array) {
            // Verifica se o nome do arquivo e igual ao do JSON
            if ($array['file'] == $valor) {
                return $key . ' - ' . $array['hmac']; // Retorna a chave do indice presente e o HMAC
            }
        }
        return null;
    }

    // Deleta o arquivo JSON da pasta passada como parâmetro
    public function removerHmac($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1); // Retira o "/" da pasta passada como parâmetro
        // Verifica se arquivo existe
        if (file_exists(".guarda/" . $pasta . ".json")) {
            unlink(".guarda/" . $pasta . ".json"); // Deleta o arquivo
            echo "Guarda da pasta " . $pasta . "/ desativado.\n";
        } else {
            echo "Pasta " . $pasta . " não estava guardada pelo programa.\n";
            echo "Use o comando para desativar a guarda de uma pasta usada.\n";
        }
    }
}