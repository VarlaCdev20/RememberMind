const PENDING_KEY = 'rm-password-recovery-pending';
const texto = value => typeof value === 'string' ? value : '';

export function crearAcceso(datos = {}) {
    const correo = texto(datos.correo);
    return {
        panel: 'login', correo, recoverCorreo: correo, password: '',
        correoTouched: false, passwordTouched: false, recoverTouched: false,
        showPassword: false, isSubmitting: false, recoverSubmitting: false,
        recoverySent: false, recoveryError: '',
        errorServidor: Object.values(datos.errores ?? {}).flat().find(v => typeof v === 'string') ?? '',
        init() {
            let pendiente = false;
            try { pendiente = sessionStorage.getItem(PENDING_KEY) === '1'; } catch { pendiente = false; }
            this.panel = !datos.completado && (datos.recuperacion || pendiente) ? 'recover' : 'login';
            this.recoverySent = this.panel === 'recover' && Boolean(datos.status);
            if (this.panel === 'recover') { this.recoveryError = this.errorServidor; this.errorServidor = ''; }
            try { sessionStorage.removeItem(PENDING_KEY); } catch { /* El marcador del servidor permite continuar sin storage. */ }
            this.$nextTick(() => {
                if (datos.completado) this.$refs.confirmacion?.focus();
                else (this.panel === 'recover' ? this.$refs.recoverCorreo : this.$refs.loginCorreo)?.focus();
            });
        },
        validarCorreo(value, tocado) {
            if (!tocado) return '';
            const correo = texto(value).trim();
            if (!correo) return 'Escribe el correo con el que inicias sesión.';
            if (correo.length > 120) return 'El correo no puede superar los 120 caracteres.';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) return 'Revisa el correo; debe tener un formato como nombre@dominio.com.';
            return '';
        },
        get correoError() { return this.validarCorreo(this.correo, this.correoTouched); },
        get passwordError() { return this.passwordTouched && !this.password ? 'Escribe tu contraseña.' : ''; },
        get recoverCorreoError() { return this.validarCorreo(this.recoverCorreo, this.recoverTouched); },
        abrirRecuperacion() {
            if (this.isSubmitting || this.recoverSubmitting) return;
            this.recoverCorreo = this.correo.trim();
            this.recoveryError = ''; this.recoverySent = false; this.recoverTouched = false;
            this.panel = 'recover';
            this.$nextTick(() => this.$refs.recoverCorreo?.focus());
        },
        volverLogin() {
            if (this.recoverSubmitting || this.isSubmitting) return;
            this.correo = this.recoverCorreo.trim();
            this.recoveryError = ''; this.panel = 'login';
            this.$nextTick(() => this.$refs.loginCorreo?.focus());
        },
        submitLogin(event) {
            if (this.isSubmitting || this.recoverSubmitting) { event.preventDefault(); return; }
            this.correo = this.correo.trim().toLowerCase();
            this.correoTouched = true; this.passwordTouched = true;
            if (this.correoError || this.passwordError) {
                event.preventDefault();
                (this.correoError ? this.$refs.loginCorreo : this.$refs.loginPassword)?.focus();
                return;
            }
            this.isSubmitting = true;
        },
        async submitRecovery(event) {
            event.preventDefault();
            if (this.recoverSubmitting || this.isSubmitting) return;
            this.recoverCorreo = this.recoverCorreo.trim().toLowerCase();
            this.recoverTouched = true;
            if (this.recoverCorreoError) {
                this.$refs.recoverCorreo?.focus(); return;
            }
            this.recoveryError = ''; this.recoverySent = false;
            this.recoverSubmitting = true;
            try {
                const form = event.currentTarget;
                const body = new FormData(form);
                // Se envía únicamente el formulario de recuperación, nunca la contraseña de acceso.
                body.set('correo', this.recoverCorreo);
                const response = await fetch(form.action, {
                    method: 'POST', body, credentials: 'same-origin', redirect: 'error',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;
                if (response.ok && typeof data?.message === 'string') {
                    this.recoverySent = true;
                } else if ([422, 503].includes(response.status) && typeof data?.errors?.correo?.[0] === 'string') {
                    this.recoveryError = data.errors.correo[0];
                } else if (response.status === 419) {
                    this.recoveryError = 'La sesión de esta página venció. Recarga la página y vuelve a intentarlo.';
                } else if (response.status === 429) {
                    this.recoveryError = 'Ya se realizaron varias solicitudes. Espera un momento antes de intentarlo de nuevo.';
                } else {
                    this.recoveryError = 'No pudimos procesar la solicitud. Inténtalo de nuevo en unos momentos.';
                }
            } catch {
                this.recoveryError = 'No se pudo confirmar la solicitud. Revisa tu conexión e inténtalo de nuevo.';
            } finally {
                this.recoverSubmitting = false;
            }
        },
        restaurarFormulario() { this.isSubmitting = false; this.recoverSubmitting = false; },
    };
}

if (typeof window !== 'undefined') window.rmAcceso = crearAcceso;
