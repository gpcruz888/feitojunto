<?php
/**
 * Front Controller da Área Administrativa — feito.
 * Roteamento central e proteção unificada de rotas.
 */

// Inicializa configurações e banco
require_once __DIR__ . '/../core/Schema.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Database.php';

try {
    Schema::up();
} catch (Exception $e) {
    error_log("Schema initialization failed: " . $e->getMessage());
}

// Determina rota atual a partir de ?route= ou path
$route = trim($_GET['route'] ?? '', '/');

if ($route === '' || $route === 'admin') {
    $route = 'dashboard';
}

// Rotas públicas de autenticação
if ($route === 'login') {
    require_once __DIR__ . '/auth/login.php';
    exit;
}
if ($route === 'logout') {
    require_once __DIR__ . '/auth/logout.php';
    exit;
}
if ($route === 'recuperar-senha') {
    require_once __DIR__ . '/auth/reset-password.php';
    exit;
}

// Protege todas as rotas abaixo com autenticação mandatória no servidor
Auth::requireLogin();

// Roteador de Views
switch ($route) {
    case 'dashboard':
        $currentRoute = 'dashboard';
        require_once __DIR__ . '/views/dashboard.php';
        break;

    case 'leads':
        $currentRoute = 'leads';
        require_once __DIR__ . '/views/leads/index.php';
        break;

    case 'leads/view':
        $currentRoute = 'leads';
        require_once __DIR__ . '/views/leads/view.php';
        break;

    case 'leads/export':
        $currentRoute = 'leads';
        require_once __DIR__ . '/views/leads/export.php';
        break;

    case 'blog':
        $currentRoute = 'blog';
        require_once __DIR__ . '/views/blog/index.php';
        break;

    case 'blog/novo':
        $currentRoute = 'blog/novo';
        require_once __DIR__ . '/views/blog/form.php';
        break;

    case 'blog/editar':
        $currentRoute = 'blog';
        require_once __DIR__ . '/views/blog/form.php';
        break;

    case 'blog/categorias':
        $currentRoute = 'blog/categorias';
        require_once __DIR__ . '/views/blog/categories.php';
        break;

    case 'admins':
        $currentRoute = 'admins';
        Auth::requireRole('SUPER_ADMIN');
        require_once __DIR__ . '/views/admins/index.php';
        break;

    case 'admins/novo':
    case 'admins/editar':
        $currentRoute = 'admins';
        Auth::requireRole('SUPER_ADMIN');
        require_once __DIR__ . '/views/admins/form.php';
        break;

    case 'configuracoes':
        $currentRoute = 'configuracoes';
        Auth::requireRole('SUPER_ADMIN');
        require_once __DIR__ . '/views/settings.php';
        break;

    case 'logs':
        $currentRoute = 'logs';
        Auth::requireRole('SUPER_ADMIN');
        require_once __DIR__ . '/views/logs.php';
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/views/404.php';
        break;
}
