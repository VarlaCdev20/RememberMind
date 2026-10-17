import { conductaRegistro } from './conducta-registro.js';

export const cognitiveTextFields = ['orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje'];
export const cognitiveSignalFields = ['sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo'];

export function cognitiveProgress(data = {}) {
    return [...cognitiveTextFields, ...cognitiveSignalFields].filter(key => data[key] !== null && data[key] !== undefined && data[key] !== '').length;
}

export function cognitiveResponse(field, value, options = {}) {
    if (value === null || value === undefined || value === '') return 'No observado';
    if (cognitiveSignalFields.includes(field)) return value === true || value === 1 || value === '1' ? 'Sí' : 'No';
    return options[field]?.[value] ?? String(value);
}

/** Comparación literal, nunca convierte categorías en un puntaje ordinal. */
export function cognitiveComparison(previous, current) {
    if (previous === null || previous === undefined || previous === '' || current === null || current === undefined || current === '') return 'Sin datos comparables';
    return previous === current ? 'Misma respuesta registrada' : 'Respuestas diferentes; revisar el contexto';
}

export function observacionCognitiva(fields = {}, options = {}) {
    const base = conductaRegistro();
    const geometry = window.rmSignosRegistro([], {}, {});
    return {
        ...base, fields, options, domain: 'orientacion', indicator: 'orientacion_persona', selectedRecord: null, transportError: '',
        get answered() { return cognitiveProgress(this.$wire.datos); },
        get domainFields() { return Object.keys(this.fields).filter(key => this.fields[key][3] === this.domain); },
        focusSection(id) {
            const section = this.$root.querySelector(`#${id}`);
            if (!section) return;
            if (section.tagName === 'DETAILS') section.open = true;
            this.$nextTick(() => {
                (section.querySelector('summary, [data-section-focus]'))?.focus({ preventScroll: true });
                section.scrollIntoView({ block: 'start', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
            });
        },
        response(field, value) { return cognitiveResponse(field, value, this.options); },
        count(keys) { return cognitiveProgress(Object.fromEntries(keys.map(key => [key, this.$wire.datos[key]]))); },
        domainSummary(domain) {
            const keys = Object.keys(this.fields).filter(key => this.fields[key][3] === domain);
            const answered = keys.filter(key => this.$wire.datos[key] !== null && this.$wire.datos[key] !== undefined && this.$wire.datos[key] !== '');
            if (!answered.length) return 'Sin evaluar';
            return answered.map(key => `${this.fields[key][0]}: ${this.response(key, this.$wire.datos[key])}`).join(' · ');
        },
        chooseDomain(domain) {
            this.domain = domain;
            this.indicator = this.domainFields[0];
            this.selectedRecord = null;
        },
        comparison(previous) { return cognitiveComparison(previous, this.$wire.datos[this.indicator]); },
        fitTrend(bounds = this.trendBounds) {
            const compact = window.innerWidth < 1024;
            geometry.fitTrend.call(this, bounds ?? (compact ? {width: window.innerWidth - 16, height: Math.min(700, window.innerHeight - 16), y: 8} : null));
        },
        openTrend(trigger) {
            if (this.historyOpen || this.clinicalDiscardOpen) return;
            this.trendReturnFocus = trigger;
            this.trendOpen = true;
            this.$nextTick(() => {
                this.fitTrend();
                if (!this.$refs.trendDialog.matches(':popover-open')) this.$refs.trendDialog.showPopover();
                this.$refs.trendDialog.querySelector('[data-trend-move]')?.focus({ preventScroll: true });
            });
        },
        clear() {
            if (this.busy) return;
            if (this.trendOpen) this.closeTrend();
            this.discardIntent = 'clear';
            this.clinicalDiscardOpen = true;
        },
        reset() {
            this.transportError = '';
            this.clinicalFormDirty = false;
            this.clinicalDiscardOpen = false;
            this.selectedRecord = null;
            this.$nextTick(() => this.$root.querySelector('#cognicion-orientacion_persona')?.focus());
        },
        validationFocus() {
            this.$nextTick(() => {
                const invalid = this.$root.querySelector('[aria-invalid="true"], [data-capture-error]');
                const section = invalid?.closest('details');
                if (section) section.open = true;
                invalid?.focus();
            });
        },
        async submit() {
            if (this.busy) return;
            this.busy = true;
            this.transportError = '';
            try { await this.$wire.guardar(); }
            catch { this.transportError = 'No se pudo contactar con el servidor. La captura se conserva; vuelve a intentarlo.'; }
            finally { this.busy = false; }
        },
    };
}

if (typeof window !== 'undefined') window.rmObservacionCognitiva = observacionCognitiva;
