<?php
defined('APP') or die('Acesso negado');
if (session_status() === PHP_SESSION_NONE) session_start();
AuthLogin::check(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:," type="image/x-icon" />
    <link rel="stylesheet" href="app/view/css/body_site.css">
    <link rel="stylesheet" href="app/view/vendor/layout/css/hearder.css">
    <link rel="stylesheet" href="app/view/vendor/layout/css/footer.css">
    <title>gerenciador de sala</title>
</head>

<body>
    <?php
    require_once __DIR__ . '/../../../../icon/newiconbase.html';
    require_once __DIR__ . '/../../../../app/view/vendor/layout/Top_public.php'; ?>
    <main class="content">
        Conteúdo da página
    </main>
    <?php require_once __DIR__ . '/../../../../app/view/vendor/layout/footer.php'; ?>
</body>

</html>