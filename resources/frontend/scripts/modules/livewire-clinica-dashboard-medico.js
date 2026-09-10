
    // ── Paleta de colores institucional RememberMind ──────────────
    const RM = {
        azulProfundo : '#293A59',
        azulClinico  : '#5B7C9D',
        azulClaro    : '#90AFCB',
        verdeSalud   : '#3F7D5A',
        verdeSuave   : '#7FA587',
        terracota    : '#D9795F',
        naranja      : '#E9A05F',
        salmon       : '#E28B70',
        morado       : '#9B8AC7',
        danger       : '#C9654E',
        neutro       : '#737785',
        neutroCard   : '#F4EEE7',
        borde        : 'rgba(91,98,115,0.09)',
    };

    // Opciones base compartidas
    const BASE_OPTS = {
        responsive          : true,
        maintainAspectRatio : false,
        plugins: {
            datalabels: { display: false },
            legend    : {
                labels: { color: '#5B6273', font: { size: 11, weight: '600' }, padding: 12, boxWidth: 12 }
            },
            tooltip: {
                backgroundColor : '#293A59',
                titleColor      : '#FFF8F1',
                bodyColor       : '#D8CDC0',
                padding         : 10,
                cornerRadius    : 10,
            }
        },
    };

    // ── 1. Edad por Género ────────────────────────────────────────
    Alpine.data('graficoEdad', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEdad', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'Masculino',
                            data            : d.masculino,
                            backgroundColor : RM.azulClinico,
                            borderRadius    : 8,
                            borderWidth     : 0,
                        },
                        {
                            label           : 'Femenino',
                            data            : d.femenino,
                            backgroundColor : RM.terracota,
                            borderRadius    : 8,
                            borderWidth     : 0,
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend  : { position: 'top', labels: { ...BASE_OPTS.plugins.legend.labels } },
                        datalabels: {
                            display : true,
                            anchor  : 'end',
                            align   : 'top',
                            color   : RM.neutro,
                            font    : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 11 } } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: RM.neutro, font: { size: 10 } },
                            grid : { color: RM.borde },
                        },
                    },
                },
            });
        },
    }));

    // ── 2. Diagnósticos (barras horizontales) ─────────────────────
    Alpine.data('graficoDiagnosticos', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartDiagnosticos', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colores = [
                RM.azulProfundo, RM.azulClinico, RM.verdeSalud, RM.morado,
                RM.terracota,    RM.naranja,     RM.salmon,     RM.danger,
                RM.verdeSuave,   RM.azulClaro,
            ];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        label           : 'Nº pacientes',
                        data            : d.values,
                        backgroundColor : colores.slice(0, d.labels.length),
                        borderRadius    : 6,
                        borderWidth     : 0,
                    }],
                },
                options: {
                    ...BASE_OPTS,
                    indexAxis: 'y',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'end',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: RM.neutro, font: { size: 10 } },
                            grid : { color: RM.borde },
                        },
                        y: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 10 } } },
                    },
                },
            });
        },
    }));

    // ── 3. Tendencia signos vitales (30 días) ─────────────────────
    Alpine.data('graficoTendencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartTendencia', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            if (!d.labels || d.labels.length === 0) {
                const ctx = this.$refs.canvas.getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '12px Inter, sans-serif';
                ctx.fillStyle = RM.neutro;
                ctx.textAlign = 'center';
                ctx.fillText('Sin datos de signos vitales en los últimos 30 días', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'PA Sistólica (mmHg)',
                            data            : d.pa,
                            borderColor     : RM.danger,
                            backgroundColor : 'rgba(201,101,78,0.07)',
                            fill            : true,
                            tension         : 0.4,
                            borderWidth     : 2.5,
                            pointRadius     : 3,
                            pointBackgroundColor: RM.danger,
                            yAxisID         : 'y',
                        },
                        {
                            label           : 'Glucosa (mg/dL)',
                            data            : d.gluc,
                            borderColor     : RM.naranja,
                            backgroundColor : 'rgba(0,0,0,0)',
                            fill            : false,
                            tension         : 0.4,
                            borderWidth     : 2,
                            borderDash      : [5, 3],
                            pointRadius     : 3,
                            pointBackgroundColor: RM.naranja,
                            yAxisID         : 'y',
                        },
                        {
                            label           : 'SpO2 (%)',
                            data            : d.sat,
                            borderColor     : RM.verdeSalud,
                            backgroundColor : 'rgba(63,125,90,0.07)',
                            fill            : true,
                            tension         : 0.4,
                            borderWidth     : 2.5,
                            pointRadius     : 3,
                            pointBackgroundColor: RM.verdeSalud,
                            yAxisID         : 'y1',
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { ...BASE_OPTS.plugins, legend: { position: 'top', labels: { ...BASE_OPTS.plugins.legend.labels } } },
                    scales: {
                        x : { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 10 } } },
                        y : {
                            position: 'left',
                            beginAtZero: false,
                            ticks: { color: RM.danger, font: { size: 10 } },
                            grid : { color: RM.borde },
                            title: { display: true, text: 'mmHg / mg/dL', color: RM.neutro, font: { size: 9 } },
                        },
                        y1: {
                            position: 'right',
                            min : 80, max: 100,
                            ticks: { color: RM.verdeSalud, font: { size: 10 } },
                            grid : { drawOnChartArea: false },
                            title: { display: true, text: 'SpO2 %', color: RM.verdeSalud, font: { size: 9 } },
                        },
                    },
                },
            });
        },
    }));

    // ── 4. Dependencia funcional Barthel (doughnut) ───────────────
    Alpine.data('graficoDependencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartDependencia', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colMap = {
                'Independiente'       : RM.verdeSalud,
                'Dependencia leve'    : RM.azulClinico,
                'Dependencia moderada': RM.naranja,
                'Dependencia severa'  : RM.terracota,
                'Dependencia total'   : RM.danger,
            };
            const colors = d.labels.map(l => colMap[l] || RM.azulClaro);
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{ data: d.values, backgroundColor: colors, borderColor: RM.neutroCard, borderWidth: 3, hoverOffset: 8 }],
                },
                options: {
                    ...BASE_OPTS,
                    cutout : '65%',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                    },
                },
            });
        },
    }));

    // ── 5. IMC (barras) ───────────────────────────────────────────
    Alpine.data('graficoImc', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartImc', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colors = [RM.azulClinico, RM.verdeSalud, RM.naranja, RM.terracota, RM.danger];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{ label: 'Pacientes', data: d.values, backgroundColor: colors, borderRadius: 8, borderWidth: 0 }],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'top',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: RM.neutro }, grid: { color: RM.borde } },
                    },
                },
            });
        },
    }));

    // ── 6. Estado de residentes (doughnut) ────────────────────────
    Alpine.data('graficoEstados', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEstados', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const palette = [
                RM.verdeSalud, RM.azulClinico, RM.verdeSuave, RM.morado,
                RM.naranja, RM.terracota, RM.danger, RM.salmon, RM.azulClaro,
            ];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        data            : d.values,
                        backgroundColor : d.labels.map((_, i) => palette[i % palette.length]),
                        borderColor     : RM.neutroCard,
                        borderWidth     : 3,
                        hoverOffset     : 8,
                    }],
                },
                options: {
                    ...BASE_OPTS,
                    cutout : '60%',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: {
                            position: 'bottom',
                            labels  : { ...BASE_OPTS.plugins.legend.labels, boxWidth: 10, padding: 6, font: { size: 9 } }
                        },
                    },
                },
            });
        },
    }));

    // ── 7. Notas por tipo de nota (barras) ────────────────────────
    Alpine.data('graficoNotasTipo', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartNotasTipo', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colors = [RM.verdeSalud, RM.azulClinico, RM.azulProfundo, RM.morado, RM.danger, RM.terracota];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{ label: 'Notas', data: d.values, backgroundColor: colors, borderRadius: 8, borderWidth: 0 }],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'top',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: RM.neutro }, grid: { color: RM.borde } },
                    },
                },
            });
        },
    }));
