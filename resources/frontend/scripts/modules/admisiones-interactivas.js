export function rmAdmisiones() {
    return {
        loading: false, resumen: null, trigger: null,
        filtrar() {
            const form = this.$refs.filtros;
            if (!form.reportValidity()) return;
            const url = new URL(form.action, window.location.origin);
            for (const [name, value] of new FormData(form)) {
                if (String(value).trim()) url.searchParams.set(name, String(value).trim());
            }
            if (url.href === window.location.href || this.loading) return;
            this.loading = true;
            if (window.Livewire?.navigate) window.Livewire.navigate(url.href);
            else window.location.assign(url.href);
        },
        abrirResumen(datos, trigger) {
            this.trigger = trigger; this.resumen = datos;
            this.$nextTick(() => this.$refs.cerrarResumen.focus());
        },
        cerrarResumen() {
            this.resumen = null;
            this.$nextTick(() => this.trigger?.focus({ preventScroll: true }));
        },
    };
}

if (typeof window !== 'undefined') window.rmAdmisiones = rmAdmisiones;
