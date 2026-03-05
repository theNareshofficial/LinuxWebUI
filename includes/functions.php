<?php

function getRAM() {

    $lines = file('/proc/meminfo', FILE_IGNORE_NEW_LINES);
    $mem = [];
    
    foreach ($lines as $line) {
        if (preg_match('/^(\w+):\s+(\d+)/', $line, $match)) {

            $mem[$match[1]] = (int)$match[2];
        }
    }

    $total   = $mem['MemTotal']     ?? 1;
    $free    = $mem['MemAvailable'] ?? 0;
    $used    = $total - $free;

    $percent = round(($used / $total) * 100, 1);

    return [
        'total'   => round($total / 1024),   
        'used'    => round($used  / 1024),   
        'free'    => round($free  / 1024),   
        'percent' => $percent
    ];
}


function getCPU() {

    $load = sys_getloadavg();

    return [
        'load_1m'  => round($load[0], 2),  
        'load_5m'  => round($load[1], 2),  
        'load_15m' => round($load[2], 2)  
    ];
}


function getDisk() {

    $total = disk_total_space('/');
    $free  = disk_free_space('/');
    $used  = $total - $free;

    $percent = round(($used / $total) * 100, 1);

    return [
        'total'   => round($total / 1073741824, 1),  
        'used'    => round($used  / 1073741824, 1),  
        'free'    => round($free  / 1073741824, 1),  
        'percent' => $percent
    ];
}


function getUptime() {

    $raw = @file_get_contents('/proc/uptime');


    if (!$raw) return 'Unknown';

    $seconds = (int)explode(' ', $raw)[0];


    $days    = floor($seconds / 86400);         // 86400 = seconds in a day
    $hours   = floor(($seconds % 86400) / 3600); // % = remainder
    $minutes = floor(($seconds % 3600)  / 60);

    $str = '';
    if ($days > 0)  $str .= $days  . 'd ';
    $str .= $hours   . 'h ';
    $str .= $minutes . 'm';

    return trim($str);  // remove extra spaces
}


function getHostInfo() {
    return [
        'hostname' => gethostname(), 
        'kernel'   => php_uname('r'),  
        'php'      => PHP_VERSION 
    ];
}
