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
    <title>checar token</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1 class="h1-center">recuperar senha</h1>
        <p class="error">
            <?php if (isset($_SESSION["erro_token"])) {
                echo $_SESSION["erro_token"];
                unset($_SESSION["erro_token"]);
            }
            ?>
        </p>
        <form action="/checkToken" method="post">
            <label for="token"><b>Confirme sua conta </b></label><br />
            <p style="font-size: 12px;">Enviamos um código para seu <br> email. Insira esse código para <br>confirmar sua conta.</p>
            <input type="text" name="token" id="token" autocomplete="off" /><br>

            <br>
            <div class="box-center">
                <form action="/regerar_token" method="post" style="margin-top: 15px;">
                    <button type="submit" class="bt-enter" style="background-color: #6c757d;">Reenviar token</button>
                </form><br><br>
                <input type="submit" value="enter" class="bt-enter" />
                <p></p>
                <a class="link-a" href="/EmailRecuperacao">volta</a><br>
                <a class="link-a" href="/">login</a>
            </div>
        </form>
    </div>
</body>

</html>