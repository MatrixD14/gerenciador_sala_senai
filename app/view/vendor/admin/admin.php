<?php
defined('APP') or die('Acesso negado');
if (session_status() === PHP_SESSION_NONE) session_start();
AuthLogin::check();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <nav></nav>
</body>
</html>