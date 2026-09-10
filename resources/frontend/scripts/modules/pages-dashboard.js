
        window.dashboardData = {
            adultosPorEstado: rmDatosc535f32d75d9,
            distribucionEquipo: rmDatos62d1a989ad1d,
        };

        document.addEventListener('DOMContentLoaded', () => {
            const getCssVar = (name, fallback) => {
                const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
                return value || fallback;
            };

            const getChartTheme = () => ({
                primary: getCssVar('--chart-primary', '#2F3E5C'),
                secondary: getCssVar('--chart-secondary', '#7FA587'),
                accent: getCssVar('--chart-accent', '#D9795F'),
                warning: getCssVar('--chart-warning', '#DDA15E'),
                danger: getCssVar('--chart-danger', '#D96C75'),
                muted: getCssVar('--chart-muted', '#6B7280'),
                text: getCssVar('--chart-text', '#293A59'),
                grid: getCssVar('--chart-grid', 'rgba(41,58,89,0.10)'),
                panel: getCssVar('--chart-panel', 'rgba(244,238,231,0.78)'),
                border: getCssVar('--chart-border', 'rgba(41,58,89,0.14)')
            });

            let chartAdultos = null;
            let chartEquipo = null;

            const renderCharts = () => {
                const chartTheme = getChartTheme();

                // Gráfico 1: Adultos por estado (doughnut)
                const ctxAdultos = document.getElementById('graficoAdultosPorEstado');
                if (ctxAdultos && typeof Chart !== 'undefined') {
                    if (chartAdultos) chartAdultos.destroy();
                    const dAdultos = window.dashboardData.adultosPorEstado;
                    const colorsAdultos = [chartTheme.primary, chartTheme.secondary, chartTheme.accent, chartTheme.warning, chartTheme.danger];

                    chartAdultos = new Chart(ctxAdultos, {
                        type: 'doughnut',
                        data: {
                            labels: dAdultos.labels ?? [],
                            datasets: [{
                                data: dAdultos.data ?? [],
                                backgroundColor: colorsAdultos,
                                borderWidth: 2,
                                borderColor: chartTheme.panel,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                datalabels: { display: false },
                                tooltip: {
                                    backgroundColor: chartTheme.panel,
                                    titleColor: chartTheme.text,
                                    bodyColor: chartTheme.text,
                                    borderColor: chartTheme.border,
                                    borderWidth: 1,
                                    padding: 10
                                },
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        color: chartTheme.text,
                                        boxWidth: 10,
                                        font: { size: 11, weight: '700' }
                                    }
                                }
                            }
                        }
                    });
                }

                // Gráfico 2: Distribución equipo institucional (barra horizontal)
                const ctxEquipo = document.getElementById('graficoEquipoInstitucional');
                if (ctxEquipo && typeof Chart !== 'undefined') {
                    if (chartEquipo) chartEquipo.destroy();
                    const dEquipo = window.dashboardData.distribucionEquipo;
                    const colorsEquipo = [chartTheme.accent, chartTheme.secondary, chartTheme.primary];

                    chartEquipo = new Chart(ctxEquipo, {
                        type: 'bar',
                        data: {
                            labels: dEquipo.labels ?? [],
                            datasets: [{
                                data: dEquipo.data ?? [],
                                backgroundColor: colorsEquipo,
                                borderWidth: 1,
                                borderColor: chartTheme.border,
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                datalabels: {
                                    display: true,
                                    color: chartTheme.text,
                                    font: { weight: 'bold', size: 10 },
                                    formatter: (val) => val > 0 ? val : ''
                                },
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: chartTheme.panel,
                                    titleColor: chartTheme.text,
                                    bodyColor: chartTheme.text,
                                    borderColor: chartTheme.border,
                                    borderWidth: 1,
                                    padding: 10
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: chartTheme.grid },
                                    ticks: { color: chartTheme.muted, font: { size: 11, weight: '700' } }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { color: chartTheme.muted, font: { size: 11, weight: '700' } }
                                }
                            }
                        }
                    });
                }
            };

            // Render inicial
            renderCharts();

            // Observar cambios de tema
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme') {
                        renderCharts();
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true });

        });
    