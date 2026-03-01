<?php
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
require_once __DIR__ . '/app/model/login.php';
require_once __DIR__ . '/app/controller/tabela/tabelas.php';
