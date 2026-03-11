<div class="card">
    <div class="card-title">User Management</div>
    <div class="table">
        <div class="table-row table-head admin-users-table">
            <div>ID</div>
            <div>Username</div>
            <div>Email</div>
            <div>Role</div>
            <div>Balance</div>
        </div>
        <?php if (!empty($users)) : ?>
            <?php foreach ($users as $user) : ?>
                <div class="table-row admin-users-table">
                    <div><?php echo (int) $user['id']; ?></div>
                    <div><?php echo htmlspecialchars($user['username'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                    <div><?php echo htmlspecialchars($user['role'] ?? ''); ?></div>
                    <div>$<?php echo number_format($user['balance_total'] ?? 0, 2); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="table-row admin-users-table">
                <div>—</div>
                <div>No users found.</div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        <?php endif; ?>
    </div>
</div>
