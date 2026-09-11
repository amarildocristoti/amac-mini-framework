# Mini Framework MVC

Base simples e reutilizável para aplicações web em **PHP puro**, com arquitetura MVC, roteamento, conexão PDO, segurança básica e testes automatizados.

## Recursos

- Router com GET, POST, PUT, PATCH e DELETE.
- Controllers com entrada, redirecionamento interno e resposta JSON.
- Conexão PDO com prepared statements e exceções.
- Proteção CSRF, escape HTML e validação de e-mail.
- Sessão com `HttpOnly`, `SameSite` e `Secure` em produção.
- PHPUnit e lint PHP executáveis pelo Composer.

## Requisitos

PHP 8.1 ou superior, Composer e PDO com o driver do banco utilizado pela aplicação.

## Instalação

```bash
cp .env.example .env
composer install
composer validate --no-check-publish
composer lint
composer test
php -S localhost:8000 -t public
```

No Windows PowerShell, use os mesmos comandos dentro da pasta do projeto, substituindo apenas a cópia do ambiente quando necessário:

```powershell
Copy-Item .env.example .env
composer install
```

Configure o `.env` antes de utilizar banco de dados. Nunca publique o `.env` real.

## Estrutura

```text
config/                 Bootstrap, ambiente e sessão
public/                 Front controller e arquivos públicos
src/Core/               Componentes reutilizáveis do framework
src/Controllers/        Controllers da aplicação
src/Views/              Views e layouts
tests/                  Testes automatizados
tools/                  Ferramentas de qualidade
composer.json           Dependências e comandos
composer.lock           Versões fixadas das dependências
.env.example            Modelo de configuração
```

## Criando rotas

```php
// Página de leitura.
$router->get('/tasks', 'TaskController@index');

// Operação de criação.
$router->post('/tasks', 'TaskController@store');
```

## Segurança

Use `Security::csrfField()` nos formulários e valide o token antes de alterar dados. 
Use `Security::escape()` ao exibir valores externos em HTML. 
Toda consulta com dados recebidos do usuário deve utilizar prepared statements.

Em produção, utilize HTTPS, defina `APP_ENV=production` e mantenha `APP_KEY` em segredo.

## Testes

```bash
composer lint
composer test
```

Os testes atuais verificam segurança e roteamento. Novas funcionalidades devem ser acompanhadas por testes próprios.

## Licença

Distribuído sob a licença MIT. Consulte o arquivo `LICENSE`.
