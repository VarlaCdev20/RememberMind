<x-sistema-layout>

                <x-ui.encabezado-dashboard :estadisticas="$estadisticas ?? []" />

                <x-ui.seccion-graficos-dashboard />

                <x-ui.tabla-usuarios-dashboard :usuarios="$usuariosDashboard ?? []" />

                <div class="grid gap-4 xl:grid-cols-3">
                    <x-ui.panel-actividades-dashboard :actividades="$actividadesDashboard ?? $operacionHoy ?? []" />
                    <x-ui.panel-alertas-dashboard :alertas="$alertasAdministrativas ?? []" />
                    <x-ui.panel-modulos-dashboard :modulos="$modulos ?? []" />
                </div>

                <x-ui.tabla-bitacora-dashboard :registros="$bitacoraDashboard ?? []" />

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            window.dashboardData = {
                usuariosPorRol: @json($usuariosPorRol ?? ['labels' => [], 'data' => []]),
                actividadMensual: @json($actividadMensual ?? ['labels' => [], 'data' => []]),
                estadisticas: @json($estadisticas ?? []),
            };

            document.addEventListener('DOMContentLoaded', () => {
                const light = document.querySelector('.mouse-light');

                if (light) {
                    document.addEventListener('mousemove', (e) => {
                        light.style.opacity = '1';
                        light.style.transform = `translate(${e.clientX - 85}px, ${e.clientY - 85}px)`;
                    });

                    document.addEventListener('mouseleave', () => {
                        light.style.opacity = '0';
                    });
                }

                const colores = ['#E97A5F', '#2F3E5C', '#8DA280', '#967B66', '#F4A261'];

                function crearGrafico(id, tipo, labels, data) {
                    const ctx = document.getElementById(id);
                    if (!ctx || typeof Chart === 'undefined') return;

                    new Chart(ctx, {
                        type: tipo,
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: tipo === 'line' ? 'rgba(233,122,95,0.16)' : colores,
                                borderColor: '#E97A5F',
                                borderWidth: tipo === 'line' ? 3 : 0,
                                fill: tipo === 'line',
                                tension: 0.4,
                                borderRadius: tipo === 'bar' ? 10 : 0,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: tipo === 'doughnut' ? '65%' : undefined,
                            plugins: {
                                legend: {
                                    display: tipo !== 'bar',
                                    position: 'bottom',
                                    labels: {
                                        color: '#2F3E5C',
                                        boxWidth: 10,
                                        font: { size: 11, weight: 'bold' }
                                    }
                                }
                            },
                            scales: tipo === 'bar' || tipo === 'line' ? {
                                y: {
                                    grid: { color: 'rgba(47,62,92,0.08)' },
                                    ticks: {
                                        color: '#2F3E5C',
                                        font: { size: 11, weight: 'bold' }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: '#2F3E5C',
                                        font: { size: 11, weight: 'bold' }
                                    }
                                }
                            } : {}
                        }
                    });
                }

                crearGrafico(
                    'graficoUsuariosRol',
                    'bar',
                    window.dashboardData.usuariosPorRol.labels ?? [],
                    window.dashboardData.usuariosPorRol.data ?? []
                );

                crearGrafico(
                    'graficoActividadMensual',
                    'line',
                    window.dashboardData.actividadMensual.labels ?? [],
                    window.dashboardData.actividadMensual.data ?? []
                );

                crearGrafico(
    'graficoCumplimientoAdmin',
    'bar',
    [
        'Usuarios con rol',
        'Adultos vinculados',
        'Voluntarios asignados',
        'Actividades programadas'
    ],
    [
        window.dashboardData.estadisticas.usuarios_con_rol ?? 0,
        window.dashboardData.estadisticas.adultos_con_familiar ?? 0,
        window.dashboardData.estadisticas.voluntarios_asignados ?? 0,
        window.dashboardData.estadisticas.actividades_programadas ?? 0
    ]
);
            });
        </script>
</x-sistema-layout>