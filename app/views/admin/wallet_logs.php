<div class="card">
    <div class="card-title">Wallet Deposit Logs</div>
    <div class="table">
        <div class="table-row table-head admin-wallet-table">
            <div>#</div>
            <div>User</div>
            <div>Email</div>
            <div>Amount</div>
            <div>Status</div>
            <div>Note</div>
            <div>Date</div>
        </div>
        <?php if (!empty($logs)) : ?>
            <?php foreach ($logs as $row) : ?>
                <div class="table-row admin-wallet-table">
                    <div><?php echo (int) $row['id']; ?></div>
                    <div><?php echo htmlspecialchars($row['username'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
                    <div>$<?php echo number_format($row['amount'] ?? 0, 2); ?></div>
                    <div><?php echo htmlspecialchars($row['status'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($row['note'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row admin-wallet-table">
                <div>—</div>
                <div>No logs found.</div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        <?php endif; ?>
    </div>
</div>
