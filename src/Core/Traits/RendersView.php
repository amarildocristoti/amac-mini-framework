<?php

namespace App\Core\Traits;

use RuntimeException;

// Disponibiliza renderização padronizada para controllers.
trait RendersView
{
    // Renderiza uma view específica dentro do layout global.
    protected function view(string $view, array $data = []): void
    {
        // Aceita somente identificadores de view com segmentos previsíveis.
        if (!preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#', $view)) {
            throw new RuntimeException('Nome de view inválido.');
        }

        // Converte cada chave do array em uma variável disponível na view.
        extract($data, EXTR_SKIP);

        // Resolve o arquivo sempre dentro de src/Views.
        $viewPath = dirname(__DIR__, 2) . '/Views/' . $view . '.php';

        // Interrompe com exceção quando o arquivo não existe.
        if (!is_file($viewPath)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }

        // Define os caminhos dos fragmentos comuns da página.
        $headerPath = dirname(__DIR__, 2) . '/Views/layout/header.php';
        $footerPath = dirname(__DIR__, 2) . '/Views/layout/footer.php';

        // Inclui o cabeçalho somente se ele existir no projeto.
        if (is_file($headerPath)) {
            require $headerPath;
        }
        // Inclui o conteúdo específico solicitado pelo controller.
        require $viewPath;
        // Inclui o rodapé somente se ele existir no projeto.
        if (is_file($footerPath)) {
            require $footerPath;
        }
    }
}
