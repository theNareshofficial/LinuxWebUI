<?php
ob_start();
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$ALLOWED_SERVICES = ['apache2', 'ssh', 'vsftpd', 'mongod', 'mysql', 'nginx'];
$ALLOWED_ACTIONS  = ['start', 'stop', 'restart', 'status'];

$service = trim($_POST['service'] ?? '');
$action  = trim($_POST['action']  ?? '');

if (!in_array($service, $ALLOWED_SERVICES, true)) {
    ob_end_clean();
    echo json_encode(['error' => 'Service not allowed']);
    exit;
}

if (!in_array($action, $ALLOWED_ACTIONS, true)) {
    ob_end_clean();
    echo json_encode(['error' => 'Action not allowed']);
    exit;
}

$port_map = [
    'apache2' => 80,
    'ssh'     => 22,
    'vsftpd'  => 21,
    'mongod'  => 27017,
    'mysql'   => 3306,
    'nginx'   => 8080,
];

$proc_map = [
    'apache2' => 'apache2',
    'ssh'     => 'ssh',
    'vsftpd'  => 'vsftpd',
    'mongod'  => 'mongod',
    'mysql'   => 'mysql',
    'nginx'   => 'nginx',
];

$port = $port_map[$service] ?? null;

if ($action === 'status') {
    $running = checkPort($port);
    ob_end_clean();
    echo json_encode([
        'running' => $running,
        'output'  => $running
            ? "$service is running (port $port open)"
            : "$service is stopped (port $port closed)"
    ]);
    exit;
}

// start / stop / restart
$out    = [];
$code   = 0;
putenv('HOME=/tmp');
exec("/usr/local/bin/svc-control "
    . escapeshellarg($action) . " "
    . escapeshellarg($proc_map[$service])
    . " 2>&1", $out, $code);

// $out is array — join into string
$output = implode("\n", $out);

sleep(1);
$running = checkPort($port);

ob_end_clean();
echo json_encode([
    'success' => ($code === 0),
    'running' => $running,
    'output'  => $output ?: ($running ? "$service started." : "$service stopped.")
]);

// Running on host Apache — just check localhost directly
function checkPort($port) {
    if (!$port) return false;
    $conn = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if ($conn) {
        fclose($conn);
        return true;
    }
    return false;
}