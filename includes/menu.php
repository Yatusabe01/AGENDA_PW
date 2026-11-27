<?php
require_once __DIR__ . '/functions.php';

$currentPage = basename(isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : 'index.php');
?>
<nav class="menu" aria-label="Navegación principal">
    <a href="index.php"<?php echo $currentPage === 'index.php' ? ' class="active" aria-current="page"' : ''; ?>>Contactos</a>
    <a href="crear.php"<?php echo $currentPage === 'crear.php' ? ' class="active" aria-current="page"' : ''; ?>>Nuevo contacto</a>
</nav>
