<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$ram  = getRAM();
$cpu  = getCPU();
$disk = getDisk();

$response = [
    'ram'    => $ram,
    'cpu'    => [
        'load_1m'  => $cpu['load_1m']  ?? $cpu[0] ?? 0,
        'load_5m'  => $cpu['load_5m']  ?? $cpu[1] ?? 0,
        'load_15m' => $cpu['load_15m'] ?? $cpu[2] ?? 0,
    ],
    'disk'   => $disk,
    'uptime' => getUptime(),
    'host'   => getHostInfo(),
];

echo json_encode($response);