
        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. Iniciar Animaciones de Scroll (AOS)
            if(typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    once: true,
                    offset: 100,
                    easing: 'ease-out-cubic'
                });
            }

            // 2. Efectos Parallax Avanzados (GSAP + ScrollTrigger)
            if(typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);

                // Parallax en blobs del Hero
                gsap.to('.bg-blob-1', { y: 150, ease: "none", scrollTrigger: { trigger: "#inicio", scrub: 0.5 }});
                gsap.to('.bg-blob-2', { y: 250, ease: "none", scrollTrigger: { trigger: "#inicio", scrub: 0.8 }});
                
                // Parallax en sección de imágenes Experiencia
                gsap.to('.parallax-img-1', {
                    y: -40,
                    scrollTrigger: { trigger: "#experiencia", start: "top bottom", end: "bottom top", scrub: 1 }
                });
                gsap.to('.parallax-img-2', {
                    y: -80,
                    scrollTrigger: { trigger: "#experiencia", start: "top bottom", end: "bottom top", scrub: 1.5 }
                });

                // Parallax sutil en fondo de impacto
                gsap.to('.parallax-bg', {
                    y: -100,
                    scrollTrigger: { trigger: "#impacto", start: "top bottom", end: "bottom top", scrub: 0.5 }
                });
            }

            // 3. Ambient Glow Interactivo FLUIDO (GSAP quickTo)
            const light = document.querySelector('.mouse-light');
            if (light && typeof gsap !== 'undefined') {
                gsap.set(light, { xPercent: -50, yPercent: -50 });
                let xTo = gsap.quickTo(light, "x", {duration: 0.6, ease: "power3"});
                let yTo = gsap.quickTo(light, "y", {duration: 0.6, ease: "power3"});

                document.addEventListener('mousemove', (e) => {
                    gsap.to(light, { opacity: 1, duration: 0.5 });
                    xTo(e.clientX);
                    yTo(e.clientY);
                });
                
                document.addEventListener('mouseleave', () => { 
                    gsap.to(light, { opacity: 0, duration: 0.5 }); 
                });
            }
        });

        // 4. Inicialización del Gráfico Chart.js (Alpine Data)
        document.addEventListener('alpine:init', () => {
            Alpine.data('impactDashboard', () => ({
                init() {
                    const ctx = document.getElementById('impactChart');
                    // Esperar a que ScrollTrigger y Chart estén listos
                    if (!ctx || typeof Chart === 'undefined' || typeof ScrollTrigger === 'undefined') return;

                    ScrollTrigger.create({
                        trigger: "#impacto",
                        start: "top 70%",
                        onEnter: () => {
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: ['Mes 1', 'Mes 2', 'Mes 3', 'Mes 4', 'Mes 5', 'Mes 6'],
                                    datasets: [{
                                        label: 'Estabilidad Cognitiva (%)',
                                        data: [65, 72, 78, 85, 88, 92],
                                        borderColor: 'var(--color-boton-acento)',
                                        backgroundColor: 'var(--welcome-chart-fill)',
                                        borderWidth: 4,
                                        tension: 0.4,
                                        fill: true,
                                        pointBackgroundColor: 'var(--welcome-chart-point)',
                                        pointBorderColor: 'var(--color-boton-acento)',
                                        pointBorderWidth: 3,
                                        pointRadius: 6,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { 
                                            beginAtZero: false, 
                                            min: 50,
                                            grid: { color: 'var(--welcome-chart-grid)' },
                                            ticks: { color: 'var(--welcome-chart-ticks)' }
                                        },
                                        x: { 
                                            grid: { display: false },
                                            ticks: { color: 'var(--welcome-chart-ticks)' }
                                        }
                                    }
                                }
                            });
                        },
                        once: true
                    });
                }
            }))
        });
    