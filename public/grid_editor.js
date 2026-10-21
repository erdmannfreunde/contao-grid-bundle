/*
 * Raster-Editor im Grid-Element.
 *
 * Zeichnet die Zellen des Grids, schaltet zwischen Viewports und Mustern um und
 * hält zwei versteckte Felder aktuell: das Muster je Viewport (eigener Wert des
 * Feldes) und die Geometrie je Kind (euf_grid_cells).
 *
 * Im freien Modus trägt jede Zelle zwei Regler, dort wo die Achse hinzeigt:
 * Breite unten, Höhe rechts an der Kante. Die Tasten folgen der
 * Wachstumsrichtung und sitzen an der linken Kante — so bleibt die Taste unter
 * dem Zeiger, auch wenn die Zelle breiter wird. Die Startwerte liegen im
 * Dreipunkt-Feld, sie sind die Ausnahme.
 */
(function () {
    'use strict';

    var ACHSEN = ['col', 'rowSpan', 'colStart', 'rowStart'];
    var MAX_HOEHE = 6;
    var MIN_BEDIENBAR = 3;

    function bauen(wurzel) {
        if (wurzel.dataset.eufGridReady === '1') {
            return;
        }

        wurzel.dataset.eufGridReady = '1';

        var config = JSON.parse(wurzel.dataset.eufGridConfig);

        // Der Editor öffnet dort, wo dieses Grid zuerst etwas zu sagen hat:
        // bei der kleinsten Ansicht mit eigenem Muster oder eigenen Werten.
        // Ist nichts gesetzt, bleibt es bei der Basis.
        function ersteAnsicht() {
            for (var i = 0; i < config.viewports.length; i++) {
                var viewport = config.viewports[i].key;

                if ((config.pattern || {})[viewport]) {
                    return viewport;
                }

                var belegt = config.cells.some(function (zelle) {
                    return ACHSEN.some(function (achse) {
                        return (zelle[achse] || {})[viewport] !== undefined;
                    });
                });

                if (belegt) {
                    return viewport;
                }
            }

            return config.viewports.length ? config.viewports[0].key : 'md';
        }

        var zustand = {
            viewport: ersteAnsicht(),
            pattern: Object.assign({}, config.pattern || {}),
            cells: {}
        };

        config.cells.forEach(function (zelle) {
            zustand.cells[zelle.id] = {
                col: Object.assign({}, zelle.col || {}),
                rowSpan: Object.assign({}, zelle.rowSpan || {}),
                colStart: Object.assign({}, zelle.colStart || {}),
                rowStart: Object.assign({}, zelle.rowStart || {}),
                alignment: Object.assign({}, zelle.alignment || {})
            };
        });

        // Die Texte kommen aus den Sprachdateien; %s wird der Reihe nach ersetzt
        function t(schluessel) {
            var text = (config.labels || {})[schluessel] || schluessel;
            var werte = Array.prototype.slice.call(arguments, 1);

            return werte.reduce(function (satz, wert) {
                return satz.replace('%s', String(wert));
            }, text);
        }

        var flaeche = document.createElement('div');
        flaeche.className = 'euf-grid-editor__body';
        wurzel.insertBefore(flaeche, wurzel.firstChild);

        // Das Menü liegt außerhalb der Fläche, damit es ein Neuzeichnen übersteht
        var menue = null;

        function effektiv(werte, standard, viewport) {
            var ziel = viewport || zustand.viewport;
            var wert = standard;

            for (var i = 0; i < config.viewports.length; i++) {
                var v = config.viewports[i].key;

                if (werte[v] !== undefined && werte[v] !== null && werte[v] !== '') {
                    wert = parseInt(werte[v], 10);
                }

                if (v === ziel) {
                    break;
                }
            }

            return wert;
        }

        function musterFuer(viewport) {
            var wert = '';

            for (var i = 0; i < config.viewports.length; i++) {
                var v = config.viewports[i].key;

                if (zustand.pattern[v]) {
                    wert = zustand.pattern[v];
                }

                if (v === viewport) {
                    break;
                }
            }

            return wert;
        }

        // Was in dieser Ansicht höchstens nebeneinander stehen darf
        function ansicht(viewport) {
            var treffer = config.viewports.filter(function (eintrag) {
                return eintrag.key === (viewport || zustand.viewport);
            });

            return treffer.length ? treffer[0] : {};
        }

        function standard(achse) {
            return 'col' === achse ? config.columns : ('rowSpan' === achse ? 1 : 0);
        }

        function grenzen(achse) {
            if ('rowSpan' === achse) {
                return [1, MAX_HOEHE];
            }

            if ('col' === achse) {
                return [ansicht().minWidth || 1, config.columns];
            }

            // Startspalte und Startzeile kennen "auto": ohne Wert gilt, was eine
            // Stufe darunter gilt
            return [0, 'colStart' === achse ? config.columns : config.cells.length];
        }

        function wertFuer(zelle, achse) {
            var muster = musterFuer(zustand.viewport);

            // Ein Muster besitzt den Viewport allein, auch Höhe und Start
            if (muster && muster !== 'frei') {
                return achse === 'col' ? Math.floor(config.columns / parseInt(muster, 10)) : standard(achse);
            }

            return effektiv(zustand.cells[zelle.id][achse], standard(achse));
        }

        function setzen(zelle, achse, wert) {
            var paar = grenzen(achse);

            wert = Math.max(paar[0], Math.min(paar[1], wert));

            // Für "auto" gibt es keine Klasse, also fällt der Wert weg
            if (0 === wert && 0 === paar[0]) {
                delete zustand.cells[zelle.id][achse][zustand.viewport];
            } else {
                zustand.cells[zelle.id][achse][zustand.viewport] = wert;
            }

            zeichnen();
        }

        function ausrichten(zelle, praefix, richtung) {
            zustand.cells[zelle.id].alignment[praefix] = richtung;
            zeichnen();
        }

        function felderAktualisieren() {
            // Muster: je Viewport ein verstecktes Feld. Auch die serverseitig
            // gerenderten Felder weichen, sonst stünde jeder Wert doppelt im POST.
            wurzel.querySelectorAll('input[type="hidden"]').forEach(function (el) {
                if (el.name !== 'euf_grid_cells') {
                    el.remove();
                }
            });

            Object.keys(zustand.pattern).forEach(function (viewport) {
                if (!zustand.pattern[viewport]) {
                    return;
                }

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = config.name + '[' + viewport + ']';
                input.value = zustand.pattern[viewport];
                input.setAttribute('data-euf-pattern', '');
                wurzel.appendChild(input);
            });

            var zellenFeld = wurzel.querySelector('input[name="euf_grid_cells"]');

            if (zellenFeld) {
                zellenFeld.value = JSON.stringify(geometrie());
            }
        }

        // Kind-ID => { viewports: Viewport => Achse => Wert, Präfix => Richtung }
        function geometrie() {
            var daten = {};

            config.cells.forEach(function (zelle) {
                var je = {};

                ACHSEN.forEach(function (achse) {
                    var werte = zustand.cells[zelle.id][achse];

                    Object.keys(werte).forEach(function (viewport) {
                        je[viewport] = je[viewport] || {};
                        je[viewport][achse] = werte[viewport];
                    });
                });

                // Eine Zelle ganz ohne Breitenklasse belegt im Frontend eine
                // Spur, der Editor zeigt sie aber voll breit. Sie bekommt genau
                // dort eine Klasse, wo sonst keine Breite entstünde — ein Muster
                // weiter unten zählt als Breite, es vererbt sich mit.
                var hatBreite = false;

                for (var i = 0; i < config.viewports.length; i++) {
                    var viewport = config.viewports[i].key;

                    if (zustand.pattern[viewport] && 'frei' !== zustand.pattern[viewport]) {
                        hatBreite = true;
                        continue;
                    }

                    if (zustand.cells[zelle.id].col[viewport] !== undefined) {
                        hatBreite = true;
                        continue;
                    }

                    if ('frei' === zustand.pattern[viewport] && !hatBreite) {
                        je[viewport] = je[viewport] || {};
                        je[viewport].col = config.columns;
                        hatBreite = true;
                    }
                }

                daten[zelle.id] = {viewports: je};

                config.positioning.forEach(function (position) {
                    daten[zelle.id][position.key] = zustand.cells[zelle.id].alignment[position.key] || '';
                });
            });

            return daten;
        }

        function zeichnen() {
            flaeche.textContent = '';

            // Viewport-Umschalter
            var tabs = document.createElement('div');
            tabs.className = 'euf-grid-editor__tabs';
            tabs.appendChild(beschriftung(t('view')));

            config.viewports.forEach(function (viewport) {
                var knopf = document.createElement('button');
                knopf.type = 'button';
                knopf.className = 'euf-grid-editor__tab';
                knopf.textContent = viewport.label;
                knopf.setAttribute('aria-pressed', viewport.key === zustand.viewport ? 'true' : 'false');
                knopf.addEventListener('click', function () {
                    zustand.viewport = viewport.key;
                    zeichnen();
                });
                tabs.appendChild(knopf);
            });

            flaeche.appendChild(tabs);

            // Musterauswahl
            var muster = document.createElement('div');
            muster.className = 'euf-grid-editor__tabs';
            muster.appendChild(beschriftung(t('pattern')));
            var aktiv = musterFuer(zustand.viewport);

            var hoechstens = ansicht().maxPerRow;

            config.patterns.forEach(function (wert) {
                // Ein Muster, das mehr Elemente nebeneinander stellt, als diese
                // Ansicht verträgt, steht nicht zur Wahl — es sei denn, es ist
                // bereits gesetzt: Was gespeichert ist, muss sichtbar bleiben
                if (hoechstens && 'frei' !== wert && wert !== aktiv && parseInt(wert, 10) > hoechstens) {
                    return;
                }

                var knopf = document.createElement('button');
                knopf.type = 'button';
                knopf.className = 'euf-grid-editor__pattern';
                knopf.textContent = 'frei' === wert ? t('patternFree') : ('1' === wert ? t('patternStacked') : t('patternEqual', wert));
                knopf.setAttribute('aria-pressed', wert === aktiv ? 'true' : 'false');
                knopf.addEventListener('click', function () {
                    musterWechseln(wert);
                });
                muster.appendChild(knopf);
            });

            flaeche.appendChild(muster);

            // Vorschau
            var raster = document.createElement('div');
            raster.className = 'euf-grid-editor__grid';
            raster.style.setProperty('--euf-grid-columns', String(config.columns));
            raster.appendChild(spuren());

            var frei = aktiv === 'frei';

            config.cells.forEach(function (zelle) {
                raster.appendChild(karte(
                    zelle,
                    wertFuer(zelle, 'col'),
                    wertFuer(zelle, 'rowSpan'),
                    wertFuer(zelle, 'colStart'),
                    wertFuer(zelle, 'rowStart'),
                    frei
                ));
            });

            flaeche.appendChild(raster);

            var zeile = document.createElement('p');
            zeile.className = 'euf-grid-editor__summary';
            zeile.setAttribute('role', 'status');
            zeile.textContent = zusammenfassung(raster, aktiv);
            flaeche.appendChild(zeile);

            felderAktualisieren();
            menueFuellen();
        }

        function musterWechseln(wert) {
            if ('frei' !== wert && !ersetzenBestaetigt()) {
                return;
            }

            if (wert === 'frei') {
                // Die Werte des Musters als Ausgangspunkt übernehmen, damit nichts springt
                config.cells.forEach(function (zelle) {
                    ACHSEN.forEach(function (achse) {
                        var wert = wertFuer(zelle, achse);

                        // Startwerte stehen auf "automatisch", solange sie 0 sind
                        if (wert > 0) {
                            zustand.cells[zelle.id][achse][zustand.viewport] = wert;
                        }
                    });
                });
            } else {
                // Ein Muster ersetzt alle eigenen Werte dieses Viewports
                config.cells.forEach(function (zelle) {
                    ACHSEN.forEach(function (achse) {
                        delete zustand.cells[zelle.id][achse][zustand.viewport];
                    });
                });
            }

            zustand.pattern[zustand.viewport] = wert;
            zeichnen();
        }

        /**
         * Ein Muster besitzt seinen Viewport allein und räumt dort Breite, Höhe
         * und Startwerte weg. Wie viele Zellen das trifft, gehört vorher gesagt.
         */
        function ersetzenBestaetigt() {
            var betroffen = config.cells.filter(function (zelle) {
                return ACHSEN.some(function (achse) {
                    return zustand.cells[zelle.id][achse][zustand.viewport] !== undefined;
                });
            }).length;

            if (!betroffen) {
                return true;
            }

            return window.confirm(1 === betroffen ? t('replaceOne') : t('replaceMany', betroffen));
        }

        // Rasterhintergrund: zwölf Spuren hinter den Zellen, damit die Breite ablesbar ist
        function spuren() {
            var hintergrund = document.createElement('div');
            hintergrund.className = 'euf-grid-editor__tracks';
            hintergrund.setAttribute('aria-hidden', 'true');

            for (var i = 0; i < config.columns; i++) {
                hintergrund.appendChild(document.createElement('span'));
            }

            return hintergrund;
        }

        function karte(zelle, breite, hoehe, start, zeile, frei) {
            var kasten = document.createElement('div');
            kasten.className = 'euf-grid-editor__cell' + (zelle.invisible ? ' euf-grid-editor__cell--hidden' : '');
            kasten.style.gridColumn = (start > 0 ? start + ' / span ' + breite : 'span ' + breite);
            kasten.style.gridRow = (zeile > 0 ? zeile + ' / span ' + hoehe : 'span ' + hoehe);
            kasten.dataset.eufCol = String(breite);

            // Die Startspalte steht nur im Dreipunkt-Feld: eine Ausnahme, kein
            // Regler für jeden Tag
            var bedienbar = frei && breite >= MIN_BEDIENBAR;

            var kopf = document.createElement('div');
            kopf.className = 'euf-grid-editor__cell-head';

            var typ = document.createElement('span');
            typ.textContent = zelle.type;
            kopf.appendChild(typ);

            // Der Weg zu allen Werten — in schmalen Zellen der einzige
            kopf.appendChild(knopf('⋮', t('openMenu', zelle.type), function () {
                menueOeffnen(zelle);
            }, 'euf-grid-editor__menu'));

            kasten.appendChild(kopf);

            if (breite >= MIN_BEDIENBAR && zelle.label) {
                var text = document.createElement('div');
                text.className = 'euf-grid-editor__cell-label';
                text.textContent = zelle.label;
                kasten.appendChild(text);
            }

            if (bedienbar) {
                kasten.appendChild(regler(zelle, 'col', breite, '↔', t('width'), 'width'));
                kasten.appendChild(regler(zelle, 'rowSpan', hoehe, '↕', t('height'), 'height'));
            } else {
                var zahl = document.createElement('div');
                zahl.className = 'euf-grid-editor__cell-value';
                zahl.textContent = breite + '/' + config.columns + (hoehe > 1 ? ' · ' + hoehe + '×' : '');
                kasten.appendChild(zahl);
            }

            // Was gespeichert wird, steht in der Zelle — kein Rätselraten.
            // Weicht es vom gespeicherten Stand ab, sagt ein Punkt das dazu.
            var liste = klassenliste(zelle);
            var geaendert = liste.join(' ') !== klassenliste(zelle, zelle, config.pattern).join(' ');

            if (liste.length && breite >= MIN_BEDIENBAR) {
                var klassen = document.createElement('div');
                klassen.className = 'euf-grid-editor__classes' + (geaendert ? ' euf-grid-editor__classes--offen' : '');
                klassen.textContent = (geaendert ? '• ' : '') + liste.join(' ');
                klassen.title = liste.join(' ') + (geaendert ? ' — noch nicht gespeichert' : '');
                kasten.appendChild(klassen);
            }

            return kasten;
        }

        /**
         * Ein Regler je Achse. Senkrecht steht Minus oben und Plus unten, sonst
         * Minus links und Plus rechts: die Taste zeigt dorthin, wohin sich die
         * Kante bewegt.
         */
        function regler(zelle, achse, wert, zeichen, name, variante) {
            var box = document.createElement('div');
            box.className = 'euf-grid-editor__stepper euf-grid-editor__stepper--' + variante;

            var marke = document.createElement('span');
            marke.className = 'euf-grid-editor__axis';
            marke.textContent = zeichen;
            marke.title = name;
            box.appendChild(marke);

            box.appendChild(knopf('−', t('decrease', name, zelle.type), function () {
                setzen(zelle, achse, wert - 1);
            }));

            var zahl = document.createElement('span');
            zahl.className = 'euf-grid-editor__value';
            zahl.textContent = 'colStart' === achse && wert < 1 ? t('auto') : String(wert);
            box.appendChild(zahl);

            box.appendChild(knopf('+', t('increase', name, zelle.type), function () {
                setzen(zelle, achse, wert + 1);
            }));

            return box;
        }

        /**
         * Die Klassen, die beim Speichern entstehen — nicht die errechneten
         * Werte, sondern das, was in den beiden Feldern landet.
         *
         * @return array<string>
         */
        function klassenliste(zelle, quelle, musterQuelle) {
            var werte = quelle || zustand.cells[zelle.id];
            var muster_ = musterQuelle || zustand.pattern;
            var liste = [];

            config.viewports.forEach(function (viewport) {
                var vp = viewport.key;
                var muster = muster_[vp];

                if (muster && 'frei' !== muster) {
                    liste.push('col-' + vp + '-' + Math.floor(config.columns / parseInt(muster, 10)));

                    return;
                }

                if ((werte.col || {})[vp] !== undefined) {
                    liste.push('col-' + vp + '-' + werte.col[vp]);
                }

                if ((werte.rowSpan || {})[vp] > 1) {
                    liste.push('row-' + vp + '-' + werte.rowSpan[vp]);
                }

                if ((werte.colStart || {})[vp] > 0) {
                    liste.push('col-start-' + vp + '-' + werte.colStart[vp]);
                }

                if ((werte.rowStart || {})[vp] > 0) {
                    liste.push('row-start-' + vp + '-' + werte.rowStart[vp]);
                }
            });

            config.positioning.forEach(function (position) {
                if ((werte.alignment || {})[position.key]) {
                    liste.push(position.key + '-' + werte.alignment[position.key]);
                }
            });

            return liste;
        }

        function knopf(zeichen, beschriftungText, aktion, klasse) {
            var el = document.createElement('button');
            el.type = 'button';
            el.className = klasse || 'euf-grid-editor__btn';
            el.textContent = zeichen;
            el.setAttribute('aria-label', beschriftungText);
            el.addEventListener('click', aktion);

            return el;
        }

        function beschriftung(text) {
            var el = document.createElement('span');
            el.className = 'euf-grid-editor__legend';
            el.textContent = text;

            return el;
        }

        /**
         * Die Belegung wird an der Vorschau gemessen, nicht gerechnet: nur so
         * stimmt sie mit dem überein, was zu sehen ist — auch bei Startspalten
         * und mehrzeiligen Zellen, die das Raster auf eigene Weise umbrechen.
         *
         * @return array<int> Belegte Spalten je Zeile
         */
        function belegung(raster) {
            var kasten = Array.prototype.map.call(raster.querySelectorAll('.euf-grid-editor__cell'), function (zelle) {
                return {
                    oben: zelle.offsetTop,
                    unten: zelle.offsetTop + zelle.offsetHeight,
                    breite: parseInt(zelle.dataset.eufCol, 10)
                };
            });

            if (!kasten.length || !raster.offsetHeight) {
                return [];
            }

            var kanten = [];

            kasten.forEach(function (zelle) {
                if (kanten.indexOf(zelle.oben) < 0) {
                    kanten.push(zelle.oben);
                }
            });

            kanten.sort(function (a, b) {
                return a - b;
            });

            return kanten.map(function (kante) {
                var summe = 0;

                kasten.forEach(function (zelle) {
                    // Eine hohe Zelle belegt jede Zeile, durch die sie reicht
                    if (zelle.oben <= kante && zelle.unten > kante + 1) {
                        summe += zelle.breite;
                    }
                });

                return summe;
            });
        }

        /**
         * Alle Werte einer Zelle in bequemer Größe: die vier Zahlen der aktuellen
         * Ansicht und die Ausrichtung, die ohne Viewport-Ebene für die Zelle gilt.
         */
        function menueOeffnen(zelle) {
            if (!menue) {
                menue = document.createElement('dialog');
                menue.className = 'euf-grid-dialog';
                menue.addEventListener('close', function () {
                    menue.zelle = null;
                });
                wurzel.appendChild(menue);
            }

            menue.zelle = zelle;
            menueFuellen();

            if (!menue.open) {
                menue.showModal();
            }
        }

        function menueFuellen() {
            if (!menue || !menue.zelle) {
                return;
            }

            var zelle = menue.zelle;
            var frei = 'frei' === musterFuer(zustand.viewport);
            menue.textContent = '';

            var titel = document.createElement('h3');
            titel.textContent = zelle.type + (zelle.label ? ' · ' + zelle.label : '');
            menue.appendChild(titel);

            var ansicht = document.createElement('p');
            ansicht.className = 'euf-grid-dialog__hint';
            ansicht.textContent = frei ? t('menuFree', viewportLabel()) : t('menuPattern', viewportLabel());
            menue.appendChild(ansicht);

            if (frei) {
                menue.appendChild(zahlenfeld(zelle, 'col', t('width')));
                menue.appendChild(zahlenfeld(zelle, 'rowSpan', t('height')));
                menue.appendChild(zahlenfeld(zelle, 'colStart', t('colStart')));
                menue.appendChild(zahlenfeld(zelle, 'rowStart', t('rowStart')));
            }

            config.positioning.forEach(function (position) {
                menue.appendChild(richtungsgruppe(zelle, position));
            });

            var fuss = document.createElement('div');
            fuss.className = 'euf-grid-dialog__footer';
            fuss.appendChild(knopf(t('close'), t('closeMenu'), function () {
                menue.close();
            }, 'tl_submit'));
            menue.appendChild(fuss);
        }

        function viewportLabel() {
            var treffer = config.viewports.filter(function (viewport) {
                return viewport.key === zustand.viewport;
            });

            return treffer.length ? treffer[0].label : zustand.viewport;
        }

        function zahlenfeld(zelle, achse, name) {
            var paar = grenzen(achse);
            var zeile = document.createElement('label');
            zeile.className = 'euf-grid-dialog__row';

            var text = document.createElement('span');
            text.textContent = name;
            zeile.appendChild(text);

            var feld = document.createElement('input');
            feld.type = 'number';
            feld.className = 'tl_text';
            feld.min = String(paar[0]);
            feld.max = String(paar[1]);
            feld.value = String(wertFuer(zelle, achse));
            feld.addEventListener('change', function () {
                setzen(zelle, achse, parseInt(feld.value, 10) || 0);
            });
            zeile.appendChild(feld);

            if (0 === paar[0]) {
                var hinweis = document.createElement('small');
                hinweis.textContent = t('autoHint');
                zeile.appendChild(hinweis);
            }

            return zeile;
        }

        function richtungsgruppe(zelle, position) {
            var zeile = document.createElement('div');
            zeile.className = 'euf-grid-dialog__row';

            var text = document.createElement('span');
            text.textContent = position.label;
            zeile.appendChild(text);

            var gruppe = document.createElement('div');
            gruppe.className = 'euf-grid-editor__tabs euf-grid-dialog__group';
            var aktuell = zustand.cells[zelle.id].alignment[position.key] || '';

            [{key: '', label: t('default')}].concat(config.directions).forEach(function (richtung) {
                var el = document.createElement('button');
                el.type = 'button';
                el.className = 'euf-grid-editor__pattern';
                el.textContent = richtung.label;
                el.setAttribute('aria-pressed', richtung.key === aktuell ? 'true' : 'false');
                el.addEventListener('click', function () {
                    ausrichten(zelle, position.key, richtung.key);
                });
                gruppe.appendChild(el);
            });

            zeile.appendChild(gruppe);

            return zeile;
        }

        function zusammenfassung(raster, aktiv) {
            if (!aktiv) {
                return t('summaryInherited');
            }

            var zeilen = belegung(raster);

            if (!zeilen.length) {
                return '';
            }

            var anzahl = 1 === zeilen.length ? t('rowOne') : t('rowMany', zeilen.length);

            if ('frei' !== aktiv) {
                if ('1' === aktiv) {
                    return t('summaryStacked', anzahl);
                }

                return t('summaryEqual', aktiv, Math.floor(config.columns / parseInt(aktiv, 10)), config.columns, anzahl);
            }

            var rest = config.columns - zeilen[zeilen.length - 1];
            var gross = anzahl.charAt(0).toUpperCase() + anzahl.slice(1);

            if (rest < 1) {
                return t('summaryFull', gross);
            }

            return t(1 === zeilen.length ? 'summaryFree' : 'summaryFreeLast', gross, rest, config.columns);
        }

        zeichnen();
    }

    function start() {
        document.querySelectorAll('[data-euf-grid-editor]').forEach(bauen);
    }

    // turbo:load und turbo:render feuern nur bei Seitenwechseln. Das Formular
    // öffnet im Overlay, deshalb auch auf frame-load hören.
    document.addEventListener('DOMContentLoaded', start);
    document.addEventListener('turbo:load', start);
    document.addEventListener('turbo:render', start);
    document.addEventListener('turbo:frame-load', start);
    document.addEventListener('turbo:frame-render', function () {
        setTimeout(start, 0);
    });

    if (document.readyState !== 'loading') {
        start();
    }
})();
