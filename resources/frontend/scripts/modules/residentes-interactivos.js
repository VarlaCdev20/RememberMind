import { rmAdmisiones } from './admisiones-interactivas.js';

export function rmResidentes() {
    return {
        ...rmAdmisiones(),
        cama: null,
        estadisticas: true,
        abrirCama(datos, trigger) {
            this.trigger = trigger;
            this.cama = datos;
            this.$nextTick(() => this.$refs.cerrarCama.focus());
        },
        cerrarCama() {
            this.cama = null;
            this.$nextTick(() => this.trigger?.focus({ preventScroll: true }));
        },
        actualizar() {
            this.cama = null;
            this.loading = true;
            if (window.Livewire?.navigate) window.Livewire.navigate(window.location.href);
            else window.location.reload();
        },
    };
}

if (typeof window !== 'undefined') window.rmResidentes = rmResidentes;
