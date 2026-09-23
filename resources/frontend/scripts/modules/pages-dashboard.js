        window.dashboardData = {
            adultosPorEstado: rmDatosc535f32d75d9,
            distribucionEquipo: rmDatos62d1a989ad1d,
        };

        document.addEventListener('DOMContentLoaded', () => {
            const renderCharts = () => {
                const isDark = window.RMCharts?.isDark() || false;
                const palette = window.RMCharts?.palette() || ['#344D7A', '#D9745B', '#5F9271', '#C9913E', '#7565A8'];
                const sem = window.RMCharts?.semanticColors() || {
                    primary: '#344D7A',
                    success: '#5F9271',
                    danger: '#D9745B',
                    warning: '#C9913E',
                    neutral: '#64748B'
                };

                // Gráfico 1: Adultos por estado (doughnut grueso y translúcido)
                const ctxAdultos = document.getElementById('graficoAdultosPorEstado');
                if (ctxAdultos && typeof Chart !== 'undefined') {
                    const dAdultos = window.dashboardData.adultosPorEstado;
                    const colorsAdultos = [palette[0], palette[2], palette[1], palette[3], palette[4]];

                    const config = window.RMCharts && window.RMCharts.presets
                        ? window.RMCharts.presets.doughnut(
                            dAdultos.labels ?? [],
                            dAdultos.data ?? [],
                            colorsAdultos,
                            {
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 10,
                                            font: { family: 'Inter', size: 10, weight: '700' },
                                            padding: 10,
                                        }
                                    }
                                }
                            }
                        )
                        : {
                            type: 'doughnut',
                            data: {
                                labels: dAdultos.labels ?? [],
                                datasets: [{ data: dAdultos.data ?? [], backgroundColor: colorsAdultos }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, cutout: '58%' }
                        };

                    if (window.RMCharts) {
                        window.RMCharts.init('dashboard_adultos_estado', ctxAdultos, config, renderCharts);
                    } else {
                        new Chart(ctxAdultos, config);
                    }
                }

                // Gráfico 2: Distribución equipo institucional (barras horizontales gruesas y translúcidas)
                const ctxEquipo = document.getElementById('graficoEquipoInstitucional');
                if (ctxEquipo && typeof Chart !== 'undefined') {
                    const dEquipo = window.dashboardData.distribucionEquipo;
                    const colorsEquipo = [palette[1], palette[2], palette[0]];

                    const config = window.RMCharts && window.RMCharts.presets
                        ? window.RMCharts.presets.barHorizontal(
                            dEquipo.labels ?? [],
                            dEquipo.data ?? [],
                            colorsEquipo,
                            {
                                _datasetLabel: 'Miembros',
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        )
                        : {
                            type: 'bar',
                            data: {
                                labels: dEquipo.labels ?? [],
                                datasets: [{ data: dEquipo.data ?? [], backgroundColor: colorsEquipo, borderRadius: 8 }]
                            },
                            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
                        };

                    if (window.RMCharts) {
                        window.RMCharts.init('dashboard_equipo_dist', ctxEquipo, config, renderCharts);
                    } else {
                        new Chart(ctxEquipo, config);
                    }
                }
            };

            // Render inicial
            renderCharts();

            // Observador de modo oscuro
            if (window.RMCharts) {
                window.RMCharts.onThemeChange(renderCharts);
            } else {
                const observer = new MutationObserver(() => renderCharts());
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
            }
        });
