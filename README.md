# MuKi Turnenlaufen Website

Eine moderne, responsive Website für MuKi (Mutter-Kind) Turnenlaufen Programme.

## 📋 Inhalt

- [Übersicht](#übersicht)
- [Features](#features)
- [Struktur](#struktur)
- [Installation](#installation)
- [Anpassungen](#anpassungen)
- [Bilder](#bilder)
- [Hosting](#hosting)

## 🎯 Übersicht

Diese Website ersetzt die ursprüngliche Wix-Website `mukiturnenlaufen.wixsite.com/website` und bietet:

- Vollständig responsive Design
- Moderne, kinderfreundliche Optik
- Kontaktformular mit Validierung
- Mobile Navigation
- Bildergalerie mit Modal-Ansicht
- SEO-optimierte Struktur

## ✨ Features

### Benutzerfreundlichkeit
- 🎨 Modernes, farbenfrohes Design
- 📱 Vollständig responsive für alle Geräte
- 🚀 Schnelle Ladezeiten
- ♿ Barrierefrei (WCAG konform)

### Funktionalität
- 📧 Funktionsfähiges Kontaktformular
- 🖼️ Interaktive Bildergalerie
- 🍔 Mobile Hamburger-Navigation  
- 💫 Smooth Scroll Navigation
- 🔔 Toast-Benachrichtigungen

### Inhalte
- 🏠 Startseite mit Hero-Section
- ℹ️ Über uns Seite
- 🏃 Programm-Übersichten (MuKi Turnen & Laufen)
- 📅 Kurszeiten und Termine
- 💰 Preisübersicht
- 📞 Kontakt und Anmeldeformular
- 🖼️ Bildergalerie
- 📋 Impressum, Datenschutz, AGB

## 📁 Struktur

```
MuKi/
├── index.html          # Hauptseite
├── impressum.html      # Rechtliche Seiten
├── styles.css          # Alle CSS-Styles
├── script.js          # JavaScript-Funktionalität
├── images/            # Bilder-Ordner
│   └── README.md      # Bildanforderungen
└── README.md          # Diese Datei
```

## 🚀 Installation

### Lokale Entwicklung

1. **Repository klonen/herunterladen**
   ```bash
   # Falls Git verwendet wird
   git clone [repository-url]
   cd MuKi
   ```

2. **Lokalen Server starten**
   ```bash
   # Option 1: Python SimpleHTTPServer
   python3 -m http.server 8000
   
   # Option 2: Node.js http-server
   npx http-server
   
   # Option 3: PHP Built-in Server
   php -S localhost:8000
   ```

3. **Website öffnen**
   - Öffnen Sie `http://localhost:8000` in Ihrem Browser

### Direkte Nutzung
- Öffnen Sie einfach `index.html` in einem modernen Browser
- Alle Funktionen außer dem Kontaktformular funktionieren offline

## 🎨 Anpassungen

### Farben ändern
Bearbeiten Sie die CSS-Variablen in `styles.css`:

```css
:root {
    --primary-color: #ff6b6b;    /* Hauptfarbe (rot) */
    --secondary-color: #4ecdc4;  /* Sekundärfarbe (türkis) */
    --accent-color: #45b7d1;     /* Akzentfarbe (blau) */
    --text-dark: #2c3e50;        /* Dunkler Text */
    --text-light: #7f8c8d;       /* Heller Text */
}
```

### Kontaktdaten ändern
Ersetzen Sie in `index.html` die Platzhalter:

```html
<!-- Suchen Sie nach diesen Platzhaltern: -->
Sandra Müller                 → Ihr Name
+41 79 123 45 67             → Ihre Telefonnummer
info@muki-turnenlaufen.ch    → Ihre E-Mail
Musterstrasse 12, 8000 Zürich → Ihre Adresse
```

### Kurse und Preise
Passen Sie die Kurszeiten und Preise in den entsprechenden Sections an.

## 🖼️ Bilder

Die Website benötigt folgende Bilder (siehe `images/README.md`):

### Erforderliche Bilder:
- `hero-muki.jpg` (1200x800px) - Hauptbild
- `about-us.jpg` (800x600px) - Über uns Bild
- `muki-turnen.jpg` (400x300px) - MuKi Turnen
- `muki-laufen.jpg` (400x300px) - MuKi Laufen  
- `gallery1-4.jpg` (300x250px) - Galerie-Bilder

### Temporäre Lösung:
Verwenden Sie Platzhalter-Bilder von https://picsum.photos oder ähnlichen Diensten.

## 🌐 Hosting

### Statisches Hosting (Empfohlen)

**Netlify** (Kostenlos):
1. Registrieren auf netlify.com
2. Ordner hochladen oder Git verbinden
3. Automatisches Deployment

**Vercel** (Kostenlos):
1. Registrieren auf vercel.com  
2. GitHub Repository verbinden
3. Automatisches Deployment

**GitHub Pages** (Kostenlos):
1. Repository auf GitHub hochladen
2. GitHub Pages in Settings aktivieren
3. Website unter `username.github.io/MuKi` verfügbar

### Traditionelles Hosting
- Laden Sie alle Dateien per FTP auf Ihren Webserver
- Stellen Sie sicher, dass `index.html` im Root-Verzeichnis liegt

## 📧 Kontaktformular Setup

Das Kontaktformular benötigt ein Backend für die Verarbeitung:

### Option 1: Netlify Forms (Einfachste Lösung)
```html
<form netlify>
  <!-- Ihre Formularfelder -->
</form>
```

### Option 2: Formspree.io
```html
<form action="https://formspree.io/f/ihre-form-id" method="POST">
  <!-- Ihre Formularfelder -->
</form>
```

### Option 3: Eigener Server
Implementieren Sie ein PHP/Node.js Script zur E-Mail-Verarbeitung.

## 🔧 Browser-Unterstützung

- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Mobile Browser (iOS Safari, Chrome Mobile)

## 📱 Mobile Optimierung

- Responsive Breakpoints: 768px, 480px
- Touch-freundliche Navigation
- Optimierte Bildgrößen
- Fast-loading Design

## 🚀 Performance

- Minimale externe Abhängigkeiten
- Optimierte CSS (CSS-Grid, Flexbox)
- Vanilla JavaScript (keine Frameworks)
- Web-optimierte Bilder erforderlich

## 📞 Support

Bei Fragen oder Problemen:

1. Überprüfen Sie die Browser-Konsole auf Fehler
2. Stellen Sie sicher, dass alle Bilder korrekt benannt sind
3. Testen Sie auf verschiedenen Geräten
4. Kontrollieren Sie die Internetverbindung für externe Schriften

## 📄 Lizenz

Dieses Projekt wurde speziell für MuKi Turnenlaufen erstellt. Alle Rechte vorbehalten.

---

**Viel Erfolg mit Ihrer neuen Website! 🎉**