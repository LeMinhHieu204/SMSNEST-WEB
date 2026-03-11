<?php
$currentUser = Auth::user();
$balanceValue = null;
if ($currentUser) {
    $balanceValue = (new WalletTransaction())->getNetByUserId($currentUser['id']);
}
$avatarUrl = $currentUser['avatar'] ?? '';
if ($avatarUrl !== '' && !preg_match('/^https?:\\/\\//i', $avatarUrl)) {
    $avatarUrl = $baseUrl . $avatarUrl;
}
?>
<div class="topbar">
    <button class="topbar-menu" type="button" data-mobile-sidebar aria-label="Open menu">
        <span class="sidebar-toggle-icon"></span>
    </button>
    <div class="topbar-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Dashboard'; ?></div>
    <div class="topbar-actions">
        <div class="badge">Admin Panel</div>
        <?php if ($currentUser) : ?>
            <div class="badge">Balance $<?php echo number_format((float) $balanceValue, 2); ?></div>
            <a href="<?php echo $baseUrl; ?>/settings" class="avatar" style="<?php echo !empty($avatarUrl) ? 'background-image:url(' . htmlspecialchars($avatarUrl) . '); background-size:cover; background-position:center; color:transparent;' : ''; ?>">
                <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
            </a>
            <a class="badge link" href="<?php echo $baseUrl; ?>/logout">Logout</a>
        <?php else : ?>
            <a class="badge link" href="<?php echo $baseUrl; ?>/login">Login</a>
        <?php endif; ?>
    </div>
</div>
