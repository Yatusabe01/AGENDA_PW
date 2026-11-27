<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Agenda de contactos';
$successMessage = take_flash('success');
$errorMessage = take_flash('error');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($pageTitle); ?> — Agenda</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">Agenda</a>
        <?php require __DIR__ . '/menu.php'; ?>
    </div>
</header>
<main class="container">
    <?php if ($successMessage): ?>
        <div class="alert alert-success" role="status"><?php echo h($successMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error" role="alert"><?php echo h($errorMessage); ?></div>
    <?php endif; ?>
