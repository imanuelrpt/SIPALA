/**
 * SIPALA — peta sebaran aduan (Leaflet + MarkerCluster).
 *
 * Kontrak HTML:
 *   #peta-sebaran  -> div peta dengan data-points (JSON) dan data-lat/data-lng/data-zoom
 *
 * Titik data dibaca dari atribut data (bukan skrip inline) agar CSP `script-src 'self'`
 * tetap dapat dipertahankan.
 */
(function () {
    'use strict';

    var STATUS_COLORS = {
        menunggu: '#6c757d',
        diverifikasi: '#0dcaf0',
        diproses: '#ffc107',
        selesai: '#198754',
        ditolak: '#dc3545'
    };

    function parsePoints(container) {
        var raw = container.getAttribute('data-points');

        if (!raw) {
            return [];
        }

        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function colorFor(status) {
        return STATUS_COLORS[status] || '#0d3b66';
    }

    function buildPopup(point) {
        var wrapper = document.createElement('div');
        wrapper.style.minWidth = '13rem';

        var title = document.createElement('p');
        title.className = 'map-popup-title mb-1';
        title.textContent = point.title;
        wrapper.appendChild(title);

        var meta = document.createElement('p');
        meta.className = 'mb-1 small text-body-secondary';
        meta.textContent = point.ticket_code + ' · ' + (point.category_name || '-');
        wrapper.appendChild(meta);

        var agency = document.createElement('p');
        agency.className = 'mb-2 small';
        agency.textContent = point.agency_name || '-';
        wrapper.appendChild(agency);

        var badge = document.createElement('span');
        badge.className = 'badge rounded-pill mb-2';
        badge.style.backgroundColor = colorFor(point.status);
        badge.textContent = point.status_label;
        wrapper.appendChild(badge);

        if (point.url) {
            var link = document.createElement('a');
            link.className = 'btn btn-sm btn-outline-primary w-100 mt-2';
            link.href = point.url;
            link.textContent = 'Lihat detail';
            wrapper.appendChild(link);
        }

        return wrapper;
    }

    function init() {
        var container = document.getElementById('peta-sebaran');

        if (!container || typeof window.L === 'undefined') {
            return;
        }

        var points = parsePoints(container);
        var lat = parseFloat(container.getAttribute('data-lat')) || -6.9175;
        var lng = parseFloat(container.getAttribute('data-lng')) || 107.6191;
        var zoom = parseInt(container.getAttribute('data-zoom') || '12', 10);

        var map = window.L.map(container, { scrollWheelZoom: true }).setView([lat, lng], zoom);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; kontributor OpenStreetMap'
        }).addTo(map);

        var hasCluster = typeof window.L.markerClusterGroup === 'function';
        var layer = hasCluster ? window.L.markerClusterGroup({ showCoverageOnHover: false }) : window.L.layerGroup();

        var bounds = [];

        points.forEach(function (point) {
            var pointLat = parseFloat(point.latitude);
            var pointLng = parseFloat(point.longitude);

            if (!isFinite(pointLat) || !isFinite(pointLng)) {
                return;
            }

            var marker = window.L.circleMarker([pointLat, pointLng], {
                radius: 8,
                color: '#ffffff',
                weight: 2,
                fillColor: colorFor(point.status),
                fillOpacity: 1
            });

            marker.bindPopup(buildPopup(point));
            layer.addLayer(marker);
            bounds.push([pointLat, pointLng]);
        });

        map.addLayer(layer);

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
        }

        // Pengendali kotak centang per status (opsional).
        document.querySelectorAll('[data-map-filter]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var active = {};

                document.querySelectorAll('[data-map-filter]').forEach(function (item) {
                    active[item.value] = item.checked;
                });

                var visible = window.L.layerGroup();

                points.forEach(function (point) {
                    if (active[point.status] === false) {
                        return;
                    }

                    var marker = window.L.circleMarker([parseFloat(point.latitude), parseFloat(point.longitude)], {
                        radius: 8,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: colorFor(point.status),
                        fillOpacity: 1
                    });

                    marker.bindPopup(buildPopup(point));
                    visible.addLayer(marker);
                });

                map.removeLayer(layer);
                layer = visible;
                map.addLayer(layer);
            });
        });

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
