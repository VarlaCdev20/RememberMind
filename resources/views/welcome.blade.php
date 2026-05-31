<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS | RememberMind</title>

    <!-- Meta SEO Básico -->
    <meta name="description" content="Cuidado integral, memoria viva y acompañamiento humano para adultos mayores. CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.">
    
    <!-- Favicon / Logo opcional -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>C</text></svg>">

    <!-- CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Íconos Modernos (Phosphor Icons) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- LIBRERÍAS FALTANTES (Corrección de Experto) -->
    <!-- Chart.js para el gráfico de impacto -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- GSAP & ScrollTrigger para animaciones complejas y Parallax -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- AOS CSS & JS para animaciones fluidas al hacer scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <style>
        /* Micro-interacciones personalizadas */
        .hover-underline::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2.5px;
            bottom: -6px;
            left: 50%;
            background-color: #F28B54;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .hover-underline:hover::after {
            width: 100%;
        }
        
        .float-anim {
            animation: float 6s ease-in-out infinite;
        }
        
        .float-anim-delayed {
            animation: float 6s ease-in-out 3s infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        .floating-element {
            position: absolute;
            pointer-events: none;
            opacity: 0.15;
            z-index: 1;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes scrollHint {
            0% { transform: translateY(0); opacity: 0; }
            50% { transform: translateY(10px); opacity: 1; }
            100% { transform: translateY(20px); opacity: 0; }
        }

        .scroll-indicator {
            animation: scrollHint 2s ease-in-out infinite;
        }

        .icon-pulse:hover {
            animation: iconPulse 1s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        /* Ocultar barra de scroll */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Animación para el Carrusel Infinito (Marquee súper rápido) */
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            /* Velocidad rápida de Marquesina */
            animation: marquee 10s linear infinite;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }

        /* Sistema de Sombras Tipográficas */
        .text-shadow-deep {
            text-shadow: 0 8px 24px rgba(7, 60, 53, 0.16), 0 2px 4px rgba(250, 246, 239, 0.75);
        }
        .text-shadow-title {
            text-shadow: 0 6px 18px rgba(7, 60, 53, 0.14), 0 1px 2px rgba(250, 246, 239, 0.75);
        }
        .text-shadow-light {
            text-shadow: 0 2px 8px rgba(7, 60, 53, 0.12);
        }

        /* Ambient Glow (Mouse Light interactivo) SUTIL Y FLUIDO */
        .mouse-light {
            position: fixed;
            top: 0; left: 0;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(94,211,230,0.14) 0%, rgba(242,139,84,0.06) 50%, transparent 80%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 40;
            opacity: 0;
            mix-blend-mode: normal;
        }
    </style>
</head>
<body class="bg-app-institucional font-sans antialiased text-texto-principal min-h-screen overflow-x-hidden selection:bg-fondo-panel selection:text-inverso relative">

    {{-- Textura de Papel (Ruido Orgánico Premium) --}}
    <div class="fixed inset-0 z-[100] pointer-events-none opacity-[0.35] mix-blend-overlay" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    {{-- Trama Geométrica de Puntos (Estructura Sutil) --}}
    <div class="fixed inset-0 z-0 pointer-events-none opacity-[0.40]" style="background-image: radial-gradient(rgba(217,108,51,0.45) 1.5px, transparent 1.5px), linear-gradient(90deg, rgba(198,93,41,0.20) 1px, transparent 1px), linear-gradient(0deg, rgba(217,108,51,0.20) 1px, transparent 1px); background-size: 20px 20px, 60px 60px, 60px 60px;"></div>
    {{-- Efecto Global de Luz Interactiva (Visible y Notorio) --}}
    <div class="mouse-light pointer-events-none"></div>

    {{-- 1. NAVBAR SUPERIOR INTERACTIVO --}}
    <header x-data="{ open: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)" 
            class="fixed inset-x-0 top-4 z-50 w-full transition-all duration-300 pointer-events-none flex items-center">
        <div :class="scrolled 
                ? 'bg-fondo-card backdrop-blur-xl shadow-[0_14px_35px_rgba(47,36,31,0.16)] border border-borde h-16 md:h-18' 
                : 'bg-fondo-card backdrop-blur-md shadow-[0_8px_24px_rgba(47,36,31,0.08)] border border-borde h-20 md:h-22'" 
             class="max-w-7xl mx-auto px-8 sm:px-10 lg:px-12 w-[calc(100%-1rem)] sm:w-[calc(100%-2rem)] rounded-[2rem] transition-all duration-300 flex items-center pointer-events-auto"
             style="backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important;">
            <div class="flex justify-between items-center w-full">
                {{-- Logo Institucional --}}
                <div class="flex-shrink-0 flex items-center gap-2.5 group cursor-pointer z-[60]">
                    <img src="{{ asset('storage/images/LOGO.png') }}"
                         alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                         class="h-9 w-auto object-contain transition-transform duration-500 group-hover:scale-105">
                    <a href="#" class="hidden max-w-[210px] font-outfit text-[11px] font-extrabold uppercase leading-[1.05] tracking-wide text-titulo transition-colors group-hover:text-parrafo sm:block">CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS</a>
                </div>

                {{-- Menú Escritorio --}}
                <div class="hidden lg:flex items-center space-x-5 xl:space-x-8">
                    <a href="#inicio" class="relative flex items-center gap-1.5 text-base font-extrabold text-titulo hover:text-parrafo transition-colors hover-underline group"><i class="ph-bold ph-house text-lg group-hover:scale-110 transition-transform"></i>Inicio</a>
                    <a href="#problema" class="relative flex items-center gap-1.5 text-base font-extrabold text-titulo hover:text-parrafo transition-colors hover-underline group"><i class="ph-bold ph-stethoscope text-lg group-hover:scale-110 transition-transform"></i>Servicios</a>
                    <a href="#experiencia" class="relative flex items-center gap-1.5 text-base font-extrabold text-titulo hover:text-parrafo transition-colors hover-underline group"><i class="ph-bold ph-heart text-lg group-hover:scale-110 transition-transform"></i>Experiencia</a>
                    <a href="#servicios" class="relative flex items-center gap-1.5 text-base font-extrabold text-titulo hover:text-parrafo transition-colors hover-underline group"><i class="ph-bold ph-star text-lg group-hover:scale-110 transition-transform"></i>Actividades</a>
                    <a href="#impacto" class="relative flex items-center gap-1.5 text-base font-extrabold text-titulo hover:text-parrafo transition-colors hover-underline group"><i class="ph-bold ph-chart-line-up text-lg group-hover:scale-110 transition-transform"></i>Impacto</a>
                </div>

                {{-- Botones Escritorio: Tema + Acceso --}}
                <div class="hidden lg:flex items-center gap-3">
                    {{-- Botón modo oscuro --}}
                    <button
                        type="button"
                        data-theme-toggle
                        aria-label="Cambiar modo claro u oscuro"
                        class="flex items-center gap-1.5 rounded-full bg-fondo-panel px-3 py-2 text-xs font-black text-parrafo border border-borde shadow-sm transition-all hover:bg-fondo-panel hover:text-inverso hover:border-borde active:scale-95"
                    >
                        <i class="ph-bold ph-moon text-base" data-theme-icon data-icon-dark="ph-bold ph-moon text-base" data-icon-light="ph-bold ph-sun text-base"></i>
                        <span data-theme-label class="hidden xl:inline">Modo oscuro</span>
                    </button>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primario-institucional relative group px-5 py-2 font-black rounded-full text-sm active:scale-90 active:translate-y-1 overflow-hidden">
                                <span class="relative z-10">Dashboard</span>
                                <div class="absolute inset-0 bg-fondo-card/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></div>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-acento-institucional relative group px-5 py-2 font-black rounded-full text-sm active:scale-90 active:translate-y-1 overflow-hidden">
                                <span class="relative z-10">Acceder al Portal</span>
                                <div class="absolute inset-0 bg-fondo-card/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></div>
                            </a>
                        @endauth
                    @endif
                </div>

                {{-- Botón Menú Móvil (visible hasta lg) --}}
                <div class="lg:hidden flex items-center gap-2">
                    {{-- Botón modo oscuro móvil --}}
                    <button
                        type="button"
                        data-theme-toggle
                        aria-label="Cambiar modo claro u oscuro"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-fondo-panel text-parrafo border border-borde shadow-sm transition-all hover:bg-fondo-panel hover:text-inverso active:scale-95"
                    >
                        <i class="ph-bold ph-moon text-base" data-theme-icon data-icon-dark="ph-bold ph-moon text-base" data-icon-light="ph-bold ph-sun text-base"></i>
                    </button>
                    <button @click.stop="open = true" type="button" class="text-parrafo bg-fondo-panel p-1.5 rounded-full hover:bg-fondo-panel hover:text-inverso focus:outline-none transition-all duration-300 shadow-sm" aria-label="Abrir menú">
                        <i class="ph-bold ph-list text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Overlay para móvil y tablet --}}
        <div x-show="open" x-cloak
             x-transition.opacity.duration.300ms
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[80] lg:hidden pointer-events-auto"
             @click="open = false">
        </div>

        {{-- DRAWER LATERAL MÓVIL (visible hasta lg) --}}
        <aside x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full opacity-70"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-70"
             class="fixed top-0 right-0 h-screen w-[85%] max-w-sm bg-fondo-app shadow-[0_0_50px_rgba(47,36,31,0.30)] z-[90] lg:hidden flex flex-col border-l border-borde pointer-events-auto">
             
            <div class="p-5 flex justify-between items-center border-b border-borde">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('storage/images/LOGO.png') }}"
                         alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                         class="h-10 w-auto object-contain">
                    <div class="leading-tight">
                        <p class="font-outfit text-xs font-extrabold uppercase text-titulo">CENTRO GERIÁTRICO</p>
                        <p class="font-outfit text-xs font-extrabold uppercase text-parrafo">JARDÍN DE LOS RECUERDOS</p>
                    </div>
                </div>
                <button @click="open = false" type="button" class="text-titulo p-2 bg-fondo-panel rounded-full hover:bg-fondo-panel hover:text-inverso transition-all shadow-md active:scale-90" aria-label="Cerrar menú">
                    <i class="ph-bold ph-x text-xl"></i>
                </button>
            </div>

            <div class="px-6 py-6 flex flex-col gap-3 overflow-y-auto flex-grow">
                <a @click="open = false" href="#inicio" class="mobile-welcome-link"><i class="ph-bold ph-house text-2xl"></i>Inicio</a>
                <a @click="open = false" href="#problema" class="mobile-welcome-link"><i class="ph-bold ph-stethoscope text-2xl"></i>Servicios</a>
                <a @click="open = false" href="#experiencia" class="mobile-welcome-link"><i class="ph-bold ph-heart text-2xl"></i>Experiencia</a>
                <a @click="open = false" href="#servicios" class="mobile-welcome-link"><i class="ph-bold ph-star text-2xl"></i>Actividades</a>
                <a @click="open = false" href="#impacto" class="mobile-welcome-link"><i class="ph-bold ph-chart-line-up text-2xl"></i>Impacto</a>

                <div class="mt-auto pt-6 border-t border-borde space-y-3">
                    {{-- Botón modo oscuro en móvil --}}
                    <button
                        type="button"
                        data-theme-toggle
                        aria-label="Cambiar modo claro u oscuro"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-fondo-panel border border-borde px-5 py-3 text-base font-black text-parrafo transition-all hover:bg-fondo-panel hover:text-inverso hover:border-borde active:scale-95"
                    >
                        <i class="ph-bold ph-moon text-xl" data-theme-icon data-icon-dark="ph-bold ph-moon text-xl" data-icon-light="ph-bold ph-sun text-xl"></i>
                        <span data-theme-label>Modo oscuro</span>
                    </button>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primario-institucional block text-center w-full px-5 py-4 font-black rounded-full text-lg active:scale-95 transition-transform">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn-acento-institucional block text-center w-full px-5 py-4 font-black rounded-full text-lg active:scale-95 transition-transform">Acceder al Portal</a>
                        @endauth
                    @endif
                </div>
            </div>
        </aside>
    </header>

    {{-- 2. HERO PRINCIPAL --}}
    <section id="inicio" class="relative min-h-screen flex flex-col justify-center pt-36 md:pt-40 pb-12 overflow-hidden z-0 w-full">
        
        {{-- Blobs Envolventes Suaves (Con efecto Parallax) --}}
        <div class="hero-blob bg-blob-1 absolute top-10 left-10 w-[40rem] h-[40rem] bg-fondo-panel rounded-full mix-blend-multiply filter blur-[120px] animate-pulse-slow -z-10"></div>
        <div class="hero-blob bg-blob-2 absolute top-40 right-0 w-[35rem] h-[35rem] bg-fondo-panel rounded-full mix-blend-multiply filter blur-[120px] animate-pulse-slow -z-10" style="animation-delay: 2s;"></div>
        <div class="hero-blob bg-blob-3 absolute -bottom-20 left-1/4 w-[45rem] h-[35rem] bg-fondo-panel rounded-[100%] mix-blend-multiply filter blur-[120px] animate-pulse-slow -z-10" style="animation-delay: 4s;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center relative z-10 flex flex-col justify-center mt-6">
            
            {{-- Elementos Decorativos - Gran Población para Atmósfera Inmersiva --}}
            <div class="floating-element top-[-5%] left-[10%] text-titulo text-6xl hidden lg:block" style="animation-delay: 0s;"><i class="ph ph-heart"></i></div>
            <div class="floating-element top-[10%] left-[25%] text-parrafo text-3xl hidden lg:block" style="animation-delay: 3s;"><i class="ph ph-hand-heart"></i></div>
            <div class="floating-element bottom-[30%] left-[8%] text-parrafo text-4xl hidden lg:block" style="animation-delay: 2s;"><i class="ph ph-brain"></i></div>
            <div class="floating-element bottom-[10%] left-[20%] text-titulo text-2xl hidden lg:block" style="animation-delay: 5s;"><i class="ph ph-house"></i></div>
            <div class="floating-element top-[40%] left-[2%] text-titulo text-3xl hidden lg:block" style="animation-delay: 2.5s;"><i class="ph ph-coffee"></i></div>
            <div class="floating-element top-[60%] left-[15%] text-parrafo text-4xl hidden lg:block" style="animation-delay: 7s; opacity: 0.1;"><i class="ph ph-leaf"></i></div>
            <div class="floating-element top-[20%] left-[45%] text-titulo text-2xl hidden lg:block" style="animation-delay: 1s; opacity: 0.08;"><i class="ph ph-stethoscope"></i></div>
            
            <div class="floating-element top-[5%] right-[15%] text-titulo text-5xl hidden lg:block" style="animation-delay: 4s;"><i class="ph ph-sun"></i></div>
            <div class="floating-element top-[25%] right-[5%] text-parrafo text-4xl hidden lg:block" style="animation-delay: 1.5s;"><i class="ph ph-pill"></i></div>
            <div class="floating-element bottom-[15%] right-[10%] text-titulo text-6xl hidden lg:block" style="animation-delay: 1s;"><i class="ph ph-hands-holding"></i></div>
            <div class="floating-element bottom-[40%] right-[20%] text-parrafo text-2xl hidden lg:block" style="animation-delay: 6s;"><i class="ph ph-flower"></i></div>
            <div class="floating-element top-[50%] right-[5%] text-titulo text-4xl hidden lg:block" style="animation-delay: 3.5s; opacity: 0.12;"><i class="ph ph-users-three"></i></div>
            <div class="floating-element bottom-[5%] right-[30%] text-parrafo text-3xl hidden lg:block" style="animation-delay: 4.5s; opacity: 0.1;"><i class="ph ph-first-aid"></i></div>
            <div class="floating-element top-[70%] right-[12%] text-titulo text-2xl hidden lg:block" style="animation-delay: 2.2s; opacity: 0.09;"><i class="ph ph-calendar"></i></div>

            <div class="hero-text flex flex-col items-center">

                {{-- Logo institucional hero --}}
                <div class="mb-5 md:mb-7" data-aos="fade-down" data-aos-delay="-50">
                    <img src="{{ asset('storage/images/LOGO.png') }}"
                         alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                         class="h-20 w-auto object-contain drop-shadow-lg mx-auto">
                </div>

                {{-- Badge de Confianza --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-fondo-panel border border-borde text-parrafo text-xs md:text-sm font-bold mb-6 md:mb-8" data-aos="fade-down">
                    <span class="flex h-2 w-2 rounded-full bg-fondo-panel animate-pulse"></span>
                    Confianza de +500 familias en la región
                </div>

                {{-- TITULO PRINCIPAL --}}
                <h1 class="font-outfit text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-titulo leading-[1.05] mb-6 md:mb-8 tracking-tighter uppercase text-shadow-deep text-center">
                    JARDÍN DE
                    <span class="text-parrafo relative whitespace-nowrap block sm:inline-block">
                        LOS RECUERDOS
                        <svg class="absolute -bottom-2 left-0 w-full text-apoyo -z-10 parallax-underline" viewBox="0 0 100 20" preserveAspectRatio="none">
                            <path d="M0,10 Q50,20 100,10" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h1>

                <p class="text-sm font-black uppercase tracking-widest text-titulo/60 mb-3 md:mb-4" data-aos="fade-up" data-aos-delay="150">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </p>

                <p class="text-lg md:text-3xl font-medium text-titulo/90 mb-4 md:mb-6 leading-relaxed max-w-4xl" data-aos="fade-up" data-aos-delay="200">
                    Cuidado integral, memoria viva y acompañamiento humano para adultos mayores.
                </p>

                <p class="text-base md:text-xl text-titulo/80 mb-8 md:mb-10 max-w-3xl mx-auto leading-relaxed font-medium" data-aos="fade-up" data-aos-delay="300">
                    Brindamos atención integral a nuestros adultos mayores, apoyados por tecnología avanzada para el seguimiento temprano del deterioro cognitivo y memoria.
                </p>
                
                <div class="flex flex-col sm:flex-row flex-wrap items-center justify-center gap-4 sm:gap-6 w-full sm:w-auto" data-aos="zoom-in" data-aos-delay="400">
                    <a href="#experiencia" class="btn-acento-institucional w-full sm:w-auto relative overflow-hidden group px-8 md:px-10 py-4 md:py-5 font-extrabold rounded-full text-base md:text-lg transition-all duration-300 hover:-translate-y-1 active:scale-95 active:translate-y-1 shadow-lg text-center">
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Vive la Experiencia
                            <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        <div class="absolute inset-0 bg-fondo-card/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out"></div>
                    </a>
                    <a href="#problema" class="btn-secundario-institucional w-full sm:w-auto relative overflow-hidden group px-8 md:px-10 py-4 md:py-5 font-extrabold rounded-full text-base md:text-lg transition-all duration-300 hover:-translate-y-1 active:scale-95 active:translate-y-1 shadow-md text-center">
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <i class="ph-bold ph-star text-xl group-hover:rotate-180 transition-transform duration-700"></i>
                            Nuestros Servicios
                        </span>
                        <div class="absolute inset-0 bg-fondo-panel translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out"></div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Carrusel Automático Infinito y Orgánico --}}
        <div class="mt-16 md:mt-24 w-full relative overflow-hidden flex flex-col justify-center" data-aos="fade-in" data-aos-delay="600">
            
            {{-- Viñetas laterales para efecto infinito --}}
            <div class="absolute left-0 top-0 bottom-0 w-8 md:w-32 bg-gradient-to-r from-[#FAF6EF] to-transparent z-20 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-8 md:w-32 bg-gradient-to-l from-[#FAF6EF] to-transparent z-20 pointer-events-none"></div>

            <div class="inline-flex gap-4 md:gap-8 animate-marquee w-max py-8 hover:cursor-grab active:cursor-grabbing">
                @php
                    $images = [
                        'https://images.unsplash.com/photo-1551076805-e18690c5e53b?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1573668202287-c866d9f3f4c6?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1527613426441-4da17471b66d?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1516843468087-0b8bb0849929?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1551076805-e18690c5e53b?auto=format&fit=crop&w=600&q=80'
                    ];
                    $loopImages = array_merge($images, $images);
                @endphp
                
                @foreach($loopImages as $i => $img)
                    <div class="shrink-0 overflow-hidden shadow-[0_15px_30px_rgba(47,36,31,0.14)] relative border-4 border-borde transition-transform duration-500 hover:-translate-y-2 hover:shadow-[0_25px_50px_rgba(0,107,94,0.20)]"
                         style="width: 200px; height: 280px; border-radius: 8rem; {{ $i % 2 !== 0 ? 'transform: translateY(20px);' : '' }}">
                        <img src="{{ $img }}" alt="Fotografía" class="object-cover w-full h-full hover:scale-110 transition-transform duration-1000">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Indicador de Scroll --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-titulo/40 z-20" data-aos="fade-up" data-aos-delay="1000">
            <span class="text-[10px] font-black uppercase tracking-widest">Descubrir</span>
            <div class="w-[1px] h-12 bg-gradient-to-b from-azul-profundo/40 to-transparent relative">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-fondo-panel rounded-full scroll-indicator"></div>
            </div>
        </div>
    </section>

    {{-- 3. SERVICIOS QUE OFRECEMOS --}}
    <section id="problema" class="py-16 md:py-24 lg:py-32 bg-fondo-panel relative overflow-hidden">
        {{-- Iconos flotantes en Servicios --}}
        <div class="floating-element top-[10%] left-[5%] text-titulo text-5xl opacity-[0.05]" style="animation-delay: 1s;"><i class="ph ph-stethoscope"></i></div>
        <div class="floating-element bottom-[15%] right-[5%] text-parrafo text-6xl opacity-[0.07]" style="animation-delay: 3s;"><i class="ph ph-heartbeat"></i></div>
        <div class="floating-element top-[40%] right-[10%] text-titulo text-4xl opacity-[0.05]" style="animation-delay: 5s;"><i class="ph ph-user-circle"></i></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-12 md:mb-16" data-aos="fade-up">
                <h2 class="font-outfit text-4xl md:text-5xl lg:text-6xl font-extrabold text-titulo mb-4 md:mb-6 text-shadow-title">
                    Servicios que Ofrecemos
                </h2>
                <p class="text-lg md:text-xl text-titulo/90 font-medium">
                    Diseñamos un ecosistema de cuidado integral que garantiza bienestar físico, mental y emocional en todo momento para nuestros adultos mayores.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <div class="card-interactiva group rounded-[3rem] p-8 md:p-10 cursor-default" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-fondo-panel rounded-2xl flex items-center justify-center mb-8 group-hover:bg-fondo-panel group-hover:text-inverso transition-all duration-500 text-parrafo shadow-inner group-hover:rounded-full group-hover:rotate-12">
                        <i class="ph-fill ph-stethoscope text-3xl icon-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-titulo mb-4 group-hover:text-parrafo transition-colors">Cuidado Médico</h3>
                    <p class="text-texto-secundario text-base md:text-lg leading-relaxed font-medium">
                        Monitoreo constante de signos vitales, administración de medicamentos y asistencia médica preventiva.
                    </p>
                </div>

                <div class="card-interactiva group rounded-[3rem] p-8 md:p-10 cursor-default" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-fondo-panel rounded-2xl flex items-center justify-center mb-8 group-hover:bg-fondo-panel group-hover:text-inverso transition-all duration-500 text-parrafo shadow-inner group-hover:rounded-full group-hover:rotate-12">
                        <i class="ph-fill ph-brain text-3xl icon-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-titulo mb-4 group-hover:text-parrafo transition-colors">Estimulación</h3>
                    <p class="text-texto-secundario text-base md:text-lg leading-relaxed font-medium">
                        Terapias especializadas y actividades diarias diseñadas para fortalecer la memoria.
                    </p>
                </div>

                <div class="card-interactiva group rounded-[3rem] p-8 md:p-10 cursor-default" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-fondo-panel rounded-2xl flex items-center justify-center mb-8 group-hover:bg-fondo-panel group-hover:text-inverso transition-all duration-500 text-parrafo shadow-inner group-hover:rounded-full group-hover:rotate-12">
                        <i class="ph-fill ph-hands-clapping text-3xl icon-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-titulo mb-4 group-hover:text-parrafo transition-colors">Acompañamiento</h3>
                    <p class="text-texto-secundario text-base md:text-lg leading-relaxed font-medium">
                        Personal altamente capacitado y empático que brinda compañía, asistencia y seguridad total.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. SECCIÓN: EXPERIENCIA (Con Parallax Images) --}}
    <section id="experiencia" class="py-16 md:py-24 lg:py-32 relative overflow-hidden bg-fondo-app">
        {{-- Iconos flotantes en Experiencia --}}
        <div class="floating-element top-[20%] right-[15%] text-parrafo text-5xl opacity-[0.08]" style="animation-delay: 2s;"><i class="ph ph-hand-waving"></i></div>
        <div class="floating-element bottom-[10%] left-[10%] text-titulo text-4xl opacity-[0.06]" style="animation-delay: 4s;"><i class="ph ph-peace"></i></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                
                {{-- Texto --}}
                <div class="space-y-6 md:space-y-8" data-aos="fade-right">
                    <h2 class="font-outfit text-4xl sm:text-5xl lg:text-6xl font-extrabold text-titulo leading-[1.1] text-shadow-title">
                        El valor profundo de <br class="hidden sm:block"><span class="text-parrafo">sentirse en casa</span>.
                    </h2>
                    
                    <div class="space-y-4 md:space-y-6 text-lg md:text-xl text-titulo/90 leading-relaxed font-medium">
                        <p>
                            En CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS reemplazamos los ambientes fríos por espacios llenos de luz, risas y comprensión. Creemos fielmente en que la edad dorada debe celebrarse rodeada de paciencia y calidez humana.
                        </p>
                        <p>
                            Nuestro equipo no solo asiste; acompaña. Respetamos los tiempos, las historias y las memorias de cada residente.
                        </p>
                    </div>

                    <ul class="space-y-4 md:space-y-6 pt-4 md:pt-6">
                        <li class="card-interactiva group flex items-center gap-4 md:gap-5 p-4 md:p-5 rounded-[2rem] cursor-default">
                            <div class="flex-shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-fondo-panel text-parrafo flex items-center justify-center transition-transform duration-500 group-hover:rotate-12 group-hover:rounded-full group-hover:scale-110 shadow-inner">
                                <i class="ph-fill ph-sun-horizon text-2xl md:text-3xl"></i>
                            </div>
                            <span class="text-xl md:text-2xl font-bold text-titulo group-hover:text-parrafo transition-colors">Ambiente cálido y familiar</span>
                        </li>
                        <li class="card-interactiva group flex items-center gap-4 md:gap-5 p-4 md:p-5 rounded-[2rem] cursor-default">
                            <div class="flex-shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-fondo-panel text-parrafo flex items-center justify-center transition-transform duration-500 group-hover:rotate-12 group-hover:rounded-full group-hover:scale-110 shadow-inner">
                                <i class="ph-fill ph-hand-heart text-2xl md:text-3xl"></i>
                            </div>
                            <span class="text-xl md:text-2xl font-bold text-titulo group-hover:text-parrafo transition-colors">Vocación de servicio real</span>
                        </li>
                    </ul>
                </div>

                {{-- Collage Orgánico (Con Parallax) --}}
                <div class="relative h-[400px] sm:h-[500px] md:h-[650px] w-full mt-10 lg:mt-0" data-aos="fade-left">
                    <div class="parallax-img-1 absolute top-0 right-0 w-[85%] h-[75%] rounded-[2.5rem] md:rounded-[3rem] overflow-hidden shadow-[0_30px_60px_rgba(47,36,31,0.22)] z-20 group border-4 md:border-8 border-borde">
                        <img src="https://images.unsplash.com/photo-1527613426441-4da17471b66d?auto=format&fit=crop&w=800&q=80" alt="Acompañamiento humano" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        <div class="absolute inset-0 bg-fondo-panel group-hover:bg-fondo-panel transition-colors duration-500"></div>
                    </div>
                    
                    <div class="parallax-img-2 absolute bottom-0 left-0 w-[70%] h-[55%] rounded-[2.5rem] md:rounded-[3rem] overflow-hidden shadow-[0_30px_60px_rgba(47,36,31,0.22)] border-4 md:border-[8px] border-borde z-30 group">
                        <img src="https://images.unsplash.com/photo-1551076805-e18690c5e53b?auto=format&fit=crop&w=600&q=80" alt="Terapia y cuidado" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        <div class="absolute inset-0 bg-fondo-panel group-hover:bg-fondo-panel transition-colors duration-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. SECCIÓN: ACTIVIDADES Y DETALLES --}}
    <section id="servicios" class="py-16 md:py-24 lg:py-32 bg-fondo-panel relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto mb-12 md:mb-20" data-aos="fade-up">
                <h2 class="font-outfit text-4xl md:text-5xl lg:text-6xl font-extrabold text-titulo mb-4 md:mb-6 text-shadow-title">
                    Atención diseñada con el <span class="text-parrafo">corazón</span>
                </h2>
                <p class="text-lg md:text-2xl text-titulo/90 font-medium">
                    Actividades pensadas milimétricamente para mantener la salud física, la paz emocional y el brillo cognitivo.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                @php
                    $actividades = [
                        ['Nutrición Adaptada', 'Dietas personalizadas elaboradas por especialistas para mantener una salud digestiva.', 'ph-apple-logo', '#F28B54', 'bg-fondo-panel'],
                        ['Fisioterapia', 'Movimiento constante guiado por expertos para conservar la movilidad.', 'ph-person-arms-spread', '#006B5E', 'bg-fondo-panel'],
                        ['Arte y Color', 'Espacios creativos donde la pintura libera el estrés y fomenta la expresión.', 'ph-palette', '#2EA9C0', 'bg-fondo-panel'],
                        ['Musicoterapia', 'Tardes musicales que despiertan emociones y devuelven la sonrisa.', 'ph-music-notes', '#F28B54', 'bg-fondo-panel'],
                        ['Lazos Familiares', 'Organizamos celebraciones para que el vínculo familiar nunca se debilite.', 'ph-users-three', '#0B4F46', 'bg-fondo-panel'],
                        ['Paz Mental', 'Psicólogos disponibles para asegurar que cada día se viva con tranquilidad.', 'ph-flower-lotus', '#006B5E', 'bg-fondo-panel'],
                    ];
                @endphp

                @foreach($actividades as $index => $s)
                    <div class="card-interactiva group h-full flex flex-col rounded-[3rem] p-8 md:p-10 cursor-default" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                        <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl {{ $s[4] }} flex items-center justify-center mb-6 transition-all duration-500 group-hover:scale-110 shadow-inner group-hover:rounded-full group-hover:rotate-12">
                            <i class="ph-fill {{ $s[2] }} text-2xl md:text-3xl icon-pulse" style="color: {{ $s[3] }};"></i>
                        </div>
                        <h3 class="text-xl md:text-2xl font-bold text-titulo mb-3 md:mb-4 group-hover:text-[{{ $s[3] }}] transition-colors">{{ $s[0] }}</h3>
                        <p class="text-texto-secundario text-base md:text-lg leading-relaxed font-medium flex-grow">
                            {{ $s[1] }}
                        </p>
                        
                        <div class="w-8 h-1.5 bg-fondo-panel rounded-full mt-6 transition-all duration-500 group-hover:bg-[{{ $s[3] }}] group-hover:w-full"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 6. IMPACTO E INSTITUCIÓN --}}
    <section id="impacto" class="py-16 md:py-24 bg-fondo-panel relative overflow-hidden text-inverso" x-data="impactDashboard()">
        {{-- Patrón de fondo con ligero parallax --}}
        <div class="parallax-bg absolute inset-0 opacity-12" style="background-image: radial-gradient(#5ED3E6 1px, transparent 1px); background-size: 30px 30px; height: 130%;"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div data-aos="fade-right">
                    <span class="text-parrafo font-bold tracking-wider uppercase text-sm mb-4 block">Nuestro Impacto</span>
                    <h2 class="font-outfit text-4xl md:text-5xl font-extrabold mb-6 text-shadow-title">
                        Tecnología al servicio de la <span class="text-parrafo">memoria</span>
                    </h2>
                    <p class="text-base md:text-lg text-inverso/90 font-bold mb-8 leading-relaxed">
                        El sistema RememberMind nos permite visualizar la evolución cognitiva de nuestros residentes en tiempo real. 
                        Este seguimiento métrico reduce en un 40% las detecciones tardías y optimiza el tiempo de nuestros cuidadores.
                    </p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="group border-l-4 border-borde pl-4 transition-all duration-300 hover:border-white">
                            <p class="text-4xl md:text-5xl font-outfit font-extrabold text-inverso group-hover:scale-105 origin-left transition-transform text-shadow-title">98%</p>
                            <p class="text-sm md:text-base text-inverso/80 font-bold mt-2">Familiares satisfechos con el reporte continuo</p>
                        </div>
                        <div class="group border-l-4 border-borde pl-4 transition-all duration-300 hover:border-white">
                            <p class="text-4xl md:text-5xl font-outfit font-extrabold text-inverso group-hover:scale-105 origin-left transition-transform text-shadow-title">+500</p>
                            <p class="text-sm md:text-base text-inverso/80 font-bold mt-2">Horas ahorradas en gestión administrativa al mes</p>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="{{ route('admin.adultos-mayores.reporte-institucional') }}" target="_blank" class="inline-flex items-center gap-2 rounded-full bg-fondo-card/10 px-6 py-3 text-sm font-black text-inverso border border-white/20 transition hover:bg-fondo-card/20 hover:scale-105 active:scale-95">
                            <i class="ph-bold ph-file-pdf"></i> Impacto Institucional
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reporte-bienestar') }}" target="_blank" class="inline-flex items-center gap-2 rounded-full bg-fondo-card/10 px-6 py-3 text-sm font-black text-inverso border border-white/20 transition hover:bg-fondo-card/20 hover:scale-105 active:scale-95">
                            <i class="ph-bold ph-heartbeat"></i> Bienestar y Salud
                        </a>
                    </div>
                </div>

                {{-- Contenedor del Gráfico Interactivo --}}
                <div class="group bg-fondo-card/10 backdrop-blur-md rounded-[2.5rem] md:rounded-[3rem] p-6 md:p-8 border border-borde shadow-[0_30px_60px_rgba(0,0,0,0.34)] transition-all duration-500 hover:bg-fondo-card/15 hover:shadow-[0_40px_80px_rgba(0,0,0,0.48)] hover:border-borde w-full overflow-hidden" data-aos="fade-left">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base md:text-lg font-bold">Evolución Cognitiva Promedio</h3>
                        <div class="w-3 h-3 rounded-full bg-fondo-panel animate-ping"></div>
                    </div>
                    
                    <div class="relative h-48 md:h-64 w-full bg-black/20 rounded-3xl p-3 shadow-inner border border-white/5">
                        <canvas id="impactChart"></canvas>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between text-xs sm:text-sm text-inverso/70 font-medium gap-2">
                        <span>Datos ilustrativos del sistema.</span>
                        <span class="font-bold text-inverso">Actualizado hoy</span>
                    </div>
                </div>
            </div>
        </div>

    </section>

    {{-- 7. FOOTER INTERACTIVO --}}
    <footer id="contacto" class="bg-fondo-app pt-16 md:pt-20 pb-8 border-t border-borde">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10 md:gap-12 mb-12 md:mb-16">
                
                <div class="sm:col-span-2" data-aos="fade-up">
                    <div class="flex items-center gap-3 mb-6 group cursor-pointer w-fit">
                        <img src="{{ asset('storage/images/LOGO.png') }}"
                             alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                             class="h-12 w-auto object-contain transition-transform duration-500 group-hover:scale-105">
                        <h2 class="font-outfit text-xl font-extrabold tracking-tight text-titulo group-hover:text-parrafo transition-colors leading-tight">CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS</h2>
                    </div>
                    <p class="text-titulo font-semibold mb-8 max-w-sm leading-relaxed text-base">
                        Un hogar de amor, tecnología y respeto. Dedicados a mejorar la calidad de vida y proteger la memoria de quienes más queremos.
                    </p>
                    
                    {{-- Botones de Redes Sociales --}}
                    <div class="flex flex-wrap gap-4">
                        @php
                            $socials = [
                                ['Facebook', 'ph-fill ph-facebook-logo', '#1877F2'],
                                ['Instagram', 'ph-fill ph-instagram-logo', '#E4405F'],
                                ['WhatsApp', 'ph-fill ph-whatsapp-logo', '#25D366'],
                            ];
                        @endphp
                        
                        @foreach($socials as $social)
                            <a href="#" class="group flex items-center gap-0 overflow-hidden rounded-full bg-fondo-card p-3 shadow-md border border-borde transition-all duration-300 hover:gap-3 hover:px-5 hover:shadow-lg hover:border-borde hover:-translate-y-1 active:scale-95">
                                <i class="{{ $social[1] }} text-xl text-titulo/80 group-hover:text-[{{ $social[2] }}] transition-colors"></i>
                                <span class="w-0 overflow-hidden whitespace-nowrap text-sm font-bold text-titulo opacity-0 transition-all duration-300 group-hover:w-auto group-hover:opacity-100">
                                    {{ $social[0] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="100">
                    <h4 class="font-black text-titulo text-lg mb-6">Enlaces Rápidos</h4>
                    <ul class="space-y-4 text-base font-bold text-titulo/80">
                        <li><a href="#inicio" class="hover:text-parrafo transition-colors hover:translate-x-1 inline-block">Inicio</a></li>
                        <li><a href="#problema" class="hover:text-parrafo transition-colors hover:translate-x-1 inline-block">Servicios</a></li>
                        <li><a href="#experiencia" class="hover:text-parrafo transition-colors hover:translate-x-1 inline-block">Experiencia</a></li>
                        <li><a href="#servicios" class="hover:text-parrafo transition-colors hover:translate-x-1 inline-block">Actividades</a></li>
                        @if (Route::has('login'))
                            <li><a href="{{ route('login') }}" class="hover:text-parrafo transition-colors hover:translate-x-1 inline-block">Acceso Institucional</a></li>
                        @endif
                    </ul>
                </div>

                <div data-aos="fade-up" data-aos-delay="200">
                    <h4 class="font-black text-titulo text-lg mb-6">Contáctanos</h4>
                    <ul class="space-y-4 text-base font-bold text-titulo/80">
                        <li class="flex items-start gap-3 group cursor-pointer">
                            <i class="ph-fill ph-map-pin text-2xl text-parrafo mt-0.5 group-hover:-translate-y-1 transition-transform"></i>
                            <span class="group-hover:text-titulo transition-colors">Av. Principal 123, Ciudad<br>Zona Central</span>
                        </li>
                        <li class="flex items-center gap-3 group cursor-pointer">
                            <i class="ph-fill ph-phone-call text-2xl text-parrafo group-hover:scale-110 transition-transform"></i>
                            <span class="group-hover:text-titulo transition-colors">+1 234 567 890</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="border-t border-borde pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm font-bold text-titulo/80 text-center md:text-left">
                <p>&copy; {{ date('Y') }} CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS. Todos los derechos reservados.</p>
                <p>Potenciado por <span class="text-titulo font-black">RememberMind System</span></p>
            </div>
        </div>
    </footer>

    {{-- LÓGICA DE ANIMACIONES Y EFECTOS EXPERTOS --}}
    <script>
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
                                        borderColor: '#F28B54',
                                        backgroundColor: 'rgba(242, 139, 84, 0.20)',
                                        borderWidth: 4,
                                        tension: 0.4,
                                        fill: true,
                                        pointBackgroundColor: '#fff',
                                        pointBorderColor: '#F28B54',
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
                                            grid: { color: 'rgba(255,255,255,0.05)' },
                                            ticks: { color: '#97E3D5' }
                                        },
                                        x: { 
                                            grid: { display: false },
                                            ticks: { color: '#97E3D5' }
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
    </script>
</body>
</html>
