<?php
if (!isset($basePrefix)) {
    $basePrefix = '.';
}
if (!isset($activeNav)) {
    $activeNav = '';
}

$homeLink = ($activeNav === 'home') ? '#' : "{$basePrefix}/";
$maintenanceLink = ($activeNav === 'maintenance') ? '#' : "{$basePrefix}/maintenance";
$searchLink = ($activeNav === 'search') ? '#' : "{$basePrefix}/search/";
$loginLink = "{$basePrefix}/login/";
$logoutLink = "{$basePrefix}/utils/logout.php";
?>
<nav class="navbar">
    <div class="container navbar-content">
        <div class="navbar-brand">
            <img class="logo-icon" src="<?php echo $basePrefix; ?>/img/logo_main.png" alt="EventLink Logo" />
            <span class="brand-text">EventLink</span>
        </div>
        <div class="navbar-links">
            <a href="<?php echo $homeLink; ?>" class="nav-link">Browse Events</a>
            <a href="<?php echo $maintenanceLink; ?>" class="nav-link">Maintenance</a>
            <a href="<?php echo $searchLink; ?>" class="nav-link">Search</a>
        </div>
        <div class="navbar-actions">
            <?php if (is_user_logged_in()): ?>
                <span class="btn-ghost" style="display:flex;pointer-events:none;">
                    <i data-lucide="user"></i>
                    <?php echo htmlspecialchars(get_user_name(), ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <a href="<?php echo $logoutLink; ?>"><button class="btn-primary">Sign Out</button></a>
            <?php else: ?>
                <a href="<?php echo $loginLink; ?>"><button class="btn-ghost"><i data-lucide="user"></i>Sign In</button></a>
                <button class="btn-primary">Sign Up</button>
            <?php endif; ?>
            <button class="menu-toggle"><i data-lucide="menu"></i></button>
        </div>
    </div>
</nav>
