<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value)
{
    return htmlspecialchars($value === null ? '' : (string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($location)
{
    header('Location: ' . $location);
    exit;
}

function flash($type, $message)
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $message;
}

function take_flash($type, $default = null)
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash']) || !array_key_exists($type, $_SESSION['flash'])) {
        return $default;
    }
    $value = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $value;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    echo '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function csrf_is_valid($token)
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function input($key, $default = '')
{
    if (!isset($_POST[$key]) || is_array($_POST[$key])) {
        return $default;
    }
    return trim((string) $_POST[$key]);
}

function old_value(array $old, $key, $default = '')
{
    if (!array_key_exists($key, $old) || is_array($old[$key])) {
        return $default;
    }
    return (string) $old[$key];
}

function to_id($value)
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? 0 : (int) $id;
}

function is_too_long($value, $max)
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8') > $max
        : strlen($value) > $max;
}

function validate_contact($data)
{
    $clean = [
        'nombre' => isset($data['nombre']) ? (string) $data['nombre'] : '',
        'apellido' => isset($data['apellido']) ? (string) $data['apellido'] : '',
        'telefono' => isset($data['telefono']) ? (string) $data['telefono'] : '',
        'email' => isset($data['email']) ? (string) $data['email'] : '',
        'direccion' => isset($data['direccion']) ? (string) $data['direccion'] : '',
        'notas' => isset($data['notas']) ? (string) $data['notas'] : '',
    ];
    $errors = [];

    if ($clean['nombre'] === '') {
        $errors[] = 'El nombre es obligatorio.';
    } elseif (is_too_long($clean['nombre'], 100)) {
        $errors[] = 'El nombre no puede superar 100 caracteres.';
    }

    if (is_too_long($clean['apellido'], 100)) {
        $errors[] = 'El apellido no puede superar 100 caracteres.';
    }

    if ($clean['telefono'] === '') {
        $errors[] = 'El teléfono es obligatorio.';
    } elseif (!preg_match('/^[0-9+()\s-]{7,40}$/', $clean['telefono'])) {
        $errors[] = 'El teléfono solo puede contener números, espacios, +, - y paréntesis.';
    } elseif (preg_match_all('/\d/', $clean['telefono']) < 7) {
        $errors[] = 'El teléfono debe tener al menos 7 dígitos.';
    }

    if ($clean['email'] !== '' && !filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo electrónico no tiene un formato válido.';
    } elseif (is_too_long($clean['email'], 255)) {
        $errors[] = 'El correo electrónico no puede superar 255 caracteres.';
    }

    if (is_too_long($clean['direccion'], 255)) {
        $errors[] = 'La dirección no puede superar 255 caracteres.';
    }

    if (is_too_long($clean['notas'], 2000)) {
        $errors[] = 'Las notas no pueden superar 2000 caracteres.';
    }

    return [$clean, $errors];
}

function upload_photo(array $file, $required)
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return [null, 'No se recibió una foto válida.'];
    }

    $error = (int) $file['error'];
    if ($error === UPLOAD_ERR_NO_FILE) {
        return $required ? [null, 'La foto es obligatoria.'] : [null, null];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return [null, 'No se pudo subir la foto. Intente nuevamente.'];
    }

    if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return [null, 'No se pudo subir la foto. Intente nuevamente.'];
    }

    if ((int) $file['size'] > 2 * 1024 * 1024) {
        return [null, 'La foto no puede superar 2 MB.'];
    }

    if (!function_exists('finfo_open') || !function_exists('finfo_file')) {
        return [null, 'No se puede validar el tipo de imagen en este servidor.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $info = finfo_open(FILEINFO_MIME_TYPE);
    if ($info === false) {
        return [null, 'No se puede validar el tipo de imagen en este servidor.'];
    }
    $mime = (string) finfo_file($info, $file['tmp_name']);
    finfo_close($info);
    if (!isset($allowed[$mime])) {
        return [null, 'La foto debe ser JPEG, PNG, GIF o WebP.'];
    }

    $originalName = isset($file['name']) && is_string($file['name'])
        ? basename(str_replace('\\', '/', $file['name']))
        : '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) {
        $extension = $allowed[$mime];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = __DIR__ . '/../archivos/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [null, 'No se pudo guardar la foto. Intente nuevamente.'];
    }

    return [$filename, null];
}

function delete_photo($filename)
{
    if (!is_string($filename) || $filename === '') {
        return false;
    }

    $safeName = basename(str_replace('\\', '/', $filename));
    if ($safeName !== $filename || $safeName === '.' || $safeName === '..') {
        return false;
    }

    $path = __DIR__ . '/../archivos/' . $safeName;
    if (!is_file($path)) {
        return false;
    }

    return @unlink($path);
}

function photo_src($filename)
{
    if (!is_string($filename) || $filename === '') {
        return '';
    }
    return 'archivos/' . rawurlencode($filename);
}

function display_name($contact)
{
    $first = isset($contact['nombre']) ? trim((string) $contact['nombre']) : '';
    $last = isset($contact['apellido']) ? trim((string) $contact['apellido']) : '';
    return trim($first . ' ' . $last);
}
