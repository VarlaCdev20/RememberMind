// Presentación únicamente. El evento de éxito lo emite el servidor tras persistir.
window.rmClinicalFormFeedback = () => ({
    clinicalFeedbackOpen: false,
    clinicalFormOpen: false,
    clinicalOperation: 'curacion',
    clinicalReturnTarget: null,
    clinicalFormDirty: false,
    clinicalDiscardOpen: false,
    feedbackAttempt: null,
    feedbackResident: '',
    feedbackProfessional: '',
    feedbackTitle: '',
    prepareFeedback(type) {
        const root = this.$el.closest('.rm-resident-directory, .rm-clinical-workspace, .rm-pilot-enfermeria');
        const context = root?.querySelector('.rm-clinical-form-modal .rm-modal-context, .rm-clinical-form-drawer .rm-clinical-form__context');
        if (!context || !['dolor', 'alimentacion', 'hidratacion', 'eliminacion', 'movilidad', 'seguimiento', 'curacion', 'cierre-herida'].includes(type)) return;
        this.feedbackAttempt = type;
        this.feedbackTitle = { dolor: 'Valoración de dolor', alimentacion: 'Registro de ingesta', hidratacion: 'Registro de hidratación', eliminacion: 'Registro de eliminación', movilidad: 'Registro de movilidad', seguimiento: 'Seguimiento diario completo', curacion: 'Curación registrada', 'cierre-herida': 'Herida cerrada' }[type];
        this.feedbackResident = context.querySelector('[data-clinical-resident]')?.textContent.trim() ?? '';
        this.feedbackProfessional = context.querySelector('[data-clinical-professional]')?.textContent.trim() ?? '';
    },
    confirmFeedback(type) {
        if (this.feedbackAttempt !== type) return;
        this.feedbackAttempt = null;
        this.$nextTick(() => { this.clinicalFeedbackOpen = true; });
    },
    confirmCareFeedback() {
        if (['alimentacion', 'eliminacion', 'movilidad'].includes(this.feedbackAttempt)) this.confirmFeedback(this.feedbackAttempt);
    },
    closeClinicalFeedback() {
        this.clinicalFeedbackOpen = false;
        const root = this.$el.closest('.rm-resident-directory, .rm-clinical-workspace, .rm-pilot-enfermeria');
        this.$nextTick(() => {
            const target = this.clinicalReturnTarget?.isConnected ? this.clinicalReturnTarget : root?.querySelector('#resident-register-trigger, #residente-registro');
            target?.focus();
        });
    },
    closeClinicalForm() {
        const root = this.$el.closest('.rm-resident-directory, .rm-clinical-workspace, .rm-pilot-enfermeria');
        if (root?.querySelector('.rm-clinical-form button[type="submit"]:disabled, .rm-clinical-form-modal button[form]:disabled')) return;
        this.feedbackAttempt = null;
        if (this.clinicalFormDirty) { this.clinicalDiscardOpen = true; return; }
        this.clinicalFormOpen = false;
        this.closeClinicalFeedback();
    },
    closeClinicalDrawer() {
        if (this.$el.closest('.rm-clinical-workspace')?.querySelector('.rm-drawer-footer button[type="submit"]:disabled')) return;
        this.feedbackAttempt = null;
        if (this.clinicalFormDirty) { this.clinicalDiscardOpen = true; return; }
        this.$wire.cerrarModales();
        this.closeClinicalFeedback();
    },
    discardClinicalDrawer() {
        this.clinicalFormDirty = false;
        this.clinicalDiscardOpen = false;
        this.feedbackAttempt = null;
        this.$wire.cerrarModales();
        this.closeClinicalFeedback();
    },
    confirmDailyFeedback() {
        if (this.feedbackAttempt !== 'seguimiento') return;
        this.clinicalFormDirty = false;
        this.clinicalDiscardOpen = false;
        this.confirmFeedback('seguimiento');
    },
    prepareDailyFeedback() {
        // Los casts decimales pueden precargar «200.00» en campos del contrato entero.
        // Conserva fracciones inválidas para que el servidor las rechace; nunca redondea.
        ['porcentajeAlimentacion', 'cantidadHidratacionMl'].forEach(field => {
            const value = this.$wire[field];
            if (value !== null && value !== '' && Number.isFinite(Number(value)) && Number.isInteger(Number(value))) {
                this.$wire.$set(field, Number(value), false);
            }
        });
        this.prepareFeedback('seguimiento');
    },
    discardClinicalForm(initialValues) {
        Object.entries(initialValues).forEach(([field, value]) => this.$wire.$set(field, value, false));
        this.clinicalFormDirty = false;
        this.clinicalDiscardOpen = false;
        this.clinicalFormOpen = false;
        this.closeClinicalFeedback();
    },
    confirmWorkspaceFeedback(detail) {
        const result = Array.isArray(detail) ? detail[0] : detail;
        const titles = { hidratacion: 'Cuidado registrado', curacion: 'Curación registrada', 'cierre-herida': 'Herida cerrada' };
        const type = this.feedbackAttempt;
        if (!titles[type] || result?.icon !== 'success' || result?.title !== titles[type]) return false;
        this.clinicalFormOpen = false;
        this.clinicalFormDirty = false;
        this.clinicalDiscardOpen = false;
        this.confirmFeedback(type);
        return true;
    },
});

