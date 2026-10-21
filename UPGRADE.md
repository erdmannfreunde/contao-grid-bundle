# Umstieg auf Version 3

Version 3 bringt ein neues Grid-Element und räumt dabei auf. Alte Reihen rendern unverändert weiter — **nach dem Update musst du nichts tun**, solange du die neuen Elemente nicht nutzen willst. Wer sie nutzen will, legt die betreffenden Bereiche neu an; eine Umwandlung bestehender Reihen gibt es nicht.

## Voraussetzungen

- **Contao 5.7**, darunter läuft Version 3 nicht. Die verschachtelten Inhaltselemente, auf denen das Grid-Element aufbaut, gibt es erst ab 5.7. Für 5.3 bleibt Version 2 nutzbar.
- **PHP 8.2**

## Nach dem Update

1. **`contao:migrate` ausführen.** Es kommt die Spalte `tl_content.grid_layout` dazu, und `tl_layout.addEuFGridCss` fällt weg.
2. **Rechte freischalten.** `grid` und `grid_cell` sind neue Elementtypen. Contao prüft sie über eine Whitelist: Solange die Typen nicht in den Benutzergruppen stehen, sehen Redakteur:innen sie nicht. Als Admin merkst du davon nichts — deine Redaktion schon.
3. **Grid-Klassen im Theme prüfen** (siehe unten).

## Was sich geändert hat

### Das Paket liefert kein Stylesheet mehr

Die Layout-Option „EuF-Grid CSS laden" (`addEuFGridCss`) und die Datei `public/euf_grid.css` sind entfallen, samt Listener, Feld und Datenbankspalte. Die Grid-Klassen gehören ins Theme.

**Wenn dein Theme sie noch nicht mitbringt:** Die [Datei aus Version 2](https://github.com/ErdmannFreunde/contao-grid-bundle/blob/2.x/public/euf_grid.css) dient als Vorlage. Themes auf Basis von Nutshell bringen die Klassen ab Version 2.0.18 mit.

### Das Grid-Element setzt keine Klasse `row` mehr

Es trägt nur noch `content-grid` und was du ihm selbst gibst. Dein Theme muss das Raster also an einer Klasse deiner Wahl definieren — bei Nutshell ist das ab 2.0.18 `.content-grid`.

### Die Höhe heißt `row-{viewport}-{n}`

Statt `row-span-{viewport}-{n}`. Bestehende `row-span`-Klassen werden nicht mehr gelesen. Eine Umwandlung gibt es nicht, weil Version 3 ohnehin voraussetzt, dass die Bereiche neu angelegt werden. Nutshell unterstützt ab 2.0.18 beide Schreibweisen, damit Bestände im Frontend unverändert aussehen.

### Die Option `translated_labels` ist entfallen

Sie konnte in den beiden Expertenfeldern „Grid Columns" und „Grid Optionen" statt des Klassennamens einen Text anzeigen, etwa „6/12 Spalte (Medium Screen)". Diese Texte waren fest in den Sprachdateien hinterlegt, deckten nur einen Teil der Klassen ab (`col-*` und `col-md-*`, für die Optionsklassen gab es gar keine) und wurden falsch, sobald ein Projekt mit einer anderen Spaltenzahl als zwölf arbeitete.

**Wenn du `translated_labels: true` gesetzt hast**, entferne den Schlüssel aus deiner `config.yml` — sonst bricht die Konfiguration mit einer Fehlermeldung ab. In den Selects stehen dann die Klassennamen (`col-md-6`), so wie in der Standardeinstellung bisher auch.

Für den Alltag sind die beiden Felder ohnehin nicht mehr der Weg: Breite, Höhe, Startwerte und Ausrichtung setzt man im Raster-Editor des Grid-Elements. Die Felder bleiben als Expertenweg erhalten und bearbeiten dieselben Werte.

## Was bleibt

- Die vier Legacy-Elementtypen (`rowStart`, `rowEnd`, `colStart`, `colEnd`) samt Templates und Hook. Sie stehen jetzt in der Kategorie „Legacy-Elemente" und **enden mit Version 4**.
- Die vier Formularfeldtypen, unverändert mit der bisherigen Eingabemaske.
- Beide Datenbankspalten (`grid_columns`, `grid_options`) und jeder bestehende Konfigurationsschlüssel außer `translated_labels`.
