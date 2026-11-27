<?php
require __DIR__ . '/includes/functions.php';

$errors = take_flash('errors', []);
$old = take_flash('old', []);
if (!is_array($errors)) {
    $errors = [];
}
if (!is_array($old)) {
    $old = [];
}
$pageTitle = 'Nuevo contacto';
require __DIR__ . '/includes/header.php';
?>
<section class="card card-narrow">
    <div class="section-header">
        <div>
            <h1>Nuevo contacto</h1>
            <p class="subtitle">Los campos marcados con * son obligatorios.</p>
        </div>
    </div>

    <?php if (count($errors) > 0): ?>
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo h($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="guardar.php" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <div class="field">
            <label for="nombre">Nombre <span class="required">*</span></label>
            <input id="nombre" name="nombre" type="text" maxlength="100" autocomplete="given-name" required value="<?php echo h(old_value($old, 'nombre')); ?>">
        </div>
        <div class="field">
            <label for="apellido">Apellido</label>
            <input id="apellido" name="apellido" type="text" maxlength="100" autocomplete="family-name" value="<?php echo h(old_value($old, 'apellido')); ?>">
        </div>
        <div class="field">
            <label for="telefono">Teléfono <span class="required">*</span></label>
            <input id="telefono" name="telefono" type="tel" minlength="7" maxlength="15" pattern="[0-9]{7,15}" autocomplete="tel" required value="<?php echo h(old_value($old, 'telefono')); ?>">
        </div>
        <div class="field">
            <label for="foto">Foto <span class="required">*</span></label>
            <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <span class="field-hint">Formatos permitidos: JPEG, PNG, GIF o WebP. Máximo 2 MB.</span>
        </div>
        <div class="field">
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" maxlength="255" autocomplete="email" value="<?php echo h(old_value($old, 'email')); ?>">
        </div>
        <div class="field">
            <label for="direccion">Dirección</label>
            <input id="direccion" name="direccion" type="text" maxlength="255" autocomplete="street-address" value="<?php echo h(old_value($old, 'direccion')); ?>">
        </div>
        <div class="field">
            <label for="notas">Notas</label>
            <textarea id="notas" name="notas" maxlength="2000"><?php echo h(old_value($old, 'notas')); ?></textarea>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar contacto</button>
            <a class="btn btn-secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
