<?php

namespace App\Core;

// Reúne os controles de segurança reutilizados por controllers e views.
final class Security
{
    // Impede a criação de instâncias de uma classe apenas utilitária.
    private function __construct()
    {
    }

    // Cria ou recupera o token CSRF da sessão atual.
    public static function csrfToken(): string
    {
        // Garante que a sessão esteja disponível antes de acessá-la.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Gera 32 bytes aleatórios na primeira utilização.
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        // Devolve o token para o formulário ou para uma requisição AJAX.
        return $_SESSION['_csrf_token'];
    }

    // Gera diretamente o input hidden usado em formulários HTML.
    public static function csrfField(string $name = 'csrf_token'): string
    {
        // Permite apenas nomes simples para impedir injeção de atributos HTML.
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_-]*$/', $name)) {
            throw new \InvalidArgumentException('Nome de campo CSRF inválido.');
        }
        // Escapa o nome e o token antes de montar o elemento HTML.
        $fieldName = self::escape($name);
        $token = self::escape(self::csrfToken());
        // Retorna um campo pronto para inserção em uma view.
        return '<input type="hidden" name="' . $fieldName . '" value="' . $token . '">';
    }

    // Mantém o nome usado pelo framework original.
    public static function generateCsrfToken(): string
    {
        // Encaminha para o método novo sem duplicar a lógica.
        return self::csrfToken();
    }

    // Mantém o nome usado pelo framework original.
    public static function validateCsrfToken(?string $token): bool
    {
        // Encaminha para o validador novo.
        return self::validateCsrf($token);
    }

    // Compara um token recebido com o token armazenado na sessão.
    public static function validateCsrf(?string $token): bool
    {
        // Reprova imediatamente valores vazios.
        if ($token === null || $token === '' || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        // Usa comparação resistente a timing attack.
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    // Regenera o token após uma operação sensível.
    public static function regenerateCsrf(): string
    {
        // Substitui o token antigo por uma nova sequência aleatória.
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        // Retorna o novo valor para o chamador.
        return $_SESSION['_csrf_token'];
    }

    // Escapa texto para ser exibido em contexto HTML.
    public static function escape(string $value): string
    {
        // ENT_SUBSTITUTE evita falha silenciosa em UTF-8 inválido.
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // Mantém o nome antigo para facilitar migração de projetos existentes.
    public static function sanitize(string $value): string
    {
        // Encaminha para o helper de escape padronizado.
        return self::escape($value);
    }

    // Valida um endereço de e-mail usando o filtro nativo do PHP.
    public static function isValidEmail(string $email): bool
    {
        // Retorna true somente quando o filtro reconhece um e-mail válido.
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }
}
