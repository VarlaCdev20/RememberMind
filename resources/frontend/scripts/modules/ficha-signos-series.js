// Series, escalas auxiliares y minigráficas de la ficha.
export const seriesSignos = {
        getDatasetsForMetrica(metrica, isDark) {
            const chartToken = (name) => window.RMCharts?.getCss(name);
            const colors = {
                mint: window.RMCharts.color('care'),
                blue: window.RMCharts.color('clinical'),
                mintSoft: window.RMCharts.color('rehab'),
                blueSoft: window.RMCharts.color('reference'),
                mocha: window.RMCharts.color('neutral'),
                warm: window.RMCharts.color('neutral'),
                blueDeep: window.RMCharts.color('clinical'),
                coral: window.RMCharts.color('clinical'),
                pointBorder: chartToken('--rm-chart-surface-bg'),
            };
            const makeGradient = (ctx, colorHex, alphaTop = 0.22, alphaBottom = 0.0) => {
                const chart = ctx.chart;
                const { ctx: c, chartArea } = chart;
                if (!chartArea) return this.hexToRgba(colorHex, alphaTop);
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, this.hexToRgba(colorHex, alphaTop));
                gradient.addColorStop(1, this.hexToRgba(colorHex, alphaBottom));
                return gradient;
            };

            if (metrica === 'PA') {
                return [
                    {
                        label: 'Sistólica',
                        data: this.sistolica,
                        borderColor: colors.blue,
                        backgroundColor: (context) => makeGradient(context, colors.blue, isDark ? 0.25 : 0.15, 0.01),
                        borderWidth: 2.4,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: colors.blue,
                        pointBorderColor: colors.pointBorder,
                        pointBorderWidth: 1.5,
                        tension: 0.35,
                        fill: false,
                    },
                    {
                        label: 'Diastólica',
                        data: this.diastolica,
                        borderColor: colors.blueSoft,
                        backgroundColor: (context) => makeGradient(context, colors.blueSoft, isDark ? 0.20 : 0.12, 0.01),
                        borderWidth: 2.4,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: colors.blueSoft,
                        pointBorderColor: colors.pointBorder,
                        pointBorderWidth: 1.5,
                        tension: 0.35,
                        fill: false,
                    }
                ];
            }

            const mapData = {
                'FC': { label: 'Frecuencia Cardíaca', data: this.fc, color: colors.mint, fallback: [] },
                'SPO2': { label: 'Saturación SpO₂', data: this.spo2, color: colors.mintSoft, fallback: [] },
                'TEMP': { label: 'Temperatura', data: this.temp, color: colors.warm, fallback: [] },
                'FR': { label: 'Frecuencia Respiratoria', data: this.fr, color: colors.blueDeep, fallback: [] },
                'DOLOR': { label: 'Dolor EVA', data: this.dolor, color: colors.coral, fallback: [] },
                'GLUCOSA': { label: 'Glucemia', data: this.glucosa, color: colors.blue, fallback: [] },
                'PESO': { label: 'Peso Corporal', data: this.peso, color: colors.mocha, fallback: [] },
            };

            const cfg = mapData[metrica] || mapData['FC'];
            const dataToUse = (cfg.data && cfg.data.length) ? cfg.data : cfg.fallback;

            return [{
                label: cfg.label,
                data: dataToUse,
                borderColor: cfg.color,
                backgroundColor: (context) => makeGradient(context, cfg.color, isDark ? 0.30 : 0.20, 0.01),
                borderWidth: 2.4,
                pointRadius: 4.5,
                pointHoverRadius: 7,
                pointBackgroundColor: cfg.color,
                pointBorderColor: colors.pointBorder,
                pointBorderWidth: 1.5,
                tension: 0.35,
                fill: true,
            }];
        },

        // =========================================================================
        // SPARKLINES (SEGUNDA FILA: 6 CARDS)
        // =========================================================================
        renderSparklines() {
            const isDark = document.documentElement.classList.contains('dark');
            const self = this;
            const chartToken = (name) => window.RMCharts?.getCss(name);
            const colors = {
                mint: window.RMCharts.color('care'),
                blue: window.RMCharts.color('clinical'),
                mintSoft: window.RMCharts.color('rehab'),
                blueSoft: window.RMCharts.color('reference'),
                warm: window.RMCharts.color('neutral'),
                blueDeep: window.RMCharts.color('clinical'),
                coral: window.RMCharts.color('clinical'),
            };

            const cards = [
                {
                    id: 'sparklineCanvasPA',
                    isPA: true,
                    dataA: this.sistolica,
                    dataB: this.diastolica,
                    colorA: colors.blue,
                    colorB: colors.blueSoft,
                    yLimits: { suggestedMin: 50, suggestedMax: 160 }
                },
                {
                    id: 'sparklineCanvasFC',
                    dataA: this.fc,
                    colorA: colors.mint,
                    yLimits: { suggestedMin: 50, suggestedMax: 110 }
                },
                {
                    id: 'sparklineCanvasSPO2',
                    dataA: this.spo2,
                    colorA: colors.mintSoft,
                    yLimits: { suggestedMin: 88, suggestedMax: 100 }
                },
                {
                    id: 'sparklineCanvasTEMP',
                    dataA: this.temp,
                    colorA: colors.warm,
                    yLimits: { suggestedMin: 35.0, suggestedMax: 38.5 }
                },
                {
                    id: 'sparklineCanvasFR',
                    dataA: this.fr,
                    colorA: colors.blueDeep,
                    yLimits: { suggestedMin: 10, suggestedMax: 26 }
                },
                {
                    id: 'sparklineCanvasDOLOR',
                    dataA: this.dolor,
                    colorA: colors.coral,
                    yLimits: { suggestedMin: 0, suggestedMax: 10 }
                },
            ];

            cards.forEach((item) => {
                const canvas = document.getElementById(item.id);
                if (!canvas) return;

                if (canvas.offsetParent === null) return;

                let plotA = [...(item.dataA || [])];
                let plotB = item.dataB ? [...item.dataB] : [];
                const sLabels = this.labels.length ? [...this.labels] : [];


                let datasets = [];
                if (item.isPA) {
                    datasets = [
                        {
                            data: plotA,
                            borderColor: item.colorA,
                            borderWidth: 2,
                            tension: 0.38,
                            pointRadius: plotA.length === 1 ? 3 : 0,
                            fill: false,
                        },
                        {
                            data: plotB,
                            borderColor: item.colorB,
                            borderWidth: 2,
                            tension: 0.38,
                            pointRadius: plotA.length === 1 ? 3 : 0,
                            fill: false,
                        }
                    ];
                } else {
                    const makeGrad = (ctx, chartArea) => {
                        if (!chartArea) return self.hexToRgba(item.colorA, 0.25);
                        const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, self.hexToRgba(item.colorA, isDark ? 0.38 : 0.26));
                        g.addColorStop(1, self.hexToRgba(item.colorA, 0.01));
                        return g;
                    };

                    datasets = [{
                        data: plotA,
                        borderColor: item.colorA,
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            return makeGrad(ctx, chartArea);
                        },
                        borderWidth: 2,
                        tension: 0.38,
                        pointRadius: plotA.length === 1 ? 3 : 0,
                        fill: true,
                    }];
                }

                window.RMCharts.init(`signos-mini-${item.id}`, canvas, {
                    type: 'line',
                    data: {
                        labels: sLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            datalabels: { display: false }, // DESACTIVAR DATALABELS EN MINI SPARKLINES
                            tooltip: { enabled: false },
                        },
                        scales: {
                            x: { display: false },
                            y: {
                                display: false,
                                ...(item.yLimits || {})
                            },
                        }
                    }
                });
            });
        },

        hexToRgba(hex, alpha = 1) {
            return window.RMCharts.hexToRgba(hex, alpha);
        }

};
