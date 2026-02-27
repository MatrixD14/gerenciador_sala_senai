<?php
defined('APP') or die('Acesso negado');
if (session_status() === PHP_SESSION_NONE) session_start();
AuthLogin::check();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:," type="image/x-icon" />
    <link rel="stylesheet" href="app/view/vendor/admin/css/style.css">
    <title>admin</title>
</head>

<body>
    <?php require_once __DIR__ . '/../../../../icon/iconbases.html'; ?>
    <nav class="topbar"><a class="NameSite" href="/admin">Gerenciador de Sala</a><svg class="menu">
            <use href="#icon-afirma"></use>
        </svg></nav>
</body>

</html>