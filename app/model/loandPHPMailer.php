<?php
if (!interface_exists('Psr\Log\LoggerInterface')) {
    eval('
        namespace Psr\Log;
        interface LoggerInterface {
            public function emergency($m, array $c = []); public function alert($m, array $c = []);
            public function critical($m, array $c = []); public function error($m, array $c = []);
            public function warning($m, array $c = []); public function notice($m, array $c = []);
            public function info($m, array $c = []); public function debug($m, array $c = []);
            public function log($l, $m, array $c = []);
        }
        interface LoggerAwareInterface { public function setLogger(LoggerInterface $logger); }
        class LogLevel { const EMERGENCY="emergency"; const ALERT="alert"; const CRITICAL="critical"; const ERROR="error"; const WARNING="warning"; const NOTICE="notice"; const INFO="info"; const DEBUG="debug"; }
    ');
}

// Subindo dois níveis para chegar na raiz e entrar em PHPMailer
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
