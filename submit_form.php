<?php
/**
 * Form submission handler for MuKi website
 * Logs form submissions and saves form data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

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

// Get form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Generate filename with timestamp and IP
$timestamp = date('Y-m-d_H:i:s.v');
$ip = getRealClientIP();
$filename = sprintf('%s-%s.txt', $timestamp, str_replace('.', '-', $ip));
$logDir = __DIR__ . '/logs/log_forms';
$filepath = $logDir . '/' . $filename;

// Ensure directory exists
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Format form data
$formData = "MuKi Musik & Bewegung - Anmeldung\n";
$formData .= "========================================\n";
$formData .= "Datum/Zeit: " . date('Y-m-d H:i:s') . "\n";
$formData .= "IP-Adresse: " . $ip . "\n";
$formData .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n";
$formData .= "========================================\n\n";

// Process form fields
$fields = [
    'parent-name' => 'Name der Eltern',
    'address' => 'Adresse',
    'email' => 'E-Mail',
    'child-name' => 'Name des Kindes',
    'gender' => 'Geschlecht',
    'birthdate' => 'Geburtsdatum',
    'time-slot' => 'Zeitfenster'
];

foreach ($fields as $key => $label) {
    if (isset($input[$key]) && !empty($input[$key])) {
        $value = $input[$key];
        $formData .= sprintf("%-25s: %s\n", $label, $value);
    }
}

$formData .= "\n========================================\n";
$formData .= "Ende der Anmeldung\n";

// Save form data
$success = file_put_contents($filepath, $formData, LOCK_EX);

if ($success !== false) {
    // Also log in main access log
    $accessLogFile = __DIR__ . '/logs/access.log';
    $logEntry = sprintf(
        "[%s] IP: %s | Action: FORM_SUBMITTED | File: %s\n",
        date('Y-m-d H:i:s.v'),
        $ip,
        $filename
    );
    file_put_contents($accessLogFile, $logEntry, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'Anmeldung erfolgreich gespeichert',
        'filename' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Speichern'
    ]);
}
?>
