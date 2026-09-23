
    function rmDestroyCharts() {
        if (!window.Chart) return;

        if (Chart.instances) {
            Object.values(Chart.instances).forEach((chart) => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
        }

        document.querySelectorAll('canvas').forEach((canvas) => {
            const chart = Chart.getChart(canvas);
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
    }

    $wire.on('destroy-charts', () => {
        rmDestroyCharts();
    });

    $wire.on('swal', (event) => {
        const data = event[0] ?? event;

        Swal.fire({
            icon: data.icon ?? 'info',
            title: data.title ?? '',
            text: data.text ?? '',
            confirmButtonColor: '#3F7D5A',
            timer: data.icon === 'success' ? 2200 : undefined,
            timerProgressBar: data.icon === 'success',
        });
    });
