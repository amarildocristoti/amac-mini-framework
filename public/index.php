<?php

// Carrega ambiente, sessão, autoload e tratamento global de exceções.
require_once __DIR__ . '/../config/config.php';

// Importa o roteador usado para registrar e executar as rotas.
use App\Core\Router;

// Cria uma instância nova do roteador para esta requisição.
$router = new Router();

// Registra a página inicial do projeto de exemplo.
$router->get('/', 'HomeController@index');

// Adicione as rotas específicas da sua aplicação abaixo.
// Exemplo: $router->post('/tasks', 'TaskController@store');

// Entrega a URI e o método HTTP atuais ao roteador.
$router->dispatch(
    $_SERVER['REQUEST_URI'] ?? '/',
    $_SERVER['REQUEST_METHOD'] ?? 'GET'
);
