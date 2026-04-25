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
    <title>login</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1 class="h1-center">logar</h1>
        <p class="error">
            <?php if (isset($_SESSION["log_create"]))
                echo $_SESSION["log_create"];
            session_destroy();
            ?>
        </p>
        <form action="/login" method="post">
            <label for="nome">Nome</label><br />
            <input type="text" name="nome" id="nome" autocomplete="off" required /><br />
            <label for="senha">Senha</label><br />

            <div class="password-box">
                <input type="password" name="senha" id="senha" required />
                <button type="button" class="toggle-password" onclick="toggleSenha()">
                    👀
                </button>
            </div>
            <div class="box-center">
                <input type="submit" value="enter" class="bt-enter" /><br>
                <a class="link-a" href="/EmailRecuperacao">Esqueceu a senha?</a>
                <p></p>
                <a class="link-a" href="/cadastrar">Cadastrar</a>
            </div>
        </form>
    </div>
    <script>
        function toggleSenha() {
            const input = document.getElementById("senha");
            const button = document.querySelector(".toggle-password");

            if (input.type === "password") {
                input.type = "text";
                button.textContent = "🙈";
            } else {
                input.type = "password";
                button.textContent = "👀";
            }
        }
    </script>
</body>

</html>