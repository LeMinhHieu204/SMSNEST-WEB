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

function smspoolGet($baseUrl, $apiKey, $path)
{
    $ch = curl_init($baseUrl . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        throw new RuntimeException('API error: ' . $path);
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid response: ' . $path);
    }
    return $data;
}

$services = smspoolGet($baseUrl, $apiKey, '/service/retrieve_all');
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$pdo->exec('TRUNCATE TABLE services');
$stmt = $pdo->prepare('INSERT INTO services (id, service_name, base_price, success_rate) VALUES (?, ?, 0.00, 95)');
foreach ($services as $service) {
    $stmt->execute([(int) $service['ID'], $service['name']]);
}

$countries = smspoolGet($baseUrl, $apiKey, '/country/retrieve_all');
$pdo->exec('TRUNCATE TABLE countries');
$stmt = $pdo->prepare('INSERT INTO countries (id, country_name, code, region) VALUES (?, ?, ?, ?)');
foreach ($countries as $country) {
    $stmt->execute([
        (int) $country['ID'],
        $country['name'],
        $country['short_name'],
        $country['region'] ?? null,
    ]);
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

echo "Sync complete\n";
