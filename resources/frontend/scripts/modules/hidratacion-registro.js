import { painTimestamp } from './dolor-registro.js';

const cleanText = value => typeof value === 'string' && !/^(null|undefined|NaN|Infinity|\[object Object\])$/i.test(value.trim()) ? value.trim() : '';
const dateText = value => `${value.slice(8, 10)}/${value.slice(5, 7)}/${value.slice(0, 4)} ${value.slice(11, 16)}`;
export const hydrationNumber = value => {
    if (!['string', 'number'].includes(typeof value) || !/^\d+(?:\.\d{1,2})?$/.test(String(value))) return null;
    const number = Number(value);
    return Number.isFinite(number) && number >= 0 && number <= 999999.99 ? number : null;
};
const toleranceLabels = new Map([['ADECUADA', 'Adecuada'], ['PARCIAL', 'Parcial'], ['RECHAZO', 'Rechazo'], ['NAUSEAS', 'Náuseas']]);
export const toleranceLabel = value => toleranceLabels.get(value) ?? 'Sin registrar';
export const normalizeHydrationHistory = history => (Array.isArray(history) ? history : [])
    .filter(row => row && painTimestamp(row.fecha_hora) !== null && hydrationNumber(row.volumen) !== null)
    .map(row => ({ code: cleanText(row.codigo), time: painTimestamp(row.fecha_hora), date: dateText(row.fecha_hora),
        value: hydrationNumber(row.volumen), liquid: cleanText(row.tipo) || 'Sin registrar',
        tolerance: toleranceLabel(row.tolerancia) !== 'Sin registrar' ? toleranceLabel(row.tolerancia) : cleanText(row.tolerancia) || 'Sin registrar', preview: false }))
    .sort((a, b) => a.time - b.time || a.code.localeCompare(b.code)).slice(-10);
const fields = { quantity: 'hidratacionCantidad', liquid: 'hidratacionTipo', tolerance: 'hidratacionTolerancia', observation: 'hidratacionObservacion' };

export function createHydrationCapture(history, initial, measurementTime, persisted = false) {
    // Solo geometría e interacción de la ventana clínica compartida; ninguna regla de Dolor.
    const geometry = window.rmDolorRegistro([], {}, '');
    const keys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth', 'trendPointer',
        'trendReturnFocus', 'restoringTrendFocus', 'closeTrend', 'fitTrend', 'trendStyle', 'moveTrend',
        'resizeTrend', 'resizeTrendEdge', 'resetTrend', 'startTrendPointer', 'updateTrendPointer', 'endTrendPointer', 'openTrend'];
    return {
        ...Object.fromEntries(keys.map(key => [key, geometry[key]])),
        values: { ...initial }, initialValues: { ...initial }, history: normalizeHydrationHistory(history),
        measurementTime, persisted, selectedPoint: null, toleranceLabel,
        init() {
            this.captureShell = this.$el.closest('.rm-modal-shell');
            this.beforeUnloadHandler = event => {
                if (!this.captureShell?.classList.contains('is-open') || !this.isDirty()) return;
                event.preventDefault(); event.returnValue = '';
            };
            this.savingGuard = event => {
                if (!this.captureShell?.querySelector('[wire\\:click="guardarHidratacion"]:disabled')) return;
                if ((event.type === 'keydown' && event.key === 'Escape' && !this.trendOpen)
                    || (event.type === 'click' && event.target.closest('.rm-modal-panel__close, .rm-modal-panel__back, .rm-modal-shell__overlay, [wire\\:click="cerrarSelectorRegistro"]'))) {
                    event.preventDefault(); event.stopImmediatePropagation();
                }
            };
            this.clearFocusHandler = () => this.$nextTick(() => this.$el.querySelector('#hidratacion-volumen')?.focus());
            this.captureShell?.addEventListener('click', this.savingGuard, true);
            window.addEventListener('keydown', this.savingGuard, true);
            window.addEventListener('beforeunload', this.beforeUnloadHandler);
            window.addEventListener('registro-campos-limpiados', this.clearFocusHandler);
        },
        destroy() {
            if (this.$refs?.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
            window.removeEventListener('keydown', this.savingGuard, true);
            window.removeEventListener('registro-campos-limpiados', this.clearFocusHandler);
            this.captureShell?.removeEventListener('click', this.savingGuard, true);
        },
        isDirty() { return !this.persisted && Object.keys(this.initialValues).some(key => String(this.values[key] ?? '') !== String(this.initialValues[key] ?? '')); },
        setValue(key, value) {
            if (!fields[key]) return;
            this.values[key] = value; this.$wire?.$set(fields[key], value, false);
        },
        current() {
            const number = hydrationNumber(this.values.quantity);
            return number !== null && Number.isInteger(number) && number >= 1 && number <= 10000 ? number : null;
        },
        quantityInvalid() { return this.values.quantity !== '' && this.values.quantity != null && this.current() === null; },
        previous() { return this.history.at(-1) ?? null; },
        comparison() {
            if (!this.previous() || this.current() === null) return 'Sin comparación disponible';
            const delta = Math.round((this.current() - this.previous().value) * 100) / 100;
            return delta === 0 ? 'Sin cambio' : `${delta > 0 ? '+' : ''}${delta} mL`;
        },
        rows() {
            const time = painTimestamp(this.measurementTime);
            return [...this.history.slice(-6), ...(!this.persisted && this.current() !== null && time !== null
                ? [{ value: this.current(), time, date: dateText(this.measurementTime), liquid: cleanText(this.values.liquid) || 'Sin registrar', tolerance: toleranceLabel(this.values.tolerance), preview: true }] : [])];
        },
        axisMaximum() {
            const maximum = Math.max(1, ...this.rows().map(row => row.value));
            const magnitude = 10 ** Math.floor(Math.log10(maximum));
            return Math.ceil(maximum * 1.1 / magnitude) * magnitude;
        },
        ticks() { return Array.from({ length: 5 }, (_, i) => ({ value: Math.round(this.axisMaximum() * i / 4 * 100) / 100, y: 170 - i * 35 })); },
        markers() {
            const rows = this.rows(), times = rows.map(row => row.time);
            if (!times.length) return [];
            const min = Math.min(...times), max = Math.max(...times);
            return rows.map((row, index) => ({ ...row, index,
                x: max === min ? 196 : 48 + ((row.time - min) / (max - min)) * 296,
                y: 170 - row.value / this.axisMaximum() * 140,
                label: `${row.liquid} · ${row.value} mL · ${row.date} · ${row.tolerance}${row.preview ? ' · Sin guardar' : ''}` }));
        },
        historyLine() { return this.markers().filter(row => !row.preview).map(row => `${row.x},${row.y}`).join(' '); },
        previewLine() {
            const markers = this.markers(), preview = markers.find(row => row.preview), previous = markers.filter(row => !row.preview).at(-1);
            return preview && previous ? `${previous.x},${previous.y} ${preview.x},${preview.y}` : '';
        },
        selectedMarker() {
            return this.selectedPoint ? this.markers().find(row => this.selectedPoint.preview ? row.preview
                : row.code === this.selectedPoint.code && row.time === this.selectedPoint.time) ?? null : null;
        },
    };
}
window.rmHidratacionRegistro = createHydrationCapture;
