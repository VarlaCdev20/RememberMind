// Vista local de las mediciones del formulario. La validación y autoría definitivas
// siguen en SignosVitalesService; aquí no se definen umbrales clínicos.
window.rmSignosRegistro = function (history, initial) {
    const metadata = {
        pa: { label: 'presión arterial', unit: 'mmHg', fields: ['sis', 'dia'] },
        fc: { label: 'pulso', unit: 'lpm', fields: ['fc'] },
        fr: { label: 'respiración', unit: 'rpm', fields: ['fr'] },
        temp: { label: 'temperatura', unit: '°C', fields: ['temp'] },
        sat: { label: 'SpO₂', unit: '%', fields: ['sat'] },
        glucosa: { label: 'glucemia', unit: 'mg/dL', fields: ['glucosa'] },
    };
    const order = { pa: 10, fc: 20, fr: 30, temp: 40, sat: 50, glucosa: 60 };
    const technicalMax = { sis: 999, dia: 999, fc: 9999, fr: 9999, temp: 999.9, sat: 100, glucosa: 999999.99 };

    return {
        values: { ...initial },
        touched: {},
        errors: {},
        history: Array.isArray(history) ? history : [],
        active: null,
        trendOpen: true,
        meta: metadata,
        focus(key) {
            this.active = key === 'sis' || key === 'dia' ? 'pa' : key;
            this.trendOpen = true;
        },
        activeOrder() { return this.active ? order[this.active] + 1 : 70; },
        validate(key) {
            this.touched[key] = true;
            const raw = String(this.values[key] ?? '').trim();
            let error = '';
            if (raw !== '') {
                const integer = ['sis', 'dia', 'fc', 'fr'].includes(key);
                const decimals = key === 'temp' ? 1 : 2;
                const pattern = integer ? /^\d+$/ : new RegExp('^' + (key === 'temp' ? '-?' : '') + '\\d+(?:\\.\\d{1,' + decimals + '})?$');
                if (!pattern.test(raw)) error = raw.startsWith('-') && key !== 'temp'
                    ? (key === 'sat' ? 'La saturación no puede ser menor a 0 %.' : 'El valor no puede ser negativo.')
                    : 'Ingresa un valor numérico válido.';
                else if (key === 'sat' && Number(raw) > 100) error = 'La saturación no puede superar el 100 %.';
                else if (key === 'temp' && Number(raw) < -99.9) error = 'El valor está fuera de la capacidad permitida.';
                else if (Number(raw) > technicalMax[key]) error = 'El valor supera la capacidad permitida.';
            }
            this.errors[key] = error;
            if (key === 'sis' || key === 'dia') {
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
        validPreview(key) {
            const fields = metadata[key]?.fields ?? [];
            if (!fields.length || fields.some(field => this.hasError(field))) return false;
            if (fields.some(field => String(this.values[field] ?? '').trim() === '')) return false;
            return fields.every(field => {
                const raw = String(this.values[field]).trim();
                const integer = ['sis', 'dia', 'fc', 'fr'].includes(field);
                const precision = field === 'temp' ? 1 : 2;
                const pattern = integer ? /^\d+$/ : new RegExp('^' + (field === 'temp' ? '-?' : '') + '\\d+(?:\\.\\d{1,' + precision + '})?$');
                const value = Number(raw);
                return pattern.test(raw) && value <= technicalMax[field] && (field === 'temp' ? value >= -99.9 : value >= 0);
            });
        },
        valueOf(row, key) {
            if (key === 'pa') return row.sis !== null && row.sis !== undefined && row.dia !== null && row.dia !== undefined
                ? Number(row.sis) : null;
            return row[key] !== null && row[key] !== undefined ? Number(row[key]) : null;
        },
        labelOf(row, key) {
            return key === 'pa' ? `${row.sis}/${row.dia}` : String(row[key]);
        },
        previewLabel(key) {
            if (!this.validPreview(key)) return '';
            return key === 'pa' ? `${this.values.sis}/${this.values.dia}` : String(this.values[key]);
        },
        records(key) {
            return this.history.filter(row => this.valueOf(row, key) !== null).slice(0, 5);
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
            const rows = this.records(key).slice(0, preview ? 4 : 5).reverse().map(row => ({ value: this.valueOf(row, key), label: this.labelOf(row, key), date: row.fecha, preview: false }));
            if (preview) rows.push({ value: key === 'pa' ? Number(this.values.sis) : Number(this.values[key]), label: this.previewLabel(key), date: 'Sin guardar', preview: true });
            return rows;
        },
        chartPoints(key) {
            const rows = this.chartRows(key);
            if (rows.length < 3) return '';
            const values = rows.map(row => row.value);
            const min = Math.min(...values), max = Math.max(...values);
            const spread = max - min || 1;
            return rows.map((row, i) => `${18 + i * (264 / (rows.length - 1))},${116 - ((row.value - min) / spread) * 84}`).join(' ');
        },
        chartMarkers(key) {
            const rows = this.chartRows(key);
            if (rows.length < 3) return [];
            const values = rows.map(row => row.value);
            const min = Math.min(...values), max = Math.max(...values);
            const spread = max - min || 1;
            return rows.map((row, i) => ({ ...row, x: 18 + i * (264 / (rows.length - 1)), y: 116 - ((row.value - min) / spread) * 84 }));
        },
        review() {
            const invalid = this.$el.querySelector('[aria-invalid="true"]');
            (invalid || this.$el.querySelector('.rm-signos__card--active input'))?.focus();
        },
    };
};
