// Estado de presentación; la autorización y la persistencia corresponden al servidor.
window.rmWoundWorkspace = () => ({
    nueva: false, previews: [], uploading: false, progress: 0, uploadError: '', pendingFiles: [],
    viewerOpen: false, compareOpen: false, gallery: [], current: 0, before: 0, after: 1, returnFocus: null, replacement: null,
    historyOpen: false, historyReturnFocus: null, evolutionOpen: false, evolutionReturnFocus: null, curationToast: false,
    init() {
        this.beforeUnload = event => {
            if (!this.clinicalFormOpen || !this.clinicalFormDirty) return;
            event.preventDefault(); event.returnValue = '';
        };
        this.beforeNavigate = event => {
            if (this.clinicalFormOpen && this.clinicalFormDirty && !window.confirm('¿Salir sin guardar? Los datos y las fotografías seleccionadas se perderán.')) event.preventDefault();
        };
        window.addEventListener('beforeunload', this.beforeUnload);
        document.addEventListener('livewire:navigate', this.beforeNavigate);
        const popup = this.$root?.querySelector('[data-wound-popup]');
        this.isPopup = popup?.dataset.woundPopup === 'true';
        if (this.isPopup) this.openForm({currentTarget: null}, popup.dataset.woundOperation);
    },
    openForm(event, operation = 'curacion') {
        if (this.uploading || this.clinicalFormOpen) return;
        this.nueva = operation === 'nueva-herida';
        this.$wire.$set('registrandoNuevaHerida', this.nueva, false);
        if (this.nueva) this.$wire.$set('lesionId', '', false);
        this.clinicalOperation = operation;
        this.curationToast = false;
        this.clinicalReturnTarget = event.currentTarget;
        this.clinicalFormDirty = false; this.clinicalDiscardOpen = false; this.clinicalFormOpen = true;
        this.$wire.$set('mostrarFormularioHerida', true, false);
        this.$nextTick(() => this.$root.querySelector(this.nueva ? '#nueva-nuevaHeridaTipo' : (operation === 'cierre-herida' ? '#herida-resultado' : '#curacion-herida'))?.focus());
    },
    chooseWound(code) {
        if (this.clinicalFormDirty && !window.confirm('¿Cambiar de herida sin guardar? Los datos y las fotografías seleccionadas se perderán.')) return;
        if (!code) return;
        this.closeConsultations();
        this.clearPreviews(); this.$wire.seleccionarHerida(code);
        this.clinicalFormDirty = false;
    },
    async startNewWound(event) {
        if (this.uploading || (this.clinicalFormDirty && !window.confirm('¿Registrar otra herida sin guardar? Los datos y las fotografías seleccionadas se perderán.'))) return;
        const trigger = event.currentTarget;
        await this.$wire.prepararNuevaHerida();
        this.closeConsultations();
        this.clearPreviews(); this.clinicalFormOpen = false;
        this.openForm({currentTarget: trigger}, 'nueva-herida');
    },
    async startFirstCuration() {
        await this.$wire.prepararPrimeraCuracion();
        this.nueva = false; this.clinicalOperation = 'curacion';
        this.$nextTick(() => this.$root.querySelector('#curacion-herida')?.focus());
    },
    returnToResident() {
        this.closeConsultations();
        this.clinicalFormOpen = false; this.$wire.$set('mostrarFormularioHerida', false, false);
        this.$wire.$dispatch('heridas-popup-cerrado', {volverAlResidente: true});
    },
    areaPreview(length, width) {
        if (length === null || width === null || length === '' || width === '') return 'Sin medidas';
        const values = [Number(length), Number(width)];
        if (values.some(value => !Number.isFinite(value) || value < 0 || value > 99999)) return 'Revisa las medidas';
        return (values[0] * values[1]).toFixed(2);
    },
    procedureIncludes(label) { return String(this.$wire.accionLesion ?? '').split('; ').includes(label); },
    materialIncludes(label) { return String(this.$wire.materialesLesion ?? '').split('; ').includes(label); },
    toggleMaterial(label) {
        const parts = String(this.$wire.materialesLesion ?? '').split('; ').filter(Boolean);
        this.$wire.$set('materialesLesion', (parts.includes(label) ? parts.filter(part => part !== label) : [...parts, label]).join('; '), false);
        this.clinicalFormDirty = true;
    },
    chooseText(field, value) {
        if (!['aspectoLesion', 'exudadoLesion', 'olorLesion', 'dolorLesion'].includes(field)) return;
        this.$wire.$set(field, value, false); this.clinicalFormDirty = true;
    },
    hasArea(length, width) {
        return [length, width].every(value => value !== null && value !== '' && Number.isFinite(Number(value)) && Number(value) >= 0 && Number(value) <= 99999);
    },
    curationReady() {
        const w = this.$wire;
        if (!w.lesionId || this.uploading || this.uploadError) return false;
        if (![w.aspectoLesion, w.accionLesion].every(value => typeof value === 'string' && value.trim().length >= 5)) return false;
        if (w.aspectoLesion.length > 80 || w.accionLesion.length > 2000) return false;
        if ([w.largoLesion, w.anchoLesion, w.profundidadLesion].some(value => value !== null && value !== '' && (!Number.isFinite(Number(value)) || Number(value) < 0 || Number(value) > 99999))) return false;
        if (w.dolorLesion !== null && w.dolorLesion !== '' && (!Number.isInteger(Number(w.dolorLesion)) || Number(w.dolorLesion) < 0 || Number(w.dolorLesion) > 10)) return false;
        return ['exudadoLesion', 'olorLesion'].every(field => String(w[field] ?? '').length <= 80)
            && ['materialesLesion', 'respuestaLesion', 'observacionLesion'].every(field => String(w[field] ?? '').length <= 2000);
    },
    toggleProcedure(label) {
        const parts = String(this.$wire.accionLesion ?? '').split('; ').filter(Boolean);
        const value = parts.includes(label) ? parts.filter(part => part !== label) : [...parts, label];
        this.$wire.$set('accionLesion', value.join('; '), false); this.clinicalFormDirty = true;
    },
    stepPain(delta) {
        const current = this.$wire.dolorLesion;
        const value = current === null || current === '' ? 0 : Number(current);
        if (!Number.isInteger(value) || value < 0 || value > 10) return;
        this.$wire.$set('dolorLesion', Math.min(10, Math.max(0, value + delta)), false); this.clinicalFormDirty = true;
    },
    openEvolution(event) {
        this.evolutionReturnFocus = event.currentTarget;
        this.evolutionOpen = true;
        this.$nextTick(() => { const dialog = this.$el.closest('.rm-wound').querySelector('#herida-evolucion-popup'); dialog.showPopover(); dialog.querySelector('button')?.focus(); window.dispatchEvent(new Event('resize')); });
    },
    closeEvolution() { this.$el.closest('.rm-wound').querySelector('#herida-evolucion-popup').hidePopover(); this.evolutionOpen = false; this.$nextTick(() => this.evolutionReturnFocus?.focus()); },
    closeConsultations() { if (this.historyOpen) this.closeHistory(); if (this.evolutionOpen) this.closeEvolution(); },
    openHistory(event) { if (this.evolutionOpen) this.closeEvolution(); this.historyReturnFocus = event.currentTarget; this.historyOpen = true; this.$nextTick(() => this.$el.closest('.rm-wound').querySelector('#herida-historial-popup button')?.focus()); },
    closeHistory() { this.historyOpen = false; this.$nextTick(() => this.historyReturnFocus?.focus()); },
    selectFiles(event) {
        this.pendingFiles = Array.from(event.target.files ?? []); this.replacement = null;
        this.$wire.$set('reemplazarFotografiaIndice', null, false);
        if (!this.pendingFiles.length || this.uploading) return;
        this.sendFiles(); event.target.value = '';
    },
    sendFiles() {
        if (!this.pendingFiles.length || this.uploading) return;
        this.uploading = true; this.progress = 0; this.uploadError = ''; this.clinicalFormDirty = true;
        const start = Math.max(-1, ...this.previews.map(photo => photo.index)) + 1;
        const revision = this.$wire.revisionFotos;
        this.$wire.uploadMultiple('loteFotografias', this.pendingFiles,
            () => {
                const count = Object.keys(this.$wire.fotografias ?? {}).length;
                if (this.$wire.revisionFotos === revision || count !== this.previews.length + (this.replacement === null ? this.pendingFiles.length : 0)) {
                    this.uploadError = 'No se pudieron añadir las fotografías. Revisa formato, tamaño y límite de imágenes.';
                } else {
                    if (this.replacement !== null) {
                        const old = this.previews[this.replacement];
                        URL.revokeObjectURL(old.url);
                        const file = this.pendingFiles[0];
                        this.previews[this.replacement] = { index: this.replacement, url: URL.createObjectURL(file), name: file.name, size: (file.size / 1024 / 1024).toFixed(2) + ' MB' };
                    } else this.pendingFiles.forEach((file, offset) => this.previews.push({ index: start + offset, url: URL.createObjectURL(file), name: file.name, size: (file.size / 1024 / 1024).toFixed(2) + ' MB' }));
                    this.pendingFiles = [];
                    this.replacement = null;
                }
                this.uploading = false;
            },
            () => { this.uploading = false; this.uploadError = 'Error al cargar la fotografía.'; },
            event => { this.progress = event.detail.progress; });
    },
    removePhoto(photo) {
        URL.revokeObjectURL(photo.url); this.previews = this.previews.filter(item => item.index !== photo.index);
        this.$wire.quitarFotografia(photo.index); this.clinicalFormDirty = true;
        this.previews.forEach((item, index) => { item.index = index; });
    },
    replacePhoto(photo, event) {
        if (!event.target.files?.length || this.uploading) return;
        this.replacement = photo.index; this.pendingFiles = [event.target.files[0]];
        this.$wire.$set('reemplazarFotografiaIndice', photo.index, false);
        this.sendFiles(); event.target.value = '';
    },
    clearPreviews() {
        this.previews.forEach(photo => URL.revokeObjectURL(photo.url));
        this.previews = []; this.pendingFiles = []; this.uploadError = ''; this.progress = 0; this.replacement = null;
    },
    notifyPopupClosed() { if (this.isPopup) this.$wire.$dispatch('heridas-popup-cerrado'); },
    closeWoundForm() {
        if (this.evolutionOpen) { this.closeEvolution(); return; }
        if (this.historyOpen) { this.closeHistory(); return; }
        if (this.uploading) return;
        this.closeClinicalForm();
        if (!this.clinicalFormOpen) { this.$wire.$set('mostrarFormularioHerida', false, false); this.notifyPopupClosed(); }
    },
    discardWoundForm(initial) {
        if (this.uploading) return;
        this.clearPreviews(); this.$wire.descartarFotosHerida(); this.discardClinicalForm(initial);
        this.$wire.$set('mostrarFormularioHerida', false, false);
        this.notifyPopupClosed();
    },
    saved() { this.clearPreviews(); this.clinicalFormDirty = false; this.clinicalDiscardOpen = false; this.curationToast = !this.nueva; this.clinicalFormOpen = !!this.isPopup || this.nueva; this.$wire.$set('mostrarFormularioHerida', this.clinicalFormOpen, false); this.$nextTick(() => (this.nueva ? this.$refs.result : this.$refs.curationToast)?.focus()); },
    viewGallery(items, index, event) {
        this.gallery = items; this.current = index; this.returnFocus = event.currentTarget;
        this.viewerOpen = true; this.$nextTick(() => this.$refs.viewerClose?.focus());
    },
    closeGallery() { this.viewerOpen = false; this.compareOpen = false; this.$nextTick(() => this.returnFocus?.focus()); },
    step(direction) { if(this.gallery.length) this.current = (this.current + direction + this.gallery.length) % this.gallery.length; },
    compare(items, event) { this.gallery = items; this.before = items.length - 1; this.after = 0; this.returnFocus = event.currentTarget; this.compareOpen = true; this.$nextTick(() => this.$refs.compareClose?.focus()); },
    destroy() { this.clearPreviews(); window.removeEventListener('beforeunload', this.beforeUnload); document.removeEventListener('livewire:navigate', this.beforeNavigate); },
});

