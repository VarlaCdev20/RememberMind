export default function redApoyoTree() {
    return {
        nodoActivo: null,
        lines: [],
        
        init() {
            // Un pequeño retardo para asegurar que el DOM esté posicionado
            setTimeout(() => {
                this.updateLines();
            }, 100);

            // Observador para cuando se redimensiona
            window.addEventListener('resize', () => {
                this.updateLines();
            });
            
            // Observador para cambios en DOM por si Livewire actualiza la vista
            const observer = new MutationObserver(() => {
                this.updateLines();
            });
            if (this.$refs.container) {
                observer.observe(this.$refs.container, { childList: true, subtree: true });
            }
        },

        seleccionarNodo(tipo, realId) {
            this.nodoActivo = `${tipo}-${realId}`;
        },

        updateLines() {
            if (!this.$refs.container) return;
            const container = this.$refs.container.getBoundingClientRect();
            const root = document.getElementById('nodo-adulto');
            
            if (!root) {
                this.lines = [];
                return;
            }

            const rootRect = root.getBoundingClientRect();
            const rootX = rootRect.left - container.left + rootRect.width / 2;
            const rootBottomY = rootRect.top - container.top + rootRect.height;

            const newLines = [];
            const midY = rootBottomY + 30; // Nivel intermedio para la barra horizontal
            
            // Conexiones a Familiares
            const familiares = document.querySelectorAll('.nodo-familiar');
            familiares.forEach(el => {
                const rect = el.getBoundingClientRect();
                const x = rect.left - container.left + rect.width / 2;
                const topY = rect.top - container.top;

                const isResponsable = el.dataset.responsable === 'true';
                const isContacto = el.dataset.contacto === 'true';
                
                let color = '#CBD5E1'; // slate-300
                let stroke = 1.5;
                if (isResponsable) { color = '#F9735B'; stroke = 2.5; }
                else if (isContacto) { color = '#D9A441'; stroke = 2; }

                // Rama ortogonal: Baja un poco, va horizontal, y baja hasta el nodo
                let path = `M ${rootX} ${rootBottomY} V ${midY} H ${x} V ${topY}`;
                
                // Si el nodo está muy abajo (ej. nietos o sobrinos), la línea horizontal principal no debe bajar tanto, 
                // pero como midY es fijo, todos comparten la misma barra horizontal!
                newLines.push({
                    d: path,
                    color, stroke, dashed: false
                });
            });

            // Apoyo Institucional
            const inst = document.getElementById('nodo-inst');
            if (inst) {
                const rect = inst.getBoundingClientRect();
                const instX = rect.left - container.left + rect.width / 2;
                const instBottomY = rect.top - container.top + rect.height;

                // Conexiones Institucional -> Voluntarios
                const voluntarios = document.querySelectorAll('.nodo-voluntario');
                if (voluntarios.length > 0) {
                    const instMidY = instBottomY + 20;
                    voluntarios.forEach(el => {
                        const vRect = el.getBoundingClientRect();
                        const vx = vRect.left - container.left + vRect.width / 2;
                        const vTopY = vRect.top - container.top;
                        
                        newLines.push({
                            d: `M ${instX} ${instBottomY} V ${instMidY} H ${vx} V ${vTopY}`,
                            color: '#7DD3FC', stroke: 1.5, dashed: true
                        });
                    });
                }
            }

            this.lines = newLines;
        }
    };
}
