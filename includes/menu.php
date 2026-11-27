<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$menuItems = [
    'index.php' => 'Inicio',
    'crear.php' => 'Nuevo contacto',
];
?>
<nav class="site-nav" aria-label="Navegación principal">
    <ul><?php foreach ($menuItems as $file => $label): ?>
        <li><a class="nav-link<?php echo $currentPage === $file ? ' active' : ''; ?>" href="<?php echo h($file); ?>"<?php echo $currentPage === $file ? ' aria-current="page"' : ''; ?>><?php echo h($label); ?></a></li>
    <?php endforeach; ?></ul>
</nav>
