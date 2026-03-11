<div class="auth-card auth-card-wide">
    <div class="auth-grid">
        <div class="auth-side">
            <div class="brand auth-brand">
                <div class="brand-mark brand-logo"></div>
            </div>
            <div class="auth-side-title">SMSNest</div>
            <div class="auth-side-subtitle">Email verification result</div>
            <div class="auth-side-glow"></div>
        </div>
        <div class="auth-main">
            <div class="auth-title">Verify Email</div>
            <?php if (!empty($success)) : ?>
                <div class="alert" style="border-color: rgba(34, 197, 94, 0.6); color: #bbf7d0; background: rgba(34, 197, 94, 0.15);">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php elseif (!empty($error)) : ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="auth-footer">
                <a class="link" href="<?php echo $baseUrl; ?>/login">Go to login</a>
            </div>
        </div>
    </div>
</div>
