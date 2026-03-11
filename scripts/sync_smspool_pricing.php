<?php
$config = require __DIR__ . '/../config/config.php';
$apiKey = $config['smspool']['api_key'] ?? '';
$baseUrl = rtrim($config['smspool']['base_url'] ?? '', '/');

if ($apiKey === '' || $baseUrl === '') {
    fwrite(STDERR, "Missing SMSPool API config\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['db']['host'], $config['db']['name'], $config['db']['charset']);
$pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$options = getopt('', ['service::', 'max-price::', 'sleep::']);
$serviceFilter = isset($options['service']) ? (int) $options['service'] : 0;
$maxPrice = isset($options['max-price']) ? (float) $options['max-price'] : 0;
$sleep = isset($options['sleep']) ? (int) $options['sleep'] : 0;

$serviceSql = 'SELECT id FROM services';
$params = [];
if ($serviceFilter > 0) {
    $serviceSql .= ' WHERE id = ?';
    $params[] = $serviceFilter;
}
$stmt = $pdo->prepare($serviceSql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_COLUMN);

$upsertCountry = $pdo->prepare(
    'INSERT INTO countries (id, country_name, code, region) VALUES (?, ?, ?, NULL) '
    . 'ON DUPLICATE KEY UPDATE country_name = VALUES(country_name), code = VALUES(code)'
);

$upsert = $pdo->prepare(
    'INSERT INTO service_countries (service_id, country_id, stock, min_price, max_price) '
    . 'VALUES (?, ?, 0, ?, ?) '
    . 'ON DUPLICATE KEY UPDATE min_price = VALUES(min_price), max_price = VALUES(max_price)'
);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
    ],
]);

foreach ($services as $serviceId) {
    $payload = [
        'key' => $apiKey,
        'service' => (int) $serviceId,
    ];
    if ($maxPrice > 0) {
        $payload['max_price'] = $maxPrice;
    }

    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/request/pricing');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $status >= 400) {
        fwrite(STDERR, "Pricing failed for service {$serviceId}\n");
        continue;
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        fwrite(STDERR, "Invalid response for service {$serviceId}\n");
        continue;
    }

    foreach ($data as $row) {
        $upsertCountry->execute([
            (int) $row['country'],
            $row['country_name'] ?? 'Unknown',
            $row['short_name'] ?? '',
        ]);
        $upsert->execute([
            (int) $row['service'],
            (int) $row['country'],
            (float) $row['price'],
            (float) $row['price'],
        ]);
    }

    if ($sleep > 0) {
        sleep($sleep);
    }
}

curl_close($ch);
echo "Pricing sync complete\n";
