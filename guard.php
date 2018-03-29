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