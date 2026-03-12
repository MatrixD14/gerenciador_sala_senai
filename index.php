<?php
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file($file))
        return false;
}
ob_start();
header('Content-Type: text/html; charset=UTF-8');
define('APP', true);
if (session_status() === PHP_SESSION_NONE)
    session_start();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$staticDirs = ['css', 'js', 'img', 'fonts'];

foreach ($staticDirs as $dir) {
    if (strpos($path, "/$dir/") !== false) {
        $base = realpath(__DIR__ . '/app/view');

        $file = realpath($base . $path);

        if ($file && str_starts_with($file, $base) && is_file($file)) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $mime = [
                'css'  => 'text/css; charset=UTF-8',
                'js'   => 'application/javascript; charset=UTF-8',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'svg'  => 'image/svg+xml',
            ];

            header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
            readfile($file);
            exit;
        }
    }
}
require_once __DIR__ . '/bootstrap.php';
//essa aria deleta os agendamento de 1 ano que passa que e no caso de 365dia
TabelaCleanup::autoCleanup(365);
$uri = rtrim($path, '/');
if ($uri === '') $uri = '/';
if ($uri === '/') {
    require __DIR__ . '/app/view/login.php';
    exit;
}
if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthLogin::login();
    exit;
}

if ($uri === '/logout') {
    AuthLogin::logout();
    exit;
}
if ($uri === '/cadastrar') {
    require __DIR__ . '/app/view/vendor/cadastro/cadastar.php';
    exit;
}
if ($uri === '/cadastro') {
    Database::connects();
    AuthLogin::cadastro();
    exit;
}

$rotasAdmin = ['/usuarios', '/salas', '/agendamentos'];
$rotaAcao = ['/delete', '/editar', '/insert', '/pesquisa'];
$HomeGenciador = __DIR__ . '/app/view/vendor/layout/main.php';
if ($uri === "/gerenciado_de_Sala") {
    AuthLogin::check();
    require $HomeGenciador;
    exit;
}
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
if (in_array($uri, $rotaAcao)) {
    AuthLogin::check();

    if ($isAjax) {
        $arquivo = ltrim($uri, '/');
        require __DIR__ . "/app/view/vendor/tabelas/menuPainel/{$arquivo}.php";
    } else {
        require $HomeGenciador;
    }
    exit;
}

if (in_array($uri, $rotasAdmin)) {
    AuthLogin::check();

    $Tabelas = ltrim($uri, '/');
    if ($isAjax) {
        require __DIR__ . "/app/view/vendor/tabelas/Table.php";
    } else {
        require $HomeGenciador;
    }
    exit;
}
if ($uri === "/calendario") {
    AuthLogin::check();
    if ($isAjax) {
        require __DIR__ . '/app/view/vendor/agendamentos/Calendario.php';
    } else {
        require $HomeGenciador;
    }
    exit;
}
if ($uri === "/deleted") {
    Delete::delete();
    exit;
}
if ($uri === '/edito') {
    editor::editoDados();
    exit;
}
if ($uri === '/inserted') {
    Inserted::inserted();
    exit;
}
if ($uri === '/CalendarioAgendamento') {
    Calendario::datasAgendamentos();
    exit;
}
if ($uri === '/menssageCalendario') {
    require __DIR__ . "/app/view/vendor/agendamentos/menssageAgenda.php";
    exit;
}
if ($uri === '/recuperarSenha') {
    require __DIR__ . "/app/view/vendor/recuperaSenha/verificarToken.php";
    exit;
}
if ($uri === '/EmailRecuperacao') {
    require __DIR__ . "/app/view/vendor/recuperaSenha/verificarEmail.php";
    exit;
}
http_response_code(404);
echo 'Página não encontrada';
ob_end_flush();
