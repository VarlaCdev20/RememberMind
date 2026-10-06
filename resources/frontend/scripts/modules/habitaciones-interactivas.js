import { rmResidentes } from './residentes-interactivos.js';

export function rmHabitaciones() {
    return {
        ...rmResidentes(),
        colapsar: false,
        vistaPrevia: null,
        temporizadorPrevia: null,
        posicion: { left: '16px', top: '16px' },
        mostrarVistaPrevia(datos, elemento) {
            this.conservarVistaPrevia();
            const rect = elemento.getBoundingClientRect();
            const ancho = Math.min(300, window.innerWidth - 32);
            this.posicion = {
                left: `${Math.max(16, Math.min(rect.left, window.innerWidth - ancho - 16))}px`,
                top: `${Math.max(16, Math.min(rect.bottom + 8, window.innerHeight - 150))}px`,
            };
            this.vistaPrevia = datos;
            this.$nextTick?.(() => {
                const altura = this.$refs.previa?.getBoundingClientRect().height;
                if (altura) this.posicion.top = `${Math.max(16, Math.min(rect.bottom + 8, window.innerHeight - altura - 16))}px`;
            });
        },
        conservarVistaPrevia() {
            if (this.temporizadorPrevia !== null) clearTimeout(this.temporizadorPrevia);
            this.temporizadorPrevia = null;
        },
        diferirCierrePrevia() {
            this.conservarVistaPrevia();
            this.temporizadorPrevia = setTimeout(() => this.ocultarVistaPrevia(), 180);
        },
        ocultarVistaPrevia() {
            this.conservarVistaPrevia();
            this.vistaPrevia = null;
        },
    };
}
if (typeof window !== 'undefined') window.rmHabitaciones = rmHabitaciones;
