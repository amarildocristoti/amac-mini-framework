<?php

namespace App\Core;

use RuntimeException;

// Representa o roteador responsável por encaminhar cada requisição.
final class Router
{
    // Guarda as rotas separadas pelo método HTTP.
    // Exemplo de estrutura interna:
    // $routes = [
    //     'GET' => ['/usuarios' => ['action' => 'UserController@index', 'middlewares' => []]],
    //     'POST' => ['/usuarios' => ['action' => 'UserController@store', 'middlewares' => []]]
    // ];
    private array $routes = [];

    // Registra uma rota GET para páginas de leitura.
    // Exemplo: $router->get('/produtos', 'ProductController@index');
    public function get(string $uri, string $action, array $middlewares = []): void
    {
        // Reutiliza o método interno para evitar duplicação.
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    // Registra uma rota POST para formulários e criação de recursos.
    // Exemplo: $router->post('/produtos', 'ProductController@store');
    public function post(string $uri, string $action, array $middlewares = []): void
    {
        // Armazena a rota usando o método HTTP correspondente.
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    // Registra uma rota PUT para substituição de recursos.
    // Exemplo: $router->put('/produtos/{id}', 'ProductController@update');
    public function put(string $uri, string $action, array $middlewares = []): void
    {
        // Encaminha a definição para o armazenamento central.
        $this->addRoute('PUT', $uri, $action, $middlewares);
    }

    // Registra uma rota PATCH para atualização parcial.
    // Exemplo: $router->patch('/produtos/{id}', 'ProductController@updateStatus');
    public function patch(string $uri, string $action, array $middlewares = []): void
    {
        // Mantém a mesma estrutura das demais rotas.
        $this->addRoute('PATCH', $uri, $action, $middlewares);
    }

    // Registra uma rota DELETE para remoção de recursos.
    // Exemplo: $router->delete('/produtos/{id}', 'ProductController@destroy');
    public function delete(string $uri, string $action, array $middlewares = []): void
    {
        // Usa o método HTTP DELETE sem criar uma implementação paralela.
        $this->addRoute('DELETE', $uri, $action, $middlewares);
    }

    // Armazena uma rota depois de validar seus argumentos básicos.
    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        // Garante que a URI seja um caminho relativo iniciado por barra.
        // Válido: '/users' | Inválido: 'users'
        if ($uri === '' || $uri[0] !== '/') {
            throw new RuntimeException('A rota deve começar com /.');
        }

        // Garante que a ação tenha o formato Controller@metodo.
        // Válido: 'UserController@index' | Inválido: 'UserController::index' ou 'index'
        if (!preg_match('/^[A-Za-z0-9_]+@[A-Za-z0-9_]+$/', $action)) {
            throw new RuntimeException('A ação da rota deve usar Controller@metodo.');
        }

        // Salva a configuração usando a URI como chave única por método.
        $this->routes[$method][$uri] = [
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    // Executa a rota compatível com a URI e o método recebidos.
    // Exemplo de chamada: $router->dispatch('/users/10?ref=google', 'GET');
    public function dispatch(string $uri, string $method): void
    {
        // Mantém somente o caminho e descarta a query string.
        // Exemplo: '/users/10?ref=google' vira '/users/10'
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Remove o caminho físico da pasta pública quando necessário.
        // Exemplo: '/meu-projeto/public/users' vira '/users'
        $path = $this->normalizeBasePath($path);

        // Normaliza barras finais, preservando a rota raiz.
        // Exemplo: '/users/' vira '/users'
        $path = rtrim($path, '/') ?: '/';

        // Procura somente nas rotas do método HTTP solicitado.
        foreach ($this->routes[strtoupper($method)] ?? [] as $route => $config) {
            // Converte a sintaxe /items/{id} em uma expressão regular segura.
            // Exemplo: '/users/{id}' vira o padrão Regex '#^/users/([^/]+)/?$#'
            $pattern = $this->compilePattern($route);

            // Verifica se a URI inteira coincide com a rota.
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            // Remove o texto completo, deixando apenas os parâmetros capturados.
            // Exemplo: $matches passa de ['/users/10', '10'] para ['10']
            array_shift($matches);

            // Executa cada middleware antes do controller.
            // Exemplo: $middlewareClass pode ser App\Middlewares\AuthMiddleware
            foreach ($config['middlewares'] as $middlewareClass) {
                // Instancia o middleware configurado pela aplicação.
                $middleware = new $middlewareClass();
                // Interrompe o fluxo se o middleware redirecionar ou responder.
                $middleware->handle();
            }

            // Divide a ação no nome do controller e no nome do método.
            // Exemplo: 'UserController@show' vira ['UserController', 'show']
            [$controllerName, $methodName] = explode('@', $config['action'], 2);
            
            // Monta o nome completo dentro do namespace da aplicação.
            // Exemplo: 'App\Controllers\UserController'
            $controllerClass = "App\\Controllers\\{$controllerName}";

            // Verifica se a classe existe antes de tentar instanciá-la.
            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller não encontrado: {$controllerClass}");
            }

            // Cria o controller responsável pela requisição.
            $controller = new $controllerClass();

            // Verifica se o método público existe antes de chamá-lo.
            if (!is_callable([$controller, $methodName])) {
                throw new RuntimeException("Método não encontrado: {$config['action']}");
            }

            // Entrega os parâmetros da URL ao método do controller.
            // Exemplo: chama $controller->show('10')
            $controller->{$methodName}(...$matches);
            
            // Encerra porque a primeira rota compatível já foi executada.
            return;
        }

        // Diferencia caminho inexistente de método HTTP não cadastrado.
        $hasPath = $this->pathExistsForOtherMethod($path, strtoupper($method));
        
        // Retorna 405 quando a URL existe, mas o método não é permitido.
        // Exemplo: A rota existe para GET /users, mas o usuário chamou DELETE /users
        if ($hasPath) {
            http_response_code(405);
            header('Allow: ' . $this->allowedMethodsFor($path));
            echo '405 - Método não permitido';
            return;
        }

        // Retorna 404 quando nenhuma rota conhece o caminho solicitado.
        http_response_code(404);
        $notFound = __DIR__ . '/../Views/errors/404.php';
        
        // Usa uma view existente quando o projeto da aplicação fornecê-la.
        if (is_file($notFound)) {
            require $notFound;
            return;
        }
        
        // Mantém uma resposta mínima para um projeto recém-criado.
        echo '404 - Página não encontrada';
    }

    // Converte uma rota declarativa em uma regex ancorada e segura.
    // Exemplo: Entrada '/posts/{id}/comments/{commentId}' -> Saída '#^/posts/([^/]+)/comments/([^/]+)/?$#'
    private function compilePattern(string $route): string
    {
        // Divide o caminho em segmentos para não aceitar travessia de diretório.
        $segments = explode('/', trim($route, '/'));
        
        // A raiz precisa de um padrão especial.
        if ($route === '/') {
            return '#^/$#';
        }

        // Escapa cada segmento literal antes de substituir parâmetros.
        $compiled = array_map(function (string $segment): string {
            // Aceita somente nomes simples entre chaves como parâmetros (ex: {id}, {slug})
            if (preg_match('/^\{[A-Za-z][A-Za-z0-9_]*\}$/', $segment)) {
                // Parâmetros não atravessam barras e são passados ao controller.
                return '([^/]+)';
            }
            // Segmentos comuns (ex: 'users') são tratados como texto literal.
            return preg_quote($segment, '#');
        }, $segments);

        // Reconstrói a rota e exige coincidencia completa.
        return '#^/' . implode('/', $compiled) . '/?$#';
    }

    // Remove o prefixo /public ou da subpasta quando o Apache o expõe.
    // Exemplo: Se o WampServer roda em 'localhost/mini-framework/public/users'
    // esta função limpa a URI para entregar apenas '/users'
    private function normalizeBasePath(string $path): string
    {
        // Obtém o diretório do script realmente executado (ex: '/mini-framework/public')
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        
        // Retorna imediatamente quando o script está na raiz pública.
        if ($scriptDir === '' || $scriptDir === '.') {
            return $path;
        }
        
        // Remove o prefixo somente quando ele é um segmento completo.
        if ($path === $scriptDir || str_starts_with($path, $scriptDir . '/')) {
            return substr($path, strlen($scriptDir)) ?: '/';
        } else {
            // 2. SEGUNDA TENTATIVA (FALLBACK): Executada se a path não começou diretamente com o $scriptDir.
            // Isso acontece quando o usuário acessa o projeto sem especificar a subpasta inteira ou via reescrita do Apache.

            // rtrim(dirname($scriptDir), '/') descobre a pasta pai do $scriptDir e remove barras no final.
            // Exemplo: Se $scriptDir for "/mini-framework/public", o dirname() devolve "/mini-framework".
            $projectRoot = rtrim(dirname($scriptDir), '/');

            // Validações de segurança e consistência para a pasta pai ($projectRoot):
            // 1. $projectRoot !== ''  -> Garante que o caminho pai não é vazio.
            // 2. $projectRoot !== '.' -> Garante que o caminho não é o diretório atual do sistema de arquivos.
            // 3. str_starts_with($path, $projectRoot) -> Confirma se a URL acessada começa com a pasta raiz do projeto.
            if ($projectRoot !== '' && $projectRoot !== '.' && str_starts_with($path, $projectRoot)) {

                // Se passar nas 3 validações, corta o caminho da pasta raiz ($projectRoot) do início da path.
                // Exemplo: Se $path for "/mini-framework/login" e $projectRoot for "/mini-framework",
                // a path limpa para o roteador passa a ser "/login".
                $path = substr($path, strlen($projectRoot));
            }
        }
        
        // Mantém o caminho original quando não há correspondência.
        return $path;
    }

    // Informa se o caminho existe cadastrado em outro método HTTP.
    // Usado para saber se deve disparar o erro 405 (Método Não Permitido) em vez de 404.
    private function pathExistsForOtherMethod(string $path, string $method): bool
    {
        // Percorre todos os métodos diferentes do método atual.
        foreach ($this->routes as $registeredMethod => $routes) {
            if ($registeredMethod === $method) {
                continue;
            }
            // Testa o caminho contra cada rota registrada.
            foreach (array_keys($routes) as $route) {
                if (preg_match($this->compilePattern($route), $path)) {
                    return true;
                }
            }
        }
        // Nenhum outro método reconheceu o caminho.
        return false;
    }

    // Monta o cabeçalho Allow para uma rota existente.
    // Exemplo de saída: 'GET, POST'
    private function allowedMethodsFor(string $path): string
    {
        // Seleciona os métodos que possuem rota para o caminho.
        $methods = [];
        foreach ($this->routes as $method => $routes) {
            foreach (array_keys($routes) as $route) {
                if (preg_match($this->compilePattern($route), $path)) {
                    $methods[] = $method;
                    break;
                }
            }
        }
        // Ordena e remove duplicidades antes de montar o cabeçalho.
        return implode(', ', array_unique($methods));
    }
}