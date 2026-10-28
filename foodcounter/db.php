<?php

/**
 * QUAF 2026 - Food Counter Management Database Connection
 * Hostinger MySQL Database Settings
 */

declare(strict_types=1);

// Set Indian Standard Time (IST - Asia/Kolkata)
date_default_timezone_set('Asia/Kolkata');

$dbHost = 'localhost';
$dbName = 'u788415270_foodcounter';
$dbUser = 'u788415270_foodcounter';
$dbPass = 'Foodcounter@786';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Sync MySQL session timezone with Indian Standard Time (+05:30)
    $pdo->exec("SET time_zone = '+05:30'");

    // Auto-migration: Ensure start_time and end_time exist on meal_sessions
    try {
        $col1 = $pdo->query("SHOW COLUMNS FROM `meal_sessions` LIKE 'start_time'")->fetch();
        if (! $col1) {
            $pdo->exec('ALTER TABLE `meal_sessions` ADD COLUMN `start_time` TIME NULL AFTER `meal_type`');
        }
        $col2 = $pdo->query("SHOW COLUMNS FROM `meal_sessions` LIKE 'end_time'")->fetch();
        if (! $col2) {
            $pdo->exec('ALTER TABLE `meal_sessions` ADD COLUMN `end_time` TIME NULL AFTER `start_time`');
        }

        // Auto-cleanup: remove 'General' / 'OFFICE' so only the 5 official teams exist
        $pdo->exec("DELETE FROM students WHERE group_name = 'General' OR chest_number = 'OFFICE'");
    } catch (Exception $e) {
        // Migration check completed or table not ready yet
    }

} catch (PDOException $e) {
    // If running in an API context, return JSON error
    if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'api.php')) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: '.$e->getMessage().'. Please verify database credentials in db.php.',
        ]);
        exit;
    }

    exit('<!DOCTYPE html><html><body style="font-family:sans-serif;padding:30px;background:#f8fafc;color:#0f172a;">'
        .'<div style="max-width:600px;margin:auto;background:#fff;padding:25px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">'
        .'<h2 style="color:#be1e2d;margin-top:0;">Database Connection Required</h2>'
        .'<p>Cannot connect to MySQL database <code>'.htmlspecialchars($dbName).'</code>.</p>'
        .'<p style="color:#64748b;font-size:14px;">Error: '.htmlspecialchars($e->getMessage()).'</p>'
        .'<hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0;">'
        .'<p style="font-size:13px;color:#475569;">1. Ensure the database and user are created in Hostinger hPanel.<br>'
        .'2. Import <code>foodcounter_setup.sql</code> in phpMyAdmin.<br>'
        .'3. Check credentials in <code>db.php</code>.</p>'
        .'</div></body></html>');
}
