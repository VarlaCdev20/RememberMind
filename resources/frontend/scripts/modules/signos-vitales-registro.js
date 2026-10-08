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
    // Los timestamps del DTO usan la misma zona institucional, sin offset.
    // UTC aquí conserva sus intervalos y evita que la zona del navegador los altere.
    const timestamp = value => {
        if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2})?$/.test(value)) return null;
        const full = value.length === 16 ? value + ':00' : value;
        const parsed = Date.parse(full + 'Z');
        return Number.isFinite(parsed) && new Date(parsed).toISOString().slice(0, 19) === full ? parsed : null;
    };
    const formatNumber = value => Number.isFinite(value) ? String(Number(value.toFixed(3))) : '';
    const tonesFor = results => {
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
        for (const result of results ?? []) {
            const key = keys[result.variable];
            if (!key) continue;
            const tone = levels[result.severidad] ?? (result.comportamiento_alerta === 'SUGERIR' ? 'warning' : 'neutral');
            if ((priority[tone] ?? 0) >= (priority[tones[key]] ?? -1)) tones[key] = tone;
        }
        return tones;
    };
    const historyFields = ['sis', 'dia', 'fc', 'fr', 'temp', 'sat', 'glucosa'];
    const normalizedHistory = (Array.isArray(history) ? history : []).map(row => {
        const clean = { fecha: safeDate(row?.fecha), fecha_hora: typeof row?.fecha_hora === 'string' ? row.fecha_hora : undefined, tones: tonesFor(row?.resultados), alerta_estado: typeof row?.alerta_estado === 'string' ? row.alerta_estado : undefined };
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
        captureCleared: false,
        selectedPoint: null,
        plot: { left: 48, right: 336, top: 28, bottom: 160 },
        history: normalizedHistory,
        active: 'pa',
        trendOpen: false,
        trendBounds: null,
        trendViewportCompact: null,
        trendViewportWidth: null,
        trendPointer: null,
        trendReturnFocus: null,
        restoringTrendFocus: false,
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
            if (this.$refs?.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        },
        openTrend(key = this.active, trigger = null) {
            key = key === 'sis' || key === 'dia' ? 'pa' : key;
            if (!this.meta[key]) return;
            this.active = key;
            this.trendReturnFocus = trigger;
            this.selectedPoint = null;
            this.trendOpen = true;
            this.$nextTick(() => {
                this.fitTrend();
                if (!this.$refs.trendDialog.matches(':popover-open')) this.$refs.trendDialog.showPopover();
                if (trigger?.matches?.('input') && window.innerWidth < 1024) trigger.scrollIntoView({ block: 'start', behavior: 'auto' });
            });
        },
        closeTrend() {
            this.$refs.trendDialog.hidePopover();
            this.trendOpen = false;
            this.trendPointer = null;
            this.selectedPoint = null;
            this.$nextTick(() => {
                this.restoringTrendFocus = true;
                this.trendReturnFocus?.focus({ preventScroll: true });
                this.restoringTrendFocus = false;
            });
        },
        fitTrend(bounds = this.trendBounds) {
            const edge = 8;
            const compact = window.innerWidth < 1024;
            if (this.trendViewportCompact !== null && compact !== this.trendViewportCompact) bounds = null;
            this.trendViewportCompact = compact;
            if (bounds && this.trendViewportWidth !== null && this.trendViewportWidth !== window.innerWidth) {
                bounds = { ...bounds, x: window.innerWidth - bounds.width - 20 };
            }
            this.trendViewportWidth = window.innerWidth;
            const maxWidth = Math.max(0, window.innerWidth - edge * 2);
            const maxHeight = Math.max(0, window.innerHeight - edge * 2);
            const width = Math.min(maxWidth, Math.max(Math.min(360, maxWidth), bounds?.width ?? 440));
            const height = Math.min(maxHeight, Math.max(Math.min(240, maxHeight), bounds?.height ?? (compact ? 280 : 680)));
            this.trendBounds = {
                width, height,
                x: Math.max(edge, Math.min(bounds?.x ?? window.innerWidth - width - 20, window.innerWidth - width - edge)),
                y: Math.max(edge, Math.min(bounds?.y ?? (compact ? window.innerHeight - height - edge : (window.innerHeight - height) / 2), window.innerHeight - height - edge)),
            };
            const shell = this.$el?.closest('.rm-modal-shell');
            shell?.style.setProperty('--rm-signos-graph-width', `${width}px`);
            shell?.style.setProperty('--rm-signos-graph-height', `${height}px`);
        },
        trendStyle() {
            if (!this.trendBounds) return '';
            const { x, y, width, height } = this.trendBounds;
            return `inset: auto; margin: 0; left: ${x}px; top: ${y}px; width: ${width}px; height: ${height}px;`;
        },
        moveTrend(dx, dy) {
            if (!this.trendBounds) this.fitTrend();
            this.fitTrend({ ...this.trendBounds, x: this.trendBounds.x + dx, y: this.trendBounds.y + dy });
        },
        resizeTrend(dw, dh) {
            if (!this.trendBounds) this.fitTrend();
            this.fitTrend({ ...this.trendBounds, width: this.trendBounds.width + dw, height: this.trendBounds.height + dh });
        },
        resizeTrendEdge(edge, dx, dy) {
            if (!this.trendBounds) this.fitTrend();
            const bounds = this.trendBounds;
            const minWidth = Math.min(360, window.innerWidth - 16);
            const minHeight = Math.min(240, window.innerHeight - 16);
            let left = bounds.x, right = bounds.x + bounds.width;
            let top = bounds.y, bottom = bounds.y + bounds.height;
            if (edge.includes('w')) left = Math.max(8, Math.min(left + dx, right - minWidth));
            if (edge.includes('e')) right = Math.min(window.innerWidth - 8, Math.max(right + dx, left + minWidth));
            if (edge.includes('n')) top = Math.max(8, Math.min(top + dy, bottom - minHeight));
            if (edge.includes('s')) bottom = Math.min(window.innerHeight - 8, Math.max(bottom + dy, top + minHeight));
            this.fitTrend({ x: left, y: top, width: right - left, height: bottom - top });
        },
        resetTrend() {
            this.fitTrend(null);
        },
        startTrendPointer(event, mode, edge = null) {
            if (event.button !== 0 || !this.trendOpen) return;
            event.preventDefault();
            event.stopPropagation();
            event.currentTarget.focus({ preventScroll: true });
            event.currentTarget.setPointerCapture(event.pointerId);
            this.trendPointer = { id: event.pointerId, mode, edge, x: event.clientX, y: event.clientY };
        },
        updateTrendPointer(event) {
            const pointer = this.trendPointer;
            if (!pointer || pointer.id !== event.pointerId) return;
            const dx = event.clientX - pointer.x, dy = event.clientY - pointer.y;
            if (pointer.mode === 'resize' && pointer.edge) this.resizeTrendEdge(pointer.edge, dx, dy);
            else if (pointer.mode === 'resize') this.resizeTrend(dx, dy);
            else this.moveTrend(dx, dy);
            this.trendPointer = { ...pointer, x: event.clientX, y: event.clientY };
        },
        endTrendPointer(event) {
            if (this.trendPointer?.id !== event.pointerId) return;
            this.trendPointer = null;
            if (event.currentTarget.hasPointerCapture(event.pointerId)) event.currentTarget.releasePointerCapture(event.pointerId);
        },
        isDirty() {
            return Object.keys(this.initialValues).some(key =>
                String(this.values[key] ?? '') !== String(this.initialValues[key] ?? '')
            );
        },
        activateMeasurement(key) {
            this.captureCleared = false;
            this.selectedPoint = null;
            this.active = key === 'sis' || key === 'dia' ? 'pa' : key;
        },
        focusMeasurement(key, trigger) {
            if (this.restoringTrendFocus) return;
            this.activateMeasurement(key);
            if (this.meta[this.active]) this.openTrend(this.active, trigger);
            else if (this.trendOpen) this.closeTrend();
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
            return String(this.values.fecha_hora ?? '') === String(this.evaluatedValues.fecha_hora ?? '') && fields.length > 0 && fields.every(field =>
                String(this.values[field] ?? '').trim() === String(this.evaluatedValues[field] ?? '').trim()
            ) && this.clinicalTones?.[key] !== undefined;
        },
        syncEvaluation(detail) {
            this.selectedPoint = null;
            const tones = tonesFor(detail?.evaluacion?.resultados);
            this.clinicalTones = tones;
            if (detail?.bandas !== undefined) this.objectiveBands = { ...detail.bandas };
            this.evaluatedValues = { ...(detail?.valores ?? {}) };
        },
        resetCapture(detail) {
            if (this.trendOpen) this.closeTrend();
            this.values = { ...detail.valores };
            this.initialValues = { ...detail.valores };
            this.clinicalTones = {};
            this.evaluatedValues = {};
            this.touched = {};
            this.errors = {};
            this.active = 'pa';
            this.captureCleared = true;
            this.selectedPoint = null;
        },
        toneOf(key) { return this.evaluationCurrent(key) ? this.clinicalTones[key] : 'neutral'; },
        awaitingMedicalGoal(key) {
            return key === 'sat' && this.validPreview(key) && this.evaluationCurrent(key) && this.toneOf(key) === 'neutral';
        },
        toneLabel(key) {
            if (!this.evaluationCurrent(key)) return 'Evaluando lectura';
            if (this.awaitingMedicalGoal(key)) return 'Sin objetivo médico';
            return ({ success: 'Normal', target: 'En objetivo', warning: 'Advertencia', high: 'Alto', danger: 'Crítico' })[this.toneOf(key)] ?? 'Sin clasificación adicional';
        },
        validPreview(key) {
            const fields = metadata[key]?.fields ?? [];
            if (!fields.length || fields.some(field => this.hasError(field))) return false;
            if (fields.some(field => String(this.values[field] ?? '').trim() === '')) return false;
            return fields.every(field => !this.technicalError(field));
        },
        measurementValue(row, key) {
            if (key === 'pa') return finite(row?.sis) !== null && finite(row?.dia) !== null ? finite(row.sis) : null;
            return finite(row?.[key]);
        },
        diastolicOf(row) { return finite(row?.dia); },
        labelOf(row, key) {
            if (this.measurementValue(row, key) === null) return '';
            return key === 'pa' ? `${finite(row.sis)}/${finite(row.dia)}` : String(this.measurementValue(row, key));
        },
        previewLabel(key) {
            if (!this.validPreview(key)) return '';
            return key === 'pa' ? `${this.values.sis}/${this.values.dia}` : String(this.values[key]);
        },
        records(key) {
            const cutoff = timestamp(this.values.fecha_hora);
            const rows = this.history.filter(row => this.measurementValue(row, key) !== null
                && (cutoff === null || timestamp(row.fecha_hora) === null || timestamp(row.fecha_hora) <= cutoff));
            if (rows.every(row => timestamp(row.fecha_hora) !== null)) rows.sort((a, b) => timestamp(b.fecha_hora) - timestamp(a.fecha_hora));
            return rows.slice(0, 5);
        },
        emptyHistoryMessage(key) {
            const records = this.records(key);
            if (records.length === 1) return `Última medición: ${records[0].fecha || 'registro previo'} · ${this.labelOf(records[0], key)} ${metadata[key]?.unit ?? ''}.`;
            return `No hay mediciones anteriores de ${metadata[key]?.label ?? 'este parámetro'}. Puedes capturar una lectura; aparecerá como vista previa sin guardar.`;
        },
        previous(key) { return this.records(key)[0] ?? null; },
        comparisonRows(key) {
            const previous = this.previous(key);
            if (!previous || !this.validPreview(key)) return [];
            return metadata[key].fields.map(field => {
                const delta = Number((finite(this.values[field]) - finite(previous[field])).toFixed(field === 'temp' ? 1 : 2));
                return {
                    label: key === 'pa' ? (field === 'sis' ? 'Sistólica' : 'Diastólica') : metadata[key].label,
                    previous: formatNumber(finite(previous[field])), current: formatNumber(finite(this.values[field])),
                    unit: metadata[key].unit, delta,
                    difference: `${delta > 0 ? '+' : ''}${formatNumber(delta)} ${key === 'sat' ? (Math.abs(delta) === 1 ? 'punto porcentual' : 'puntos porcentuales') : metadata[key].unit}`,
                    direction: delta > 0 ? 'Subió' : delta < 0 ? 'Bajó' : 'Sin cambio',
                    icon: delta > 0 ? 'ph-arrow-up-right' : delta < 0 ? 'ph-arrow-down-right' : 'ph-minus',
                };
            });
        },
        change(key) {
            const rows = this.comparisonRows(key);
            if (!rows.length) return '';
            if (key === 'pa') return rows.map(row => `${row.delta > 0 ? '+' : ''}${formatNumber(row.delta)}`).join(' / ') + ' mmHg';
            return rows[0].difference;
        },
        chartRows(key) {
            const preview = this.validPreview(key);
            const rows = this.records(key).slice(0, preview ? 4 : 5).reverse().map(row => ({
                value: this.measurementValue(row, key), dia: key === 'pa' ? this.diastolicOf(row) : null,
                label: this.labelOf(row, key), date: row.fecha || 'Registro previo', timestamp: timestamp(row.fecha_hora), preview: false, tone: row.tones?.[key] ?? 'neutral', alerta_estado: row.alerta_estado,
            }));
            if (preview) rows.push({
                value: key === 'pa' ? finite(this.values.sis) : finite(this.values[key]),
                dia: key === 'pa' ? finite(this.values.dia) : null,
                label: this.previewLabel(key), tone: this.toneOf(key), timestamp: timestamp(this.values.fecha_hora), date: timestamp(this.values.fecha_hora) !== null ? this.values.fecha_hora.replace('T', ' ') + ' · sin guardar' : 'Ahora · sin guardar', preview: true,
            });
            return rows.filter(row => Number.isFinite(row.value) && (key !== 'pa' || Number.isFinite(row.dia)));
        },
        chartRange(key) {
            const rows = this.chartRows(key);
            const values = rows.flatMap(row => key === 'pa' ? [row.value, row.dia] : [row.value]);
            this.availableBands(key).forEach(band => values.push(band.min, band.max));
            if (!values.length) return { min: 0, spread: 1 };
            const low = Math.min(...values), high = Math.max(...values);
            const precision = key === 'temp' ? 0.1 : ['sat', 'glucosa'].includes(key) ? 0.01 : 1;
            const padding = Math.max((high - low) * 0.15, precision);
            const rawStep = (high - low + padding * 2) / 4;
            const power = 10 ** Math.floor(Math.log10(rawStep));
            let step = [1, 2, 2.5, 5, 10].find(n => n * power >= rawStep) * power;
            step = Math.max(precision, precision === 1 ? Math.ceil(step) : step);
            const min = Math.max(0, Math.floor((low - padding) / step) * step);
            const max = Math.ceil((high + padding) / step) * step;
            return { min, max, spread: max - min || step, step };
        },
        chartY(key, value) {
            const { min, spread } = this.chartRange(key);
            return this.plot.bottom - ((value - min) / spread) * (this.plot.bottom - this.plot.top);
        },
        chartTicks(key) {
            if (!this.chartRows(key).length) return [];
            const range = this.chartRange(key);
            const count = Math.round(range.spread / range.step) + 1;
            return Array.from({ length: count }, (_, i) => {
                const value = range.min + i * range.step;
                return { label: formatNumber(value), y: this.chartY(key, value) };
            });
        },
        temporalChart(key) {
            const rows = this.chartRows(key);
            return rows.length > 1 && rows.every(row => row.timestamp !== null);
        },
        chartX(key, index) {
            const rows = this.chartRows(key);
            if (rows.length === 1) return (this.plot.left + this.plot.right) / 2;
            if (this.temporalChart(key) && rows.at(-1).timestamp === rows[0].timestamp) return (this.plot.left + this.plot.right) / 2;
            const fraction = this.temporalChart(key)
                ? (rows[index].timestamp - rows[0].timestamp) / (rows.at(-1).timestamp - rows[0].timestamp)
                : index / (rows.length - 1);
            return this.plot.left + fraction * (this.plot.right - this.plot.left);
        },
        chartTimeLabels(key) {
            const rows = this.chartRows(key);
            // Solo extremos: evita etiquetas superpuestas con intervalos cortos.
            const indices = rows.length === 1 || (rows.length > 1 && this.chartX(key, 0) === this.chartX(key, rows.length - 1)) ? [rows.length - 1] : rows.length ? [0, rows.length - 1] : [];
            return indices.map(index => {
                const row = rows[index];
                const date = row.timestamp !== null ? new Date(row.timestamp).toISOString() : null;
                return { x: this.chartX(key, index), anchor: indices.length === 1 ? 'middle' : index === 0 ? 'start' : 'end',
                    day: date ? `${row.preview ? 'Actual · ' : ''}${date.slice(8, 10)}/${date.slice(5, 7)}` : row.preview ? 'Actual · Sin guardar' : `Lectura ${index + 1}`,
                    time: date ? date.slice(11, 16) : 'Fecha no disponible' };
            });
        },
        chartSummary(key) {
            const rows = this.chartRows(key), saved = rows.filter(row => !row.preview).length;
            if (!rows.length) return 'Sin mediciones para representar';
            if (rows.length === 1) return 'Una lectura · No permite establecer una tendencia';
            return `${saved} ${saved === 1 ? 'lectura guardada' : 'lecturas guardadas'}${rows.some(row => row.preview) ? ' + vista previa' : ''} · ${this.temporalChart(key) ? 'Intervalos de tiempo reales' : 'Orden de lecturas; tiempo no disponible'}`;
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
            return this.availableBands(key).map(band => ({
                ...band,
                y: this.chartY(key, band.max),
                height: this.chartY(key, band.min) - this.chartY(key, band.max),
                label: `Objetivo médico ${band.min}–${band.max} ${metadata[key].unit}`,
            }));
        },
        chartPoints(key, series = 'value') {
            const rows = this.chartRows(key);
            if (rows.length < 2) return '';
            return this.chartMarkers(key, series).map(row => `${row.x},${row.y}`).join(' ');
        },
        chartHistoryPoints(key, series = 'value') {
            const rows = this.chartMarkers(key, series).filter(row => !row.preview);
            return rows.length >= 2 ? rows.map(row => `${row.x},${row.y}`).join(' ') : '';
        },
        chartPreviewPoints(key, series = 'value') {
            const rows = this.chartMarkers(key, series);
            return rows.length >= 2 && rows.at(-1).preview ? rows.slice(-2).map(row => `${row.x},${row.y}`).join(' ') : '';
        },
        chartAreaPoints(key, series = 'value', preview = false) {
            const markers = this.chartMarkers(key, series);
            const rows = preview ? (markers.at(-1)?.preview ? markers.slice(-2) : []) : markers.filter(row => !row.preview);
            if (rows.length < 2 || rows[0].x === rows.at(-1).x) return '';
            return `${rows[0].x},${this.plot.bottom} ` + rows.map(row => `${row.x},${row.y}`).join(' ') + ` ${rows.at(-1).x},${this.plot.bottom}`;
        },
        chartTooltip(key) {
            if (!this.selectedPoint) return [];
            const row = this.chartRows(key)[this.selectedPoint.index];
            if (!row) return [];
            const fields = key === 'pa' ? ['value', 'dia'] : ['value'];
            let previousY = this.plot.top - 26;
            return fields.map(field => {
                const y = Math.max(previousY + 26, Math.min(this.plot.bottom - fields.length * 26, this.chartY(key, row[field]) - 24));
                previousY = y;
                return { field, x: Math.max(this.plot.left, Math.min(this.plot.right - 142, this.selectedPoint.x - 152)), y,
                    label: `${key === 'pa' ? (field === 'dia' ? 'Diastólica ' : 'Sistólica ') : ''}${formatNumber(row[field])} ${metadata[key].unit}` };
            });
        },
        chartMarkers(key, series = 'value') {
            const rows = this.chartRows(key);
            if (!rows.length) return [];
            return rows.map((row, i) => ({
                ...row, index: i, x: this.chartX(key, i), y: this.chartY(key, row[series]),
                markerLabel: `${key === 'pa' ? `${series === 'dia' ? 'Diastólica' : 'Sistólica'} ${row[series]} mmHg` : `${row.label} ${metadata[key].unit}`}${row.tone !== 'neutral' ? ` · ${({success: 'Normal', target: 'En objetivo', warning: 'Advertencia', high: 'Alto', danger: 'Crítico'})[row.tone] ?? ''}` : ''}${row.alerta_estado ? ' · Alerta: ' + row.alerta_estado.replaceAll('_', ' ') : ''}`,
            }));
        },
        review() {
            const invalid = this.$el.querySelector('[aria-invalid="true"]');
            (invalid || this.$el.querySelector('.rm-signos__card--active input'))?.focus();
        },
    };
};
