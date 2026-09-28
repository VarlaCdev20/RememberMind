const initAuthLoginParallax = () => {
    const scene = document.querySelector('[data-auth-scene]');

    if (!scene || scene.dataset.parallaxReady === 'true') {
        return;
    }

    scene.dataset.parallaxReady = 'true';

    const layers = [...scene.querySelectorAll('[data-auth-layer]')];
    const card = scene.querySelector('[data-auth-card]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const interactivePointer = window.matchMedia('(min-width: 768px) and (pointer: fine)');
    let pointerX = 0;
    let pointerY = 0;
    let frame = null;

    const render = () => {
        frame = null;

        if (reducedMotion.matches) {
            return;
        }

        const scrollOffset = Math.min(window.scrollY, window.innerHeight) / window.innerHeight;

        layers.forEach((layer) => {
            const depth = Number(layer.dataset.authLayer || 0);
            const x = interactivePointer.matches ? pointerX * depth * 18 : 0;
            const y = (interactivePointer.matches ? pointerY * depth * 13 : 0) - scrollOffset * depth * 24;

            layer.style.setProperty('--auth-parallax-x', `${x.toFixed(2)}px`);
            layer.style.setProperty('--auth-parallax-y', `${y.toFixed(2)}px`);
        });

        if (card) {
            const enabled = interactivePointer.matches && !card.matches(':focus-within');
            card.style.setProperty('--auth-card-x', `${(enabled ? pointerX * 2.5 : 0).toFixed(2)}px`);
            card.style.setProperty('--auth-card-y', `${(enabled ? pointerY * 1.8 : 0).toFixed(2)}px`);
            card.style.setProperty('--auth-card-rx', `${(enabled ? pointerY * -1.2 : 0).toFixed(2)}deg`);
            card.style.setProperty('--auth-card-ry', `${(enabled ? pointerX * 1.5 : 0).toFixed(2)}deg`);
        }
    };

    const requestRender = () => {
        if (frame === null) {
            frame = window.requestAnimationFrame(render);
        }
    };

    const updatePointer = (event) => {
        pointerX = (event.clientX / window.innerWidth - 0.5) * 2;
        pointerY = (event.clientY / window.innerHeight - 0.5) * 2;
        requestRender();
    };

    const resetPointer = () => {
        pointerX = 0;
        pointerY = 0;
        requestRender();
    };

    window.addEventListener('pointermove', updatePointer, { passive: true });
    document.documentElement.addEventListener('mouseleave', resetPointer);
    window.addEventListener('scroll', requestRender, { passive: true });
    window.addEventListener('resize', requestRender, { passive: true });
    reducedMotion.addEventListener('change', requestRender);
    interactivePointer.addEventListener('change', requestRender);
    requestRender();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthLoginParallax, { once: true });
} else {
    initAuthLoginParallax();
}
