<?php

// Define as pastas que contêm código próprio do projeto.
$directories = ['src', 'public', 'config', 'tests'];

// Mantém uma lista de arquivos PHP encontrados para validar.
$files = [];

// Percorre cada pasta configurada.
foreach ($directories as $directory) {
    // Ignora a pasta quando ela não existir em um projeto mínimo.
    if (!is_dir($directory)) {
        continue;
    }

    // Cria um iterador recursivo para encontrar subpastas e arquivos.
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    // Examina cada item encontrado pelo iterador.
    foreach ($iterator as $file) {
        // Seleciona somente arquivos com extensão PHP.
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

// Ordena o resultado para produzir saídas reproduzíveis no CI.
sort($files);

// Guarda a quantidade de erros encontrados.
$failures = 0;

// Executa o lint do próprio PHP em cada arquivo.
foreach ($files as $file) {
    // Monta o comando usando o mesmo interpretador que executou este script.
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    // Imprime a saída do PHP e captura seu código de retorno.
    passthru($command, $exitCode);
    // Conta o arquivo quando o PHP reportar erro de sintaxe.
    if ($exitCode !== 0) {
        $failures++;
    }
}

// Retorna código zero somente quando todos os arquivos passaram.
exit($failures === 0 ? 0 : 1);
