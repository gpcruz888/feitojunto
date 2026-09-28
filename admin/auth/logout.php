<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF
    if (Csrf::validate()) {
        Auth::logout();
    }
} else {
    // Permite logout via GET caso acionado conscientemente
    Auth::logout();
}

header('Location: /admin/login');
exit;