// Solo datos persistidos. Los valores ausentes son gaps y nunca se convierten en cero.
window.rmWoundEvolution = (rows = []) => ({
    rows: Array.isArray(rows) ? rows.map(row => ({...row, area: typeof row.area === 'number' && Number.isFinite(row.area) && row.area >= 0 ? row.area : null})) : [],
    limit: '5', chartError: false, chartKey: null,
    get visibleRows() { const size = Number(this.limit); return size > 0 ? this.rows.slice(-size) : this.rows; },
    get enough() { return this.visibleRows.filter(row => row.area !== null).length >= 2; },
    init() { this.chartKey = this.$id('wound-chart'); this.$nextTick(() => this.refresh()); },
    config() {
        const api = window.RMCharts, rows = this.visibleRows;
        const color = this.$refs.canvas && typeof window.getComputedStyle === 'function'
            ? window.getComputedStyle(this.$refs.canvas).getPropertyValue('--rm-wound-care').trim() : '';
        const config = api.presets.semantic('line', 'care', rows.map(row => row.etiqueta), [{label:'Área aproximada (cm²)', data:rows.map(row => row.area), spanGaps:false, tension:0.2, fill:true, borderColor:color || undefined, backgroundColor:color ? api.hexToRgba(color, .15) : undefined, borderWidth:2.5, pointRadius:3.5, pointHoverRadius:5}]);
        config.options.animation = false;
        config.options.maintainAspectRatio = false;
        config.options.scales.y = {...config.options.scales.y, beginAtZero:true, title:{display:true,text:'Área (cm²)',color:api.getCss('--rm-chart-axis-text')}};
        config.options.plugins.legend = {display:false};
        config.options.plugins.datalabels = {display:false};
        return config;
    },
    refresh() {
        const api = window.RMCharts;
        if (!this.enough) { api?.destroy(this.chartKey); this.chartError = false; return; }
        this.$nextTick(() => {
            this.chartError = !api || !api.init(this.chartKey, this.$refs.canvas, this.config(), () => this.refresh());
        });
    },
    destroy() { window.RMCharts?.destroy(this.chartKey); },
});
