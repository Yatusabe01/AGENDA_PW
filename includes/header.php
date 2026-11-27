<?php
require_once __DIR__ . '/functions.php';

if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Agenda de contactos';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <?php
    $messages = take_flash('success', []);

    if (!is_array($messages)) {
        $messages = [$messages];
    }

    foreach ($messages as $message) {
        if ($message === null || $message === '') {
            continue;
        }

        echo '<div class="alert alert-success" role="alert">' . h($message) . '</div>';
    }

    $messages = take_flash('error', []);

    if (!is_array($messages)) {
        $messages = [$messages];
    }

    foreach ($messages as $message) {
        if ($message === null || $message === '') {
            continue;
        }

        echo '<div class="alert alert-error" role="alert">' . h($message) . '</div>';
    }

    $errors = take_flash('errors', []);

    if (!is_array($errors)) {
        $errors = [$errors];
    }

    if ($errors !== []) {
        echo '<div class="alert alert-error" role="alert"><ul>';

        foreach ($errors as $error) {
            if ($error === null || $error === '') {
                continue;
            }

            echo '<li>' . h($error) . '</li>';
        }

        echo '</ul></div>';
    }
    ?>
