<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="card">
    <div class="card-title">Order Logs</div>
    <div class="table-toolbar">
        <form class="form inline" method="get" action="<?php echo $baseUrl; ?>/admin/order-logs">
            <input class="input-sm" type="text" name="order_id" placeholder="ID" value="<?php echo htmlspecialchars($filters['order_id'] ?? ''); ?>">
            <input class="input-sm" type="text" name="username" placeholder="User" value="<?php echo htmlspecialchars($filters['username'] ?? ''); ?>">
            <input class="input-sm" type="text" name="service" placeholder="Service" value="<?php echo htmlspecialchars($filters['service'] ?? ''); ?>">
            <input class="input-sm" type="text" name="country" placeholder="Country" value="<?php echo htmlspecialchars($filters['country'] ?? ''); ?>">
            <select class="input-sm" name="status">
                <option value="">All status</option>
                <?php
                $selectedStatus = $filters['status'] ?? '';
                $statusOptions = ['pending', 'completed', 'cancelled', 'failed'];
                foreach ($statusOptions as $status) :
                    $selectedAttr = $selectedStatus === $status ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $selectedAttr; ?>><?php echo htmlspecialchars($status); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn primary" type="submit">Search</button>
        </form>
    </div>
    <div class="table">
        <div class="table-row table-head admin-order-table">
            <div>ID</div>
            <div>User</div>
            <div>Service</div>
            <div>Country</div>
            <div>Phone</div>
            <div>Qty</div>
            <div>Status</div>
            <div>Cost</div>
            <div>Date</div>
        </div>
        <?php if (!empty($orders)) : ?>
            <?php foreach ($orders as $row) : ?>
                <?php
                $status = strtolower($row['status'] ?? '');
                $pillClass = 'warning';
                if ($status === 'completed') {
                    $pillClass = 'success';
                } elseif ($status === 'cancelled' || $status === 'failed') {
                    $pillClass = 'danger';
                }
                ?>
                <div class="table-row admin-order-table">
                    <div><?php echo htmlspecialchars($row['id']); ?></div>
                    <div><?php echo htmlspecialchars($row['username']); ?></div>
                    <div><?php echo htmlspecialchars($row['service_name']); ?></div>
                    <div><?php echo htmlspecialchars($row['country']); ?></div>
                    <div><?php echo htmlspecialchars($row['phone_number']); ?></div>
                    <div>1</div>
                    <div class="pill <?php echo $pillClass; ?>"><?php echo htmlspecialchars($status !== '' ? $status : 'unknown'); ?></div>
                    <div>$<?php echo number_format($row['cost'], 2); ?></div>
                    <div><?php echo htmlspecialchars($row['created_at']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row admin-order-table">
                <div>-</div>
                <div>No orders found</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
                <div>-</div>
            </div>
        <?php endif; ?>
    </div>
</div>
