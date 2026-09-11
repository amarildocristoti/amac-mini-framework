<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- TROQUE o título abaixo pelo nome real da sua aplicação -->
    <title>Mini Framework</title>

    <!-- Bootstrap + ícones + SweetAlert2 já vêm prontos via CDN.
         Se não for usar algum, pode remover a linha correspondente. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Personalize aqui o visual global da sua aplicação */
        body { background-color: #f4f6f9; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06); }
    </style>
</head>
<body>

<!-- Barra de navegação simples. Troque o nome/ícone pelo da sua app.
     Se sua aplicação usar login (módulo de Auth), normalmente aqui
     entraria também um bloco condicional mostrando nome do usuário
     e um link de "Sair" -- veja o projeto de gerenciamento de
     usuários como referência para esse padrão. -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/">
            <i class="bi bi-app-indicator me-1"></i> Meu App
        </a>
    </div>
</nav>

<div class="container">
    <!-- Mensagens "flash" (aparecem uma vez e somem). Qualquer
         Controller pode disparar assim:
             $_SESSION['error']   = 'Algo deu errado.';
             $_SESSION['success'] = 'Feito com sucesso!';
         antes de um $this->redirect(...). -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- A partir daqui, o conteúdo de cada página específica é
         inserido pelo método view() (veja Traits/RendersView.php) -->
