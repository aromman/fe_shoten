<?php 
    require_once __DIR__ . '/app/config/url.php';
    require_once __DIR__ . '/app/config/navigation.php';

    $welcome = 'Bienvenido ' . $_SESSION["user.nombre"] . ' !';
    $canalNombre = $_SESSION["user.canal.nombre"];
    $base_url = app_base_url();
    $topbarRole = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
    $topbarChannelType = isset($_SESSION['user.canal.tipo']) ? $_SESSION['user.canal.tipo'] : '';
    $topbarNavigation = build_navigation($topbarRole, $topbarChannelType);

?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand" href="<?php echo app_url('/index.php');?>">
        <span class="topbar-brand-mark" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
        <span class="topbar-brand-name"><?php echo htmlspecialchars($canalNombre, ENT_QUOTES, 'UTF-8');?></span>
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" type="button" aria-label="Mostrar u ocultar el menú principal" aria-controls="layoutSidenav_nav"><i class="fas fa-bars" aria-hidden="true"></i></button>
    <!-- Areas principales -->
    <div class="topbar-sections" role="navigation" aria-label="Secciones principales">
        <?php foreach ($topbarNavigation as $sectionKey => $section) {
            $safeSectionKey = htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8');
            $safeSectionLabel = htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8');
            $safeSectionIcon = htmlspecialchars($section['icon'], ENT_QUOTES, 'UTF-8');
            $firstSectionItem = $section['groups'][0]['items'][0];
            $safeSectionUrl = htmlspecialchars(app_url($firstSectionItem['path']), ENT_QUOTES, 'UTF-8');
        ?>
            <a class="topbar-section" href="<?php echo $safeSectionUrl; ?>" data-menu-area="<?php echo $safeSectionKey; ?>" aria-label="<?php echo $safeSectionLabel; ?>" title="<?php echo $safeSectionLabel; ?>">
                <i class="fas <?php echo $safeSectionIcon; ?>" aria-hidden="true"></i><span><?php echo $safeSectionLabel; ?></span>
            </a>
        <?php } ?>
    </div>
    <!-- Navbar-->
    <ul class="navbar-nav topbar-user ms-auto me-2 me-lg-3">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="topbar-avatar" aria-hidden="true"><i class="fas fa-user"></i></span>
                <span class="topbar-welcome"><?php echo htmlspecialchars($welcome, ENT_QUOTES, 'UTF-8');?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="<?php echo $base_url;?>/logout.php">Salir</a></li>
            </ul>
        </li>
    </ul>
</nav>
