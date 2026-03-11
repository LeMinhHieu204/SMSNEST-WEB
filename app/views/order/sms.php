<div class="grid two order-sms-grid">
    <div class="card order-sms-card">
        <div class="card-title">Order SMS</div>
        <p class="muted">
            Order your SMS here, then check pending SMS to see your current order.
            If the service is not listed, contact support or use the advanced view.
        </p>
        <div class="section-title">Configure order</div>
        <form class="form">
            <label>Service</label>
            <div class="dropdown" data-dropdown="service">
                <div class="dropdown-control">
                    <input class="dropdown-display" type="text" readonly value="Select a service">
                    <button class="dropdown-toggle" type="button">?</button>
                </div>
                <div class="dropdown-panel">
                    <input class="dropdown-search" type="text" placeholder="Search service">
                    <div class="dropdown-list">
                        <?php foreach ($services as $service) : ?>
                            <button class="dropdown-item" type="button" data-value="<?php echo htmlspecialchars($service['id']); ?>">
                                <?php echo htmlspecialchars($service['service_name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" id="service-select" name="service_id" value="">
            </div>

            <label>Country</label>
            <div class="dropdown" data-dropdown="country">
                <div class="dropdown-control">
                    <input class="dropdown-display" type="text" readonly value="Select a country">
                    <button class="dropdown-toggle" type="button">?</button>
                </div>
                <div class="dropdown-panel">
                    <input class="dropdown-search" type="text" placeholder="Search country">
                    <div class="dropdown-list" id="country-list"></div>
                </div>
                <input type="hidden" id="country-select" name="country_id" value="">
            </div>

            <label>Stock</label>
            <input type="text" id="stock-value" value="0" readonly>

            <label>Quantity</label>
            <input type="number" id="quick-quantity" min="1" value="1">

            <label>Pricing Option</label>
            <select id="quick-pricing-option">
                <option value="1">Select highest success rate</option>
                <option value="0">Lowest price</option>
            </select>

            <label>Maximum Price</label>
            <input type="text" id="quick-max-price" placeholder="0.00">

            <label>Price Range</label>
            <input type="text" id="price-range" placeholder="$0.00 - $0.00" readonly>

            <button class="btn primary" id="quick-purchase-btn" type="button">Quick Purchase</button>
        </form>
        <div class="notice toast-stack" id="quick-purchase-result" style="display:none;"></div>
        <div class="advanced-link">Advanced view</div>
    </div>

    <div class="card pending-sms-card">
        <div class="card-header">
            <div class="card-title">Pending SMS</div>
        </div>
        <p class="muted">
            A list of all your pending SMS. Sometimes a number must be activated
            and it can take a few minutes to receive an SMS verification.
        </p>
        <div class="table-toolbar">
            <input class="input-sm" type="text" placeholder="Filter">
        </div>
        <div class="table" id="pending-table">
            <div class="table-row table-head table-compact table-pending">
                <div>Phonenumber</div>
                <div>Code</div>
                <div>Service</div>
                <div>Country</div>
                <div>Qty</div>
                <div>Status</div>
                <div>Cost</div>
                <div>Actions</div>
            </div>
            <div id="pending-body">
            <?php if (!empty($pending)) : ?>
            <?php foreach ($pending as $row) : ?>
                <?php
                    $nowEpoch = time();
                    $createdAtEpoch = isset($row['created_at_epoch']) ? (int) $row['created_at_epoch'] : $nowEpoch;
                    if ($createdAtEpoch > $nowEpoch) {
                        $createdAtEpoch = $nowEpoch;
                    }
                    $remainingSeconds = max(0, 1200 - ($nowEpoch - $createdAtEpoch));
                    if ($remainingSeconds > 1200) {
                        $remainingSeconds = 1200;
                    }
                    $smsCode = isset($row['sms_code']) && $row['sms_code'] !== '' ? (string) $row['sms_code'] : '';
                    $displayStatus = $smsCode !== '' ? 'completed' : $row['status'];
                ?>
                    <div class="table-row table-compact table-pending"
                        data-provider-order-id="<?php echo htmlspecialchars($row['provider_order_id'] ?? ''); ?>"
                        data-order-id="<?php echo htmlspecialchars($row['id']); ?>"
                        data-created-at-epoch="<?php echo htmlspecialchars((string) $createdAtEpoch); ?>"
                        data-remaining="<?php echo htmlspecialchars((string) $remainingSeconds); ?>"
                        data-loaded-at-epoch="<?php echo htmlspecialchars((string) time()); ?>"
                        data-status="<?php echo htmlspecialchars($displayStatus); ?>">
                        <div><?php echo htmlspecialchars($row['phone_number']); ?></div>
                        <div class="pending-code">
                            <?php if ($smsCode !== '') : ?>
                                <?php echo 'OTP: ' . htmlspecialchars($smsCode); ?>
                            <?php elseif ($row['status'] === 'pending') : ?>
                                <?php echo 'Waiting for OTP...'; ?>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div><?php echo htmlspecialchars($row['service_name']); ?></div>
                        <div><?php echo htmlspecialchars($row['country']); ?></div>
                        <div><?php echo htmlspecialchars($row['quantity'] ?? 1); ?></div>
                        <div class="pill <?php echo $displayStatus === 'completed' ? 'success' : 'warning'; ?> pending-status"><?php echo htmlspecialchars($displayStatus); ?></div>
                        <div>$<?php echo number_format($row['cost'], 2); ?></div>
                        <div class="pending-actions">
                            <?php
                            $isPending = $row['status'] === 'pending';
                            $hasProviderId = !empty($row['provider_order_id']);
                            $hasTimeRemaining = $remainingSeconds > 0;
                            $canShowRefund = $isPending && $hasTimeRemaining;
                            $refundReady = $isPending && $hasProviderId && $hasTimeRemaining && $remainingSeconds <= 900;
                            ?>
                            <button class="btn icon pending-refund" type="button"
                                style="display:<?php echo $canShowRefund ? 'inline-flex' : 'none'; ?>;"
                                data-refund-locked="<?php echo $refundReady ? '0' : '1'; ?>">
                                Refund
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="table-row table-compact table-pending pending-empty">
                    <div>No pending SMS</div>
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
    </div>
</div>
