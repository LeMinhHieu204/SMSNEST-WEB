<?php
$input = __DIR__ . '/../database/services.tsv';
$output = __DIR__ . '/../database/import_services.sql';
if (!file_exists($input)) {
    fwrite(STDERR, "Missing services.tsv\n");
    exit(1);
}
$lines = file($input, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$values = [];
foreach ($lines as $line) {
    $parts = preg_split('/\t+/', trim($line));
    if (count($parts) < 2 || !is_numeric($parts[0])) {
        continue;
    }
    $id = (int) $parts[0];
    $name = str_replace("'", "''", $parts[1]);
    $values[] = "($id, '$name', 0.00, 95)";
}
$sql = "INSERT INTO services (id, service_name, base_price, success_rate) VALUES\n" . implode(",\n", $values) . ";\n";
file_put_contents($output, $sql);
