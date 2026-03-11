<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$avatarUrl = $user['avatar'] ?? '';
if ($avatarUrl !== '' && !preg_match('/^https?:\\/\\//i', $avatarUrl)) {
    $avatarUrl = $baseUrl . $avatarUrl;
}
?>
<div class="grid two">
    <div class="card">
        <div class="card-title">Account Settings</div>
        <div class="form">
            <?php if (!empty($success)) : ?>
                <div class="alert"><?php echo htmlspecialchars($success); ?></div>
            <?php elseif (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($baseUrl ?? ''); ?>/settings">
                <input type="hidden" name="action" value="profile">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

                <button class="btn primary" type="submit">Save Profile</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Change Password</div>
        <div class="form">
            <form method="post" action="<?php echo htmlspecialchars($baseUrl ?? ''); ?>/settings">
                <input type="hidden" name="action" value="password">
                <label>Current Password</label>
                <input type="password" name="current_password" required>

                <label>New Password</label>
                <input type="password" name="new_password" minlength="6" required>

                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" minlength="6" required>

                <button class="btn primary" type="submit">Update Password</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Avatar</div>
        <div class="form">
            <div class="profile-avatar" style="<?php echo !empty($avatarUrl) ? 'background-image:url(' . htmlspecialchars($avatarUrl) . '); background-size:cover; background-position:center;' : ''; ?>"></div>
            <form method="post" action="<?php echo htmlspecialchars($baseUrl ?? ''); ?>/settings" enctype="multipart/form-data">
                <input type="hidden" name="action" value="avatar">
                <label>Upload Image</label>
                <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" required>
                <button class="btn primary" type="submit">Upload Avatar</button>
            </form>
            <div class="muted">JPG/PNG/WEBP up to 2MB.</div>
        </div>
    </div>
</div>
