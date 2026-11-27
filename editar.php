<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

$id = to_id(isset($_GET['id']) ? $_GET['id'] : 0);
$contact = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, nombre, apellido, telefono, foto, email, direccion, notas FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $contact = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error al cargar contacto para edición: ' . $e->getMessage());
    }
}

$errors = take_flash('errors', []);
$old = take_flash('old', []);
if (!is_array($errors)) {
    $errors = [];
}
if (!is_array($old)) {
    $old = [];
}
$pageTitle = 'Editar contacto';
require __DIR__ . '/includes/header.php';
?>
<?php if ($contact === false || $id === 0): ?>
    <section class="card card-narrow">
        <h1>Contacto no encontrado</h1>
        <p class="subtitle">El contacto que intenta editar no existe o ya fue eliminado.</p>
        <p><a class="btn btn-secondary" href="index.php">Volver a la lista</a></p>
    </section>
<?php elseif ($contact === null): ?>
    <section class="card card-narrow">
        <h1>Servicio no disponible</h1>
        <p class="subtitle">No se pudo cargar el contacto. Intente nuevamente más tarde.</p>
        <p><a class="btn btn-secondary" href="index.php">Volver a la lista</a></p>
    </section>
<?php else: ?>
    <section class="card card-narrow">
        <div class="section-header">
            <div>
                <h1>Editar contacto</h1>
                <p class="subtitle">Actualice los datos que desee cambiar.</p>
            </div>
        </div>

        <?php if (count($errors) > 0): ?>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php
        $name = display_name($contact);
        $src = photo_src(isset($contact['foto']) ? $contact['foto'] : '');
        ?>
        <div class="field">
            <span class="subtitle">Foto actual</span>
            <div>
                <?php if ($src !== ''): ?>
                    <img class="thumb" src="<?php echo h($src); ?>" alt="<?php echo h($name); ?>">
                <?php else: ?>
                    <span class="thumb-placeholder" aria-hidden="true">—</span>
                <?php endif; ?>
            </div>
        </div>

        <form method="post" action="actualizar.php" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo h((int) $contact['id']); ?>">
            <div class="field">
                <label for="nombre">Nombre <span class="required">*</span></label>
                <input id="nombre" name="nombre" type="text" maxlength="100" autocomplete="given-name" required value="<?php echo h(old_value($old, 'nombre', (string) $contact['nombre'])); ?>">
            </div>
            <div class="field">
                <label for="apellido">Apellido</label>
                <input id="apellido" name="apellido" type="text" maxlength="100" autocomplete="family-name" value="<?php echo h(old_value($old, 'apellido', (string) ($contact['apellido'] ?? ''))); ?>">
            </div>
            <div class="field">
                <label for="telefono">Teléfono <span class="required">*</span></label>
                <input id="telefono" name="telefono" type="tel" minlength="7" maxlength="40" pattern="[0-9+() -]{7,40}" autocomplete="tel" required value="<?php echo h(old_value($old, 'telefono', (string) $contact['telefono'])); ?>">
            </div>
            <div class="field">
                <label for="foto">Nueva foto</label>
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
                <span class="field-hint">Formatos permitidos: JPEG, PNG, GIF o WebP. Máximo 2 MB. Deje el campo vacío para conservar la foto actual.</span>
            </div>
            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" name="email" type="email" maxlength="255" autocomplete="email" value="<?php echo h(old_value($old, 'email', (string) ($contact['email'] ?? ''))); ?>">
            </div>
            <div class="field">
                <label for="direccion">Dirección</label>
                <input id="direccion" name="direccion" type="text" maxlength="255" autocomplete="street-address" value="<?php echo h(old_value($old, 'direccion', (string) ($contact['direccion'] ?? ''))); ?>">
            </div>
            <div class="field">
                <label for="notas">Notas</label>
                <textarea id="notas" name="notas" maxlength="2000"><?php echo h(old_value($old, 'notas', (string) ($contact['notas'] ?? ''))); ?></textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Actualizar contacto</button>
                <a class="btn btn-secondary" href="<?php echo h('ver.php?id=' . (int) $contact['id']); ?>">Cancelar</a>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
