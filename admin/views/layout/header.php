<?php
require_once dirname(__DIR__, 3) . '/core/Security.php';
Security::sendHeaders(true);
require_once dirname(__DIR__, 3) . '/core/Auth.php';
require_once dirname(__DIR__, 3) . '/core/Csrf.php';
require_once dirname(__DIR__, 3) . '/core/Helpers.php';

Auth::requireLogin();

$currentUser = Auth::user();
$pageTitle = $adminTitle ?? 'Painel';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?php echo Csrf::getToken(); ?>">
  <title><?php echo Helpers::e($pageTitle); ?> | Admin feito.</title>

  <!-- Satoshi Font -->
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=satoshi@900,700,600,500,400,300&display=swap" rel="stylesheet">

  <!-- Core tokens and Admin Styles -->
  <link rel="stylesheet" href="/css/variables.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/css/components/buttons.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/admin/assets/css/admin.css?v=<?= time(); ?>">
  
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png?v=3">
</head>
<body class="admin-body">

<div class="admin-wrapper">
  <!-- Sidebar Navigation -->
  <?php require_once __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content Wrapper -->
  <main class="admin-main">
    <!-- Topbar Sticky Header -->
    <header class="admin-topbar">
      <div style="display: flex; align-items: center; gap: 16px;">
        <button class="admin-toggle-menu" id="admin-sidebar-toggle" aria-label="Abrir Menu">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <h1 class="admin-topbar__title">
          <?php echo Helpers::e($pageTitle); ?>
        </h1>
      </div>

      <div class="admin-topbar__actions">
        <?php if (!empty($topbarActions)) echo $topbarActions; ?>
        <a href="/" target="_blank" rel="noopener" class="admin-action-btn" title="Visualizar site público">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      </div>
    </header>

    <div class="admin-content">
