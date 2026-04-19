<?php
/**
 * Form submission handler for MuKi website.
 *
 * - Speichert jede Anmeldung als einzelne Textdatei (logs/log_forms/)
 * - Schreibt einen Eintrag in logs/access.log
 * - Schreibt eine Zeile ins Google Sheet (Tab je nach Lektion)
 *
 * Antwort ist immer JSON. Fehler beim Sheet-Schreiben sind non-fatal:
 * das Frontend oeffnet WhatsApp trotzdem, und die Dateisystem-Logs bleiben
 * die Wahrheitsquelle.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/google_sheets_lib.php';

// ---------- Konfiguration ----------
$KEY_FILE       = getenv('GOOGLE_SHEETS_KEY') ?: '/run/secrets/google-sheets-key.json';
$SPREADSHEET_ID = getenv('GOOGLE_SHEETS_ID')  ?: '1N9Q_VkVhrVbLRpZIiZDVfeq-Y55pgyP4piTCVZWKGS0';
$TAB_MAP = [
    '9:10 - 10:00'  => 'MUKI 1 2026-27',
    '10:10 - 11:00' => 'MUKI 2 2026-27',
];
$LOG_DIR        = __DIR__ . '/logs';
$FORM_LOG_DIR   = $LOG_DIR . '/log_forms';
$ACCESS_LOG     = $LOG_DIR . '/access.log';

// ---------- Eingabe lesen ----------
$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '', true);
if (!is_array($input)) {
    $input = $_POST;
}

$ip = getRealClientIP();

// Pflichtfelder
$required = [
    'parent-nachname', 'parent-vorname',
    'address-strasse', 'address-plz-ort',
    'mobile', 'email',
    'child-nachname', 'child-vorname',
    'gender', 'birthdate', 'time-slot',
];
$missing = [];
foreach ($required as $k) {
    if (!isset($input[$k]) || trim((string)$input[$k]) === '') {
        $missing[] = $k;
    }
}
if ($missing) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Fehlende Felder', 'missing' => $missing]);
    exit;
}

// Normalisieren / Plausibilisieren
$gender = strtoupper(trim((string)$input['gender']));
if (!in_array($gender, ['K', 'M'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Ungueltiges Geschlecht (K oder M erwartet)']);
    exit;
}
$timeSlot = trim((string)$input['time-slot']);
if (!isset($TAB_MAP[$timeSlot])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Ungueltiges Zeitfenster']);
    exit;
}
$tab = $TAB_MAP[$timeSlot];

// Email
$email = filter_var(trim((string)$input['email']), FILTER_VALIDATE_EMAIL);
if ($email === false) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Ungueltige E-Mail-Adresse']);
    exit;
}

// Geburtsdatum: Input ist ISO (YYYY-MM-DD) → formatieren als DD.MM.YYYY
$birthIso = trim((string)$input['birthdate']);
$birthFormatted = $birthIso;
$d = DateTimeImmutable::createFromFormat('!Y-m-d', $birthIso);
if ($d !== false) {
    $birthFormatted = $d->format('d.m.Y');
}

// Sheet-Daten vorbereiten (capitalize Konsistenz optional — wir lassen Eingabe wie ist,
// trimmen nur Whitespace)
$sheetData = [
    'nachname_kind'   => trim((string)$input['child-nachname']),
    'vorname_kind'    => trim((string)$input['child-vorname']),
    'geschlecht'      => $gender,
    'strasse'         => trim((string)$input['address-strasse']),
    'plz_ort'         => trim((string)$input['address-plz-ort']),
    'mobile'          => trim((string)$input['mobile']),
    'email'           => $email,
    'geburtsdatum'    => $birthFormatted,
    'nachname_eltern' => trim((string)$input['parent-nachname']),
    'vorname_eltern'  => trim((string)$input['parent-vorname']),
];

// ---------- Dateisystem-Log ----------
@mkdir($FORM_LOG_DIR, 0755, true);
$ts = (new DateTimeImmutable('now', new DateTimeZone('Europe/Zurich')))->format('Y-m-d_H:i:s.v');
$fileSafeIp = str_replace(['.', ':'], '-', $ip);
$logFileName = "{$ts}-{$fileSafeIp}.txt";
$logFilePath = $FORM_LOG_DIR . '/' . $logFileName;

$logLines  = "MuKi-Turnen Laufen — Anmeldung\n";
$logLines .= str_repeat('=', 40) . "\n";
$logLines .= 'Datum/Zeit : ' . $ts . "\n";
$logLines .= 'IP-Adresse : ' . $ip . "\n";
$logLines .= 'User-Agent : ' . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n";
$logLines .= 'Lektion    : ' . $timeSlot . ' (' . $tab . ")\n";
$logLines .= str_repeat('=', 40) . "\n\n";
$logLines .= sprintf("%-22s: %s %s\n", 'Eltern',          $sheetData['vorname_eltern'], $sheetData['nachname_eltern']);
$logLines .= sprintf("%-22s: %s, %s\n", 'Adresse',        $sheetData['strasse'], $sheetData['plz_ort']);
$logLines .= sprintf("%-22s: %s\n", 'Mobile',             $sheetData['mobile']);
$logLines .= sprintf("%-22s: %s\n", 'E-Mail',             $sheetData['email']);
$logLines .= sprintf("%-22s: %s %s\n", 'Kind',            $sheetData['vorname_kind'], $sheetData['nachname_kind']);
$logLines .= sprintf("%-22s: %s\n", 'Geschlecht',         $sheetData['geschlecht']);
$logLines .= sprintf("%-22s: %s\n", 'Geburtsdatum',       $sheetData['geburtsdatum']);
file_put_contents($logFilePath, $logLines, LOCK_EX);

// Access-log
@file_put_contents(
    $ACCESS_LOG,
    sprintf("[%s] IP: %s | Action: FORM_SUBMITTED | File: %s | Tab: %s\n", $ts, $ip, $logFileName, $tab),
    FILE_APPEND | LOCK_EX
);

// ---------- Google Sheets schreiben ----------
$sheetStatus = 'skipped';
$sheetRow    = null;
$sheetError  = null;
try {
    if (!is_readable($KEY_FILE)) {
        throw new RuntimeException("Key nicht lesbar: $KEY_FILE");
    }
    $client = new GoogleSheetsClient($KEY_FILE);
    $sheetRow = $client->appendRegistration($SPREADSHEET_ID, $tab, $sheetData);
    $sheetStatus = 'ok';
} catch (Throwable $e) {
    $sheetStatus = 'error';
    $sheetError  = $e->getMessage();
    @file_put_contents(
        $ACCESS_LOG,
        sprintf("[%s] SHEETS_ERROR: %s\n", $ts, $sheetError),
        FILE_APPEND | LOCK_EX
    );
}

echo json_encode([
    'success'      => true,
    'message'      => 'Anmeldung gespeichert',
    'log_file'     => $logFileName,
    'sheet_status' => $sheetStatus,
    'sheet_row'    => $sheetRow,
    'sheet_error'  => $sheetError,
], JSON_UNESCAPED_UNICODE);
exit;


// ---------- Helpers ----------
function getRealClientIP(): string
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (empty($_SERVER[$k])) continue;
        $ip = $_SERVER[$k];
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
