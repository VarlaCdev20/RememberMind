// Vista local de las mediciones del formulario. La validación y autoría definitivas
// siguen en SignosVitalesService; aquí no se definen umbrales clínicos.
window.rmSignosRegistro = function (history, initial, limits, objectiveBands = {}, clinicalTones = {}) {
    const metadata = {
        pa: { label: 'presión arterial', unit: 'mmHg', fields: ['sis', 'dia'] },
        fc: { label: 'pulso', unit: 'lpm', fields: ['fc'] },
        fr: { label: 'respiración', unit: 'rpm', fields: ['fr'] },
        temp: { label: 'temperatura', unit: '°C', fields: ['temp'] },
        sat: { label: 'SpO₂', unit: '%', fields: ['sat'] },
        glucosa: { label: 'glucemia', unit: 'mg/dL', fields: ['glucosa'] },
    };
    const finite = value => {
        if (typeof value !== 'number' && typeof value !== 'string') return null;
        const raw = String(value).trim();
        if (!/^-?\d+(?:\.\d+)?$/.test(raw)) return null;
        const parsed = Number(raw);
        return Number.isFinite(parsed) ? parsed : null;
    };
    const safeDate = value => {
        const date = typeof value === 'string' ? value.trim() : '';
        return date && !/^(null|undefined|nan|infinity)$/i.test(date) ? date : 'Registro previo';
    };
    const historyFields = ['sis', 'dia', 'fc', 'fr', 'temp', 'sat', 'glucosa'];
    const normalizedHistory = (Array.isArray(history) ? history : []).map(row => {
        const clean = { fecha: safeDate(row?.fecha) };
        historyFields.forEach(field => { clean[field] = finite(row?.[field]); });
        return clean;
    }).filter(row => historyFields.some(field => row[field] !== null));

    return {
        values: { ...initial },
        initialValues: { ...initial },
        limits,
        objectiveBands,
        clinicalTones,
        evaluatedValues: Object.keys(clinicalTones).length ? { ...initial } : {},
        touched: {},
        errors: {},
        history: normalizedHistory,
        active: 'pa',
        trendOpen: true,
        meta: metadata,
        init() {
            this.beforeUnloadHandler = (event) => {
                const modalOpen = this.$el.closest('.rm-modal-shell')?.classList.contains('is-open');
                if (!modalOpen || !this.isDirty()) return;
                event.preventDefault();
                event.returnValue = '';
            };
            window.addEventListener('beforeunload', this.beforeUnloadHandler);
        },
        destroy() {
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        },
        isDirty() {
            return Object.keys(this.initialValues).some(key =>
                String(this.values[key] ?? '') !== String(this.initialValues[key] ?? '')
            );
        },
        focus(key) {
            this.active = key === 'sis' || key === 'dia' ? 'pa' : key;
            this.trendOpen = true;
        },
        technicalError(key) {
            const raw = String(this.values[key] ?? '').trim();
            if (raw === '') return '';
            const limit = this.limits[key];
            if (!limit) return '';
            const decimals = Number(limit.decimales);
            const pattern = decimals === 0 ? /^\d+$/ : new RegExp('^\\d+(?:\\.\\d{1,' + decimals + '})?$');
            if (!pattern.test(raw) || finite(raw) === null) return 'Revisa el valor ingresado. Introduce un número válido.';
            const value = finite(raw);
            if (value < Number(limit.min) || value > Number(limit.max)) {
                if (key === 'sat' && value > 100) return 'La saturación de oxígeno no puede superar 100 %.';
                if (key === 'temp') return 'Revisa el valor ingresado. Está fuera del intervalo admitido para una medición de temperatura corporal. Comprueba que no falte un dígito.';
                return `Revisa el valor ingresado. El intervalo técnico admitido es ${limit.min}–${limit.max}.`;
            }
            return '';
        },
        validate(key, checkPair = true) {
            this.touched[key] = true;
            const raw = String(this.values[key] ?? '').trim();
            const error = this.technicalError(key);
            this.errors[key] = error;
            if (checkPair && (key === 'sis' || key === 'dia')) {
                const other = key === 'sis' ? 'dia' : 'sis';
                const otherRaw = String(this.values[other] ?? '').trim();
                if (raw === '' && otherRaw !== '') {
                    this.errors[key] = key === 'dia' ? 'Completa también la presión diastólica.' : 'Completa también la presión sistólica.';
                } else if (otherRaw === '' && raw !== '' && !error) {
                    this.errors[other] = other === 'dia' ? 'Completa también la presión diastólica.' : 'Completa también la presión sistólica.';
                } else if (this.errors[other]?.startsWith('Completa también la presión')) {
                    this.errors[other] = '';
                }
            }
        },
        hasError(key) { return Boolean(this.errors[key]); },
        hasAnyError() { return Object.values(this.errors).some(Boolean); },
        hasCardError(key) { return (metadata[key]?.fields ?? []).some(field => this.hasError(field) || Boolean(this.technicalError(field))); },
        hasEntered(key) {
            return (metadata[key]?.fields ?? []).some(field => String(this.values[field] ?? '').trim() !== '');
        },
        evaluationCurrent(key) {
            const fields = metadata[key]?.fields ?? [];
            return fields.length > 0 && fields.every(field =>
                String(this.values[field] ?? '').trim() === String(this.evaluatedValues[field] ?? '').trim()
            ) && this.clinicalTones?.[key] !== undefined;
        },
        syncEvaluation(detail) {
            const priority = { neutral: 0, success: 1, target: 2, warning: 3, high: 4, danger: 5 };
            const tones = {};
            const keys = {
                presion_arterial: 'pa', presion_sistolica: 'pa', presion_diastolica: 'pa',
                frecuencia_cardiaca: 'fc', frecuencia_respiratoria: 'fr', temperatura: 'temp',
                saturacion_oxigeno: 'sat', glucemia: 'glucosa',
            };
            const levels = {
                NORMAL: 'success', OBJETIVO_PERSONALIZADO: 'target',
                ADVERTENCIA: 'warning', ALTO: 'high', CRITICO: 'danger',
            };
            for (const result of detail?.evaluacion?.resultados ?? []) {
                const key = keys[result.variable];
                if (!key) continue;
                const tone = levels[result.severidad] ?? (result.comportamiento_alerta === 'SUGERIR' ? 'warning' : 'neutral');
                if ((priority[tone] ?? 0) >= (priority[tones[key]] ?? -1)) tones[key] = tone;
            }
            this.clinicalTones = tones;
            this.evaluatedValues = { ...(detail?.valores ?? {}) };
        },
        toneOf(key) { return this.evaluationCurrent(key) ? this.clinicalTones[key] : 'neutral'; },
        toneLabel(key) {
            if (!this.evaluationCurrent(key)) return 'Evaluando lectura';
            return ({ success: 'Normal', target: 'En objetivo', warning: 'Advertencia', high: 'Alto', danger: 'Crítico' })[this.toneOf(key)] ?? 'Sin clasificación adicional';
        },
        validPreview(key) {
            const fields = metadata[key]?.fields ?? [];
            if (!fields.length || fields.some(field => this.hasError(field))) return false;
            if (fields.some(field => String(this.values[field] ?? '').trim() === '')) return false;
            return fields.every(field => !this.technicalError(field));
        },
        valueOf(row, key) {
            if (key === 'pa') return finite(row?.sis) !== null && finite(row?.dia) !== null ? finite(row.sis) : null;
            return finite(row?.[key]);
        },
        diastolicOf(row) { return finite(row?.dia); },
        labelOf(row, key) {
            if (this.valueOf(row, key) === null) return '';
            return key === 'pa' ? `${finite(row.sis)}/${finite(row.dia)}` : String(this.valueOf(row, key));
        },
        previewLabel(key) {
            if (!this.validPreview(key)) return '';
            return key === 'pa' ? `${this.values.sis}/${this.values.dia}` : String(this.values[key]);
        },
        records(key) {
            return this.history.filter(row => this.valueOf(row, key) !== null).slice(0, 5);
        },
        emptyHistoryMessage(key) {
            const records = this.records(key);
            if (records.length === 1) return `Última medición: ${records[0].fecha || 'registro previo'} · ${this.labelOf(records[0], key)} ${metadata[key]?.unit ?? ''}.`;
            return `No hay mediciones anteriores de ${metadata[key]?.label ?? 'este parámetro'}.`;
        },
        previous(key) { return this.records(key)[0] ?? null; },
        change(key) {
            const previous = this.previous(key);
            if (!previous || !this.validPreview(key)) return '';
            if (key === 'pa') {
                const sis = Number(this.values.sis) - Number(previous.sis);
                const dia = Number(this.values.dia) - Number(previous.dia);
                return `${sis > 0 ? '+' : ''}${sis} / ${dia > 0 ? '+' : ''}${dia} mmHg`;
            }
            const decimals = key === 'temp' ? 1 : key === 'sat' || key === 'glucosa' ? 2 : 0;
            const delta = Number((Number(this.values[key]) - this.valueOf(previous, key)).toFixed(decimals));
            return `${delta > 0 ? '+' : ''}${delta} ${metadata[key].unit}`;
        },
        chartRows(key) {
            const preview = this.validPreview(key);
            const rows = this.records(key).slice(0, preview ? 4 : 5).reverse().map(row => ({
                value: this.valueOf(row, key), dia: key === 'pa' ? this.diastolicOf(row) : null,
                label: this.labelOf(row, key), date: row.fecha || 'Registro previo', preview: false,
            }));
            if (preview) rows.push({
                value: key === 'pa' ? finite(this.values.sis) : finite(this.values[key]),
                dia: key === 'pa' ? finite(this.values.dia) : null,
                label: this.previewLabel(key), date: 'Ahora · sin guardar', preview: true,
            });
            return rows.filter(row => Number.isFinite(row.value) && (key !== 'pa' || Number.isFinite(row.dia)));
        },
        chartRange(key) {
            const rows = this.chartRows(key);
            const values = rows.flatMap(row => key === 'pa' ? [row.value, row.dia] : [row.value]);
            this.availableBands(key).forEach(band => values.push(band.min, band.max));
            if (!values.length) return { min: 0, spread: 1 };
            const min = Math.min(...values), max = Math.max(...values);
            return { min, spread: max - min || 1 };
        },
        availableBands(key) {
            return (key === 'pa' ? ['sis', 'dia'] : [key]).flatMap(field => {
                const band = this.objectiveBands?.[field];
                const min = finite(band?.min), max = finite(band?.max);
                return min !== null && max !== null && max > min ? [{ field, min, max }] : [];
            });
        },
        chartBands(key) {
            if (!this.chartRows(key).length) return [];
            const { min, spread } = this.chartRange(key);
            return this.availableBands(key).map(band => ({
                ...band,
                y: 116 - ((band.max - min) / spread) * 84,
                height: ((band.max - band.min) / spread) * 84,
                label: `Objetivo médico ${band.min}–${band.max} ${metadata[key].unit}`,
            }));
        },
        chartPoints(key, series = 'value') {
            const rows = this.chartRows(key);
            if (rows.length < 2) return '';
            const { min, spread } = this.chartRange(key);
            return rows.map((row, i) => `${18 + i * (264 / (rows.length - 1))},${116 - ((row[series] - min) / spread) * 84}`).join(' ');
        },
        chartMarkers(key, series = 'value') {
            const rows = this.chartRows(key);
            if (!rows.length) return [];
            const { min, spread } = this.chartRange(key);
            return rows.map((row, i) => ({
                ...row, x: rows.length === 1 ? 150 : 18 + i * (264 / (rows.length - 1)),
                y: 116 - ((row[series] - min) / spread) * 84,
                markerLabel: `${key === 'pa' ? `${series === 'dia' ? 'Diastólica' : 'Sistólica'} ${row[series]} mmHg` : `${row.label} ${metadata[key].unit}`}${row.preview && this.toneOf(key) !== 'neutral' ? ` · ${this.toneLabel(key)}` : ''}`,
            }));
        },
        review() {
            const invalid = this.$el.querySelector('[aria-invalid="true"]');
            (invalid || this.$el.querySelector('.rm-signos__card--active input'))?.focus();
        },
    };
};
