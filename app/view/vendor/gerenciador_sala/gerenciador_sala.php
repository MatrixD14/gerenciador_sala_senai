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

    <title>gerenciador de sala</title>
</head>

<body>
    <nav></nav>
</body>

</html>