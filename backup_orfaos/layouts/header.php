<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>OPUS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS Externos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bombeiros-theme.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/navbar-universal.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/footer-robust.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>

<body class="d-flex flex-column min-vh-100 has-public-navbar" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">

<?php
$current = $_GET['url'] ?? 'home';
$segments = explode('/', $current);
$mainRoute = $segments[0] ?? 'home';
$subRoute  = $segments[1] ?? null;

// Importa navbar centralizada (site público)
$navbar_type = 'public';
include __DIR__ . '/../components/navbar.php';
?>

<?php
// O footer será incluído no final de cada página após o conteúdo principal
// Template: include 'layouts/components/footer.php' com $footer_type = 'public'
?>