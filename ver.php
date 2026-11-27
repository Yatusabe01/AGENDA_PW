<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

$id = to_id(isset($_GET['id']) ? $_GET['id'] : 0);
$contact = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, nombre, apellido, telefono, foto, email, direccion, notas, fecha_registro FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $contact = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error al ver contacto: ' . $e->getMessage());
    }
}
if ($contact === false) {
    http_response_code(404);
}
$pageTitle = 'Detalle del contacto';
require __DIR__ . '/includes/header.php';
?>
<?php if ($contact === false || $id === 0): ?>
    <section class="card card-narrow">
        <h1>Contacto no encontrado</h1>
        <p class="subtitle">El contacto que busca no existe o ya fue eliminado.</p>
        <p><a class="btn btn-secondary" href="index.php">Volver a la lista</a></p>
    </section>
<?php elseif ($contact === null): ?>
    <section class="card card-narrow">
        <h1>Servicio no disponible</h1>
        <p class="subtitle">No se pudo cargar el contacto. Intente nuevamente más tarde.</p>
        <p><a class="btn btn-secondary" href="index.php">Volver a la lista</a></p>
    </section>
<?php else: ?>
    <?php
    $name = display_name($contact);
    $src = photo_src(isset($contact['foto']) ? $contact['foto'] : '');
    ?>
    <section class="card">
        <div class="section-header">
            <div>
                <h1><?php echo h($name); ?></h1>
                <p class="subtitle">Detalle del contacto</p>
            </div>
            <div class="actions">
                <a class="btn btn-secondary" href="<?php echo h('editar.php?id=' . (int) $contact['id']); ?>">Editar</a>
                <a class="btn btn-danger" href="<?php echo h('eliminar.php?id=' . (int) $contact['id']); ?>">Eliminar</a>
                <a class="btn btn-secondary" href="index.php">Volver</a>
            </div>
        </div>

        <?php if ($src !== ''): ?>
            <img class="detail-photo" src="<?php echo h($src); ?>" alt="<?php echo h($name); ?>">
        <?php endif; ?>

        <dl class="detail-grid">
            <div>
                <dt>ID</dt>
                <dd><?php echo h((int) $contact['id']); ?></dd>
            </div>
            <div>
                <dt>Nombre</dt>
                <dd><?php echo h($contact['nombre']); ?></dd>
            </div>
            <div>
                <dt>Apellido</dt>
                <dd><?php echo h($contact['apellido'] !== null && $contact['apellido'] !== '' ? $contact['apellido'] : '—'); ?></dd>
            </div>
            <div>
                <dt>Teléfono</dt>
                <dd><?php echo h($contact['telefono']); ?></dd>
            </div>
            <div>
                <dt>Foto</dt>
                <dd><?php echo h($src !== '' ? basename($contact['foto']) : '—'); ?></dd>
            </div>
            <div>
                <dt>Correo electrónico</dt>
                <dd><?php echo h($contact['email'] !== null && $contact['email'] !== '' ? $contact['email'] : '—'); ?></dd>
            </div>
            <div>
                <dt>Dirección</dt>
                <dd><?php echo h($contact['direccion'] !== null && $contact['direccion'] !== '' ? $contact['direccion'] : '—'); ?></dd>
            </div>
            <div>
                <dt>Fecha de registro</dt>
                <dd><?php echo h($contact['fecha_registro']); ?></dd>
            </div>
            <div class="wide">
                <dt>Notas</dt>
                <dd><?php echo nl2br(h($contact['notas'] !== null && $contact['notas'] !== '' ? $contact['notas'] : '—')); ?></dd>
            </div>
        </dl>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
