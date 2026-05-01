# Release Log — muki-laufen.ch

Versionierungs-Konvention:
- **Major (v1 → v2):** Neue Hauptseite oder grosse strukturelle Aenderung
- **Minor (v1.1 → v1.2):** UI-Aenderungen, Fixes, Korrekturen, Inhaltsupdates

Versionsnummer steht im Footer aller Seiten (`<p class="version-number">`) und muss bei jeder Aenderung mitgepflegt werden.

---

## v1.3 — 2026-05-01
- Abstand zwischen Header und Seitentitel auf Sub-Seiten reduziert (margin-top 90px → 0, padding 40px → 20px)
- „Teilnahmebedingungen" bricht auf schmalen Screens via Soft-Hyphen (`&shy;`) sauber um → „Teilnahme-/bedingungen", verhindert horizontalen Scroll auf dem Handy

## v1.2 — 2026-05-01
- „:)" hinter „Turnen macht glücklich" durch Sonnenblumen-Emoji 🌻 ersetzt (alle 5 Seiten + meta-description)

## v1.1 — 2026-05-01

**Cache-Strategie etabliert:**
- HTML wird nie gecached (`Cache-Control: no-store, no-cache, must-revalidate`) — Browser holt immer die neuste Fassung
- CSS / JS bleiben 1 Jahr cached, aber URLs sind versions-stamped (`styles.css?v=1.1`)
- Beim naechsten Release einfach Versionsnummer in Footer + Asset-URLs in allen 5 HTMLs hochzaehlen → automatischer Cache-Bust ohne Hard-Refresh

**Aenderungen:**
- Hamburger-Menue (drei Linien) fuer Mobile <=768px in allen 5 Seiten via `nav.js`
- Subtitle „Zufriedene Kinder machen glueckliche Eltern" entfernt (alle Seiten + meta-description)
- Doppelter `<h2>MuKi-Turnen Laufen</h2>` aus Content-Block (index) entfernt
- „in Laufen" aus Ort-Zeile entfernt → „In der Turnhalle Primarschule Serafin"
- Mobile-only Zeilenumbruch vor „und von" in der Zeit-Zeile (`span.zeit-break-mobile`)
- Abstand zwischen Course-Info und „Anmeldung Formular" h2 reduziert (~140px → ~30px)
- Versionsnummer + Impressum-Link im Footer auf allen Seiten ergaenzt

## v1.0 — Baseline
- Erste versionierte Fassung der MuKi-Laufen-Webseite
