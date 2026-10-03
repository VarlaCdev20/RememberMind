        window.dashboardData = {
            adultosPorEstado: rmDatosc535f32d75d9,
            distribucionEquipo: rmDatos62d1a989ad1d,
        };

        document.addEventListener('DOMContentLoaded', () => {
            const renderCharts = () => {
                const api = window.RMCharts;
                if (!api || typeof Chart === 'undefined') return;
                const palette = api.palette();

                // Gráfico 1: Adultos por estado (doughnut grueso y translúcido)
                const ctxAdultos = document.getElementById('graficoAdultosPorEstado');
                if (ctxAdultos) {
                    const dAdultos = window.dashboardData.adultosPorEstado;
                    const colorsAdultos = [palette[0], palette[2], palette[1], palette[3], palette[4]];

                    const config = api.presets.doughnut(
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
                                            font: { family: api.getCss('--rm-chart-font-family'), size: api.number('--rm-chart-legend-size', 12), weight: '700' },
                                            padding: 10,
                                        }
                                    }
                                }
                            }
                        );
                    api.init('dashboard_adultos_estado', ctxAdultos, config, renderCharts);
                }

                // Gráfico 2: Distribución equipo institucional (barras horizontales gruesas y translúcidas)
                const ctxEquipo = document.getElementById('graficoEquipoInstitucional');
                if (ctxEquipo) {
                    const dEquipo = window.dashboardData.distribucionEquipo;
                    const colorsEquipo = [palette[1], palette[2], palette[0]];

                    const config = api.presets.barHorizontal(
                            dEquipo.labels ?? [],
                            dEquipo.data ?? [],
                            colorsEquipo,
                            {
                                _datasetLabel: 'Miembros',
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        );
                    api.init('dashboard_equipo_dist', ctxEquipo, config, renderCharts);
                }
            };

            // Render inicial
            renderCharts();

            // Observador de modo oscuro
            window.RMCharts?.onThemeChange(renderCharts, 'page-dashboard');
        });
