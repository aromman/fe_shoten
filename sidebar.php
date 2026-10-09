<?php
require_once __DIR__ . '/app/config/url.php';
require_once __DIR__ . '/app/config/navigation.php';

$role = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
$channelType = isset($_SESSION['user.canal.tipo']) ? $_SESSION['user.canal.tipo'] : '';
$navigation = build_navigation($role, $channelType);

$timezone = 'America/Argentina/Buenos_Aires';
$date = new DateTime('now', new DateTimeZone($timezone));
$currentDate = $date->format('d-m-Y');
$currentTime = $date->format('H:i:s');
$username = isset($_SESSION['user.username']) ? $_SESSION['user.username'] : '';

$escapeNavigation = function ($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion" aria-label="Navegación secundaria">
        <div class="sb-sidenav-menu">
            <div class="sidebar-context">
                <span class="sidebar-context-icon" id="sidebarAreaIcon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                <strong id="sidebarAreaTitle">Menú principal</strong>
            </div>

            <div class="nav">
                <?php foreach ($navigation as $sectionKey => $section) { ?>
                    <?php foreach ($section['groups'] as $group) { ?>
                        <div class="sb-sidenav-menu-heading" data-menu-area="<?php echo $escapeNavigation($sectionKey); ?>">
                            <?php echo $escapeNavigation($group['label']); ?>
                        </div>

                        <?php foreach ($group['items'] as $item) { ?>
                            <a class="nav-link" href="<?php echo $escapeNavigation(app_url($item['path'])); ?>">
                                <span class="sb-nav-link-icon" aria-hidden="true"><i class="fas <?php echo $escapeNavigation($item['icon']); ?>"></i></span>
                                <span><?php echo $escapeNavigation($item['label']); ?></span>
                            </a>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <span class="sidebar-user-avatar" aria-hidden="true"><i class="fas fa-user"></i></span>
            <div class="sidebar-user-meta">
                <strong><?php echo $escapeNavigation($username); ?></strong>
                <span><i class="far fa-clock" aria-hidden="true"></i> <?php echo $currentTime; ?> · <?php echo $currentDate; ?></span>
            </div>
        </div>
    </nav>
</div>
<script src="<?php echo app_url('/js/scripts.js');?>?v=<?php echo filemtime(__DIR__ . '/js/scripts.js');?>"></script>
<?php
// Entrega la navegación al navegador antes de procesar contenido posterior.
flush();
?>
