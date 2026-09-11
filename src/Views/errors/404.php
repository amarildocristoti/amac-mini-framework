<?php
/**
 * View de erro 404, usada pelo Router quando nenhuma rota bate
 * com a URL pedida. E chamada com "require" direto (nao passa por
 * $this->view()), entao monta o HTML completo sozinha -- nao tem
 * acesso ao header.php/footer.php do layout normal.
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Página não encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f4f6f9;">
    <div class="text-center">
        <i class="bi bi-signpost-2 display-1 text-secondary"></i>
        <h2 class="fw-bold mt-3">Página não encontrada</h2>
        <p class="text-muted">O endereço que você tentou acessar não existe.</p>
        <a href="<?= APP_URL ?>/" class="btn btn-primary mt-2">Voltar ao início</a>
    </div>
</body>
</html>
