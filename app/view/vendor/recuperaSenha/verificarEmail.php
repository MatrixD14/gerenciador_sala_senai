<?php
if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="data:," type="image/x-icon" />
    <link rel="stylesheet" href="app/view/css/global.css" />
    <link rel="stylesheet" href="app/view/css/component/button.css" />
    <title>login</title>
    <style>
        input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>

<body class="body-center">
    <div class="center box-border">
        <h1 class="h1-center">recuperar senha</h1>
        <div class="center">
            <p style="color: red">
                <?php if (isset($_SESSION["log_create"]))
                    echo $_SESSION["log_create"];
                session_destroy();
                ?>
            </p>
            <form action="/verificaToken" method="post">
                <label for="email">Email</label><br />
                <input type="email" name="email" id="email" autocomplete="off" /><br><br>
                <div class="box-center">
                    <input type="submit" value="enter" class="bt-enter" />
                    <p></p>
                    <a class="link-a" href="/">logan</a><br>
                </div>
            </form>
        </div>
    </div>
</body>

</html>