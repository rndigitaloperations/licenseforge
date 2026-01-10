<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$appVersion = 'v1.1.1';
$apiUrl = 'https://api.ricardoneud.com/public/products/10/latest-version';
$latestVersion = $appVersion;

try {
    $response = file_get_contents($apiUrl);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['latest_version'])) {
            $latestVersion = $data['latest_version'];
        }
    }
} catch (Exception $e) {}

$stmt = $pdo->query("SELECT @@session.time_zone AS session_tz, @@system_time_zone AS system_tz");
$tz = $stmt->fetch(PDO::FETCH_ASSOC);
$mysqlTz = $tz['session_tz'] === 'SYSTEM' ? $tz['system_tz'] : $tz['session_tz'];

try {
    $dbTz = new DateTimeZone($mysqlTz);
} catch (Exception $e) {
    $dbTz = new DateTimeZone('UTC');
}

$stmt = $pdo->query("SELECT last_run FROM cron_status WHERE id = 1 LIMIT 1");
$lastRunRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lastRunRow && !empty($lastRunRow['last_run'])) {
    try {
        $lastRun = new DateTime($lastRunRow['last_run'], $dbTz);
        $lastRun->setTimezone(new DateTimeZone(date_default_timezone_get()));
    } catch (Exception $e) {
        $lastRun = null;
    }
} else {
    $lastRun = null;
}

$minutesAgo = $lastRun ? round((time() - $lastRun->getTimestamp()) / 60) : null;
$host = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com';
?>