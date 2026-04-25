<?php
if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="data:," type="image/x-icon" />
    <link rel="stylesheet" href="<?= URL ?>/css/global.css" />
    <link rel="stylesheet" href="<?= URL ?>/css/component/button.css" />
    <title>Redefinir Senha</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1 class="h1-center">Redefinir Senha</h1>
        <p class="error">
            <?php if (isset($_SESSION["erro_token"])) {
                echo $_SESSION["erro_token"];
                unset($_SESSION["erro_token"]);
            }
            ?>
        </p>
        <form action="/verificarPassWord" method="post">
            <label for="senha">Nova senha*</label><br />
            <input type="password" name="senha" id="senha" autocomplete="off" required /><br>
            <label for="confirmar_senha">Confirmar senha*</label><br />
            <input type="password" name="confirmar_senha" id="confirmar_senha" autocomplete="off" required /><br><br>
            <div class="box-center">
                <input type="submit" value="Alterar senha" class="bt-enter" />
                <p></p>
                <a class="link-a" href="/">logan</a><br>
            </div>
        </form>
    </div>
</body>

</html>