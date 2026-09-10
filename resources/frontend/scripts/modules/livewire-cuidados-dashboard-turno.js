
        document.addEventListener('livewire:initialized', () => {
            const chartData = rmDatos46828cbc9c7f;
            if (Object.keys(chartData).length === 0) return;

            // Extractor de variables CSS dinÃ¡micas para soporte Dark Mode
            const getCssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || '#000';

            // Colores TemÃ¡ticos del Sistema RememberMind
            const colors = {
                acento: getCssVar('--color-boton-acento'),
                acentoHover: getCssVar('--color-boton-acentoHover'),
                exito: getCssVar('--color-estado-exito'),
                peligro: getCssVar('--color-estado-peligro'),
                advertencia: getCssVar('--color-estado-advertencia'),
                info: getCssVar('--color-estado-info'),
                meta: getCssVar('--color-meta'),
                apoyo: getCssVar('--color-apoyo'),
                borde: getCssVar('--color-borde'),
                fondoCard: getCssVar('--color-fondo-card'),
                grid: getCssVar('--color-borde-suave')
            };

            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = colors.apoyo;
            const tension = 0.4;

            const createDoughnutPlugin = (text) => ({
                id: 'centerText',
                beforeDraw: function(chart) {
                    var width = chart.width, height = chart.height, ctx = chart.ctx;
                    ctx.restore();
                    var fontSize = (height / 120).toFixed(2);
                    ctx.font = "900 " + fontSize + "em Outfit";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = colors.acento;
                    var textX = Math.round((width - ctx.measureText(text).width) / 2),
                        textY = height / 2;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            });

            // 1. Dona: Tareas
            const ctxTareas = document.getElementById('chartTareas');
            if (ctxTareas) {
                const totalTareas = chartData.tareas.pendientes + chartData.tareas.realizadas + chartData.tareas.omitidas;
                new Chart(ctxTareas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Realizadas', 'Omitidas'],
                        datasets: [{
                            data: [chartData.tareas.pendientes, chartData.tareas.realizadas, chartData.tareas.omitidas],
                            backgroundColor: [colors.advertencia, colors.exito, colors.peligro],
                            borderWidth: 4,
                            borderColor: getCssVar('--color-fondo-panel'),
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 11, weight: 'bold' } } }
                        }
                    },
                    plugins: totalTareas > 0 ? [createDoughnutPlugin(totalTareas)] : []
                });
            }

            // 2. LÃ­nea: Actividad
            const ctxActividad = document.getElementById('chartActividad');
            if (ctxActividad) {
                let gradient = ctxActividad.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, colors.acento + '80'); // 50% opacity
                gradient.addColorStop(1, colors.acento + '00'); // 0% opacity

                new Chart(ctxActividad, {
                    type: 'line',
                    data: {
                        labels: Object.keys(chartData.actividad),
                        datasets: [{
                            label: 'Intervenciones',
                            data: Object.values(chartData.actividad),
                            borderColor: colors.acento,
                            backgroundColor: gradient,
                            borderWidth: 3,
                            fill: true,
                            tension: tension,
                            pointBackgroundColor: getCssVar('--color-fondo-panel'),
                            pointBorderColor: colors.acento,
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid, drawBorder: false }, ticks: { stepSize: 1, padding: 10 } },
                            x: { grid: { display: false }, ticks: { padding: 10, font: { weight: 'bold' } } }
                        }
                    }
                });
            }

            // 3. Barras: SupervisiÃ³n
            const ctxSupervision = document.getElementById('chartSupervision');
            if (ctxSupervision) {
                new Chart(ctxSupervision, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(chartData.supervision),
                        datasets: [{
                            data: Object.values(chartData.supervision),
                            backgroundColor: colors.meta,
                            hoverBackgroundColor: colors.acento,
                            borderRadius: 6,
                            barThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                        }
                    }
                });
            }

            // 4. Dona: Medicacion
            const ctxMed = document.getElementById('chartMedicacion');
            if (ctxMed) {
                const totalMed = chartData.medicacion.administrada + chartData.medicacion.pendiente + chartData.medicacion.omitida;
                new Chart(ctxMed, {
                    type: 'doughnut',
                    data: {
                        labels: ['Admin', 'Pendiente', 'Omitida'],
                        datasets: [{
                            data: [chartData.medicacion.administrada, chartData.medicacion.pendiente, chartData.medicacion.omitida],
                            backgroundColor: [colors.exito, colors.advertencia, colors.peligro],
                            borderWidth: 4,
                            borderColor: getCssVar('--color-fondo-panel'),
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 10, weight: 'bold' } } } }
                    },
                    plugins: totalMed > 0 ? [createDoughnutPlugin(totalMed)] : []
                });
            }

            // 5. Barras: Alertas
            const ctxAlertas = document.getElementById('chartAlertas');
            if (ctxAlertas) {
                new Chart(ctxAlertas, {
                    type: 'bar',
                    data: {
                        labels: ['Leve', 'Moderada', 'CrÃ­tica'],
                        datasets: [{
                            data: [chartData.alertas.leve, chartData.alertas.moderada, chartData.alertas.critica],
                            backgroundColor: [colors.info, colors.advertencia, colors.peligro],
                            borderRadius: 6,
                            barThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                        }
                    }
                });
            }
        });
    