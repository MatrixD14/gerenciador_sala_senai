<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="data:," type="image/x-icon" />
    <link rel="stylesheet" href="<?= URL ?>/css/global.css" />
    <link rel="stylesheet" href="<?= URL ?>/css/component/button.css" />
    <title>cadastro</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1>Termos de Uso</h1>

        <p>Ao utilizar este sistema de gerenciamento de salas, o usuário concorda com as seguintes condições:</p>

        <ul>
            <li>As informações cadastradas devem ser verdadeiras;</li>
            <li>O sistema é destinado ao uso acadêmico;</li>
            <li>O usuário é responsável pelas reservas realizadas;</li>
            <li>Os dados não serão compartilhados com terceiros sem autorização;</li>
        </ul>

        <p>Ao se cadastrar, você concorda com estes termos.</p>
        <div class="box-center">
            <a class="link-a" href="/cadastrar">sair</a>
        </div>
    </div>
</body>

</html>