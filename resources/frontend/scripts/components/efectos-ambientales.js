export function iniciarEfectosAmbientales(AOS) {
// Inicializar AOS
document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 1000,
        once: true,
        offset: 50,
    });

    // Lógica del Mouse Light
    const root = document.documentElement;
    let mouseX = 0;
    let mouseY = 0;
    let currentX = window.innerWidth / 2;
    let currentY = window.innerHeight / 2;

    window.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function updateLight() {
        // Easing suave
        currentX += (mouseX - currentX) * 0.1;
        currentY += (mouseY - currentY) * 0.1;
        
        root.style.setProperty('--mouse-x', `${currentX}px`);
        root.style.setProperty('--mouse-y', `${currentY}px`);
        
        requestAnimationFrame(updateLight);
    }
    updateLight();
});


}
