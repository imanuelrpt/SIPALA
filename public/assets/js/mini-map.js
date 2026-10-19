/**
 * SIPALA — peta kecil penunjuk satu lokasi aduan.
 *
 * Kontrak HTML: #peta-mini dengan data-lat, data-lng, data-zoom.
 */
(function () {
    'use strict';

    function init() {
        var container = document.getElementById('peta-mini');

        if (!container || typeof window.L === 'undefined') {
            return;
        }

        var lat = parseFloat(container.getAttribute('data-lat'));
        var lng = parseFloat(container.getAttribute('data-lng'));
        var zoom = parseInt(container.getAttribute('data-zoom') || '16', 10);

        if (!isFinite(lat) || !isFinite(lng)) {
            return;
        }

        var map = window.L.map(container, { scrollWheelZoom: false }).setView([lat, lng], zoom);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; kontributor OpenStreetMap'
        }).addTo(map);

        window.L.marker([lat, lng]).addTo(map);

        window.setTimeout(function () {
            map.invalidateSize();
        }, 250);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
