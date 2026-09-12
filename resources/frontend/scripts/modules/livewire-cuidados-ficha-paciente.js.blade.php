document.addEventListener('livewire:initialized', () => {
    // Configuración global Chart.js
    if (typeof Chart === 'undefined') return;
    Chart.defaults.color = 'var(--color-apoyo)';
    Chart.defaults.font.family = "'Outfit', sans-serif";

    const hexToRgba = (hex, alpha) => {
        if (window.RMCharts && window.RMCharts.helpers && window.RMCharts.helpers.hexToRgba) {
            return window.RMCharts.helpers.hexToRgba(hex, alpha);
        }
        if (!hex || typeof hex !== 'string') return hex;
        let c = hex.replace('#', '');
        if (c.length === 3) c = c.split('').map(x => x + x).join('');
        if (c.length === 6) {
            const num = parseInt(c, 16);
            return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
        }
        return hex;
    };

    window.__rmFichaPacienteCharts = window.__rmFichaPacienteCharts || new Map();
    const setChart = (key, chart) => {
        if (window.__rmFichaPacienteCharts.has(key)) {
            try { window.__rmFichaPacienteCharts.get(key).destroy(); } catch (e) {}
        }
        window.__rmFichaPacienteCharts.set(key, chart);
        return chart;
    };
    
    @if($tabActivo === 'resumen')
        const ctxSignos = document.getElementById('chartSignos');
        if (ctxSignos) {
            setChart('signos', new Chart(ctxSignos, {
                type: 'line',
                data: {
                    labels: @json($labelsSignos),
                    datasets: [
                        {
                            label: 'Sistólica',
                            data: @json($dataPresionSis),
                            borderColor: '#ef4444',
                            backgroundColor: hexToRgba('#ef4444', 0.22),
                            tension: 0.38,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                        },
                        {
                            label: 'Diastólica',
                            data: @json($dataPresionDia),
                            borderColor: '#f59e0b',
                            backgroundColor: hexToRgba('#f59e0b', 0.22),
                            tension: 0.38,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                        },
                        {
                            label: 'Saturación O2',
                            data: @json($dataSaturacion),
                            borderColor: '#3b82f6',
                            backgroundColor: hexToRgba('#3b82f6', 0.22),
                            tension: 0.38,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: 'bold' } }
                        }
                    },
                    scales: {
                        y: { type: 'linear', display: true, position: 'left', min: 40, max: 200, grid: { color: 'rgba(47, 62, 92, 0.08)' } },
                        y1: { type: 'linear', display: true, position: 'right', min: 70, max: 100, grid: { display: false } }
                    }
                }
            }));
        }

        const ctxTareas = document.getElementById('chartTareas');
        if (ctxTareas) {
            setChart('tareas', new Chart(ctxTareas, {
                type: 'doughnut',
                data: {
                    labels: ['Realizadas', 'Pendientes', 'Omitidas'],
                    datasets: [{
                        data: @json($dataTareas),
                        backgroundColor: [hexToRgba('#10b981', 0.80), hexToRgba('#f59e0b', 0.80), hexToRgba('#ef4444', 0.80)],
                        borderColor: '#ffffff',
                        borderWidth: 2.5,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                        animateRotate: true,
                        animateScale: true,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: 'bold' } }
                        }
                    }
                }
            }));
        }

        const ctxAlertas = document.getElementById('chartAlertas');
        if (ctxAlertas) {
            setChart('alertas', new Chart(ctxAlertas, {
                type: 'bar',
                data: {
                    labels: @json($labelsAlertas),
                    datasets: [{
                        label: 'Cantidad de Alertas',
                        data: @json($dataAlertas),
                        backgroundColor: hexToRgba('#ef4444', 0.80),
                        borderColor: hexToRgba('#ef4444', 0.98),
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.86,
                        categoryPercentage: 0.92,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            }));
        }

        const ctxSeg = document.getElementById('chartSeguimientos');
        if (ctxSeg) {
            setChart('seguimientos', new Chart(ctxSeg, {
                type: 'bar',
                data: {
                    labels: @json($labelsSeguimientos),
                    datasets: [{
                        label: 'Cantidad de Seguimientos',
                        data: @json($dataSeguimientos),
                        backgroundColor: hexToRgba('#3b82f6', 0.80),
                        borderColor: hexToRgba('#3b82f6', 0.98),
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.86,
                        categoryPercentage: 0.92,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            }));
        }
    @endif
});
