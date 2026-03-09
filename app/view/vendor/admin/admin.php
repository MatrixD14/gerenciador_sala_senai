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
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0, user-scalable=no">
        <link rel=" icon" href="data:," type="image/x-icon" />
        <link rel="stylesheet" href="app/view/css/body_site.css">
        <link rel="stylesheet" href="app/view/vendor/layout/css/hearder.css">
        <link rel="stylesheet" href="app/view/vendor/layout/css/footer.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/css/tabela.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/menuTop/css/topbar.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/menuPainel/css/buttonPainel.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/menuPainel/css/painelDelete.css">
        <link rel="stylesheet" href="app/view/vendor/tabelas/menuPainel/css/painelEditor.css">
        <link rel="stylesheet" href="app/view/vendor/agendamentos/css/calendario.css">
        <title>admin</title>
    </head>

    <body>
    <?php
    require_once __DIR__ . '/../../../../icon/newiconbase.html';
    require_once __DIR__ . '/../../../../app/view/vendor/layout/Top_admin.php';
} ?>
    <main class="content">
        <?php
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $rotasValidas = ['/usuarios', '/salas', '/agendamentos'];

        if (in_array($uri, $rotasValidas)) {
            require __DIR__ . "/../tabelas/Table.php";
        } elseif ($uri === '/calendario') {
            require __DIR__ . '/../agendamentos/Calendario.php';
        } else {
            echo "<h1>Bem-vindo ao Agendamento de Sala</h1>";
        }
        ?>
    </main>
    <?php if (!$isAjax) {
        require_once __DIR__ . '/../../../../app/view/vendor/layout/footer.php'; ?>
    </body>

    </html>
<?php } ?>