<?php
/**
 * Minimal Google Sheets client (Service-Account, JWT-Bearer).
 *
 * Usage:
 *   $client = new GoogleSheetsClient('/path/to/service-account-key.json');
 *   $client->appendRegistration(
 *       '1N9Q_VkVhrVbLRpZIiZDVfeq-Y55pgyP4piTCVZWKGS0',
 *       'MUKI 1 2026-27',
 *       $dataArray
 *   );
 */

final class GoogleSheetsClient
{
    private string $clientEmail;
    private string $privateKey;
    private ?string $tokenCache = null;
    private int $tokenExpiresAt = 0;

    public function __construct(string $keyFilePath)
    {
        if (!is_readable($keyFilePath)) {
            throw new RuntimeException("Key-Datei nicht lesbar: $keyFilePath");
        }
        $key = json_decode(file_get_contents($keyFilePath), true);
        if (!is_array($key) || empty($key['client_email']) || empty($key['private_key'])) {
            throw new RuntimeException('Key-Datei ungueltig (client_email / private_key fehlt)');
        }
        $this->clientEmail = $key['client_email'];
        $this->privateKey  = $key['private_key'];
    }

    /**
     * Traegt eine Anmeldung in die naechste vornummerierte freie Zeile ein
     * (erste Zeile >=2 mit leerer Spalte C). Faellt sonst zurueck auf append.
     *
     * $data Schluessel: nachname_kind, vorname_kind, geschlecht (K|M),
     *   strasse, plz_ort, mobile, email, geburtsdatum (DD.MM.YYYY),
     *   nachname_eltern, vorname_eltern
     */
    public function appendRegistration(string $spreadsheetId, string $tab, array $data): int
    {
        $nowCh = (new DateTimeImmutable('now', new DateTimeZone('Europe/Zurich')))
            ->format('d.m.Y H:i');

        $row = [
            '',                            // A: lfd. Nummer (unten setzen)
            '',                            // B: Spacer
            $data['nachname_kind']    ?? '',
            $data['vorname_kind']     ?? '',
            $data['geschlecht']       ?? '',
            $data['strasse']          ?? '',
            $data['plz_ort']          ?? '',
            $data['mobile']           ?? '',
            $data['email']            ?? '',
            $data['geburtsdatum']     ?? '',
            $data['nachname_eltern']  ?? '',
            $data['vorname_eltern']   ?? '',
            $nowCh,                        // M: Anmeldedatum
        ];

        // 1) Spalten A+C auslesen, naechste freie Zeile finden
        $existing = $this->get($spreadsheetId, "'{$tab}'!A2:C");
        $targetRow = null;          // 1-basierte Zeilennummer
        $maxIndex  = 0;
        foreach ($existing as $idx => $rowValues) {
            $sheetRowNo = $idx + 2; // A2 ist idx=0
            $colA = $rowValues[0] ?? '';
            $colC = $rowValues[2] ?? '';
            if ($colA !== '' && is_numeric($colA)) {
                $maxIndex = max($maxIndex, (int)$colA);
            }
            if ($targetRow === null && $colA !== '' && $colC === '') {
                $targetRow = $sheetRowNo;
                $row[0] = (string)$colA; // bestehende Nummer uebernehmen
            }
        }

        // 2) Falls keine vornummerierte Zeile frei: neu anhaengen
        if ($targetRow === null) {
            $row[0] = (string)($maxIndex + 1);
            return $this->append($spreadsheetId, $tab, $row);
        }

        // 3) Gefundene Zeile beschreiben (A:M)
        $range = sprintf("'%s'!A%d:M%d", $tab, $targetRow, $targetRow);
        $this->updateValues($spreadsheetId, $range, [$row]);
        return $targetRow;
    }

    // ---------- Low-level helpers ----------

    private function get(string $spreadsheetId, string $range): array
    {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
            rawurlencode($spreadsheetId),
            rawurlencode($range)
        );
        $resp = $this->httpJson('GET', $url);
        return $resp['values'] ?? [];
    }

    private function updateValues(string $spreadsheetId, string $range, array $values): void
    {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?valueInputOption=USER_ENTERED',
            rawurlencode($spreadsheetId),
            rawurlencode($range)
        );
        $this->httpJson('PUT', $url, ['values' => $values]);
    }

    private function append(string $spreadsheetId, string $tab, array $row): int
    {
        $range = "'{$tab}'!A:M";
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS',
            rawurlencode($spreadsheetId),
            rawurlencode($range)
        );
        $resp = $this->httpJson('POST', $url, ['values' => [$row]]);
        // updatedRange z.B. "'MUKI 1 2026-27'!A42:M42" → Zeilennummer extrahieren
        if (preg_match('/!A(\d+):/', $resp['updates']['updatedRange'] ?? '', $m)) {
            return (int)$m[1];
        }
        return 0;
    }

    private function httpJson(string $method, string $url, ?array $body = null): array
    {
        $ch = curl_init($url);
        $headers = ['Authorization: Bearer ' . $this->accessToken()];
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 15,
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($resp === false) {
            throw new RuntimeException("HTTP request failed: $err");
        }
        if ($code < 200 || $code >= 300) {
            throw new RuntimeException("Sheets API $method $url → HTTP $code: $resp");
        }
        return json_decode($resp, true) ?: [];
    }

    private function accessToken(): string
    {
        if ($this->tokenCache !== null && time() < $this->tokenExpiresAt - 60) {
            return $this->tokenCache;
        }

        $now = time();
        $header  = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims  = [
            'iss'   => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];
        $segments = self::b64url(json_encode($header)) . '.' . self::b64url(json_encode($claims));
        if (!openssl_sign($segments, $sig, $this->privateKey, 'SHA256')) {
            throw new RuntimeException('JWT signing failed');
        }
        $jwt = $segments . '.' . self::b64url($sig);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($code !== 200) {
            throw new RuntimeException("Token-Request fehlgeschlagen ($code): $resp");
        }
        $data = json_decode($resp, true);
        if (empty($data['access_token'])) {
            throw new RuntimeException('Kein access_token im Response');
        }
        $this->tokenCache     = $data['access_token'];
        $this->tokenExpiresAt = $now + (int)($data['expires_in'] ?? 3600);
        return $this->tokenCache;
    }

    private static function b64url(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }
}
