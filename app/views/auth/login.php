<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Secure SMS access with fast delivery and clean routing.</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Sign in</div>
            <p class="muted">Access your SMS portal securely.</p>
            <?php if (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="form" method="post" action="<?php echo $baseUrl; ?>/login">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button class="btn primary" type="submit">Sign in</button>
            </form>
            <div class="auth-footer">
                <a class="link" href="<?php echo $baseUrl; ?>/forgot-password">Forgot password?</a>
            </div>
            <div class="auth-footer">
                Don't have an account? <a class="link" href="<?php echo $baseUrl; ?>/register">Sign up</a>
            </div>
        </div>
    </div>
</div>
