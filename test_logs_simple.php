<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>MuKi Logs Test</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>🧪 MuKi Logs Test</h1>

    <div class="section">
        <h2>1. Verzeichnis-Status</h2>
        <?php
        $logDir = __DIR__ . '/logs';
        $formsDir = __DIR__ . '/logs/log_forms';

        echo "<p>Log-Verzeichnis: <strong>" . ($logDir) . "</strong><br>";
        if (is_dir($logDir)) {
            echo "<span class='success'>✅ Existiert</span> | ";
            echo "Beschreibbar: " . (is_writable($logDir) ? "<span class='success'>✅ JA</span>" : "<span class='error'>❌ NEIN</span>");
        } else {
            echo "<span class='error'>❌ Existiert nicht</span>";
        }
        echo "</p>";

        echo "<p>Formular-Verzeichnis: <strong>" . ($formsDir) . "</strong><br>";
        if (is_dir($formsDir)) {
            echo "<span class='success'>✅ Existiert</span> | ";
            echo "Beschreibbar: " . (is_writable($formsDir) ? "<span class='success'>✅ JA</span>" : "<span class='error'>❌ NEIN</span>");
        } else {
            echo "<span class='error'>❌ Existiert nicht</span>";
        }
        echo "</p>";
        ?>
    </div>

    <div class="section">
        <h2>2. Test: Access Log schreiben</h2>
        <?php
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
            echo "<p class='success'>✅ Log-Verzeichnis erstellt</p>";
        }

        $accessLogFile = $logDir . '/access.log';
        $testEntry = sprintf(
            "[%s] IP: %s | Page: test_logs_simple.php | Test: SUCCESS\n",
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );

        $result = @file_put_contents($accessLogFile, $testEntry, FILE_APPEND | LOCK_EX);
        if ($result !== false) {
            echo "<p class='success'>✅ Access Log Test: ERFOLG</p>";
            echo "<p>Geschrieben: " . $result . " bytes</p>";
        } else {
            echo "<p class='error'>❌ Access Log Test: FEHLER</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Test: Formular Log schreiben</h2>
        <?php
        if (!is_dir($formsDir)) {
            mkdir($formsDir, 0755, true);
            echo "<p class='success'>✅ Formular-Verzeichnis erstellt</p>";
        }

        $timestamp = date('Y-m-d_H-i-s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $testFile = $formsDir . '/TEST-' . $timestamp . '-' . str_replace('.', '-', $ip) . '.txt';

        $testData = "MuKi Musik & Bewegung - TEST ANMELDUNG\n";
        $testData .= "========================================\n";
        $testData .= "Datum/Zeit: " . date('Y-m-d H:i:s') . "\n";
        $testData .= "IP-Adresse: " . $ip . "\n";
        $testData .= "Status: TEST OK\n";
        $testData .= "========================================\n";

        $result = @file_put_contents($testFile, $testData, LOCK_EX);
        if ($result !== false) {
            echo "<p class='success'>✅ Formular Log Test: ERFOLG</p>";
            echo "<p>Datei erstellt: <strong>" . basename($testFile) . "</strong></p>";
            echo "<p>Geschrieben: " . $result . " bytes</p>";
        } else {
            echo "<p class='error'>❌ Formular Log Test: FEHLER</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Access Log (Letzte 10 Zeilen)</h2>
        <?php
        if (file_exists($accessLogFile)) {
            $lines = @file($accessLogFile);
            if ($lines) {
                echo "<p>Anzahl Einträge: <strong>" . count($lines) . "</strong></p>";
                echo "<pre>" . htmlspecialchars(implode('', array_slice($lines, -10))) . "</pre>";
            } else {
                echo "<p class='error'>❌ Kann access.log nicht lesen</p>";
            }
        } else {
            echo "<p>Noch keine Einträge</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>5. Formular Submissions (Letzte 10)</h2>
        <?php
        if (is_dir($formsDir)) {
            $files = glob($formsDir . '/*.txt');
            if ($files) {
                rsort($files);
                echo "<p>Anzahl Submissions: <strong>" . count($files) . "</strong></p>";
                echo "<ul>";
                foreach (array_slice($files, 0, 10) as $file) {
                    $date = date('Y-m-d H:i:s', filemtime($file));
                    $size = filesize($file);
                    echo "<li><strong>" . basename($file) . "</strong><br>";
                    echo "    Datum: $date | Größe: $size bytes</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Noch keine Submissions</p>";
            }
        } else {
            echo "<p class='error'>❌ Formular-Verzeichnis existiert nicht</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>6. JavaScript Test</h2>
        <p>Testet ob das Logging vom Frontend funktioniert:</p>
        <button onclick="testAccessLog()">📊 Access Log Test</button>
        <button onclick="testFormLog()">📝 Formular Log Test</button>
        <div id="result" style="margin-top: 15px;"></div>
    </div>

    <script>
        function testAccessLog() {
            document.getElementById('result').innerHTML = '<p>Sende Access Log...</p>';
            var img = new Image();
            img.src = 'log_access.php?page=test_javascript&t=' + Date.now();
            img.onload = function() {
                document.getElementById('result').innerHTML = '<p class="success">✅ Access Log gesendet! Seite neu laden um zu sehen.</p>';
            };
            img.onerror = function() {
                document.getElementById('result').innerHTML = '<p class="error">❌ Fehler beim Senden</p>';
            };
        }

        function testFormLog() {
            document.getElementById('result').innerHTML = '<p>Sende Formular Log...</p>';

            fetch('submit_form.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    'parent-name': 'Test Eltern',
                    'email': 'test@test.com',
                    'child-name': 'Test Kind',
                    'gender': 'Test',
                    'birthdate': '2020-01-01',
                    'time-slot': '9:10 - 10:00'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('result').innerHTML =
                        '<p class="success">✅ Formular Log gesendet!</p>' +
                        '<p>Datei: <strong>' + data.filename + '</strong></p>' +
                        '<p>Seite neu laden um zu sehen.</p>';
                } else {
                    document.getElementById('result').innerHTML =
                        '<p class="error">❌ Fehler: ' + data.message + '</p>';
                }
            })
            .catch(e => {
                document.getElementById('result').innerHTML =
                    '<p class="error">❌ Fehler: ' + e.message + '</p>';
            });
        }
    </script>

    <hr style="margin: 30px 0;">
    <p><small>Generiert: <?php echo date('Y-m-d H:i:s'); ?></small></p>
    <p><a href="index.html">← Zurück zur Hauptseite</a></p>
</body>
</html>
