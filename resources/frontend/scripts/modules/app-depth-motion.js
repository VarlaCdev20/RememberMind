const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

function initDepthCanvas() {
    const canvases = document.querySelectorAll('.rm-depth-canvas');

    canvases.forEach((canvas) => {
        if (canvas.dataset.rmDepthReady === 'true') return;
        canvas.dataset.rmDepthReady = 'true';

        let frame = null;
        let pointerX = 0;
        let pointerY = 0;

        const paint = () => {
            frame = null;
            if (motionQuery.matches) {
                canvas.style.removeProperty('--rm-depth-x');
                canvas.style.removeProperty('--rm-depth-y');
                canvas.style.removeProperty('--rm-depth-scroll');
                return;
            }

            canvas.style.setProperty('--rm-depth-x', `${pointerX * 9}px`);
            canvas.style.setProperty('--rm-depth-y', `${pointerY * 7}px`);
            canvas.style.setProperty('--rm-depth-scroll', `${Math.min(window.scrollY * -0.018, 18)}px`);
        };

        const schedule = () => {
            if (frame === null) frame = window.requestAnimationFrame(paint);
        };

        canvas.addEventListener('pointermove', (event) => {
            const rect = canvas.getBoundingClientRect();
            pointerX = ((event.clientX - rect.left) / Math.max(rect.width, 1) - 0.5) * 2;
            pointerY = ((event.clientY - rect.top) / Math.max(rect.height, 1) - 0.5) * 2;
            schedule();
        }, { passive: true });

        canvas.addEventListener('pointerleave', () => {
            pointerX = 0;
            pointerY = 0;
            schedule();
        }, { passive: true });

        window.addEventListener('scroll', schedule, { passive: true });
        paint();
    });
}

document.addEventListener('DOMContentLoaded', initDepthCanvas);
document.addEventListener('livewire:navigated', initDepthCanvas);

export { initDepthCanvas };
