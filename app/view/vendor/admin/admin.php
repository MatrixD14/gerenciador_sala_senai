<?php
defined('APP') or die('Acesso negado');
if (session_status() === PHP_SESSION_NONE) session_start();
AuthLogin::check();
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if (!$isAjax) {
?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="data:," type="image/x-icon" />
        <link rel="stylesheet" href="app/view/css/body_site.css">
        <link rel="stylesheet" href="app/view/vendor/layout/css/hearder.css">
        <link rel="stylesheet" href="app/view/vendor/layout/css/footer.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/css/tabela.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/menuTop/css/topbar.css">
        <title>admin</title>
    </head>

    <body>
    <?php
    require_once __DIR__ . '/../../../../icon/newiconbase.html';
    require_once __DIR__ . '/../../../../app/view/vendor/layout/Top_admin.php';
} ?>
    <main class="content">
        <?php
        if (!$isAjax) {
            $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $tabelas = [
                '/usuarios' => 'usuarios.php',
                '/salas' => 'salas.php',
                '/agendamentos' => 'agendamentos.php'
            ];

            if (isset($tabelas[$uri])) {
                require __DIR__ . "/../tabelas/" . $tabelas[$uri];
            } else {
                echo "<h1>Bem-vindo ao Agendamento de Sala</h1>";
            }
        } ?>
    </main>
    <?php if (!$isAjax) {
        require_once __DIR__ . '/../../../../app/view/vendor/layout/footer.php'; ?>
    </body>

    </html>
<?php } ?>