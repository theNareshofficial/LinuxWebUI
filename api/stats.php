<?php
// api/stats.php

require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Tell browser NOT to cache (save) this response,We want fresh data every time, not old saved data
header('Cache-Control: no-store, no-cache, must-revalidate');


$ram      = getRAM();       
$cpu      = getCPU();       
$disk     = getDisk();      
$uptime   = getUptime();    
$hostinfo = getHostInfo();  



$response = [
    'ram'      => $ram,
    'cpu'      => $cpu,
    'disk'     => $disk,
    'uptime'   => $uptime,
    'host'     => $hostinfo,

    'generated_at' => time()
];


echo json_encode($response);
