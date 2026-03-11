<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentUser = Auth::user();
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$avatarUrl = $currentUser['avatar'] ?? '';
if ($avatarUrl !== '' && !preg_match('/^https?:\\/\\//i', $avatarUrl)) {
    $avatarUrl = $baseUrl . $avatarUrl;
}
function normalizePath($path)
{
    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
}
if ($baseUrl !== '' && $baseUrl !== '/' && strpos($path, $baseUrl) === 0) {
    $path = substr($path, strlen($baseUrl));
}
$path = normalizePath($path);
function navActive($current, $path)
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
            <div class="profile-avatar" style="<?php echo !empty($avatarUrl) ? 'background-image:url(' . htmlspecialchars($avatarUrl) . '); background-size:cover; background-position:center;' : ''; ?>"></div>
            <div class="profile-info">
                <div class="profile-label">Username</div>
                <div class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="nav-section">
        <div class="nav-title">Quick Menu</div>
        <a class="nav-item <?php echo navActive('/order/quick', $path); ?>" href="<?php echo $baseUrl; ?>/order/quick">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:bolt" aria-hidden="true"></span></span>
            <span class="nav-label">Quick Order</span>
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-title">Main</div>
        <a class="nav-item <?php echo navActive('/dashboard', $path); ?>" href="<?php echo $baseUrl; ?>/dashboard">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:layout-dashboard" aria-hidden="true"></span></span>
            <span class="nav-label">Dashboard</span>
        </a>
        <a class="nav-item <?php echo navActive('/order', $path); ?>" href="<?php echo $baseUrl; ?>/order">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:message" aria-hidden="true"></span></span>
            <span class="nav-label">Order SMS</span>
        </a>
        <a class="nav-item <?php echo navActive('/order/history', $path); ?>" href="<?php echo $baseUrl; ?>/order/history">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:clock" aria-hidden="true"></span></span>
            <span class="nav-label">History</span>
        </a>
        <a class="nav-item <?php echo navActive('/deposit', $path); ?>" href="<?php echo $baseUrl; ?>/deposit">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:credit-card" aria-hidden="true"></span></span>
            <span class="nav-label">Deposit</span>
        </a>
        <a class="nav-item <?php echo navActive('/affiliate', $path); ?>" href="<?php echo $baseUrl; ?>/affiliate">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:affiliate" aria-hidden="true"></span></span>
            <span class="nav-label">Affiliate</span>
        </a>
        <a class="nav-item <?php echo navActive('/guides', $path); ?>" href="<?php echo $baseUrl; ?>/guides">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:book-2" aria-hidden="true"></span></span>
            <span class="nav-label">Guides</span>
        </a>
        <a class="nav-item <?php echo navActive('/contact', $path); ?>" href="<?php echo $baseUrl; ?>/contact">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:help-circle" aria-hidden="true"></span></span>
            <span class="nav-label">Contact</span>
        </a>
        <a class="nav-item <?php echo navActive('/settings', $path); ?>" href="<?php echo $baseUrl; ?>/settings">
            <span class="nav-icon"><span class="iconify" data-icon="tabler:settings" aria-hidden="true"></span></span>
            <span class="nav-label">Settings</span>
        </a>
    </div>
    <?php if ($currentUser && $currentUser['role'] === 'admin') : ?>
        <div class="nav-section">
            <div class="nav-title">Admin</div>
            <a class="nav-item <?php echo navActive('/admin', $path); ?>" href="<?php echo $baseUrl; ?>/admin">
                <span class="nav-icon"><span class="iconify" data-icon="tabler:shield" aria-hidden="true"></span></span>
                <span class="nav-label">Admin Dashboard</span>
            </a>
            <a class="nav-item <?php echo navActive('/admin/pricing', $path); ?>" href="<?php echo $baseUrl; ?>/admin/pricing">
                <span class="nav-icon"><span class="iconify" data-icon="tabler:coins" aria-hidden="true"></span></span>
                <span class="nav-label">Pricing</span>
            </a>
        </div>
    <?php endif; ?>
    <div class="sidebar-footer">(c) 2025 SMSNest</div>
</aside>
