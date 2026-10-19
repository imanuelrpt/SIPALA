/**
 * SIPALA — grafik dasbor (Chart.js).
 *
 * Data dibaca dari atribut `data-*` pada elemen <canvas> agar tidak perlu
 * skrip inline (CSP `script-src 'self'`).
 */
(function () {
    'use strict';

    var PALETTE = ['#0d3b66', '#1d7874', '#f0a500', '#d62828', '#5a189a', '#0aa2c0', '#6c757d'];

    function readJson(element, attribute) {
        var raw = element.getAttribute(attribute);

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

    function init() {
        if (typeof window.Chart === 'undefined') {
            return;
        }

        document.querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
            var type = canvas.getAttribute('data-chart') || 'bar';
            var labels = readJson(canvas, 'data-labels');
            var values = readJson(canvas, 'data-values');
            var explicitColors = readJson(canvas, 'data-colors');

            if (labels.length === 0) {
                return;
            }

            var colors = labels.map(function (_, index) {
                return explicitColors[index] || PALETTE[index % PALETTE.length];
            });

            var isPie = type === 'doughnut' || type === 'pie' || type === 'polarArea';

            new window.Chart(canvas.getContext('2d'), {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: canvas.getAttribute('data-label') || 'Jumlah',
                        data: values,
                        backgroundColor: isPie ? colors : colors.map(function (color) {
                            return color + 'cc';
                        }),
                        borderColor: isPie ? '#ffffff' : colors,
                        borderWidth: isPie ? 2 : 1,
                        borderRadius: isPie ? 0 : 4,
                        tension: 0.3,
                        fill: type === 'line'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: isPie,
                            position: 'bottom',
                            labels: { boxWidth: 12, usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    var value = context.parsed && typeof context.parsed === 'object'
                                        ? context.parsed.y
                                        : context.parsed;
                                    return ' ' + value + ' aduan';
                                }
                            }
                        }
                    },
                    scales: isPie ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
