<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
$selectedServiceName = 'Select service';
if ($serviceId > 0) {
    foreach ($services as $service) {
        if ((int) $service['id'] === (int) $serviceId) {
            $selectedServiceName = $service['service_name'];
            break;
        }
    }
}
$selectedCountryName = $countryQuery !== '' ? $countryQuery : 'Select country';
if ($countryQuery !== '' && !empty($rows)) {
    foreach ($rows as $row) {
        $countryName = $row['country_name'] ?? '';
        $countryCode = $row['code'] ?? '';
        if ($countryName !== '' && stripos($countryName, $countryQuery) !== false) {
            $selectedCountryName = $countryName;
            break;
        }
        if ($countryCode !== '' && stripos($countryCode, $countryQuery) !== false) {
            $selectedCountryName = $countryName !== '' ? $countryName : $countryCode;
            break;
        }
    }
}
?>
<div class="card">
    <div class="card-title">Pricing Management</div>
    <?php if (!empty($_GET['saved'])) : ?>
        <div class="alert">Saved successfully.</div>
    <?php endif; ?>
    <form class="form inline" method="get" action="<?php echo $baseUrl; ?>/admin/pricing">
        <label>Service</label>
        <div class="dropdown" data-dropdown="pricing-service">
            <div class="dropdown-control">
                <input class="dropdown-display" type="text" readonly value="<?php echo htmlspecialchars($selectedServiceName); ?>">
                <button class="dropdown-toggle" type="button">▼</button>
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
            <input type="hidden" name="service_id" value="<?php echo htmlspecialchars((string) $serviceId); ?>">
        </div>
        <label>Country</label>
        <div class="dropdown" data-dropdown="pricing-country">
            <div class="dropdown-control">
                <input class="dropdown-display" type="text" readonly value="<?php echo htmlspecialchars($selectedCountryName); ?>">
                <button class="dropdown-toggle" type="button">▼</button>
            </div>
            <div class="dropdown-panel">
                <input class="dropdown-search" type="text" placeholder="Search country">
                <div class="dropdown-list">
                    <?php foreach ($rows as $row) : ?>
                        <button class="dropdown-item" type="button" data-value="<?php echo htmlspecialchars($row['country_name']); ?>">
                            <?php echo htmlspecialchars($row['country_name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="country_query" value="<?php echo htmlspecialchars($countryQuery ?? ''); ?>">
        </div>
        <button class="btn primary" type="submit">Load</button>
    </form>
</div>

<?php if ($serviceId > 0) : ?>
    <div class="card">
        <div class="card-title">Custom Pricing</div>
        <div class="table">
            <div class="table-row table-head table-compact pricing-table">
                <div>Service</div>
                <div>Country</div>
                <div>Code</div>
                <div>Stock</div>
                <div>API Min</div>
                <div>API Max</div>
                <div>API Price</div>
                <div>Custom Price</div>
                <div>Custom Min</div>
                <div>Custom Max</div>
                <div>Action</div>
            </div>
            <?php foreach ($rows as $row) : ?>
                <form class="table-row table-compact pricing-table" method="post" action="<?php echo $baseUrl; ?>/admin/pricing">
                    <div><?php echo htmlspecialchars($selectedServiceName); ?></div>
                    <div><?php echo htmlspecialchars($row['country_name']); ?></div>
                    <div><?php echo htmlspecialchars($row['code']); ?></div>
                    <div><?php echo htmlspecialchars($row['stock']); ?></div>
                    <div>$<?php echo number_format($row['min_price'], 2); ?></div>
                    <div>$<?php echo number_format($row['max_price'], 2); ?></div>
                    <div>
                        <?php
                        $minPrice = (float) $row['min_price'];
                        $maxPrice = (float) $row['max_price'];
                        if (abs($minPrice - $maxPrice) < 0.00001) {
                            echo '$' . number_format($minPrice, 2);
                        } else {
                            echo '$' . number_format($minPrice, 2) . ' - $' . number_format($maxPrice, 2);
                        }
                        ?>
                    </div>
                    <div>
                        <?php
                        $customMin = $row['custom_min_price'] !== null ? (float) $row['custom_min_price'] : null;
                        $customMax = $row['custom_max_price'] !== null ? (float) $row['custom_max_price'] : null;
                        if ($customMin === null && $customMax === null) {
                            echo '-';
                        } elseif ($customMin !== null && $customMax !== null) {
                            if (abs($customMin - $customMax) < 0.00001) {
                                echo '$' . number_format($customMin, 2);
                            } else {
                                echo '$' . number_format($customMin, 2) . ' - $' . number_format($customMax, 2);
                            }
                        } elseif ($customMax !== null) {
                            echo '$' . number_format($customMax, 2);
                        } else {
                            echo '$' . number_format($customMin, 2);
                        }
                        ?>
                    </div>
                    <div>
                        <input class="input-sm" type="text" name="custom_min_price" value="<?php echo $row['custom_min_price'] !== null ? $row['custom_min_price'] : ''; ?>">
                    </div>
                    <div>
                        <input class="input-sm" type="text" name="custom_max_price" value="<?php echo $row['custom_max_price'] !== null ? $row['custom_max_price'] : ''; ?>">
                    </div>
                    <div>
                        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($serviceId); ?>">
                        <input type="hidden" name="country_id" value="<?php echo htmlspecialchars($row['country_id']); ?>">
                        <button class="btn success" type="submit">Save</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
