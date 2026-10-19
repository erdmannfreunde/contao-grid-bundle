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
    - row
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

## Bekannte Einschränkungen

**Ein Grid in einem Grid setzt seine eigene Breite nicht selbst.** Beim Elementtyp *Grid* fehlen die Felder *Grid Columns* und *Grid Optionen* mit Absicht: Im Formular stünden sonst zwei Raster nebeneinander — der Raster-Editor für die Kinder und die Klassen für das Element selbst — und es wäre kaum zu erkennen, welches gerade gemeint ist. Die Breite eines verschachtelten Grids setzt du deshalb im Raster-Editor des übergeordneten Grids, genau wie bei jedem anderen Kind.

## Beschriftung der Ansichten

Im Raster-Editor heißen die Viewports „Basis", „ab Screen SM", „ab Screen MD" und so weiter. Welche Breite dahintersteckt, entscheidet dein Theme — die Erweiterung kennt die Breakpoints nicht und nennt deshalb nur das Kürzel.

Wenn du es genauer haben möchtest, legst du im Projekt eine `translations/grid.de.yaml` an und überschreibst die Schlüssel, die du brauchen kannst:

```yaml
viewport:
    md: 'ab 768 px'
    lg: 'Desktop'
```

Viewports ohne eigenen Eintrag behalten das Muster aus `viewport.default`.

## Update-Hinweise
- **Die Höhe heißt ab Version 3 `row-{viewport}-{n}`** statt `row-span-{viewport}-{n}`. Bestehende `row-span`-Klassen werden nicht mehr gelesen; da Version 3 ohnehin voraussetzt, dass die Bereiche neu angelegt werden, gibt es dafür keine Umwandlung.
- **Das Grid-Element bekommt keine Klasse `row` mehr.** Es trägt nur noch `content-grid` und die Klassen, die du ihm selbst gibst — das Raster definierst du in deinem Theme an der Klasse deiner Wahl.
- **Ab Version 3 liefert das Paket kein Stylesheet mehr.** Die Layout-Option „EuF-Grid CSS laden" und die Datei `euf_grid.css` sind entfallen — die Grid-Klassen gehören in dein Theme. Als Vorlage dient die [Datei aus Version 2](https://github.com/ErdmannFreunde/contao-grid-bundle/blob/2.x/public/euf_grid.css).
- `contao-grid-bundle` ist ein Rewrite von euf_grid als Contao-Bundle. Reihen- und Spalten-Einstellungen basieren auf euf_grid Version 3 und sind somit kompatibel. Solltest du die Grid-Erweiterung über die `dcaconfig.php` angepasst haben, solltest du diese Anpassungen wie oben erwähnt über die `config.yml` vornehmen.
