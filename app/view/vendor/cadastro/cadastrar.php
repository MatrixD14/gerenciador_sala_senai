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
    <title>cadastro</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1 class="h1-center">cadastrar</h1>
        <p class="error">
            <?php if (isset($_SESSION["log_create"]))
                echo $_SESSION["log_create"];
            session_destroy();
            ?>
        </p>
        <!-- action="/cadastro"  -->
        <form method="post" autocomplete="off" onsubmit="confirmaPassword(event) ">
            <label for="nome">Nome</label><br />
            <input type="text" name="nome" id='nome' autocomplete="off" required /><br />
            <label for="email">Email</label><br />
            <input type="email" name="email" id='email' autocomplete="off" required /><br />
            <label for="senha">Senha</label><br />

            <div class="password-box">
                <input type="password" name="senha" id="senha" required />
                <button type="button" class="toggle-password" onclick="toggleSenha(this)">
                    👀
                </button>
            </div>
            <label for="confirmaSenha">Confirmar a Senha</label><br />
            <div class="password-box">
                <input type="password" name="confirmaSenha" id="confirmaSenha" required />
                <button type="button" class="toggle-password" onclick="toggleSenha(this)">
                    👀
                </button>
            </div>
            <label for="termos">
                <input type="checkbox" name="termos" id='termos' autocomplete="off" required />Eu li e aceito os <br><a class="link-a" href="/termos" target="_blank">Termos de Uso</a></label><br /><br />
            <div class="box-center">
                <input type="submit" value="enter" class="bt-enter" />
                <p></p>
                <a class="link-a" href="/">login</a>
            </div>
        </form>
    </div>
    <script src="<?= URL ?>/js/global.js"></script>
</body>

</html>