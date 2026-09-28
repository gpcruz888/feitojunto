<?php
/**
 * Head Component — feito.
 * Accepts optional $pageTitle and $pageDescription variables.
 */
if (!isset($pageTitle)) {
    $pageTitle = 'feito. presença digital feita do jeito certo.';
}
if (!isset($pageDescription)) {
    $pageDescription = 'feito. — sua presença digital, feita do jeito certo. Sites, landing pages e sistemas web desenvolvidos para empresas.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>

  <!-- Start cookieyes banner -->
  <script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/de6f787ad9c59e3c016c6a241b79ac64/script.js"></script>
  <!-- End cookieyes banner -->

  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=satoshi@900,700,600,500,400,300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/main.css?v=<?= time(); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png?v=3">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon.png?v=3">
  <link rel="shortcut icon" href="/favicon.ico?v=3" type="image/x-icon">
  <link rel="apple-touch-icon" href="/assets/favicon.png?v=3">
</head>
<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass) : ''; ?>">
