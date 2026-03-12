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
    // disk_total_space('/') is blocked by open_basedir
    $output = shell_exec('df -BG / 2>/dev/null');
    
    if (!$output) {
        return ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0];
    }

    $lines = explode("\n", trim($output));
    
    if (!isset($lines[1])) {
        return ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0];
    }

    // Split on whitespace, remove empty parts
    $parts = preg_split('/\s+/', trim($lines[1]));

    // Remove the 'G' suffix and cast to int
    $total   = (int) str_replace('G', '', $parts[1] ?? '0');
    $used    = (int) str_replace('G', '', $parts[2] ?? '0');
    $free    = (int) str_replace('G', '', $parts[3] ?? '0');
    // Use% is like "42%" — strip the %
    $percent = (int) str_replace('%', '', $parts[4] ?? '0');

    return [
        'total'   => $total,
        'used'    => $used,
        'free'    => $free,
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

    return trim($str); 
}


function getHostInfo() {
    return [
        'hostname' => gethostname(), 
        'kernel'   => php_uname('r'),  
        'php'      => PHP_VERSION 
    ];
}
