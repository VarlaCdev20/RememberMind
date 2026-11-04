// Captura factual; geometría compartida con Signos vitales, sin interpretación farmacológica.
export function medicacionRegistro() {
    return {
        dirty: false, busy: false, transportError: '',
        init() {
            this.unload = event => { if (this.$wire.showModal && this.dirty) { event.preventDefault(); event.returnValue = ''; } };
            this.navigate = event => { if (this.$wire.showModal && this.dirty && !window.confirm('¿Salir sin guardar esta captura?')) event.preventDefault(); };
            window.addEventListener('beforeunload', this.unload);
            document.addEventListener('livewire:navigate', this.navigate);
        },
        destroy() { window.removeEventListener('beforeunload', this.unload); document.removeEventListener('livewire:navigate', this.navigate); },
        focusAgenda() {
            const target = this.$el.querySelector('.rm-medication__doses button') ?? this.$el.querySelector('#med-dose-title');
            target?.scrollIntoView({ block: 'center', behavior: 'auto' });
            target?.focus({ preventScroll: true });
        },
        focusMedication() {
            if (window.innerWidth >= 1024) return;
            const target = this.$el.querySelector('#med-order-title');
            target?.scrollIntoView({ block: 'start', behavior: 'auto' });
            target?.focus({ preventScroll: true });
        },
        focusSaved() {
            const target = this.$el.querySelector('.rm-medication__saved');
            if (!target) return;
            target.setAttribute('tabindex', '-1');
            target.scrollIntoView({ block: 'start', behavior: 'auto' });
            target.focus({ preventScroll: true });
        },
        async submit() {
            if (this.busy) return;
            this.busy = true;
            this.transportError = '';
            try { await this.$wire.guardar(); }
            catch (error) { this.transportError = 'No se recibió la confirmación. La captura se conserva; consulta el historial antes de reintentar.'; }
            finally {
                this.busy = false;
                this.$nextTick(() => {
                    const visible = selector => [...this.$el.querySelectorAll(selector)].find(element => element.getClientRects().length > 0);
                    const target = visible('[aria-invalid="true"]') ?? visible('[role="alert"]');
                    if (target && !target.matches('input,select,textarea,button,a')) target.setAttribute('tabindex', '-1');
                    target?.focus();
                });
            }
        },
    };
}

export function medicacionVentana(tipo, visible) {
    const geometry = window.rmSignosRegistro([], {}, {});
    const keys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth', 'trendPointer', 'trendReturnFocus',
        'restoringTrendFocus', 'trendStyle', 'moveTrend', 'resizeTrend', 'resizeTrendEdge', 'resetTrend', 'startTrendPointer', 'updateTrendPointer', 'endTrendPointer'];
    return {
        ...Object.fromEntries(keys.map(key => [key, geometry[key]])),
        visible, mobileActive: false, selectedPeriod: '', selectedResult: 'TODAS', selectedCount: null, chartKey: '',
        init() { this.$watch('visible', value => { if (value && !this.trendOpen) this.openTrend(document.activeElement); if (!value && this.trendOpen) this.closeTrend(); }); if (this.visible) this.openTrend(document.activeElement); },
        fitTrend(bounds = this.trendBounds) {
            const sideBySide = window.innerWidth >= 1280;
            const width = Math.min(sideBySide ? 440 : 600, window.innerWidth - 16), height = Math.min(650, window.innerHeight - 16);
            // Reserva un formulario legible incluso al ampliar la evolución.
            if (sideBySide && bounds) bounds = { ...bounds, width: Math.min(bounds.width, window.innerWidth - 768) };
            geometry.fitTrend.call(this, bounds ?? { width, height, x: tipo === 'historial' && this.$wire?.mostrarEvolucion ? 16 : window.innerWidth - width - 16, y: 80 });
            this.syncWindowMode();
        },
        syncWindowMode() {
            const dialog = this.$refs.trendDialog;
            if (dialog) dialog.setAttribute('aria-modal', String(window.innerWidth < 640 && this.mobileActive));
        },
        activateWindow() {
            if (this.trendOpen && !this.mobileActive) {
                this.mobileActive = true;
                this.$dispatch('medicacion-ventana-activa', { tipo });
            }
        },
        openTrend(trigger) {
            this.trendReturnFocus = trigger; this.visible = true; this.trendOpen = true;
            this.activateWindow();
            this.$nextTick(() => { this.fitTrend(); const dialog = this.$refs.trendDialog; if (dialog && !dialog.matches(':popover-open')) dialog.showPopover(); dialog?.querySelector('[data-trend-move]')?.focus({ preventScroll: true }); });
        },
        closeTrend() {
            this.$refs.trendDialog?.hidePopover(); this.trendOpen = false; this.visible = false; this.trendPointer = null;
            this.$nextTick(() => {
                const other = window.innerWidth < 640 ? [...document.querySelectorAll('[id^="medicacion-"]:popover-open')].at(-1) : null;
                const target = other?.querySelector('[data-trend-move]') ?? this.trendReturnFocus;
                if (target?.isConnected) target.focus({ preventScroll: true });
            });
        },
        drawChart(data) {
            const rows = validMedicationSeries(data?.series);
            this.$nextTick(() => {
                const canvas = this.$refs.medicationChart;
                if (!canvas || !window.RMCharts) return;
                const signature = JSON.stringify({ rows, prescripcion: data?.prescripcion, agrupacion: data?.agrupacion });
                if (signature === this.chartKey && window.RMCharts.has(canvas.id)) return;
                this.chartKey = signature;
                this.selectedPeriod = ''; this.selectedCount = null;
                const charts = window.RMCharts;
                const colors = [charts.getCss('--rm-success'), charts.getCss('--rm-warning')];
                const datasets = ['administradas', 'omitidas'].map((key, i) => ({ label: i ? 'Omitidas' : 'Administradas', data: rows.map(row => row[key]), backgroundColor: colors[i], borderColor: colors[i] }));
                charts.init(canvas.id, canvas, charts.presets.bar(rows.map(row => row.fecha), datasets, { maintainAspectRatio: false,
                    onClick: (event, elements) => {
                        if (!elements[0]) return;
                        const {index,datasetIndex}=elements[0];
                        this.selectedPeriod=rows[index].fecha;
                        this.selectedResult=datasetIndex ? 'OMITIDA' : 'ADMINISTRADA';
                        this.selectedCount=rows[index][datasetIndex ? 'omitidas' : 'administradas'];
                    },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }));
            });
        },
        destroy() { if (this.$refs.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover(); if (tipo === 'evolucion') window.RMCharts?.destroy('medication-results-chart'); },
    };
}

export function validMedicationSeries(series) {
    return (Array.isArray(series) ? series : []).filter(row => row && /^\d{4}-\d{2}-\d{2}$/.test(row.fecha)
        && Number.isFinite(Date.parse(row.fecha+'T00:00:00Z')) && new Date(row.fecha+'T00:00:00Z').toISOString().slice(0,10)===row.fecha
        && Number.isSafeInteger(row.administradas) && row.administradas >= 0 && Number.isSafeInteger(row.omitidas) && row.omitidas >= 0);
}

if (typeof window !== 'undefined') {
    window.rmMedicacionRegistro = medicacionRegistro;
    window.rmMedicacionVentana = medicacionVentana;
}
