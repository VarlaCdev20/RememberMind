    // Paleta de colores institucional RememberMind y utilidades de translucidez
    function getRMColors() {
        if (window.RMCharts) {
            const pal = window.RMCharts.palette();
            const sem = window.RMCharts.semanticColors();
            return {
                azulProfundo : pal[0] || '#344D7A',
                azulClinico  : pal[5] || '#4E8CA6',
                azulClaro    : '#90AFCB',
                verdeSalud   : sem.success || '#5F9271',
                verdeSuave   : pal[2] || '#7FA587',
                terracota    : sem.danger || '#D9745B',
                naranja      : sem.warningHigh || '#E67A22',
                salmon       : pal[6] || '#A85C73',
                morado       : pal[4] || '#7565A8',
                danger       : sem.danger || '#E5534B',
                neutro       : sem.neutral || '#64748B',
                neutroCard   : '#F4EEE7',
                borde        : 'rgba(91,98,115,0.12)',
            };
        }
        return {
            azulProfundo : '#344D7A',
            azulClinico  : '#4E8CA6',
            azulClaro    : '#90AFCB',
            verdeSalud   : '#5F9271',
            verdeSuave   : '#7FA587',
            terracota    : '#D9745B',
            naranja      : '#E67A22',
            salmon       : '#A85C73',
            morado       : '#7565A8',
            danger       : '#E5534B',
            neutro       : '#64748B',
            neutroCard   : '#F4EEE7',
            borde        : 'rgba(91,98,115,0.12)',
        };
    }

    function toTranslucent(color, alpha = 0.78) {
        if (window.RMCharts?.hexToRgba) {
            return window.RMCharts.hexToRgba(color, alpha);
        }
        return color;
    }

    function getBaseOpts() {
        if (window.RMCharts?.baseOptions) {
            return window.RMCharts.baseOptions();
        }
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: { display: false },
                legend: { labels: { font: { family: 'Inter', size: 11, weight: '600' } } }
            }
        };
    }

    // 1. Edad por Género (Barras gruesas translúcidas)
    Alpine.data('graficoEdad', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEdad', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const BASE_OPTS = getBaseOpts();
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'Masculino',
                            data            : d.masculino,
                            backgroundColor : toTranslucent(RM.azulClinico, 0.78),
                            borderColor     : toTranslucent(RM.azulClinico, 0.95),
                            borderWidth     : 1.5,
                            borderRadius    : 8,
                            barPercentage   : 0.85,
                            categoryPercentage: 0.90,
                        },
                        {
                            label           : 'Femenino',
                            data            : d.femenino,
                            backgroundColor : toTranslucent(RM.terracota, 0.78),
                            borderColor     : toTranslucent(RM.terracota, 0.95),
                            borderWidth     : 1.5,
                            borderRadius    : 8,
                            barPercentage   : 0.85,
                            categoryPercentage: 0.90,
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: true, position: 'top' },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        },
    }));

    // 2. Tendencia de Signos Vitales (Líneas y áreas suaves translúcidas)
    Alpine.data('graficoTendencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartTendencia', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const BASE_OPTS = getBaseOpts();
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'PA Sistólica',
                            data            : d.pa,
                            borderColor     : RM.danger,
                            backgroundColor : toTranslucent(RM.danger, 0.15),
                            borderWidth     : 2.5,
                            pointRadius     : 3.5,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: RM.danger,
                            tension         : 0.38,
                            fill            : true,
                            yAxisID         : 'y',
                        },
                        {
                            label           : 'SpO2 %',
                            data            : d.spo2,
                            borderColor     : RM.verdeSalud,
                            backgroundColor : toTranslucent(RM.verdeSalud, 0.12),
                            borderWidth     : 2,
                            pointRadius     : 3,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: RM.verdeSalud,
                            tension         : 0.38,
                            fill            : true,
                            yAxisID         : 'y1',
                        },
                        {
                            label           : 'Glucosa',
                            data            : d.glucosa,
                            borderColor     : RM.naranja,
                            backgroundColor : toTranslucent(RM.naranja, 0.10),
                            borderWidth     : 2,
                            pointRadius     : 3,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: RM.naranja,
                            tension         : 0.38,
                            fill            : true,
                            yAxisID         : 'y',
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        x : { grid: { display: false } },
                        y : {
                            position: 'left',
                            beginAtZero: false,
                            title: { display: true, text: 'mmHg / mg/dL' },
                        },
                        y1: {
                            position: 'right',
                            min : 80, max: 100,
                            grid : { drawOnChartArea: false },
                            title: { display: true, text: 'SpO2 %' },
                        },
                    },
                },
            });
        },
    }));

    // 3. Dependencia funcional Barthel (Doughnut grueso translúcido)
    Alpine.data('graficoDependencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartDependencia', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const colMap = {
                'Independiente'       : RM.verdeSalud,
                'Dependencia leve'    : RM.azulClinico,
                'Dependencia moderada': RM.naranja,
                'Dependencia severa'  : RM.salmon,
                'Dependencia total'   : RM.danger,
            };
            const colors = d.labels.map(l => colMap[l] || RM.azulClaro);
            const translucentColors = colors.map(c => toTranslucent(c, 0.80));
            const borderColors = colors.map(c => toTranslucent(c, 0.98));

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        data: d.values,
                        backgroundColor: translucentColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        hoverOffset: 6
                    }],
                },
                options: {
                    ...getBaseOpts(),
                    cutout : '58%', // Anillo grueso con presencia
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.label}: ${ctx.parsed} residentes`
                            }
                        }
                    },
                },
            });
        },
    }));

    // 4. IMC (Barras gruesas translúcidas)
    Alpine.data('graficoImc', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartImc', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const BASE_OPTS = getBaseOpts();
            const colors = [RM.azulClinico, RM.verdeSalud, RM.naranja, RM.salmon, RM.danger];
            const translucentColors = colors.map(c => toTranslucent(c, 0.78));
            const borderColors = colors.map(c => toTranslucent(c, 0.95));

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        label: 'Pacientes',
                        data: d.values,
                        backgroundColor: translucentColors,
                        borderColor: borderColors,
                        borderWidth: 1.5,
                        borderRadius: 8,
                        barPercentage: 0.85,
                        categoryPercentage: 0.90,
                    }],
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
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        },
    }));

    // 5. Estado de residentes (Doughnut grueso translúcido)
    Alpine.data('graficoEstados', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEstados', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const palette = [
                RM.verdeSalud, RM.azulClinico, RM.verdeSuave, RM.morado,
                RM.naranja, RM.salmon, RM.danger, RM.azulClaro,
            ];
            const rawColors = d.labels.map((_, i) => palette[i % palette.length]);
            const translucentColors = rawColors.map(c => toTranslucent(c, 0.80));
            const borderColors = rawColors.map(c => toTranslucent(c, 0.98));

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        data            : d.values,
                        backgroundColor : translucentColors,
                        borderColor     : borderColors,
                        borderWidth     : 2,
                        hoverOffset     : 6,
                    }],
                },
                options: {
                    ...getBaseOpts(),
                    cutout : '58%', // Grueso y consistente con Alertas
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels  : { boxWidth: 10, padding: 8, font: { size: 10, weight: '600' } }
                        },
                    },
                },
            });
        },
    }));

    // 6. Notas por tipo de nota (Barras gruesas translúcidas)
    Alpine.data('graficoNotasTipo', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartNotasTipo', (d) => { this.chart?.destroy(); this.draw(d); });
            window.RMCharts?.onThemeChange(() => { this.chart?.destroy(); this.draw(initial); });
        },
        draw(d) {
            const RM = getRMColors();
            const BASE_OPTS = getBaseOpts();
            const colors = [RM.verdeSalud, RM.azulClinico, RM.azulProfundo, RM.morado, RM.danger, RM.salmon];
            const translucentColors = colors.map(c => toTranslucent(c, 0.78));
            const borderColors = colors.map(c => toTranslucent(c, 0.95));

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        label: 'Notas',
                        data: d.values,
                        backgroundColor: translucentColors,
                        borderColor: borderColors,
                        borderWidth: 1.5,
                        borderRadius: 8,
                        barPercentage: 0.85,
                        categoryPercentage: 0.90,
                    }],
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
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        },
    }));
