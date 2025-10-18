<?php
/**
 * Access Logger for MuKi website
 * Logs all page accesses with date/time/IP
 */

// Ensure we're getting the real client IP
function getRealClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Log access
function logAccess($page) {
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/access.log';

    $timestamp = date('Y-m-d H:i:s.v');
    $ip = getRealClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    $logEntry = sprintf(
        "[%s] IP: %s | Page: %s | UA: %s | Referer: %s\n",
        $timestamp,
        $ip,
        $page,
        $userAgent,
        $referer
    );

    // Ensure log directory exists
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Get requested page
$page = $_GET['page'] ?? 'unknown';
logAccess($page);

// Return empty 1x1 transparent GIF
header('Content-Type: image/gif');
header('Content-Length: 43');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
?>
