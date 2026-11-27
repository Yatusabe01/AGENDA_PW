<?php
// El estudiante debe reemplazar estos valores por las credenciales de su hosting.
define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_base_de_datos');
define('DB_USER', 'usuario_base_de_datos');
define('DB_PASS', 'contrasena_base_de_datos');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Error de conexion a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Servicio no disponible</title><link rel="stylesheet" href="css/styles.css"></head><body><main class="container"><section class="card"><h1>Servicio no disponible</h1><p>No fue posible conectar con la base de datos. Intente nuevamente más tarde.</p></section></main></body></html>';
    exit;
}