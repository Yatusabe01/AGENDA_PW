<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value)
{
    if ($value === null) {
        return '';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($location)
{
    header('Location: ' . $location);
    exit;
}

function flash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function take_flash($type, $default = null)
{
    if (!isset($_SESSION['flash'][$type])) {
        return $default;
    }

    $messages = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);

    return $messages;
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
    if (!is_string($token)) {
        return false;
    }

    return hash_equals(csrf_token(), $token);
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
    if (is_array($value) || is_object($value)) {
        return 0;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        return 0;
    }

    return $id;
}

function is_too_long($value, $max)
{
    $text = (string) $value;
    $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);

    return $length > $max;
}

function validate_contact($data)
{
    $value = static function ($key) use ($data) {
        if (is_array($data) && isset($data[$key]) && !is_array($data[$key])) {
            return trim((string) $data[$key]);
        }

        return '';
    };

    $clean = [
        'nombre' => $value('nombre'),
        'apellido' => $value('apellido'),
        'telefono' => $value('telefono'),
        'email' => $value('email'),
        'direccion' => $value('direccion'),
        'notas' => $value('notas'),
    ];

    $errors = [];

    if ($clean['nombre'] === '') {
        $errors['nombre'] = 'El nombre es obligatorio.';
    } elseif (is_too_long($clean['nombre'], 100)) {
        $errors['nombre'] = 'El nombre no puede superar los 100 caracteres.';
    }

    if (is_too_long($clean['apellido'], 100)) {
        $errors['apellido'] = 'El apellido no puede superar los 100 caracteres.';
    }

    if ($clean['telefono'] === '') {
        $errors['telefono'] = 'El teléfono es obligatorio.';
    } elseif (!preg_match('/^[0-9+()\s-]{7,40}$/', $clean['telefono'])) {
        $errors['telefono'] = 'El teléfono solo puede contener números, espacios y los símbolos + ( ) -.';
    } elseif (preg_match_all('/\d/', $clean['telefono'], $matches) < 7) {
        $errors['telefono'] = 'El teléfono debe tener al menos 7 dígitos.';
    }

    if ($clean['email'] !== '') {
        if (is_too_long($clean['email'], 255)) {
            $errors['email'] = 'El correo electrónico no puede superar los 255 caracteres.';
        } elseif (filter_var($clean['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'El correo electrónico no es válido.';
        }
    }

    if (is_too_long($clean['direccion'], 255)) {
        $errors['direccion'] = 'La dirección no puede superar los 255 caracteres.';
    }

    if (is_too_long($clean['notas'], 2000)) {
        $errors['notas'] = 'Las notas no pueden superar los 2000 caracteres.';
    }

    return [$clean, $errors];
}

function upload_photo(array $file, $required)
{
    $errorCode = isset($file['error']) && !is_array($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return ['', $required ? 'La foto es obligatoria.' : null];
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        return ['', 'No se pudo subir la foto. Inténtalo de nuevo.'];
    }

    if (empty($file['tmp_name']) || is_array($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['', 'No se pudo subir la foto. Inténtalo de nuevo.'];
    }

    $size = isset($file['size']) && !is_array($file['size']) ? (int) $file['size'] : 0;

    if ($size > 2 * 1024 * 1024) {
        return ['', 'La foto no puede superar los 2 MB.'];
    }

    $original = isset($file['name']) && !is_array($file['name'])
        ? basename(str_replace('\\', '/', (string) $file['name']))
        : '';

    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $mime = false;

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo !== false) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }

    $extension = '';

    if (is_string($mime) && isset($mimeTypes[$mime])) {
        $extension = $mimeTypes[$mime];
    } elseif ($mime === false && $original !== '') {
        $fallback = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fallback, $allowed, true)) {
            $extension = $fallback === 'jpeg' ? 'jpg' : $fallback;
        }
    }

    if ($extension === '') {
        return ['', 'La foto debe ser una imagen JPEG, PNG, GIF o WebP.'];
    }

    $directory = __DIR__ . '/../archivos/';

    if (!is_dir($directory)) {
        return ['', 'No se pudo guardar la foto. Inténtalo de nuevo.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;

    if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
        return ['', 'No se pudo guardar la foto. Inténtalo de nuevo.'];
    }

    return [$filename, null];
}

function delete_photo($filename)
{
    $filename = basename((string) $filename);

    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }

    $path = __DIR__ . '/../archivos/' . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

function photo_src($filename)
{
    $filename = basename((string) $filename);

    if ($filename === '' || $filename === '.' || $filename === '..') {
        return '';
    }

    return 'archivos/' . rawurlencode($filename);
}

function display_name($contact)
{
    $value = static function ($key) use ($contact) {
        if (is_array($contact)) {
            return isset($contact[$key]) && !is_array($contact[$key]) ? (string) $contact[$key] : '';
        }

        if (is_object($contact)) {
            return isset($contact->$key) && !is_array($contact->$key) ? (string) $contact->$key : '';
        }

        return '';
    };

    return trim($value('nombre') . ' ' . $value('apellido'));
}
