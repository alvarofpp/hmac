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
                $hmac->percorrer_dir($argv[2]); // Percorre o diretório
                $hmac->salvar_hmac($argv[2]); // Salva os dados coletados
                break;
            case '-t':
                $hmac->percorrer_dir($argv[2]); // Percorre o diretório
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
                $hmac->remover_hmac($argv[2]); // Remove o arquivo JSON com os dados da pasta
                break;
        }
    }
}


class HMAC
{
    var $dir; // Usado para informar se o diretório passado é válido ou não
    var $json; // JSON usado para armazenar os dados
    var $ipad, $opad; // Valores usados para os passos do HMAC
    var $b; // Tamanho
    var $key; // Chave

    // Construtor. Inicia as variáveis e verifica se a pasta passada existe ou não
    function __construct($pasta)
    {
        $this->verificar_pasta($pasta);
        $this->ipad = '00110110';
        $this->opad = '01011100';
        $this->b = 32;
        $this->key = 'segurancaEmR3d3s';
    }

    // Essa função serve para verificar se a pasta existe ou não
    public function verificar_pasta($pasta)
    {
        if (is_dir($pasta)) {
            $this->dir = true;
        } else {
            $this->dir = false;
            echo "A pasta não existe.\n";
        }
    }

    // Essa função percorre a pasta informada, coletando os dados e percorrendo subpastas
    public function percorrer_dir($ext)
    {
        $dir = new DirectoryIterator($ext); // Usado para interagir com o diretório
        // Percorre os arquivos no diretório
        foreach ($dir as $file) {
            // Verifica se não é /. ou /.. e se é um diretório
            if (!$file->isDot() && $file->isDir()) {
                $this->percorrer_dir($ext . $file->getFilename() . '/'); // Percorre o diretório
            } else if (!$file->isDot()) { // Verifica se não é /. ou /..
                // Realiza o HMAC do arquivo
                $hmac = $this->hmac($this->key, md5_file($ext . $file->getFilename())); // Passa a chave e o HASH do arquivo (MD5)
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
    public function hmac($key, $message)
    {
        // Verifica o tamanho da chave
        if (strlen($key) < $this->b) { // Se é menor que o tamanho exigido
            $key = str_pad($key, 64, 0, STR_PAD_LEFT); // Preenche com 0 a esquerda até possuir o tamanho necessário
        } else if (strlen($key) > $this->b) { // Verifica se é maior que o tamanho exigido
            $key = md5($key); // Realiza o MD5 da chave
        }

        // Realiza o XOR
        $array0 = str_split($key); // Array com os caracteres da chave
        $array_ipad = str_split($this->ipad); // Array com os caracteres do ipad
        $array_opad = str_split($this->opad); // Array com os caracteres do opad
        $k_ipad = array(); // Array com as chaves do ipad
        $k_opad = array(); // Array com as chaves do opad
        $k_ipad_temp = array(); // Array auxiliar do ipad
        $k_opad_temp = array(); // Array auxiliar do opad
        for ($i = 0; $i < strlen($key); $i++) {
            // Pega o caractere da chave, transforma em número ASCII e depois em binário
            // Aumenta com 0 a esquerda até possuir 8 caracteres
            $array_letrabin = str_pad(decbin(ord($array0[$i])), 8, 0, STR_PAD_LEFT);
            for ($c = 0; $c < 8; $c++) {
                $k_ipad_temp[$c] = ($array_letrabin[$c] xor $array_ipad[$c]) ? '1' : '0'; // XOR IPAD para Si
                $k_opad_temp[$c] = ($array_letrabin[$c] xor $array_opad[$c]) ? '1' : '0'; // XOR OPAD para So
            }
            $k_ipad[$i] = implode("", $k_ipad_temp); // Transforma em uma string e armazena nas chaves do ipad
            $k_opad[$i] = implode("", $k_opad_temp); // Transforma em uma string e armazena nas chaves do opad
            $k_ipad_temp = null; // Esvazia o array temporário do ipad
            $k_opad_temp = null; // Esvazia o array temporário do opad
        }

        // Converte todos os valores armazenados no array em binário e depois em caractere
        for ($i = 0; $i < sizeof($k_ipad); $i++) {
            $k_ipad[$i] = chr(bindec($k_ipad[$i]));
            $k_opad[$i] = chr(bindec($k_opad[$i]));
        }
        $Si = implode("", $k_ipad); // Transforma em uma string
        $So = implode("", $k_opad); // Transforma em uma string

        // Aplicar hash a junção de $mensagem e $Si
        $hash = md5($message . $Si);

        // Aplicar hash a junção do hash gerado anteriormente e $So
        $hash_final = md5($So . $hash);

        return $hash_final; // Retorna o HMAC
    }

    // Salva o HMAC gerado até agora
    public function salvar_hmac($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1); // Retira o "/" da pasta passada
        $this->json = '[' . (substr($this->json, 1)) . ']'; // Termina a estrutura JSON

        // Verifica se a pasta existe
        if (!file_exists(".guarda/")) {
            mkdir(".guarda"); // Cria a pasta
        }

        $th = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria/ler o arquivo na pasta
        fwrite($th, $this->json); // Salva o JSON na pasta
        fclose($th); // Fecha o ponteiro
        echo "Pasta e arquivos salvos. \n";
    }

    // Realiza o Tracking da pasta passada como parâmetro
    public function tracking($pasta)
    {
        $pasta = substr($pasta, 0, strlen($pasta) - 1); // Retira o "/" da pasta passada
        $this->json = '[' . (substr($this->json, 1)) . ']'; // Termina a estrutura do JSON
        $json_atual = json_decode($this->json, true); // Decofidica como JSON, retorna array

        $json_file = file_get_contents(".guarda/" . $pasta . ".json"); // Pega o JSON salvo
        $json_salvo = json_decode($json_file, true); // Decofidica como JSON, retorna array

        // $json_atual: conterá os novos valores obtidos
        // $json_salvo: conterá os valores já salvos em arquivo

        foreach ($json_atual as $array) {
            $valor = $this->procurar_json($json_salvo, $array['file']); // Verifica se campo já existia no JSON do arquivo já salvo
            // Verifica se está vazio
            if (!empty($valor)) {
                $valores = explode(' - ', $valor); // Transforma em um array de 2 posições
                // Verifica se o HMAC do arquivo foi modificado
                if (!($valores[1] == $array['hmac'])) {
                    echo "\e[1;36mArquivo \"" . $array['dir'] . $array['file'] . "\" foi alterado. \n"; // Exibe mensagem sobre arquivos alterados
                }
                unset($json_salvo[$valores[0]]); // Esvazia indice no array
            } else {
                echo "\e[1;32mArquivo \"" . $array['dir'] . $array['file'] . "\" foi adicionado! \n"; // Exibe mensagem sobre novos arquivos
            }
        }
        // Ao final do foreach anterior, $json_salvo só conterá os arquivos apagados
        foreach ($json_salvo as $array) {
            echo "\e[1;31mO arquivo \"" . $array['dir'] . $array['file'] . "\" foi excluído. \n"; // Exibe mensagem sobre arquivos excluídos
        }
        $th = fopen(".guarda/" . $pasta . ".json", "wb"); // Cria o arquivo na pasta
        fwrite($th, json_encode($json_atual)); // Salva o JSON na pasta
        fclose($th); // Fecha o ponteiro
        echo "\e[1;33mRastreamento realizado com sucesso! \n";
        echo "\e[1;33mA guarda da pasta ".$pasta." foi atualizada. \n";
    }

    // Procura valores referentes ao arquivo
    // Recebe JSON e valor que deve ser procurado (nome do arquivo)
    public function procurar_json($json, $valor)
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
    public function remover_hmac($pasta)
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