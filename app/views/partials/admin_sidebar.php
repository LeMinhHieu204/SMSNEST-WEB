<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentUser = Auth::user();
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
function normalizePath($path)
{
    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
}
if ($baseUrl !== '' && $baseUrl !== '/' && strpos($path, $baseUrl) === 0) {
    $path = substr($path, strlen($baseUrl));
}
$path = normalizePath($path);
function adminNavActive($current, $path)
{
    return normalizePath($current) === $path ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark brand-logo"></div>
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Toggle sidebar">
            <span class="sidebar-toggle-icon"></span>
        </button>
    </div>
    <?php if ($currentUser) : ?>
        <div class="profile">
            <div class="profile-avatar"></div>
            <div class="profile-info">
                <div class="profile-label">Admin</div>
                <div class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="nav-section">
        <div class="nav-title">Admin</div>
        <a class="nav-item <?php echo adminNavActive('/admin', $path); ?>" href="<?php echo $baseUrl; ?>/admin">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:shield" aria-hidden="true"></span></span>
            <span class="nav-label">Dashboard</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/pricing', $path); ?>" href="<?php echo $baseUrl; ?>/admin/pricing">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:coins" aria-hidden="true"></span></span>
            <span class="nav-label">Pricing</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/users', $path); ?>" href="<?php echo $baseUrl; ?>/admin/users">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:users" aria-hidden="true"></span></span>
            <span class="nav-label">Users</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/order-logs', $path); ?>" href="<?php echo $baseUrl; ?>/admin/order-logs">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:list-check" aria-hidden="true"></span></span>
            <span class="nav-label">Order Logs</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/wallet-logs', $path); ?>" href="<?php echo $baseUrl; ?>/admin/wallet-logs">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:wallet" aria-hidden="true"></span></span>
            <span class="nav-label">Wallet Logs</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/support', $path); ?>" href="<?php echo $baseUrl; ?>/admin/support">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:help-circle" aria-hidden="true"></span></span>
            <span class="nav-label">Support</span>
        </a>
        <a class="nav-item <?php echo adminNavActive('/admin/guides', $path); ?>" href="<?php echo $baseUrl; ?>/admin/guides">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:book-2" aria-hidden="true"></span></span>
            <span class="nav-label">Guides</span>
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-title">User Area</div>
        <a class="nav-item <?php echo adminNavActive('/dashboard', $path); ?>" href="<?php echo $baseUrl; ?>/dashboard">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:user" aria-hidden="true"></span></span>
            <span class="nav-label">User Dashboard</span>
        </a>
    </div>
    <div class="sidebar-footer">(c) 2025 SMSNest</div>
</aside>
