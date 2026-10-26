# Grid-Erweiterung 3.0 testen

Vielen Dank, dass du die neue Version ausprobierst. Sie bringt ein neues Grid-Element, das die bisherigen Reihe-Start/Ende-Paare ablöst. Diese Anleitung bringt dich in etwa zehn Minuten zum ersten Raster.

**Bitte nur in einer Testinstallation.** Die Version ist ein Vorab-Stand und noch nicht für Kundenprojekte gedacht.

## Voraussetzungen

- Contao **5.7** oder neuer (darunter läuft die Version nicht)
- PHP 8.2 oder neuer
- Ein Theme, das Grid-Klassen mitbringt — bei Nutshell ab Version **2.0.18**

## Installieren

In die `composer.json` des Projekts:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/erdmannfreunde/contao-grid-bundle.git" }
    ],
    "require": {
        "erdmannfreunde/contao-grid-bundle": "dev-3.x as 2.99.0"
    }
}
```

Das `as 2.99.0` ist nötig, weil manche Theme-Pakete die Version 2 voraussetzen.

Danach:

```bash
composer update erdmannfreunde/contao-grid-bundle
vendor/bin/contao-console contao:migrate
vendor/bin/contao-console cache:clear
```

Über den Contao Manager geht es genauso: Paketquelle eintragen, `dev-3.x as 2.99.0` anfordern, anschließend die Datenbank aktualisieren.

## Vor dem ersten Test

1. **Rechte freischalten.** `Grid` und `Spalte` sind neue Elementtypen. In den Benutzergruppen unter „Erlaubte Elemente" müssen sie angehakt sein — sonst sehen Redakteur:innen sie nicht. Als Administrator:in brauchst du das nicht.
2. **Theme prüfen.** Das Paket liefert kein Stylesheet mehr. Dein Theme muss `.content-grid` als Raster definieren, die Klassen `col-{viewport}-{n}` kennen und Elemente **ohne** Breitenklasse auf volle Breite stellen. Mit Nutshell ab 2.0.18 ist das erledigt.

## Was du ausprobieren kannst

1. Lege in einem Artikel ein Element vom Typ **Grid** an.
2. Im Bereich **Raster** wählst du je Ansicht ein Muster: untereinander, 2 gleich, 3 gleich … oder **frei**.
3. Speichere mit **„Speichern und bearbeiten"** und lege darin Inhaltselemente an — Text, Bilder, Akkordeon, Slider, alles außer einem weiteren Grid.
4. Zurück im Grid-Element siehst du die Elemente als Kacheln. Im freien Modus kannst du Breite und Höhe direkt an der Kachel ändern; das Dreipunkt-Feld öffnet Startspalte, Startzeile und Ausrichtung.
5. Das **Verschieben per Maus** funktioniert auch über Spalten hinweg.
6. Der Elementtyp **Spalte** fasst mehrere Elemente zu einer Rasterzelle zusammen.

Nützlich zu wissen: Änderungen im Raster werden erst mit **Speichern** in die Elemente geschrieben. Ein Punkt vor der Klassenzeile zeigt, was noch aussteht.

## Worauf wir besonders gespannt sind

- Fühlt sich die Bedienung im Raster-Editor schlüssig an, oder suchst du Dinge an der falschen Stelle?
- Stimmt das, was du im Backend siehst, mit dem Frontend überein?
- Funktioniert das Verschieben mit unterschiedlich großen Kacheln zuverlässig?
- Wie verhält sich die Erweiterung mit deinem Theme?
- Kopieren, Verschieben und die Zwischenablage mit Grids — dort erwarten wir am ehesten Überraschungen.

## Was noch nicht fertig ist

- Die Formularfelder haben weiterhin die alte Eingabemaske.
- Für die Bedienung per Tastatur gibt es noch keine Prüfung.
- Bestehende Reihe-Start/Ende-Elemente werden **nicht** umgewandelt. Sie funktionieren unverändert weiter; wer das neue Raster nutzen will, legt die Bereiche neu an.

## Rückmeldung

Am hilfreichsten sind: Contao-Version, Theme, was du getan hast, was du erwartet hast und was stattdessen passiert ist — gern mit Screenshot. Schick es einfach als Antwort auf diese E-Mail.
