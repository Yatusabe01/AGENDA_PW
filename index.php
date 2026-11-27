<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/config/db.php';

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$sql = 'SELECT id, nombre, apellido, telefono, foto, email FROM contactos';
$params = [];
if ($q !== '') {
    $sql .= " WHERE nombre LIKE :search1 ESCAPE '!' OR apellido LIKE :search2 ESCAPE '!' OR telefono LIKE :search3 ESCAPE '!' OR email LIKE :search4 ESCAPE '!'";
    $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%';
    $params = [':search1' => $term, ':search2' => $term, ':search3' => $term, ':search4' => $term];
}
$sql .= ' ORDER BY fecha_registro DESC, id DESC';

$loadError = false;
$contacts = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error al listar contactos: ' . $e->getMessage());
    $loadError = true;
}

$pageTitle = 'Contactos';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="section-header">
        <div>
            <h1>Contactos</h1>
            <p class="subtitle">Administra tu lista de contactos.</p>
        </div>
        <a class="btn btn-primary" href="crear.php">Nuevo contacto</a>
    </div>

    <form class="search-form" method="get" action="index.php">
        <label class="sr-only" for="q">Buscar contactos</label>
        <input id="q" name="q" type="search" placeholder="Buscar por nombre, teléfono o correo" value="<?php echo h($q); ?>">
        <button class="btn btn-secondary" type="submit">Buscar</button>
        <?php if ($q !== ''): ?>
            <a class="btn btn-secondary" href="index.php">Ver todos</a>
        <?php endif; ?>
    </form>

    <?php if ($loadError): ?>
        <p class="empty">No se pudieron cargar los contactos. Intente nuevamente más tarde.</p>
    <?php elseif (count($contacts) === 0): ?>
        <p class="empty">
            <?php if ($q !== ''): ?>
                No se encontraron contactos para «<?php echo h($q); ?>».
            <?php else: ?>
                Aún no hay contactos. Use «Nuevo contacto» para agregar el primero.
            <?php endif; ?>
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <?php
                    $name = display_name($contact);
                    $src = photo_src(isset($contact['foto']) ? $contact['foto'] : '');
                    $email = isset($contact['email']) && $contact['email'] !== '' ? $contact['email'] : '—';
                    ?>
                    <tr>
                        <td>
                            <?php if ($src !== ''): ?>
                                <img class="thumb" src="<?php echo h($src); ?>" alt="<?php echo h($name); ?>">
                            <?php else: ?>
                                <span class="thumb-placeholder" aria-hidden="true">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo h($name); ?></td>
                        <td><?php echo h($contact['telefono']); ?></td>
                        <td><?php echo h($email); ?></td>
                        <td>
                            <div class="actions">
                                <a href="<?php echo h('ver.php?id=' . (int) $contact['id']); ?>">Ver</a>
                                <a href="<?php echo h('editar.php?id=' . (int) $contact['id']); ?>">Editar</a>
                                <a class="danger" href="<?php echo h('eliminar.php?id=' . (int) $contact['id']); ?>">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>