<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {

    $base = dirname($_SERVER['SCRIPT_NAME']);
    header('Location: /login.php');
    exit;
}