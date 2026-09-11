<?php

namespace Tests;

use App\Core\Router;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

// Testa a compilação interna de rotas sem precisar iniciar um servidor web.
final class RouterTest extends TestCase
{
    // Confirma que literais são escapados e parâmetros não atravessam barras.
    public function testRoutePatternIsCompiledSafely(): void
    {
        // Cria o roteador usado pela aplicação.
        $router = new Router();
        // Acessa o método privado apenas para testar o núcleo determinístico.
        $method = new ReflectionMethod(Router::class, 'compilePattern');
        // Permite a chamada do método durante este teste.
        $method->setAccessible(true);
        // Compila uma rota com um parâmetro dinâmico.
        $pattern = $method->invoke($router, '/tasks/{id}');
        // Um identificador simples deve ser aceito.
        self::assertSame(1, preg_match($pattern, '/tasks/123'));
        // Uma barra adicional não deve ser absorvida pelo parâmetro.
        self::assertSame(0, preg_match($pattern, '/tasks/123/extra'));
    }

    // Confirma que a rota raiz possui um padrão próprio.
    public function testRootPatternMatchesOnlyRoot(): void
    {
        // Cria uma instância independente do roteador.
        $router = new Router();
        // Obtém o compilador interno da rota.
        $method = new ReflectionMethod(Router::class, 'compilePattern');
        // Permite inspecionar o comportamento sem expor a API de produção.
        $method->setAccessible(true);
        // Compila a rota raiz.
        $pattern = $method->invoke($router, '/');
        // A raiz deve corresponder.
        self::assertSame(1, preg_match($pattern, '/'));
        // Outro caminho não deve corresponder.
        self::assertSame(0, preg_match($pattern, '/tasks'));
    }
}
