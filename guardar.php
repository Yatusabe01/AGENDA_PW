<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Solicitud no permitida.');
    redirect('crear.php');
}

if (!csrf_is_valid(isset($_POST['_csrf']) ? $_POST['_csrf'] : '')) {
    flash('error', 'Token de seguridad no válido. Recargue el formulario e intente nuevamente.');
    redirect('crear.php');
}

$data = [
    'nombre' => input('nombre'),
    'apellido' => input('apellido'),
    'telefono' => input('telefono'),
    'email' => input('email'),
    'direccion' => input('direccion'),
    'notas' => input('notas'),
];
list($clean, $errors) = validate_contact($data);

if (count($errors) > 0) {
    flash('old', $data);
    flash('errors', $errors);
    redirect('crear.php');
}

$file = isset($_FILES['foto']) && is_array($_FILES['foto']) ? $_FILES['foto'] : [];
list($photo, $uploadError) = upload_photo($file, true);
if ($uploadError !== null) {
    $errors[] = $uploadError;
    flash('old', $data);
    flash('errors', $errors);
    redirect('crear.php');
}

$sql = 'INSERT INTO contactos (nombre, apellido, telefono, foto, email, direccion, notas) '
    . 'VALUES (:nombre, :apellido, :telefono, :foto, :email, :direccion, :notas)';
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $clean['nombre'],
        ':apellido' => $clean['apellido'] !== '' ? $clean['apellido'] : null,
        ':telefono' => $clean['telefono'],
        ':foto' => $photo,
        ':email' => $clean['email'] !== '' ? $clean['email'] : null,
        ':direccion' => $clean['direccion'] !== '' ? $clean['direccion'] : null,
        ':notas' => $clean['notas'] !== '' ? $clean['notas'] : null,
    ]);
} catch (PDOException $e) {
    error_log('Error al crear contacto: ' . $e->getMessage());
    if ($photo !== null) {
        delete_photo($photo);
    }
    flash('error', 'No se pudo guardar el contacto. Intente nuevamente.');
    redirect('crear.php');
}

flash('success', 'Contacto creado correctamente.');
redirect('index.php');
