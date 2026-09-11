<?php

// Carrega o autoloader do Composer para que as classes App\\ sejam encontradas.
require_once __DIR__ . '/../vendor/autoload.php';

// Importa o Dotenv, responsável por ler o arquivo .env local.
use Dotenv\Dotenv;

// Cria o leitor apontando para a raiz do projeto.
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');

// Lê o .env sem quebrar a aplicação quando ele ainda não existir.
$dotenv->safeLoad();

// Define as configurações do banco com valores padrão seguros para desenvolvimento.
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'meu_app');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Define as configurações gerais da aplicação.
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? 'http://localhost/mini-framework/public', '/'));
define('APP_KEY', $_ENV['APP_KEY'] ?? '');

// Ativa detalhes de erro somente no desenvolvimento.
if (APP_ENV === 'development') {
    // Mostra erros no navegador para facilitar o aprendizado e o debug local.
    ini_set('display_errors', '1');
    // Solicita todos os níveis de erro do PHP.
    error_reporting(E_ALL);
} else {
    // Nunca mostra detalhes internos ao usuário em produção.
    ini_set('display_errors', '0');
    // Mantém os erros disponíveis para o log interno do servidor.
    error_reporting(E_ALL);
}

// Configura o cookie de sessão para não ser lido por JavaScript.
ini_set('session.cookie_httponly', '1');
// Impede que o ID da sessão seja aceito pela URL.
ini_set('session.use_only_cookies', '1');
// Reduz o envio automático do cookie em navegação entre sites.
ini_set('session.cookie_samesite', 'Lax');
// Exige HTTPS para o cookie quando a aplicação está em produção.
ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');

// Inicia a sessão somente se outro código ainda não a iniciou.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Adiciona headers básicos de proteção para respostas HTML.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Registra um handler único para transformar exceções em respostas controladas.
set_exception_handler(function (Throwable $exception): void {
    // Grava a mensagem técnica no log do servidor, nunca na tela de produção.
    error_log((string) $exception);
    // Delega a apresentação para o componente reutilizável de erros.
    \App\Core\Exceptions\ErrorHandler::render($exception);
});
