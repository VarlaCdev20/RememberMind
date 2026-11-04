// Proyección visual de conteos guardados; no interpola días ni calcula gravedad.
export function conductaChart(series = []) {
    const rows = (Array.isArray(series) ? series : []).flatMap(row => {
        if (!row || typeof row.fecha !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(row.fecha)) return [];
        const time = Date.parse(row.fecha + 'T00:00:00Z');
        if (!Number.isFinite(time) || new Date(time).toISOString().slice(0, 10) !== row.fecha) return [];
        const raw = row.registros;
        if (!['number', 'string'].includes(typeof raw) || !/^\d+$/.test(String(raw))) return [];
        const value = Number(raw);
        if (!Number.isSafeInteger(value) || value < 1) return [];
        return [{ date: row.fecha, label: row.fecha.slice(8) + '/' + row.fecha.slice(5, 7), time, value,
            observed: (Array.isArray(row.observadas) ? row.observadas : []).filter(v => typeof v === 'string' && v.trim()).join(', ') || 'Sin conductas marcadas como observadas' }];
    }).sort((a, b) => a.time - b.time);
    const upper = Math.max(2, Math.ceil((Math.max(0, ...rows.map(row => row.value)) + 1) / 2) * 2);
    const first = rows[0]?.time ?? 0, span = (rows.at(-1)?.time ?? first) - first;
    const points = rows.map(row => ({ ...row, x: span ? 5 + (row.time - first) / span * 90 : 50, y: 100 - row.value / upper * 100 }));
    const line = points.length > 1 ? points.map(row => `${row.x.toFixed(4)},${row.y.toFixed(4)}`).join(' ') : '';
    const area = line ? `${points[0].x.toFixed(4)},100 ${line} ${points.at(-1).x.toFixed(4)},100` : '';
    const ticks = points.length > 1 ? [points[0], points.at(-1)] : points;
    return { active: null, points, upper, line, area, ticks,
        get selected() { return this.points[this.active] ?? null; },
    };
}

