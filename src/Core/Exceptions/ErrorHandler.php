<?php

namespace App\Core\Exceptions;

use Throwable;

// Centraliza o tratamento de falhas não capturadas pelo fluxo normal.
final class ErrorHandler
{
    // Impede a criação de instâncias: o handler expõe apenas comportamento estático.
    private function __construct()
    {
    }

    // Renderiza uma resposta HTTP adequada para uma exceção não tratada.
    public static function render(Throwable $exception): void
    {
        // Informa ao cliente que ocorreu um erro interno.
        http_response_code(500);

        // Em desenvolvimento, mostra uma mensagem útil para correção local.
        if (defined('APP_ENV') && APP_ENV === 'development') {
            // Escapa a mensagem para impedir que um erro vire XSS.
            $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
            // Retorna HTML simples quando uma view de erro ainda não estiver pronta.
            echo "<h1>Erro interno</h1><p>{$message}</p>";
            return;
        }

        // Em produção, revela apenas uma mensagem genérica.
        echo '<h1>Erro interno</h1><p>Não foi possível concluir a operação.</p>';
    }
}
