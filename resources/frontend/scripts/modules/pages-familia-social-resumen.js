(() => {
    const payload = rmDatosff6fbf562949;
    const isDark = window.RMCharts?.isDark() || false;
    const textColor = window.RMCharts ? window.RMCharts.getCss('--rm-chart-axis-text') : '#64748B';
    const gridColor = window.RMCharts ? window.RMCharts.getCss('--rm-chart-grid') : 'rgba(224,212,198,0.35)';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 950,
            easing: 'easeOutQuart',
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    usePointStyle: true,
                    font: { family: 'Inter', size: 11, weight: '700' },
                    color: textColor,
                    padding: 10,
                },
            },
            datalabels: { display: false },
        },
    };

    const toTranslucent = (hex, a = 0.80) => window.RMCharts?.hexToRgba ? window.RMCharts.hexToRgba(hex, a) : hex;

    const destroyPrevious = (canvas) => {
        if (canvas && canvas.__familiaChart) {
            canvas.__familiaChart.destroy();
            canvas.__familiaChart = null;
        }
    };

    const makeDoughnut = (id, data, colors) => {
        const canvas = document.getElementById(id);
        if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

        destroyPrevious(canvas);
        const transColors = colors.map(c => toTranslucent(c, 0.80));
        const borderColors = colors.map(c => toTranslucent(c, 0.98));

        canvas.__familiaChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: transColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    hoverOffset: 8,
                }],
            },
            options: {
                ...chartDefaults,
                cutout: '58%',
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true,
                },
            },
        });
    };

    const makeBar = (id, data) => {
        const canvas = document.getElementById(id);
        if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

        destroyPrevious(canvas);
        canvas.__familiaChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    label: 'Visitas',
                    backgroundColor: toTranslucent('#5F9271', 0.80),
                    borderColor: '#5F9271',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    barPercentage: 0.86,
                    categoryPercentage: 0.90,
                }],
            },
            options: {
                ...chartDefaults,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: textColor, font: { family: 'Inter', size: 10, weight: '600' } },
                        grid: { color: gridColor },
                    },
                    x: {
                        ticks: { color: textColor, font: { family: 'Inter', size: 10, weight: '600' } },
                        grid: { display: false },
                    },
                },
            },
        });
    };

    const renderCharts = () => {
        const pal = window.RMCharts?.palette() || ['#344D7A', '#D9745B', '#5F9271', '#C9913E', '#7565A8'];
        makeDoughnut('familiaRedChart', payload.red, [pal[2], pal[3]]);
        makeBar('familiaVisitasChart', payload.visitas);
        makeDoughnut('familiaFichaChart', payload.ficha, [pal[2], pal[3], pal[1]]);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderCharts, { once: true });
    } else {
        renderCharts();
    }

    window.RMCharts?.onThemeChange(renderCharts);
})();
