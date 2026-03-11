<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Set a new password to regain access.</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Reset password</div>
            <p class="muted">Choose a strong password with at least 6 characters.</p>
            <?php if (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!empty($success)) : ?>
                <div class="alert"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (empty($success)) : ?>
                <form class="form" method="post" action="<?php echo $baseUrl; ?>/reset-password">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                    <label>New password</label>
                    <input type="password" name="password" minlength="6" required>
                    <label>Confirm new password</label>
                    <input type="password" name="confirm_password" minlength="6" required>
                    <button class="btn primary" type="submit">Update password</button>
                </form>
            <?php endif; ?>
            <div class="auth-footer">
                <a class="link" href="<?php echo $baseUrl; ?>/login">Back to login</a>
            </div>
        </div>
    </div>
</div>
