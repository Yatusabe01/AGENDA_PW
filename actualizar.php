<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Solicitud no permitida.');
    redirect('index.php');
}

if (!csrf_is_valid(isset($_POST['_csrf']) ? $_POST['_csrf'] : '')) {
    flash('error', 'Token de seguridad no válido. Recargue el formulario e intente nuevamente.');
    redirect('index.php');
}

$id = to_id(input('id'));
if ($id === 0) {
    flash('error', 'El contacto indicado no es válido.');
    redirect('index.php');
}

try {
    $stmt = $pdo->prepare('SELECT foto FROM contactos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error al verificar contacto para actualización: ' . $e->getMessage());
    flash('error', 'No se pudo actualizar el contacto. Intente nuevamente.');
    redirect('index.php');
}

if ($existing === false) {
    flash('error', 'El contacto que intenta actualizar no existe.');
    redirect('index.php');
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
    redirect('editar.php?id=' . $id);
}

$file = isset($_FILES['foto']) && is_array($_FILES['foto']) ? $_FILES['foto'] : [];
list($photo, $uploadError) = upload_photo($file, false);
if ($uploadError !== null) {
    $errors[] = $uploadError;
    flash('old', $data);
    flash('errors', $errors);
    redirect('editar.php?id=' . $id);
}

$params = [
    ':nombre' => $clean['nombre'],
    ':apellido' => $clean['apellido'] !== '' ? $clean['apellido'] : null,
    ':telefono' => $clean['telefono'],
    ':email' => $clean['email'] !== '' ? $clean['email'] : null,
    ':direccion' => $clean['direccion'] !== '' ? $clean['direccion'] : null,
    ':notas' => $clean['notas'] !== '' ? $clean['notas'] : null,
    ':id' => $id,
];

if ($photo !== null) {
    $sql = 'UPDATE contactos SET nombre = :nombre, apellido = :apellido, telefono = :telefono, '
        . 'foto = :foto, email = :email, direccion = :direccion, notas = :notas WHERE id = :id';
    $params[':foto'] = $photo;
} else {
    $sql = 'UPDATE contactos SET nombre = :nombre, apellido = :apellido, telefono = :telefono, '
        . 'email = :email, direccion = :direccion, notas = :notas WHERE id = :id';
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    error_log('Error al actualizar contacto: ' . $e->getMessage());
    if ($photo !== null) {
        delete_photo($photo);
    }
    flash('error', 'No se pudo actualizar el contacto. Intente nuevamente.');
    redirect('editar.php?id=' . $id);
}

if ($photo !== null) {
    delete_photo(isset($existing['foto']) ? $existing['foto'] : '');
}

flash('success', 'Contacto actualizado correctamente.');
redirect('ver.php?id=' . $id);
