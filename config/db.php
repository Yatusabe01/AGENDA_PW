<?php
// Reemplaza estos valores por las credenciales de tu hosting antes de usar la agenda.
define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_base_de_datos');
define('DB_USER', 'usuario_base_de_datos');
define('DB_PASS', 'contrasena_base_de_datos');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Error de conexión - Agenda de contactos</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>No se pudo conectar con la base de datos</h1>
                <p>La agenda no está disponible en este momento. Inténtalo de nuevo más tarde.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
