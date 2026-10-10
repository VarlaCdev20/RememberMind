// Captura descriptiva: ninguna clasificación clínica ni alertas se derivan de EVA.
export const evaNumber = value => {
    if (!['number', 'string'].includes(typeof value) || !/^\d+$/.test(String(value).trim())) return null;
    const number = Number(value);
    return Number.isInteger(number) && number >= 0 && number <= 10 ? number : null;
};
export const painTimestamp = value => {
    if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2})?$/.test(value)) return null;
    const full = value.length === 16 ? `${value}:00` : value;
    const timestamp = Date.parse(`${full}Z`);
    return Number.isFinite(timestamp) && new Date(timestamp).toISOString().slice(0, 19) === full ? timestamp : null;
};
const painText = value => typeof value === 'string' && !/^(null|undefined|NaN)$/i.test(value.trim()) ? value.trim() : '';
export const normalizePainHistory = history => (Array.isArray(history) ? history : [])
    .filter(row => row && evaNumber(row.intensidad) !== null && painTimestamp(row.fecha_hora) !== null)
    .map(row => ({ value: evaNumber(row.intensidad), time: painTimestamp(row.fecha_hora),
        date: `${row.fecha_hora.slice(8, 10)}/${row.fecha_hora.slice(5, 7)}/${row.fecha_hora.slice(0, 4)} ${row.fecha_hora.slice(11, 16)}`,
        code: painText(row.codigo), origin: painText(row.origen),
        location: painText(row.ubicacion), frequency: painText(row.frecuencia), relief: painText(row.factores_alivio),
        intervention: painText(row.intervencion), response: painText(row.respuesta), preview: false }))
    .sort((a, b) => a.time - b.time).slice(-7);

// Reutiliza exclusivamente la geometría y los gestos aprobados de Signos.
// No hereda campos, validación, objetivos ni clasificación del formulario de referencia.
const trendWindow = () => {
    const source = window.rmSignosRegistro([], {}, {});
    const keys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth',
        'trendPointer', 'trendReturnFocus', 'restoringTrendFocus', 'closeTrend', 'fitTrend',
        'trendStyle', 'moveTrend', 'resizeTrend', 'resizeTrendEdge', 'resetTrend',
        'startTrendPointer', 'updateTrendPointer', 'endTrendPointer'];
    return Object.fromEntries(keys.map(key => [key, source[key]]));
};

