# Grid-Erweiterung für Contao

Das `contao-grid-bundle` ist der offizielle Nachfolger der Erweiterung [euf_grid](https://github.com/ErdmannFreunde/euf_grid). 

Mit dem Bundle kannst du Content-Elemente in Contao in einem mehrspaltigen Grid (standardmäßig 12 Spalten) ausrichten. Wir empfehlen dir, die Erweiterung in Verbindung mit unserem Contao Themes zu verwenden. Mehr Infos: https://erdmann-freunde.de/produkte/contao-themes/

## Was die Erweiterung macht:

Nach der Installation werden den Content-Elementen 2 neue Felder für die Eingabe von Grid-Klassen und weiteren Optionen hinzugefügt. Außerdem stehen weitere Content-Elemente zur Verschachtelung in Reihen und Spalten zur Verfügung.

## Konfiguration

Das ist die Standardkonfiguration. Du kannst diese (auch nur einzelne Zweige) über die `config.yml` überschreiben. Das ist nützlich, wenn du andere Breakpoints verwendest. Oder wenn eine einfache Spaltenbreite für dein Layout irrelevant ist, kannst du diese ebenfalls deaktivieren.

```yml
erdmannfreunde_contao_grid:
  row_class: row
  columns:
    - 1
    - 2
    - 3
    - 4
    - 5
    - 6
    - 7
    - 8
    - 9
    - 10
    - 11
    - 12
  columns_no_column: true
  viewports:
    - xs
    - sm
    - md
    - lg
    - xl
  viewports_no_viewport: true
  column_prefixes:
    - col
    - row-span
  options_prefixes:
    - col-start
    - row-start
  pulls:
    - pull-left
    - pull-right
  positioning:
    - align
    - justify
  directions:
    - start
    - center
    - end
    - stretch
  options_columns:
    - 1
    - 2
    - 3
    - 4
    - 5
    - 6
    - 7
    - 8
    - 9
    - 10
    - 11
    - 12
```

## Update-Hinweise
- `contao-grid-bundle` ist ein Rewrite von euf_grid als Contao-Bundle. Reihen- und Spalten-Einstellungen basieren auf euf_grid Version 3 und sind somit kompatibel. Solltest du die Grid-Erweiterung über die `dcaconfig.php` angepasst haben, solltest du diese Anpassungen wie oben erwähnt über die `config.yml` vornehmen.
