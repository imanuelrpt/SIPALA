/**
 * SIPALA — pemilih titik lokasi aduan di atas Leaflet.
 *
 * Kontrak HTML (lihat partials/map_picker.php):
 *   #peta-pilih              -> div peta (data-lat, data-lng, data-zoom)
 *   #latitude, #longitude    -> input tersembunyi yang diisi otomatis
 *   #cari-alamat             -> input pencarian alamat
 *   #hasil-alamat            -> daftar hasil pencarian
 *   #gunakan-lokasi          -> tombol geolokasi peramban
 */
(function () {
    'use strict';

    var NOMINATIM = 'https://nominatim.openstreetmap.org';

    function init() {
        var container = document.getElementById('peta-pilih');

        if (!container || typeof window.L === 'undefined') {
            return;
        }

        var latInput = document.getElementById('latitude');
        var lngInput = document.getElementById('longitude');

        if (!latInput || !lngInput) {
            return;
        }

        var defaultLat = parseFloat(container.getAttribute('data-lat')) || -6.9175;
        var defaultLng = parseFloat(container.getAttribute('data-lng')) || 107.6191;
        var zoom = parseInt(container.getAttribute('data-zoom') || '13', 10);

        var startLat = parseFloat(latInput.value) || defaultLat;
        var startLng = parseFloat(lngInput.value) || defaultLng;

        var map = window.L.map(container, { scrollWheelZoom: false }).setView([startLat, startLng], zoom);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; kontributor OpenStreetMap'
        }).addTo(map);

        var marker = null;

        function setMarker(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = window.L.marker([lat, lng], { draggable: true }).addTo(map);

                marker.on('dragend', function () {
                    var position = marker.getLatLng();
                    setMarker(position.lat, position.lng);
                    reverseGeocode(position.lat, position.lng);
                });
            }
        }

        if (latInput.value && lngInput.value) {
            setMarker(startLat, startLng);
        }

        map.on('click', function (event) {
            setMarker(event.latlng.lat, event.latlng.lng);
            reverseGeocode(event.latlng.lat, event.latlng.lng);
        });

        /* -----------------------------------------------------------------
           Auto-isi kolom alamat
           ----------------------------------------------------------------- */
        function fillAddress(fields) {
            Object.keys(fields).forEach(function (id) {
                var input = document.getElementById(id);

                if (input && fields[id] && (!input.value || input.getAttribute('data-autofill') === 'always')) {
                    input.value = fields[id];
                }
            });
        }

        function reverseGeocode(lat, lng) {
            var url = NOMINATIM + '/reverse?format=jsonv2&accept-language=id&lat=' +
                encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);

            window.Sipala.request(url).then(function (data) {
                var address = data && data.address ? data.address : {};

                fillAddress({
                    address_detail: data && data.display_name ? data.display_name : '',
                    village: address.village || address.suburb || address.neighbourhood || '',
                    district: address.city_district || address.county || '',
                    city: address.city || address.town || address.municipality || address.county || ''
                });
            }).catch(function () {
                /* Diabaikan: pengguna tetap dapat mengisi alamat manual. */
            });
        }

        /* -----------------------------------------------------------------
           Pencarian alamat (forward geocoding)
           ----------------------------------------------------------------- */
        var searchInput = document.getElementById('cari-alamat');
        var results = document.getElementById('hasil-alamat');

        function runSearch() {
            if (!searchInput || !results) {
                return;
            }

            var query = searchInput.value.trim();

            if (query.length < 3) {
                results.innerHTML = '<li class="list-group-item small text-body-secondary">Masukkan minimal 3 karakter.</li>';
                return;
            }

            results.innerHTML = '<li class="list-group-item small text-body-secondary">Mencari…</li>';

            var url = NOMINATIM + '/search?format=jsonv2&accept-language=id&countrycodes=id&limit=5&q=' +
                encodeURIComponent(query);

            window.Sipala.request(url).then(function (data) {
                results.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    results.innerHTML = '<li class="list-group-item small text-body-secondary">Alamat tidak ditemukan.</li>';
                    return;
                }

                data.forEach(function (item) {
                    var li = document.createElement('li');
                    var button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action small';
                    button.textContent = item.display_name;
                    button.addEventListener('click', function () {
                        var lat = parseFloat(item.lat);
                        var lng = parseFloat(item.lon);

                        map.setView([lat, lng], 17);
                        setMarker(lat, lng);
                        fillAddress({
                            address_detail: item.display_name,
                            village: item.address ? (item.address.village || item.address.suburb || '') : '',
                            district: item.address ? (item.address.city_district || '') : '',
                            city: item.address ? (item.address.city || item.address.town || item.address.county || '') : ''
                        });
                        results.innerHTML = '';
                    });

                    li.appendChild(button);
                    results.appendChild(li);
                });
            }).catch(function () {
                results.innerHTML = '<li class="list-group-item small text-danger">Gagal menghubungi layanan peta.</li>';
            });
        }

        if (searchInput) {
            var searchButton = document.getElementById('cari-alamat-btn');

            if (searchButton) {
                searchButton.addEventListener('click', runSearch);
            }

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    runSearch();
                }
            });
        }

        /* -----------------------------------------------------------------
           Geolokasi peramban
           ----------------------------------------------------------------- */
        var locateButton = document.getElementById('gunakan-lokasi');

        if (locateButton) {
            locateButton.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    window.alert('Peramban Anda tidak mendukung geolokasi.');
                    return;
                }

                locateButton.disabled = true;

                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;

                    map.setView([lat, lng], 17);
                    setMarker(lat, lng);
                    reverseGeocode(lat, lng);
                    locateButton.disabled = false;
                }, function () {
                    window.alert('Lokasi tidak dapat diambil. Izinkan akses lokasi pada peramban Anda.');
                    locateButton.disabled = false;
                }, { enableHighAccuracy: true, timeout: 10000 });
            });
        }

        // Memastikan peta menyesuaikan ukuran setelah tab/modal tampil.
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
