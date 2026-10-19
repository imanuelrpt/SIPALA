/**
 * SIPALA — skrip umum.
 *
 * Tidak memakai pustaka eksternal agar Content-Security-Policy tetap ketat
 * (scriptSrc: 'self' tanpa 'unsafe-inline').
 */
(function () {
    'use strict';

    var metaToken = document.querySelector('meta[name="csrf-token"]');

    var Sipala = {
        csrfTokenName: 'csrf_token_name',
        csrfHeaderName: 'X-CSRF-TOKEN',
        csrfToken: metaToken ? metaToken.getAttribute('content') : '',
        baseUrl: document.documentElement.getAttribute('data-base-url') || '/',

        /**
         * Menyegarkan token CSRF dari sebuah respons (bila server memutar token).
         */
        syncCsrf: function (response) {
            var token = response.headers.get(this.csrfHeaderName);

            if (!token) {
                return;
            }

            this.csrfToken = token;

            if (metaToken) {
                metaToken.setAttribute('content', token);
            }
        },

        /**
         * Permintaan JSON dengan token CSRF otomatis.
         *
         * @param {string} url
         * @param {object} [options]
         * @returns {Promise<object>}
         */
        request: function (url, options) {
            var self = this;
            options = options || {};

            var headers = Object.assign(
                { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                options.headers || {}
            );

            if (self.csrfToken) {
                headers[self.csrfHeaderName] = self.csrfToken;
            }

            return fetch(url, Object.assign({ credentials: 'same-origin' }, options, { headers: headers }))
                .then(function (response) {
                    self.syncCsrf(response);
                    return response.json().catch(function () {
                        return { status: 'error', message: 'Respons server tidak dapat dibaca.' };
                    });
                });
        }
    };

    window.Sipala = Sipala;

    /* ---------------------------------------------------------------------
       Konfirmasi tindakan destruktif (form[data-confirm] / [data-confirm])
       --------------------------------------------------------------------- */
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        var message = form.getAttribute('data-confirm');

        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }

        // Mencegah klik ganda pada tombol simpan.
        var submitter = form.querySelector('button[type="submit"]:not([data-no-lock])');

        if (submitter) {
            window.setTimeout(function () {
                submitter.disabled = true;
                submitter.insertAdjacentHTML(
                    'afterbegin',
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>'
                );
            }, 0);
        }
    });

    /* ---------------------------------------------------------------------
       Tutup otomatis alert flash
       --------------------------------------------------------------------- */
    window.setTimeout(function () {
        document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        });
    }, 8000);

    /* ---------------------------------------------------------------------
       Pratinjau berkas unggahan + validasi awal di sisi klien
       --------------------------------------------------------------------- */
    document.querySelectorAll('input[type="file"][data-preview-target]').forEach(function (input) {
        var target = document.querySelector(input.getAttribute('data-preview-target'));
        var maxFiles = parseInt(input.getAttribute('data-max-files') || '0', 10);
        var maxBytes = parseInt(input.getAttribute('data-max-bytes') || '0', 10);

        if (!target) {
            return;
        }

        input.addEventListener('change', function () {
            target.innerHTML = '';

            var files = Array.prototype.slice.call(input.files || []);
            var problems = [];

            if (maxFiles > 0 && files.length > maxFiles) {
                problems.push('Maksimal ' + maxFiles + ' berkas.');
            }

            files.forEach(function (file) {
                if (maxBytes > 0 && file.size > maxBytes) {
                    problems.push(file.name + ' melebihi batas ukuran.');
                }

                if (file.type && file.type.indexOf('image/') !== 0) {
                    problems.push(file.name + ' bukan berkas gambar.');
                    return;
                }

                var card = document.createElement('div');
                card.className = 'text-center small';

                var image = document.createElement('img');
                image.className = 'attachment-thumb';
                image.alt = file.name;
                image.src = URL.createObjectURL(file);

                var caption = document.createElement('div');
                caption.className = 'text-truncate';
                caption.style.maxWidth = '7.5rem';
                caption.textContent = file.name;

                card.appendChild(image);
                card.appendChild(caption);
                target.appendChild(card);
            });

            var feedback = document.querySelector('[data-upload-feedback="' + input.id + '"]');

            if (feedback) {
                feedback.textContent = problems.join(' ');
                feedback.classList.toggle('d-none', problems.length === 0);
            }
        });
    });

    /* ---------------------------------------------------------------------
       Pemilih status: catatan alasan wajib saat status "ditolak"
       --------------------------------------------------------------------- */
    document.querySelectorAll('[data-status-form]').forEach(function (form) {
        var select = form.querySelector('select[name="status"]');
        var noteWrapper = form.querySelector('[data-note-wrapper]');
        var noteInput = form.querySelector('[data-note-input]');

        if (!select || !noteWrapper || !noteInput) {
            return;
        }

        var sync = function () {
            var needsNote = select.value === 'ditolak';
            noteWrapper.classList.toggle('d-none', !needsNote);
            noteInput.required = needsNote;
        };

        select.addEventListener('change', sync);
        sync();
    });
}());
