import { painTimestamp } from './dolor-registro.js';

// Cantidades descriptivas: vacío no es cero; no se infiere severidad clínica.
export const intakeNumber = (value, maximum = 100) => {
    if (!['string', 'number'].includes(typeof value) || !/^\d+(?:\.\d{1,2})?$/.test(String(value))) return null;
    const number = Number(value);
    return Number.isFinite(number) && number >= 0 && number <= maximum ? number : null;
};
const text = value => typeof value === 'string' && !/^(null|undefined|NaN|Infinity)$/i.test(value.trim()) ? value.trim() : '';
const dateText = value => `${value.slice(8, 10)}/${value.slice(5, 7)}/${value.slice(0, 4)} ${value.slice(11, 16)}`;
export const normalizeIntakeHistory = history => (Array.isArray(history) ? history : [])
    .filter(row => row && painTimestamp(row.fecha_hora) !== null)
    .map(row => ({ code: text(row.codigo), time: painTimestamp(row.fecha_hora),
        date: dateText(row.fecha_hora),
        value: intakeNumber(row.porcentaje), meal: text(row.comida) || 'Sin registrar',
        tolerance: text(row.tolerancia) || 'Sin registrar', swallowing: text(row.deglucion) || 'Sin registrar', preview: false }))
    .sort((a, b) => a.time - b.time).slice(-10);
const fields = { meal: 'ingestaTipoComida', percentage: 'ingestaPorcentaje', tolerance: 'ingestaTolerancia',
    swallowing: 'ingestaDificultadDeglucion', liquids: 'ingestaRegistrarLiquidos', quantity: 'ingestaCantidadMl', observation: 'ingestaObservacion' };

export function createIntakeCapture(history, initial, measurementTime, threshold, persisted = false) {
    const geometry = window.rmDolorRegistro([], {}, '');
    const keys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth', 'trendPointer',
        'trendReturnFocus', 'restoringTrendFocus', 'closeTrend', 'fitTrend', 'trendStyle', 'moveTrend',
        'resizeTrend', 'resizeTrendEdge', 'resetTrend', 'startTrendPointer', 'updateTrendPointer', 'endTrendPointer', 'openTrend'];
    return {
        ...Object.fromEntries(keys.map(key => [key, geometry[key]])),
        values: { ...initial }, initialValues: { ...initial }, history: normalizeIntakeHistory(history),
        measurementTime, threshold: intakeNumber(threshold), persisted, selectedPoint: null,
        init() {
            this.captureShell = this.$el.closest('.rm-modal-shell');
            this.beforeUnloadHandler = event => {
                if (!this.captureShell?.classList.contains('is-open') || !this.isDirty()) return;
                event.preventDefault(); event.returnValue = '';
            };
            this.savingGuard = event => {
                if (!this.captureShell?.querySelector('[wire\\:click="guardarCuidado"]:disabled')) return;
                if ((event.type === 'keydown' && event.key === 'Escape' && !this.trendOpen)
                    || (event.type === 'click' && event.target.closest('.rm-modal-panel__close, .rm-modal-panel__back, .rm-modal-shell__overlay, [wire\\:click="cerrarSelectorRegistro"]'))) {
                    event.preventDefault(); event.stopImmediatePropagation();
                }
            };
            this.captureShell?.addEventListener('click', this.savingGuard, true);
            this.clearFocusHandler = () => this.$nextTick(() => this.$el.querySelector('.rm-clinical-choice')?.focus());
            window.addEventListener('registro-campos-limpiados', this.clearFocusHandler);
            window.addEventListener('keydown', this.savingGuard, true);
            window.addEventListener('beforeunload', this.beforeUnloadHandler);
        },
        destroy() {
            if (this.$refs?.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
            window.removeEventListener('registro-campos-limpiados', this.clearFocusHandler);
            window.removeEventListener('keydown', this.savingGuard, true);
            this.captureShell?.removeEventListener('click', this.savingGuard, true);
        },
        isDirty() { return !this.persisted && Object.keys(this.initialValues).some(key => String(this.values[key] ?? '') !== String(this.initialValues[key] ?? '')); },
        setValue(key, value) {
            if (!fields[key]) return;
            this.values[key] = value; this.$wire?.$set(fields[key], value, false);
            if (key === 'liquids' && !value) this.setValue('quantity', '');
        },
        current() { return intakeNumber(this.values.percentage); },
        percentageInvalid() { return this.values.percentage !== '' && this.values.percentage !== null && this.current() === null; },
        lowPreview() { return this.current() !== null && this.threshold !== null && this.current() < this.threshold; },
        previous() { return this.history.at(-1) ?? null; },
        comparison() {
            if (this.previous()?.value === null || !this.previous() || this.current() === null) return 'Sin comparación disponible';
            const delta = Math.round((this.current() - this.previous().value) * 100) / 100;
            return `${delta > 0 ? '+' : ''}${delta} puntos porcentuales`;
        },
        rows() {
            const time = painTimestamp(this.measurementTime);
            return [...this.history.filter(row => row.value !== null), ...(!this.persisted && this.current() !== null && time !== null
                ? [{ value: this.current(), time, date: `${dateText(this.measurementTime)} · Sin guardar`, meal: text(this.values.meal), preview: true }] : [])];
        },
        markers() {
            const rows = this.rows(), times = rows.map(row => row.time);
            if (!times.length) return [];
            const min = Math.min(...times), max = Math.max(...times);
            return rows.map((row, index) => ({ ...row, index,
                x: max === min ? 196 : 44 + ((row.time - min) / (max - min)) * 300,
                y: 170 - row.value * 1.4, label: `${row.date} · ${row.meal || 'Comida sin seleccionar'} · ${row.value} %${row.preview ? ' · Vista previa' : ''}` }));
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
window.rmIngestaRegistro = createIntakeCapture;
