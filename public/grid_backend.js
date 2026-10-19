/*
 * Zeichnet die Inhaltsliste eines Grids als Raster.
 *
 * Jede Karte bringt ihre Spannweite als Datenattribut mit, hier wandert sie an
 * das Listenelement. Dazu bekommt der Stift im Kopf der Liste ein breiteres
 * Overlay: 900px reichen für zwölf Spalten nicht.
 */
(function () {
    'use strict';

    var BREITE = 1200;

    function apply() {
        // Nur die Datensatzliste, nicht die Operationsmenues im Kopfbereich
        var liste = document.querySelector('.tl_listing_container.parent_view > ul');

        if (!liste || liste.dataset.eufGrid === '1' || !liste.querySelector(':scope > li[data-id]')) {
            return;
        }

        liste.dataset.eufGrid = '1';
        liste.classList.add('euf-grid-list');

        Array.prototype.forEach.call(liste.children, function (item) {
            var span = item.querySelector('.euf-grid-span');

            if (!span) {
                return;
            }

            var start = parseInt(span.dataset.eufStart, 10);

            liste.style.setProperty('--euf-grid-columns', span.dataset.eufColumns);
            item.style.gridColumn = (start > 0 ? start + ' / span ' + span.dataset.eufCol : 'span ' + span.dataset.eufCol);
            item.style.gridRow = 'span ' + span.dataset.eufRow;
        });
    }

    // Contao öffnet das Elementformular mit fester Breite. Der eigene Aufruf
    // macht das Fenster breiter und zeichnet die Liste nach dem Schließen neu.
    function oeffnen(link) {
        var fenster = new SimpleModal({
            width: Math.min(window.innerWidth - 20, BREITE),
            hideFooter: true,
            draggable: false,
            overlayClick: false,
            onShow: function () {
                document.body.style.overflow = 'hidden';
            },
            onHide: function () {
                document.body.style.overflow = 'auto';
                window.location.reload();
            }
        });

        fenster.show({
            title: link.title || link.textContent,
            contents: '<iframe src="' + link.href + '&popup=1&nb=1" width="100%" height="' + (window.innerHeight - 137) + '" frameborder="0"></iframe>',
            model: 'modal'
        });
    }

    function verknuepfen() {
        if (typeof SimpleModal === 'undefined') {
            return;
        }

        document.querySelectorAll('.tl_header .tl_content_right a.edit[onclick*="openModalIframe"]').forEach(function (link) {
            if (link.dataset.eufGridBound === '1') {
                return;
            }

            link.dataset.eufGridBound = '1';
            link.removeAttribute('onclick');
            link.addEventListener('click', function (event) {
                event.preventDefault();
                oeffnen(link);
            });
        });
    }

    function start() {
        apply();
        verknuepfen();
    }

    // turbo:load und turbo:render feuern nur bei Seitenwechseln. Die Liste
    // kommt im Turbo-Frame, deshalb auch auf frame-load hören.
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
