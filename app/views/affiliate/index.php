<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="grid two">
    <div class="card">
        <div class="card-title">Affiliate Overview</div>
        <div class="stats">
            <div class="stat">
                <div class="stat-value">0</div>
                <div class="stat-label">Registered Today</div>
            </div>
            <div class="stat">
                <div class="stat-value">$<?php echo number_format($affiliate['total_earnings'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?php echo $affiliate['total_registers'] ?? 0; ?></div>
                <div class="stat-label">Total Registers</div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Referral Tools</div>
        <div class="form">
            <?php if (!empty($_GET['success'])) : ?>
                <div class="alert">Withdrawn to balance.</div>
            <?php elseif (!empty($_GET['error'])) : ?>
                <div class="alert">Invalid or empty withdraw amount.</div>
            <?php endif; ?>
            <label>My Promo Code</label>
            <input type="text" readonly value="<?php echo htmlspecialchars($affiliate['promo_code'] ?? ''); ?>">

            <label>My Referral Link</label>
            <div class="input-copy">
                <input type="text" id="referral-link-input" readonly value="<?php echo htmlspecialchars($affiliate['referral_link'] ?? ''); ?>">
                <button class="btn icon" type="button" data-copy-target="referral-link-input">Copy</button>
            </div>

            <label>Pending Balance</label>
            <input type="text" readonly value="$<?php echo number_format($affiliate['pending_balance'] ?? 0, 2); ?>">

            <button class="btn primary" type="button" id="withdraw-toggle">Withdraw to balance</button>
            <form method="post" action="<?php echo $baseUrl; ?>/affiliate/withdraw" id="withdraw-form" style="display:none; margin-top:10px;">
                <label>Withdraw Amount</label>
                <input type="number" name="amount" min="0.01" step="0.01" placeholder="0.00" required>
                <button class="btn primary" type="submit">Confirm withdraw</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Registered Users</div>
    <div class="table">
        <div class="table-row table-head">
            <div>Username</div>
            <div>Earnings</div>
            <div>Date</div>
        </div>
        <?php if (!empty($registrations)) : ?>
            <?php foreach ($registrations as $row) : ?>
                <div class="table-row">
                    <div><?php echo htmlspecialchars($row['username']); ?></div>
                    <div>$<?php echo number_format($row['earnings'], 2); ?></div>
                    <div><?php echo htmlspecialchars($row['created_at']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row">
                <div>No data available</div>
                <div>-</div>
                <div>-</div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
document.getElementById('withdraw-toggle')?.addEventListener('click', function () {
    var form = document.getElementById('withdraw-form');
    if (!form) {
        return;
    }
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>
