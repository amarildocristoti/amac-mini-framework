<?php

namespace Tests;

use App\Core\Security;
use PHPUnit\Framework\TestCase;

// Agrupa testes unitários dos helpers de segurança do framework.
final class SecurityTest extends TestCase
{
    // Garante que caracteres HTML sejam escapados antes da saída.
    public function testEscapeProtectsHtml(): void
    {
        // Executa o helper com uma string que contém marcação.
        $escaped = Security::escape('<script>alert("x")</script>');
        // Confirma que a marcação não permanece executável.
        self::assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $escaped);
    }

    // Garante que o token CSRF correto seja aceito e o incorreto rejeitado.
    public function testCsrfTokenIsValidated(): void
    {
        // Cria uma sessão isolada em memória para o teste.
        $_SESSION = [];
        // Gera o token através do mesmo caminho usado pelas views.
        $token = Security::csrfToken();
        // O token recém-gerado deve ser válido.
        self::assertTrue(Security::validateCsrf($token));
        // Uma string diferente nunca deve ser aceita.
        self::assertFalse(Security::validateCsrf('token-invalido'));
    }

    // Garante que o validador diferencie e-mails válidos de inválidos.
    public function testEmailValidation(): void
    {
        // Confirma um formato comum válido.
        self::assertTrue(Security::isValidEmail('pessoa@example.com'));
        // Confirma que texto sem domínio é rejeitado.
        self::assertFalse(Security::isValidEmail('pessoa-sem-dominio'));
    }
}
