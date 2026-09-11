<?php

namespace App\Core;

use App\Core\Traits\RendersView;

// Define comportamentos comuns a todos os controllers da aplicação.
abstract class Controller
{
    // Importa o método view() sem criar uma hierarquia adicional.
    use RendersView;

    // Lê um valor enviado por POST ou GET, com fallback configurável.
    protected function input(string $key, mixed $default = null): mixed
    {
        // Prioriza POST para que dados mutáveis não dependam da query string.
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        // Remove espaços apenas de valores textuais.
        return is_string($value) ? trim($value) : $value;
    }

    // Redireciona para um caminho interno e encerra o processamento.
    protected function redirect(string $path, int $status = 302): never
    {
        // Evita que controllers montem redirecionamentos para destinos externos.
        $location = str_starts_with($path, '/') ? $path : '/' . $path;
        // Envia uma URL baseada na configuração da aplicação.
        header('Location: ' . APP_URL . $location, true, $status);
        // Garante que nenhum código posterior execute após o redirect.
        exit;
    }

    // Envia uma resposta JSON para futuras rotas de API.
    protected function json(mixed $data, int $status = 200): never
    {
        // Define o status HTTP antes de imprimir o corpo.
        http_response_code($status);
        // Informa ao cliente que o corpo está em JSON UTF-8.
        header('Content-Type: application/json; charset=utf-8');
        // Serializa os dados e encerra a resposta.
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
}
