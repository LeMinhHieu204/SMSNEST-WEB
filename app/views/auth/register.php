<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Start fast SMS verification with trusted routes.</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Create account</div>
            <p class="muted">Set up your account to start SMS verification.</p>
            <?php if (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="form" method="post" action="<?php echo $baseUrl; ?>/register">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button class="btn primary" type="submit">Sign up</button>
                <div class="muted">Verification email may arrive after 1-2 minutes due to high registration volume.</div>
            </form>
            <div class="auth-footer">
                Already have an account? <a class="link" href="<?php echo $baseUrl; ?>/login">Sign in</a>
            </div>
        </div>
    </div>
</div>
