<div class="grid two">
    <div class="card">
        <div class="card-title">Admin Overview</div>
        <div class="stats">
            <div class="stat">
                <div class="stat-value">$<?php echo number_format($balanceTotal ?? 0, 2); ?></div>
                <div class="stat-label">Admin Balance</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                <div class="stat-label">Orders Managed</div>
            </div>
            <div class="stat">
                <div class="stat-value">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></div>
                <div class="stat-label">Revenue Snapshot</div>
            </div>
            <div class="stat">
                <div class="stat-value">$<?php echo number_format($totalDeposits ?? 0, 2); ?></div>
                <div class="stat-label">Total Deposits (All Users)</div>
            </div>
            <div class="stat">
                <div class="stat-value">$<?php echo number_format($totalCompletedOrders ?? 0, 2); ?></div>
                <div class="stat-label">Total Completed SMS Orders</div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-title">System Alerts</div>
        <ul class="list">
            <?php if (!empty($alerts)) : ?>
                <?php foreach ($alerts as $alert) : ?>
                    <li><?php echo htmlspecialchars($alert); ?></li>
                <?php endforeach; ?>
            <?php else : ?>
                <li>No alerts right now.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-title">Recent Orders</div>
    <div class="table">
        <div class="table-row table-head admin-table">
            <div>#</div>
            <div>User</div>
            <div>Service</div>
            <div>Country</div>
            <div>Status</div>
            <div>Time</div>
            <div>Cost</div>
        </div>
        <?php if (!empty($recentOrders)) : ?>
            <?php foreach ($recentOrders as $order) : ?>
                <?php
                $status = strtolower($order['status'] ?? '');
                $statusClass = 'neutral';
                if ($status === 'completed') {
                    $statusClass = 'success';
                } elseif ($status === 'pending') {
                    $statusClass = 'warning';
                } elseif ($status === 'failed') {
                    $statusClass = 'danger';
                }
                ?>
                <div class="table-row admin-table">
                    <div><?php echo (int) $order['id']; ?></div>
                    <div><?php echo htmlspecialchars($order['username'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($order['service_name'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($order['country'] ?? ''); ?></div>
                    <div class="pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></div>
                    <div><?php echo htmlspecialchars($order['created_at'] ?? ''); ?></div>
                    <div>$<?php echo number_format($order['cost'] ?? 0, 2); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row admin-table">
                <div>—</div>
                <div>No recent orders.</div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        <?php endif; ?>
    </div>
</div>
