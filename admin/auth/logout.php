<?php
require_once __DIR__ . '/../../core/Security.php';
Security::sendHeaders(true);

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::validate()) {
    Auth::logout();
    header('Location: /admin/login');
    exit;
}

if (Auth::check()) {
    header('Location: /admin/dashboard');
    exit;
}

header('Location: /admin/login');
exit;
