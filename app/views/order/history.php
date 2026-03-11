<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="card">
    <div class="card-title">Order History</div>
    <div class="table">
        <div class="table-row table-head order-history-table">
            <div>ID</div>
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
                <div class="table-row order-history-table">
                    <div><?php echo htmlspecialchars($row['id']); ?></div>
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
            <div class="table-row order-history-table">
                <div>-</div>
                <div>No orders found</div>
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