// Captura factual: nunca calcula severidad ni interpreta conductas.
export function conductaRegistro() {
    // Same window geometry and gestures as Signos; no clinical calculations inherited.
    const geometry = window.rmSignosRegistro([], {}, {});
    const geometryKeys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth',
        'trendPointer', 'trendReturnFocus', 'restoringTrendFocus', 'closeTrend', 'trendStyle',
        'moveTrend', 'resizeTrend', 'resizeTrendEdge', 'resetTrend', 'startTrendPointer',
        'updateTrendPointer', 'endTrendPointer'];
    return {
        ...window.rmClinicalFormFeedback(),
        ...Object.fromEntries(geometryKeys.map(key => [key, geometry[key]])),
        historyOpen: false, busy: false, discardIntent: 'close', conductaViewportCompact: null,
        init() {
            this.navigationHandler = event => {
                if (this.$wire.mostrarFormulario && this.clinicalFormDirty && !window.confirm('¿Salir sin guardar? Los datos de este registro se perderán.')) event.preventDefault();
            };
            document.addEventListener('livewire:navigate', this.navigationHandler);
        },
        destroy() {
            if (this.$refs.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            document.removeEventListener('livewire:navigate', this.navigationHandler);
        },
        fitTrend(bounds = this.trendBounds) {
            const compact = window.innerWidth < 1280;
            if (this.conductaViewportCompact !== null && compact !== this.conductaViewportCompact) bounds = null;
            this.conductaViewportCompact = compact;
            if (!compact && bounds) bounds = { ...bounds, width: Math.min(bounds.width, Math.max(360, window.innerWidth - 648)) };
            const count = this.$refs.trendDialog?.querySelectorAll?.('.rm-conducta__chart-point')?.length ?? 0;
            const idealHeight = count ? 740 : 360;
            const height = Math.min(idealHeight, window.innerHeight - 32);
            geometry.fitTrend.call(this, bounds ?? { width: compact ? window.innerWidth - 16 : 520, height, y: (window.innerHeight - height) / 2 });
            this.$refs.trendDialog?.closest('.rm-modal-shell')?.style.setProperty('--rm-conducta-graph-width', `${this.trendBounds.width}px`);
        },
        openTrend(trigger) {
            if (this.historyOpen || this.clinicalDiscardOpen) return;
            this.trendReturnFocus = trigger;
            this.trendBounds = null;
            this.trendOpen = true;
            this.$nextTick(() => {
                this.fitTrend();
                if (!this.$refs.trendDialog.matches(':popover-open')) this.$refs.trendDialog.showPopover();
                this.$refs.trendDialog.querySelector('[data-trend-move]')?.focus({ preventScroll: true });
            });
        },
        openHistory(trigger = null) {
            if (this.trendOpen) this.closeTrend();
            this.historyReturnFocus = trigger ?? this.$refs.historyTrigger ?? document.activeElement;
            this.historyOpen = true;
            this.$nextTick(() => this.$el.querySelector('.rm-clinical-consultation-panel:popover-open button')?.focus({ preventScroll: true }));
        },
        close() {
            if (this.busy) return;
            if (this.trendOpen) { this.closeTrend(); return; }
            if (this.historyOpen) { this.closeHistory(); return; }
            if (this.clinicalFormDirty) { this.discardIntent = 'close'; this.clinicalDiscardOpen = true; return; }
            this.$wire.cerrarModal();
        },
        closeHistory() {
            this.historyOpen = false;
            this.$nextTick(() => requestAnimationFrame(() => (this.historyReturnFocus ?? this.$refs.historyTrigger)?.focus()));
        },
        clear() {
            if (this.busy) return;
            if (this.trendOpen) this.closeTrend();
            this.discardIntent = 'clear';
            if (this.clinicalFormDirty) this.clinicalDiscardOpen = true;
            else this.$wire.limpiarCaptura();
        },
        async confirmDiscard() {
            this.busy = true;
            try {
                if (this.discardIntent === 'clear') await this.$wire.limpiarCaptura();
                else await this.$wire.cerrarModal();
                this.clinicalFormDirty = false;
                this.clinicalDiscardOpen = false;
            } finally { this.busy = false; }
        },
        async submit() {
            if (this.busy) return;
            this.busy = true;
            try { await this.$wire.guardar(); }
            finally { this.busy = false; }
        },
        saved() {
            this.clinicalFormDirty = false;
            this.clinicalDiscardOpen = false;
            this.$nextTick(() => this.$refs.result?.focus());
        },
        reset() {
            this.clinicalFormDirty = false;
            this.clinicalDiscardOpen = false;
            this.$nextTick(() => {
                this.$root.querySelectorAll('.rm-free-text-select').forEach(field => {
                    const control = window.Alpine?.$data(field);
                    if (control) { control.choice = ''; control.customText = ''; }
                });
                this.$root.querySelector('input[name="conducta-estado_animo"]')?.focus();
            });
        },
        focusCustom(event) {
            if (!['SELECT', 'INPUT'].includes(event.target.tagName) || event.target.value !== '__custom') return;
            const input = event.target.closest('.rm-free-text-select')?.querySelector('input[type="text"]');
            // El selector sincroniza primero su entangle y habilita el campo.
            this.$nextTick(() => requestAnimationFrame(() => {
                if (input && !input.disabled && input.getClientRects().length) input.focus();
            }));
        },
        addText(field, text) {
            if (!['descripcion', 'intervencion', 'respuesta'].includes(field) || this.busy) return;
            const current = String(this.$wire.datos[field] ?? '').trim();
            if (current.split('\n').includes(text)) return;
            this.$wire.$set('datos.' + field, current ? current + '\n' + text : text, false);
            this.clinicalFormDirty = true;
        },
    };
}
if (typeof window !== 'undefined') {
    window.rmConductaRegistro = conductaRegistro;
    window.rmConductaChart = conductaChart;
}
