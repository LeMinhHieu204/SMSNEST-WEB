<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Check your inbox to verify your account.</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Verify your email</div>
            <p class="muted">We sent a verification link to <strong><?php echo htmlspecialchars($email ?? ''); ?></strong>.</p>
            <div class="auth-footer">
                Go to <a class="link" href="<?php echo $baseUrl; ?>/login">login</a> after verifying.
            </div>
        </div>
    </div>
</div>
