import { graficaSignos } from './ficha-signos-grafica.js';
import { seriesSignos } from './ficha-signos-series.js';

export function crearSignosFicha(config) {
    return {
        ...graficaSignos,
        ...seriesSignos,
        metricaActiva: config.metricaActiva || 'PA',
        periodoActivo: config.periodoActivo || '7d',
        labels: config.labels || [],
        labelsLargas: config.labelsLargas || config.labels || [],
        sistolica: config.sistolica || [],
        diastolica: config.diastolica || [],
        fc: config.fc || [],
        spo2: config.spo2 || [],
        temp: config.temp || [],
        fr: config.fr || [],
        dolor: config.dolor || [],
        glucosa: config.glucosa || [],
        peso: config.peso || [],
        metricasInfo: config.metricasInfo || {},
        resumenActivo: config.resumenPeriodo || (config.metricasInfo ? config.metricasInfo['PA'] : {}),
        registros: config.registros || [],
        interpretaciones: config.interpretaciones || [],

        mainChart: null,
        sparklinesInitialized: false,
        drawerAbierto: false,
        registroActivo: null,
        modalRangos: false,
        menuCabecera: false,
        toastMensaje: null,

        init() {
            this.reinitCharts();

            // Reaccionar al cambio de pestaña en la Ficha Médica
            if (typeof this.$watch === 'function') {
                this.$watch('activeTab', (val) => {
                    if (val === 'signos') {
                        this.reinitCharts();
                    }
                });
            }

            window.addEventListener('render-graficos-signos', () => {
                this.reinitCharts();
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'signos') {
                    this.reinitCharts();
                }
            });

            window.addEventListener('resize', () => {
                this.resizeAllCharts();
            });

            window.RMCharts?.onThemeChange?.(() => {
                this.reinitCharts();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const canvas = document.getElementById('signosVitalesMainCanvas');
                        if (canvas && canvas.offsetParent !== null && !Chart.getChart(canvas)) {
                            this.reinitCharts();
                        }
                    });
                });
            }
        },

        abrirModalRangos() {
            this.modalRangos = true;
        },

        mostrarToast(msg) {
            this.toastMensaje = msg;
            setTimeout(() => {
                this.toastMensaje = null;
            }, 3000);
        },

        exportarSignosPDF() {
            this.mostrarToast('Generando reporte en formato PDF...');
            setTimeout(() => {
                window.print();
            }, 600);
        },

        exportarSignosExcel() {
            if (!this.registros || !this.registros.length) {
                this.mostrarToast('No hay registros históricos para exportar.');
                return;
            }

            let csvContent = 'data:text/csv;charset=utf-8,';
            csvContent += 'Fecha,Hora,Presion Arterial,FC (lpm),SpO2 (%),Temp (C),FR (rpm),Dolor,Criterio Clinico,Observaciones,Responsable,Estado\n';

            this.registros.forEach(r => {
                const row = [
                    `"${r.fecha}"`,
                    `"${r.hora}"`,
                    `"${r.pa}"`,
                    `"${r.fc}"`,
                    `"${r.spo2_val}"`,
                    `"${r.temp_val}"`,
                    `"${r.fr}"`,
                    `"${r.dolor}"`,
                    `"${(r.criterio || '').replace(/"/g, '""')}"`,
                    `"${(r.observaciones || '').replace(/"/g, '""')}"`,
                    `"${(r.responsable || '').replace(/"/g, '""')}"`,
                    `"${r.estado}"`
                ].join(',');
                csvContent += row + '\n';
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `signos_vitales_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            this.mostrarToast('Archivo Excel/CSV descargado exitosamente.');
        },

        imprimirGrafica() {
            window.print();
        },

        reinitCharts() {
            setTimeout(() => {
                this.renderMainChart();
                this.renderSparklines();
            }, 60);
        },

        resizeAllCharts() {
            const main = document.getElementById('signosVitalesMainCanvas');
            if (main) Chart.getChart(main)?.resize();

            ['PA', 'FC', 'SPO2', 'TEMP', 'FR', 'DOLOR'].forEach(k => {
                const c = document.getElementById('sparklineCanvas' + k);
                if (c) Chart.getChart(c)?.resize();
            });
        },

        seleccionarMetrica(metrica) {
            this.metricaActiva = metrica;
            if (this.metricasInfo[metrica]) {
                this.resumenActivo = this.metricasInfo[metrica];
            }
            if (this.$wire && typeof this.$wire.setMetricaSignos === 'function') {
                this.$wire.setMetricaSignos(metrica);
            }
            this.updateMainChart();
        },

        cambiarPeriodo(periodo) {
            this.periodoActivo = periodo;
            if (this.$wire && typeof this.$wire.setPeriodoSignos === 'function') {
                this.$wire.setPeriodoSignos(periodo);
            }
        },

        abrirDrawer(item) {
            this.registroActivo = item;
            this.drawerAbierto = true;
        },

        abrirDrawerDesdeEvento(ev) {
            const registro = this.registros.find(r => r.id === ev.registro?.id);
            if (registro) this.abrirDrawer(registro);
        },

        cerrarDrawer() {
            this.drawerAbierto = false;
        },

        // =========================================================================
        // GRÁFICO PRINCIPAL: CHART.JS CON NÚMEROS VISIBLES EN PUNTOS (DATALABELS)
        // =========================================================================
    };
}

let registrado = false;
function registrarSignosFicha() {
    if (!window.Alpine || registrado) return;
    window.Alpine.data('rmSignosVitalesModule', crearSignosFicha);
    registrado = true;
}
document.addEventListener('alpine:init', registrarSignosFicha, { once: true });
registrarSignosFicha();