export function createPainCapture(history, initial, measurementTime, context = {}) {
    return {
        ...trendWindow(),
        values: { ...initial }, initialValues: { ...initial },
        history: normalizePainHistory(history), measurementTime,
        episode: normalizePainHistory(context.episode), origin: context.origin ?? null,
        persisted: context.persisted === true,
        intensityBands: Array.isArray(context.intensityBands) ? context.intensityBands : [],
        zones: [], locationError: '', bodyView: 'front', selectedPoint: null,
        fitTrend(bounds = this.trendBounds) {
            const geometry = window.rmSignosRegistro([], {}, {});
            geometry.fitTrend.call(this, bounds ?? { width: 560, height: window.innerWidth < 1024 ? 280 : 680 });
        },
        init() {
            this.captureShell = this.$el.closest('.rm-modal-shell');
            this.beforeUnloadHandler = event => {
                if (!this.captureShell?.classList.contains('is-open') || !this.isDirty()) return;
                event.preventDefault(); event.returnValue = '';
            };
            this.savingGuard = event => {
                if (!this.captureShell?.querySelector('[wire\\:click="guardarDolor"]:disabled')) return;
                if ((event.type === 'keydown' && event.key === 'Escape' && !this.trendOpen)
                    || (event.type === 'click' && event.target.closest('.rm-modal-panel__close, .rm-modal-panel__back, .rm-modal-shell__overlay, [wire\\:click="cerrarSelectorRegistro"]'))) {
                    event.preventDefault(); event.stopImmediatePropagation();
                }
            };
            this.captureShell?.addEventListener('click', this.savingGuard, true);
            window.addEventListener('keydown', this.savingGuard, true);
            window.addEventListener('beforeunload', this.beforeUnloadHandler);
        },
        destroy() {
            if (this.$refs?.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
            window.removeEventListener('keydown', this.savingGuard, true);
            this.captureShell?.removeEventListener('click', this.savingGuard, true);
        },
        isDirty() { return Object.keys(this.initialValues).some(key => String(this.values[key] ?? '') !== String(this.initialValues[key] ?? '')); },
        locationLength() { return Array.from(this.values.location ?? '').length; },
        locationTokens() { return String(this.values.location ?? '').split(',').map(text => text.trim()); },
        syncManualLocation() {
            this.zones = this.zones.filter(zone => this.locationTokens().includes(zone));
            this.locationError = this.locationLength() > 120 ? 'La localización no puede superar 120 caracteres.' : '';
        },
        setLocation(value) {
            this.values.location = value;
            this.$wire?.$set('dolorUbicacion', value, false);
        },
        toggleZone(label) {
            if (this.zones.includes(label)) { this.removeZone(label); return; }
            const current = String(this.values.location ?? '').trim();
            const next = this.locationTokens().includes(label) ? current : [current, label].filter(Boolean).join(', ');
            if (Array.from(next).length > 120) { this.locationError = 'No se añadió la zona: la localización superaría 120 caracteres.'; return; }
            this.zones.push(label); this.setLocation(next); this.locationError = '';
        },
        removeZone(label) {
            this.zones = this.zones.filter(zone => zone !== label);
            this.setLocation(this.locationTokens().filter(token => token !== label).join(', '));
            this.locationError = '';
        },
        clearZones() { [...this.zones].forEach(zone => this.removeZone(zone)); },
        chooseCaptureOption(key, model, choice) {
            if (choice === '__custom') return;
            const fields = { duration: 'dolorDuracionValor', unit: 'dolorDuracionUnidad', frequency: 'dolorFrecuencia' };
            if (fields[key] !== model) return;
            this.values[key] = choice;
            this.$wire?.$set(model, choice, false);
        },
        previous() { return (this.origin ? this.episode : this.history).at(-1) ?? null; },
        current() { return evaNumber(this.values.eva); },
        intensityPresentation() {
            const value = this.current();
            if (value === null) return { state: 'empty', label: 'Selecciona una intensidad' };
            const band = this.intensityBands.find(band => value >= band.min && value <= band.max);
            return band ? { state: band.state, label: band.label } : { state: 'selected', label: 'Intensidad seleccionada' };
        },
        comparison() {
            if (!this.previous() || this.current() === null) return 'Sin comparación disponible';
            const delta = this.current() - this.previous().value;
            return delta === 0 ? 'Sin cambio' : `${delta > 0 ? '+' : '−'}${Math.abs(delta)} ${Math.abs(delta) === 1 ? 'punto' : 'puntos'}`;
        },
        rows() {
            const time = painTimestamp(this.measurementTime);
            const saved = this.origin ? this.episode : this.history;
            const preview = !this.persisted && this.current() !== null && time !== null;
            return [...saved.slice(preview ? -6 : -7), ...(preview ? [{ value: this.current(), time, date: 'Actual · Sin guardar', location: this.values.location, preview: true }] : [])];
        },
        markers() {
            const rows = this.rows(), times = rows.map(row => row.time);
            if (!times.length) return [];
            const min = Math.min(...times), max = Math.max(...times);
            return rows.map((row, index) => ({ ...row, index,
                x: max === min ? 196 : 44 + ((row.time - min) / (max - min)) * 300,
                y: 170 - row.value * 14, label: `${row.date} · EVA ${row.value} / 10` }));
        },
        historyLine() { return this.markers().filter(row => !row.preview).map(row => `${row.x},${row.y}`).join(' '); },
        selectedMarker() {
            const point = this.selectedPoint;
            return point ? this.markers().find(row => point.preview ? row.preview
                : (point.code ? row.code === point.code : row.time === point.time && row.value === point.value)) ?? null : null;
        },
        previewLine() {
            const markers = this.markers(), preview = markers.find(row => row.preview);
            if (!preview) return '';
            const previous = markers.filter(row => !row.preview).at(-1);
            if (!previous) return '';
            return `${previous.x},${previous.y} ${preview.x},${preview.y}`;
        },
        openTrend(trigger) {
            this.trendReturnFocus = trigger; this.selectedPoint = null; this.trendOpen = true;
            this.$nextTick(() => {
                this.fitTrend();
                if (!this.$refs.trendDialog.matches(':popover-open')) this.$refs.trendDialog.showPopover();
                this.$refs.trendDialog.querySelector('[data-trend-move]')?.focus({ preventScroll: true });
            });
        },
    };
}
window.rmDolorRegistro = createPainCapture;
