<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="grid two">
    <div class="card">
        <div class="card-title">Deposit (Sample)</div>
        <p class="muted">This is a sample top-up form for UI preview.</p>
        <?php if (!empty($_GET['success'])) : ?>
            <div class="alert">Invoice created.</div>
        <?php elseif (!empty($_GET['error'])) : ?>
            <div class="alert">Please enter a valid amount and method.</div>
        <?php endif; ?>
        <form class="form" method="post" action="<?php echo $baseUrl; ?>/deposit" id="deposit-form">
            <label>Amount</label>
            <input type="number" name="amount" placeholder="10.00" min="1.01" step="0.01">
            <div class="muted">Minimum deposit amount is above $1.00. Amounts below $1.00 are not accepted.</div>

            <label>Currency</label>
            <select name="currency" id="currency-select">
                <option value="fiat">Fiat</option>
                <option value="crypto">Cryptocurrency</option>
            </select>

            <div class="crypto-only" style="display:none;">
                <label>Cryptocurrency</label>
                <select name="crypto" id="crypto-select">
                    <option value="">Select</option>
                    <option value="usdt">USDT</option>
                </select>
            </div>

            <div class="crypto-only" style="display:none;">
                <label>Network</label>
                <select name="network" id="network-select">
                    <option value="">Select</option>
                    <option value="tron">Tron (TRC-20)</option>
                </select>
            </div>

            <div class="crypto-only" id="qr-wrap" style="display:none;">
                <label>Payment Link</label>
                <div class="card" style="padding:16px; text-align:center;">
                    <div id="qr-status" class="muted" style="margin-top:8px;"></div>
                </div>
            </div>

            <label>Payment Method</label>
            <select name="method">
                <option value="Cryptomus">Cryptomus</option>
                <option value="Stripe">Stripe</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <label>Email for receipt</label>
            <input type="email" placeholder="you@example.com">

            <div class="muted">QR will be generated automatically after selecting crypto and entering amount.</div>
            <div class="muted" style="margin-top:12px;">
                Note: If your deposit does not arrive after 15 minutes, please contact the admin via Telegram or the Contact email and include the crypto transfer receipt.
                Please double-check the amount, coin, and network before confirming. Network fees are paid by the sender. Crypto transactions are irreversible.
            </div>
            <div class="muted" style="margin-top:12px;">
                Note: Please enter the exact amount you intend to deposit and send the exact amount shown. If you send the wrong amount, please contact the admin for support.
                For any questions, contact the admin via the Contact page for assistance.
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Wallet History</div>
        <div class="table">
            <div class="table-row table-head">
                <div>#</div>
                <div>Type</div>
                <div>Amount</div>
                <div>Status</div>
                <div>Note</div>
                <div>Date</div>
            </div>
            <?php if (!empty($transactions)) : ?>
                <?php foreach ($transactions as $row) : ?>
                    <div class="table-row">
                        <div><?php echo htmlspecialchars($row['id']); ?></div>
                        <div><?php echo htmlspecialchars($row['type']); ?></div>
                        <div>$<?php echo number_format($row['amount'], 2); ?></div>
                        <div><?php echo htmlspecialchars($row['status']); ?></div>
                        <div><?php echo htmlspecialchars($row['note'] ?? ''); ?></div>
                        <div><?php echo htmlspecialchars($row['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="table-row">
                    <div>-</div>
                    <div>No transactions found</div>
                    <div>-</div>
                    <div>-</div>
                    <div>-</div>
                    <div>-</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function () {
        var currencySelect = document.getElementById('currency-select');
        var cryptoSelect = document.getElementById('crypto-select');
        var networkSelect = document.getElementById('network-select');
        var depositForm = document.getElementById('deposit-form');
        var cryptoBlocks = document.querySelectorAll('.crypto-only');
        var qrWrap = document.getElementById('qr-wrap');
        var qrStatus = document.getElementById('qr-status');
        var amountInput = document.querySelector('input[name="amount"]');
        var baseUrl = "<?php echo $baseUrl; ?>";
        var lastInvoiceKey = '';
        var cryptoOptions = [];
        var networkOptions = {};

        function parseJsonSafe(text) {
            var clean = text ? text.replace(/^\uFEFF+/, '') : '';
            clean = clean ? clean.replace(/^[^\{]+/, '') : '';
            return clean ? JSON.parse(clean) : null;
        }

        function populateCryptoOptions(options) {
            if (!cryptoSelect) {
                return;
            }
            cryptoSelect.innerHTML = '<option value="">Select</option>';
            options.forEach(function (option) {
                var item = document.createElement('option');
                item.value = option.value;
                item.textContent = option.label;
                cryptoSelect.appendChild(item);
            });
        }

        function populateNetworkOptions(options) {
            if (!networkSelect) {
                return;
            }
            networkSelect.innerHTML = '<option value="">Select</option>';
            options.forEach(function (option) {
                var item = document.createElement('option');
                item.value = option.value;
                item.textContent = option.label;
                networkSelect.appendChild(item);
            });
        }

        function updateNetworkFromCrypto() {
            var key = cryptoSelect ? cryptoSelect.value : '';
            var options = key && networkOptions[key] ? networkOptions[key] : [];
            populateNetworkOptions(options);
        }

        function loadCryptomusServices() {
            fetch(baseUrl + '/deposit/cryptomus-services')
                .then(function (response) {
                    return response.text();
                })
                .then(function (text) {
                    return parseJsonSafe(text);
                })
                .then(function (data) {
                    if (!data || !data.success) {
                        return;
                    }
                    cryptoOptions = Array.isArray(data.coins) ? data.coins : [];
                    networkOptions = data.networks || {};
                    if (cryptoOptions.length) {
                        populateCryptoOptions(cryptoOptions);
                        updateNetworkFromCrypto();
                    }
                })
                .catch(function () {});
        }

        function updateCryptoVisibility() {
            var isCrypto = currencySelect && currencySelect.value === 'crypto';
            for (var i = 0; i < cryptoBlocks.length; i++) {
                cryptoBlocks[i].style.display = isCrypto ? '' : 'none';
            }
            if (!isCrypto) {
                if (cryptoSelect) cryptoSelect.value = '';
                if (networkSelect) networkSelect.value = '';
                if (qrWrap) qrWrap.style.display = 'none';
                if (qrStatus) qrStatus.textContent = '';
                lastInvoiceKey = '';
            }
        }

        function setQrStatus(message) {
            if (qrStatus) {
                qrStatus.textContent = message || '';
            }
        }

        function updateQr() {
            var amount = amountInput ? parseFloat(amountInput.value) : 0;
            var showQr = currencySelect.value === 'crypto'
                && cryptoSelect.value
                && networkSelect.value;
            if (!showQr) {
                if (qrWrap) qrWrap.style.display = 'none';
                setQrStatus('');
                return;
            }
            if (!amount || amount <= 1) {
                if (qrWrap) qrWrap.style.display = 'none';
                setQrStatus('Deposits below $1.00 are not accepted. Enter an amount above $1.00 to generate a payment link.');
                return;
            }
            var invoiceKey = [amount.toFixed(2), cryptoSelect.value, networkSelect.value].join(':');
            if (invoiceKey === lastInvoiceKey) {
                if (qrWrap) qrWrap.style.display = '';
                return;
            }
            lastInvoiceKey = invoiceKey;
            setQrStatus('Generating invoice...');
            if (qrWrap) qrWrap.style.display = '';
            fetch(baseUrl + '/deposit/cryptomus-invoice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: amount,
                    crypto: cryptoSelect.value,
                    network: networkSelect.value
                })
            })
                .then(function (response) {
                    return response.text();
                })
                .then(function (text) {
                    var clean = text ? text.replace(/^\uFEFF+/, '') : '';
                    clean = clean ? clean.replace(/^[^\{]+/, '') : '';
                    return clean ? JSON.parse(clean) : null;
                })
                .then(function (data) {
                    if (!data || !data.success) {
                        throw new Error(data && data.message ? data.message : 'Failed to create invoice.');
                    }
                    var payUrl = data.pay_url || '';
                    if (payUrl) {
                        setQrStatus('Invoice created. Open the payment link below.');
                        var link = document.getElementById('qr-pay-link');
                        if (!link) {
                            link = document.createElement('a');
                            link.id = 'qr-pay-link';
                            link.className = 'btn secondary';
                            link.target = '_blank';
                            link.rel = 'noopener';
                            link.style.display = 'inline-flex';
                            link.style.marginTop = '10px';
                            qrWrap.querySelector('.card').appendChild(link);
                        }
                        link.href = payUrl;
                        link.textContent = 'Open payment link';
                    } else {
                        setQrStatus('Invoice created. Scan to pay.');
                    }
                })
                .catch(function (error) {
                    lastInvoiceKey = '';
                    setQrStatus(error.message || 'Failed to create invoice.');
                });
        }

        if (currencySelect) {
            currencySelect.addEventListener('change', function () {
                updateCryptoVisibility();
                updateQr();
            });
        }
        if (cryptoSelect) {
            cryptoSelect.addEventListener('change', function () {
                updateNetworkFromCrypto();
                updateQr();
            });
        }
        if (networkSelect) networkSelect.addEventListener('change', updateQr);
        if (amountInput) amountInput.addEventListener('change', updateQr);
        updateCryptoVisibility();
        loadCryptomusServices();
        if (depositForm) {
            depositForm.addEventListener('submit', function (event) {
                event.preventDefault();
            });
        }
    })();
</script>
