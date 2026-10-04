export function iniciarEfectosAmbientales(AOS) {
    const init = () => {
        AOS.init({ duration: 1000, once: true, offset: 50 });

        const root = document.documentElement;
        const pointerQuery = window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)');
        let frame = null;
        let mouseX = 0;
        let mouseY = 0;

        const hide = () => root.style.setProperty('--rm-cursor-visible', '0');
        const paint = () => {
            frame = null;
            if (!pointerQuery.matches) {
                hide();
                return;
            }
            root.style.setProperty('--mouse-x', `${mouseX}px`);
            root.style.setProperty('--mouse-y', `${mouseY}px`);
            root.style.setProperty('--rm-cursor-visible', '1');
        };

        window.addEventListener('pointermove', (event) => {
            if (event.pointerType !== 'mouse' || !pointerQuery.matches) return;
            mouseX = event.clientX;
            mouseY = event.clientY;
            if (frame === null) frame = window.requestAnimationFrame(paint);
        }, { passive: true });
        document.addEventListener('mouseout', (event) => {
            if (!event.relatedTarget) hide();
        }, { passive: true });
        pointerQuery.addEventListener('change', hide);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}
