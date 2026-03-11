<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Recover access to your account securely.</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Forgot password</div>
            <p class="muted">We will email you a reset link.</p>
            <?php if (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!empty($success)) : ?>
                <div class="alert"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form class="form" method="post" action="<?php echo $baseUrl; ?>/forgot-password">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                <button class="btn primary" type="submit">Send reset link</button>
            </form>
            <div class="auth-footer">
                Remembered your password? <a class="link" href="<?php echo $baseUrl; ?>/login">Sign in</a>
            </div>
        </div>
    </div>
</div>