window.rmClinicalCapture = (initialValues = {}) => ({
    initialCapture: null,
    captureDefaults: [],
    snapshot(useInitial = false) {
        return JSON.stringify(Array.from(this.$el.querySelectorAll('input, select, textarea'))
            .filter(field => !field.disabled && !field.readOnly)
            .map(field => {
                const model = field.getAttribute('wire:model') ?? field.getAttribute('wire:model.live');
                const initial = useInitial && Object.hasOwn(initialValues, model);
                const value = initial ? initialValues[model] : field.value;
                return [field.id, field.type === 'radio'
                    ? (initial ? String(value) === field.value : field.checked)
                    : (field.type === 'checkbox' ? (initial ? Boolean(value) : field.checked) : String(value ?? ''))];
            }));
    },
    init() {
        this.$nextTick(() => {
            this.initialCapture = this.snapshot(true);
            this.rememberCaptureFields();
            if (typeof MutationObserver !== 'undefined') {
                this.captureObserver = new MutationObserver(() => this.rememberCaptureFields());
                this.captureObserver.observe(this.$el, { childList: true, subtree: true });
            }
        });
        this.beforeUnloadHandler = event => {
            const shell = this.$el.closest('.rm-modal-shell, .rm-drawer-shell');
            if ((this.$el.getClientRects && this.$el.getClientRects().length === 0)
                || (shell && !shell.classList.contains('is-open'))
                || this.initialCapture === null || this.snapshot() === this.initialCapture) return;
            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', this.beforeUnloadHandler);
    },
    rememberCaptureFields() {
        Array.from(this.$el.querySelectorAll('input, select, textarea'))
            .filter(field => !field.disabled && !field.readOnly)
            .forEach(field => {
                    const model = field.getAttribute('wire:model') ?? field.getAttribute('wire:model.live');
                    const existing = this.captureDefaults.find(entry => entry.model === model && entry.field.id === field.id);
                    if (existing) { existing.field = field; return; }
                    this.captureDefaults.push({ field, model, value: Object.hasOwn(initialValues, model) ? initialValues[model]
                        : (model && this.$wire ? this.$wire[model]
                            : field.type === 'checkbox' ? field.checked
                            : field.type === 'radio' ? (field.checked ? field.value : null) : field.value) });
            });
    },
    restoreCapture() {
        this.captureDefaults.forEach(({ field, model, value }) => {
            if (model) this.$wire?.$set(model, value, false);
            if (field.type === 'checkbox') field.checked = Boolean(value);
            else if (field.type === 'radio') field.checked = String(value) === field.value;
            else field.value = value ?? '';
        });
        this.initialCapture = this.snapshot();
        this.notifyDirty();
    },
    notifyDirty() { this.$dispatch('clinical-capture-changed', { dirty: this.snapshot() !== this.initialCapture }); },
    destroy() { this.captureObserver?.disconnect(); window.removeEventListener('beforeunload', this.beforeUnloadHandler); },
});
