
document.addEventListener('livewire:initialized', () => {
    // Configuración global Chart.js
    Chart.defaults.color = 'var(--color-apoyo)';
    Chart.defaults.font.family = "'Outfit', sans-serif";
    
    @if($tabActivo === 'resumen')
        const ctxSignos = document.getElementById('chartSignos');
        if(ctxSignos) {
            new Chart(ctxSignos, {
                type: 'line',
                data: {
                    labels: @json($labelsSignos),
                    datasets: [
                        {
                            label: 'Sistólica',
                            data: @json($dataPresionSis),
                            borderColor: '#ef4444',
                            backgroundColor: '#ef4444',
                            tension: 0.4
                        },
                        {
                            label: 'Diastólica',
                            data: @json($dataPresionDia),
                            borderColor: '#f59e0b',
                            backgroundColor: '#f59e0b',
                            tension: 0.4
                        },
                        {
                            label: 'Saturación O2',
                            data: @json($dataSaturacion),
                            borderColor: '#3b82f6',
                            backgroundColor: '#3b82f6',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { type: 'linear', display: true, position: 'left', min: 40, max: 200 },
                        y1: { type: 'linear', display: true, position: 'right', min: 70, max: 100 }
                    }
                }
            });
        }

        const ctxTareas = document.getElementById('chartTareas');
        if(ctxTareas) {
            new Chart(ctxTareas, {
                type: 'doughnut',
                data: {
                    labels: ['Realizadas', 'Pendientes', 'Omitidas'],
                    datasets: [{
                        data: @json($dataTareas),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const ctxAlertas = document.getElementById('chartAlertas');
        if(ctxAlertas) {
            new Chart(ctxAlertas, {
                type: 'bar',
                data: {
                    labels: @json($labelsAlertas),
                    datasets: [{
                        label: 'Cantidad de Alertas',
                        data: @json($dataAlertas),
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        const ctxSeg = document.getElementById('chartSeguimientos');
        if(ctxSeg) {
            new Chart(ctxSeg, {
                type: 'bar',
                data: {
                    labels: @json($labelsSeguimientos),
                    datasets: [{
                        label: 'Cantidad de Seguimientos',
                        data: @json($dataSeguimientos),
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    @endif
});
