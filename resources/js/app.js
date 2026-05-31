import './bootstrap';

// Alpine no se inicia aquí porque Livewire v3 ya lo carga automáticamente.
// Esto evita el warning: "Detected multiple instances of Alpine running."
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

// GSAP
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);

// AOS
import AOS from 'aos';
import 'aos/dist/aos.css';

// Chart.js
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
Chart.register(ChartDataLabels);

// Asignar al objeto window para acceso global
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
window.Chart = Chart;

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

import redApoyoTree from './modules/red-apoyo-svg.js';
window.redApoyoTree = redApoyoTree;

// Tema institucional — Geriátrico Jardín de los Recuerdos
import './gama-de-colores/cambio';

