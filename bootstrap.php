<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$isLocal = (
    strpos($host, 'localhost') !== false ||
    strpos($host, '127.0.0.1') !== false ||
    strpos($host, '192.168') !== false
);

$subFolder = $isLocal ? '/app/view' : '';

define('URL', $protocol . '://' . $host . $subFolder);
define('URLs', $protocol . '://' . $host);
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('memory_limit', '256M');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once 'app/model/editorConf.php';

Env::load(__DIR__ . '/.editorConf');
//login
require_once __DIR__ . '/app/controller/login/auth_login.php';
require_once __DIR__ . '/app/model/connectDataBase.php';
require_once __DIR__ . '/app/model/buscaInfoUser.php';
require_once __DIR__ . '/app/model/login.php';
require_once __DIR__ . "/app/model/revindica.php";
require_once __DIR__ . '/app/model/loandPHPMailer.php';
require_once __DIR__ . '/app/model/EnviaInforEmail.php';
require_once __DIR__ . '/app/controller/tabela/tabelas.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/TableTop.php';

require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/renderGeraForm/formRelationServices.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/renderGeraForm/formRenderer.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/renderGeraForm/formEngine.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/renderGeraForm/completeList.php';

require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/delete.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/editor.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/AdicionarDados.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/deleteAgendamentoOld.php';
require_once __DIR__ . '/app/controller/tabela/menutopTable/functionIcon/revindicarUsuario.php';
require_once __DIR__ . '/app/controller/calendario/calendario.php';
//essa aria deleta os agendamento de 1 ano que passa que e no caso de 365dia
TabelaCleanup::autoCleanupTableAgendamento(365);
TabelaCleanup::autoCleanupReivindicacao(365);
revindicar::ExperarReivindicacao();
//fkuq ooko salu hxhu