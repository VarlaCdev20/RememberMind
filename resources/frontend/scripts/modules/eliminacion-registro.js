const geometryKeys = ['trendOpen', 'trendBounds', 'trendViewportCompact', 'trendViewportWidth', 'trendPointer',
    'trendReturnFocus', 'restoringTrendFocus', 'closeTrend', 'fitTrend', 'trendStyle', 'moveTrend', 'resizeTrend',
    'resizeTrendEdge', 'resetTrend', 'startTrendPointer', 'updateTrendPointer', 'endTrendPointer', 'openTrend'];
const captureFields = ['cantidad_cualitativa','volumen_ml','continencia','color_orina','aspecto_orina','olor_orina','tipo_miccion',
    'tipo_bristol','color_heces','esfuerzo_defecacion','presencia_sangre','presencia_moco','molestia_eliminacion','descripcion_molestia','observacion'];
const cleanText = value => typeof value === 'string' && !/^(null|undefined|NaN|Infinity|\[object Object\])$/i.test(value.trim()) ? value : '';
export const normalizeEliminationHistory = history => (Array.isArray(history) ? history : [])
    .filter(row => row && ['URINARIA','INTESTINAL'].includes(row.tipo) && cleanText(row.codigo) && cleanText(row.fecha) && cleanText(row.hora))
    .map(row => ({ ...row, grupo: cleanText(row.grupo) || cleanText(row.fecha), tipo_label: row.tipo === 'URINARIA' ? 'Eliminación urinaria' : 'Eliminación intestinal',
        observacion: cleanText(row.observacion), campos: (Array.isArray(row.campos) ? row.campos : [])
            .filter(field => cleanText(field?.nombre) && cleanText(field?.valor)).map(field => ({nombre:field.nombre,valor:field.valor})) }));

export function createEliminationCapture(history = [], initial = {}, persisted = false, latest = {}, baseline = null) {
    const geometry = window.rmDolorRegistro([], {}, '');
    return {
        ...Object.fromEntries(geometryKeys.map(key => [key, geometry[key]])),
        values: {...initial}, initialValues: baseline?.elimDatos ? {...baseline.elimDatos,type:baseline.elimTipo} : {...initial}, history: normalizeEliminationHistory(history), latest: normalizeEliminationHistory(Object.values(latest)),
        historyFilter: 'TODOS', persisted,
        init() {
            this.captureShell = this.$el.closest('.rm-modal-shell');
            this.beforeUnloadHandler = event => {
                if (!this.captureShell?.classList.contains('is-open') || !this.isDirty()) return;
                event.preventDefault(); event.returnValue = '';
            };
            this.savingGuard = event => {
                if (!this.captureShell?.querySelector('[wire\\:click="guardarEliminacion"]:disabled')) return;
                if ((event.type === 'keydown' && event.key === 'Escape' && !this.trendOpen)
                    || (event.type === 'click' && event.target.closest('.rm-modal-panel__close,.rm-modal-panel__back,.rm-modal-shell__overlay,[wire\\:click="cerrarSelectorRegistro"]'))) {
                    event.preventDefault(); event.stopImmediatePropagation();
                }
            };
            this.clearFocusHandler = () => this.$nextTick(() => this.$el.querySelector('[data-elimination-type]')?.focus());
            this.captureShell?.addEventListener('click', this.savingGuard, true);
            window.addEventListener('keydown', this.savingGuard, true);
            window.addEventListener('beforeunload', this.beforeUnloadHandler);
            window.addEventListener('registro-campos-limpiados', this.clearFocusHandler);
        },
        destroy() {
            if (this.$refs?.trendDialog?.matches(':popover-open')) this.$refs.trendDialog.hidePopover();
            this.captureShell?.removeEventListener('click', this.savingGuard, true);
            window.removeEventListener('keydown', this.savingGuard, true);
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
            window.removeEventListener('registro-campos-limpiados', this.clearFocusHandler);
        },
        isDirty() { return !this.persisted && Object.keys(this.initialValues).some(key => this.values[key] !== this.initialValues[key]); },
        setValue(field, value) {
            if (!captureFields.includes(field)) return;
            this.values[field] = value;
            if (field === 'molestia_eliminacion' && value !== true) {
                this.values.descripcion_molestia = ''; this.$wire?.$set('elimDatos.descripcion_molestia','',false);
            }
            this.$wire?.$set('elimDatos.'+field,value,false);
        },
        filteredHistory() { return this.history.filter(row => this.historyFilter === 'TODOS' || row.tipo === this.historyFilter); },
        recent() { return this.history.slice(0,6); },
        previous() { return this.latest.find(row => row.tipo === this.values.type) ?? this.history.find(row => row.tipo === this.values.type) ?? null; },
        groups() {
            const result=[];
            for (const row of this.filteredHistory()) {
                let group=result.find(item=>item.label===row.grupo);
                if (!group) { group={label:row.grupo,rows:[]};result.push(group); }
                group.rows.push(row);
            }
            return result;
        },
    };
}
window.rmEliminacionRegistro = createEliminationCapture;
