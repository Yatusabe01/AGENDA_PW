<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['_csrf']) ? $_POST['_csrf'] : '')) {
        flash('error', 'Token de seguridad no válido. Recargue la página e intente nuevamente.');
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
        if ($existing === false) {
            flash('error', 'El contacto que intenta eliminar no existe.');
            redirect('index.php');
        }

        $stmt = $pdo->prepare('DELETE FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('Error al eliminar contacto: ' . $e->getMessage());
        flash('error', 'No se pudo eliminar el contacto. Intente nuevamente.');
        redirect('index.php');
    }

    delete_photo(isset($existing['foto']) ? $existing['foto'] : '');
    flash('success', 'Contacto eliminado correctamente.');
    redirect('index.php');
}

$id = to_id(isset($_GET['id']) ? $_GET['id'] : 0);
$contact = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, nombre, apellido, telefono, foto, email FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $contact = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error al cargar contacto para eliminación: ' . $e->getMessage());
    }
}
if ($contact === false) {
    http_response_code(404);
}
$pageTitle = 'Eliminar contacto';
require __DIR__ . '/includes/header.php';
?>
<?php if ($contact === false || $id === 0): ?>
    <section class="card card-narrow">
        <h1>Contacto no encontrado</h1>
        <p class="subtitle">El contacto que intenta eliminar no existe o ya fue eliminado.</p>
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
    $email = isset($contact['email']) && $contact['email'] !== '' ? $contact['email'] : '—';
    ?>
    <section class="card card-narrow">
        <h1>Eliminar contacto</h1>
        <p class="subtitle">Esta acción no se puede deshacer. Confirme los datos antes de continuar.</p>

        <dl class="detail-grid">
            <div>
                <dt>Nombre</dt>
                <dd><?php echo h($name); ?></dd>
            </div>
            <div>
                <dt>Teléfono</dt>
                <dd><?php echo h($contact['telefono']); ?></dd>
            </div>
            <div>
                <dt>Correo electrónico</dt>
                <dd><?php echo h($email); ?></dd>
            </div>
        </dl>

        <?php if ($src !== ''): ?>
            <p><img class="thumb" src="<?php echo h($src); ?>" alt="<?php echo h($name); ?>"></p>
        <?php endif; ?>

        <form class="form-actions" method="post" action="eliminar.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo h((int) $contact['id']); ?>">
            <button class="btn btn-danger" type="submit">Eliminar definitivamente</button>
            <a class="btn btn-secondary" href="<?php echo h('ver.php?id=' . (int) $contact['id']); ?>">Cancelar</a>
        </form>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
