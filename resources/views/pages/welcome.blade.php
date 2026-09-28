@php
    $photoBase = 'images/FOTOS CENTRO DE ADULTOS MAYORES';
    $gallery = [
        ['file' => '489963938_1158744422930145_8442506970304201426_n.jpg', 'title' => 'Bienestar en compañía', 'copy' => 'Actividades acompañadas que favorecen la calma, la participación y el bienestar cotidiano.'],
        ['file' => '569897738_1324313409706578_2951129905561208154_n.jpg', 'title' => 'Autonomía en cada actividad', 'copy' => 'Espacios acompañados que estimulan la participación y la vida diaria.'],
        ['file' => '585367654_1351376833666902_8115277307804264176_n.jpg', 'title' => 'Celebrar juntos', 'copy' => 'Música, cultura y encuentros que mantienen vivas las historias personales.'],
        ['file' => '595693419_1366687118802540_7877864884520394638_n.jpg', 'title' => 'Experiencias fuera de casa', 'copy' => 'Salidas planificadas para conectar con la ciudad, el arte y la comunidad.'],
        ['file' => '598018796_1368093295328589_9135523863250142558_n.jpg', 'title' => 'Lazos entre generaciones', 'copy' => 'Encuentros que despiertan afecto, conversación y nuevas memorias.'],
        ['file' => '600320307_1368093028661949_6493928930338402983_n.jpg', 'title' => 'Alegría compartida', 'copy' => 'Dinámicas recreativas pensadas para moverse, sonreír y acompañarse.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cuidado integral, memoria activa y acompañamiento humano para adultos mayores en el Centro Geriátrico Los Almendros.">
    <meta name="theme-color" content="#c8d8c7">
    <title>Centro Geriátrico Los Almendros</title>
    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>{!! file_get_contents(resource_path('frontend/styles/modules/pages-welcome.css')) !!}</style>
</head>
<body class="welcome-page">
    <a class="welcome-skip" href="#contenido">Saltar al contenido</a>
    <div class="welcome-cursor-light" data-cursor-light aria-hidden="true"></div>

    <header class="welcome-header" data-header>
        <div class="welcome-shell welcome-nav">
            <a href="#inicio" class="welcome-brand" aria-label="Ir al inicio">
                <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="Logo del Centro Geriátrico Los Almendros" width="66" height="66">
                <span><small>Centro Geriátrico</small>Los Almendros</span>
            </a>
            <nav class="welcome-desktop-nav" aria-label="Navegación principal">
                <a class="is-active" href="#inicio">Inicio</a>
                <a href="#servicios">Servicios</a>
                <a href="#filosofia">Nuestra filosofía</a>
                <a href="#experiencia">Experiencia</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <div class="welcome-nav-actions">
                <a class="welcome-button welcome-button--portal" href="{{ auth()->check() ? url('/dashboard') : route('login') }}">
                    <i class="ph ph-user-circle" aria-hidden="true"></i>
                    <span>{{ auth()->check() ? 'Ir al Dashboard' : 'Acceder al Portal' }}</span>
                </a>
                <button class="welcome-menu-button" type="button" data-menu-button aria-expanded="false" aria-controls="welcome-mobile-menu" aria-label="Abrir menú">
                    <i class="ph ph-list" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div id="welcome-mobile-menu" class="welcome-mobile-menu" data-mobile-menu hidden>
            <nav aria-label="Navegación móvil">
                <a href="#inicio">Inicio</a><a href="#servicios">Servicios</a><a href="#filosofia">Nuestra filosofía</a><a href="#experiencia">Experiencia</a><a href="#contacto">Contacto</a>
            </nav>
        </div>
    </header>

    <main id="contenido">
        <section id="inicio" class="welcome-hero">
            <div class="welcome-hero-glow" aria-hidden="true"></div>
            <div class="welcome-depth-mesh" data-parallax="-0.018" aria-hidden="true"></div>
            <span class="welcome-depth-orb welcome-depth-orb--one" data-parallax="0.025" aria-hidden="true"></span>
            <span class="welcome-depth-orb welcome-depth-orb--two" data-parallax="-0.035" aria-hidden="true"></span>
            <span class="welcome-floating-seed welcome-floating-seed--one" data-parallax="0.075" aria-hidden="true"><i class="ph ph-heartbeat"></i></span>
            <span class="welcome-floating-seed welcome-floating-seed--two" data-parallax="-0.055" aria-hidden="true"><i class="ph ph-first-aid"></i></span>
            <span class="welcome-floating-seed welcome-floating-seed--three" data-parallax="0.045" aria-hidden="true"><i class="ph ph-stethoscope"></i></span>
            <div class="welcome-leaf welcome-leaf--left" data-parallax="0.08" aria-hidden="true"><i class="ph ph-shield-check"></i></div>
            <div class="welcome-shell welcome-hero-grid">
                <div class="welcome-hero-copy" data-aos="fade-right">
                    <p class="welcome-eyebrow"><span>Centro geriátrico · Atención integral</span></p>
                    <h1>Los<br><em>Almendros</em></h1>
                    <p class="welcome-lead">Cuidado integral, memoria viva y acompañamiento humano para adultos mayores.</p>
                    <div class="welcome-hero-actions">
                        <a class="welcome-button welcome-button--primary" href="#experiencia"><i class="ph ph-heartbeat" aria-hidden="true"></i><span>Conoce nuestra experiencia</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a>
                        <a class="welcome-button welcome-button--outline" href="#filosofia"><span class="welcome-play"><i class="ph-fill ph-play" aria-hidden="true"></i></span><span>Ver nuestro enfoque</span></a>
                    </div>
                    <div class="welcome-trust-row" aria-label="Características principales">
                        <span><i class="ph ph-stethoscope"></i> Seguimiento clínico</span><span><i class="ph ph-hand-heart"></i> Atención humana</span><span><i class="ph ph-shield-check"></i> Cuidado seguro</span>
                    </div>
                </div>
                <div class="welcome-hero-visual" data-aos="fade-left" data-parallax="0.035">
                    <div class="welcome-hero-photo-wrap">
                        <img src="{{ asset($photoBase.'/577031711_1337134741757778_4846830420773518569_n.jpg') }}" alt="Profesional acompañando a una adulta mayor durante una actividad física" class="welcome-hero-photo" fetchpriority="high">
                        <div class="welcome-photo-caption">Cuidado clínico con calidez humana</div>
                    </div>
                    <div class="welcome-floating-note"><i class="ph ph-first-aid"></i><span><strong>Atención geriátrica</strong>Presencia profesional 24/7</span></div>
                    <div class="welcome-photo-orbit" aria-hidden="true"><i class="ph ph-heartbeat"></i></div>
                </div>
            </div>
        </section>

        <section id="servicios" class="welcome-section welcome-pillars">
            <span class="welcome-section-mark welcome-section-mark--left" data-parallax="0.055" aria-hidden="true">01</span>
            <span class="welcome-section-float welcome-section-float--leaf" data-parallax="0.06" aria-hidden="true"><i class="ph ph-first-aid"></i></span>
            <span class="welcome-section-float welcome-section-float--ring" data-parallax="-0.035" aria-hidden="true"></span>
            <div class="welcome-shell">
                <div class="welcome-section-heading" data-aos="fade-up" data-parallax="0.012">
                    <p class="welcome-kicker"><span></span>Nuestros pilares<span></span></p>
                    <h2>Bienestar hoy, <em>más vida mañana</em></h2>
                    <p>Atención integral, tecnología responsable y un equipo humano comprometido con la dignidad de cada persona.</p>
                </div>
                <div class="welcome-pillar-grid">
                    @foreach ([
                        ['ph-heartbeat', 'Cuidado integral', 'Atención médica, emocional y social adaptada a cada etapa de la vida.', 'coral'],
                        ['ph-brain', 'Memoria activa', 'Programas especializados para el bienestar cognitivo y la estimulación diaria.', 'sage'],
                        ['ph-users-three', 'Acompañamiento humano', 'Un equipo cercano que brinda calidez, respeto y dignidad.', 'clay'],
                        ['ph-shield-check', 'Entorno seguro', 'Instalaciones confortables y protocolos pensados para el adulto mayor.', 'coral-soft'],
                    ] as [$icon, $title, $copy, $tone])
                        <article class="welcome-pillar-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 70 }}">
                            <span class="welcome-icon welcome-icon--{{ $tone }}"><i class="ph {{ $icon }}"></i></span>
                            <div><h3>{{ $title }}</h3><p>{{ $copy }}</p></div><i class="ph ph-plus-circle welcome-card-leaf" aria-hidden="true"></i>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="filosofia" class="welcome-section welcome-philosophy">
            <span class="welcome-section-mark welcome-section-mark--right" data-parallax="-0.045" aria-hidden="true">02</span>
            <span class="welcome-section-float welcome-section-float--flower" data-parallax="0.045" aria-hidden="true"><i class="ph ph-heartbeat"></i></span>
            <div class="welcome-shell welcome-philosophy-grid">
                <div class="welcome-photo-stack" data-aos="fade-right">
                    <figure class="welcome-photo-stack-main"><img src="{{ asset($photoBase.'/558487013_1337134818424437_2282337776297854403_n.jpg') }}" alt="Acompañamiento personalizado durante una actividad de estimulación" loading="lazy"></figure>
                    <figure class="welcome-photo-stack-small" data-parallax="0.025"><img src="{{ asset($photoBase.'/489963938_1158744422930145_8442506970304201426_n.jpg') }}" alt="Adulta mayor sonriendo en una actividad de jardinería" loading="lazy"></figure>
                    <div class="welcome-years"><strong>24/7</strong><span>Acompañamiento<br>con calidez</span></div>
                </div>
                <div class="welcome-philosophy-copy" data-aos="fade-left" data-parallax="0.01">
                    <p class="welcome-kicker welcome-kicker--left">Nuestra filosofía</p>
                    <h2>Cuidamos la historia,<br><em>acompañamos el presente</em></h2>
                    <p class="welcome-body-large">Cada residente llega con una vida llena de vínculos, gustos y recuerdos. Nuestro enfoque parte de conocer esa historia para construir una experiencia de cuidado realmente personal.</p>
                    <div class="welcome-values">
                        <div><i class="ph ph-hand-heart"></i><span><strong>Respeto por la identidad</strong>Decisiones y rutinas centradas en la persona.</span></div>
                        <div><i class="ph ph-activity"></i><span><strong>Bienestar activo</strong>Movimiento, estimulación y conexión social cotidiana.</span></div>
                        <div><i class="ph ph-shield-check"></i><span><strong>Tranquilidad familiar</strong>Seguimiento organizado y atención oportuna.</span></div>
                    </div>
                    <a class="welcome-text-link" href="#experiencia">Descubre cómo vivimos cada día <i class="ph ph-arrow-up-right"></i></a>
                </div>
            </div>
        </section>

        <section class="welcome-section welcome-services-detail" aria-labelledby="services-title">
            <span class="welcome-section-mark welcome-section-mark--left welcome-section-mark--dark" data-parallax="0.05" aria-hidden="true">03</span>
            <div class="welcome-shell">
                <div class="welcome-section-heading welcome-section-heading--light" data-aos="fade-up" data-parallax="0.012">
                    <p class="welcome-kicker"><span></span>Atención integral<span></span></p><h2 id="services-title">Un entorno pensado para <em>vivir bien</em></h2>
                </div>
                <div class="welcome-service-grid">
                    @foreach ([
                        ['ph-stethoscope', 'Salud y seguimiento', 'Valoraciones, control clínico y coordinación de cuidados para actuar de manera oportuna.'],
                        ['ph-person-arms-spread', 'Movimiento y autonomía', 'Actividades funcionales que ayudan a conservar capacidades y confianza.'],
                        ['ph-palette', 'Vida social y cognitiva', 'Talleres, música y experiencias que favorecen la expresión y la memoria.'],
                    ] as [$icon, $title, $copy])
                        <article class="welcome-service-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 90 }}"><i class="ph {{ $icon }}"></i><span>0{{ $loop->iteration }}</span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="experiencia" class="welcome-section welcome-gallery-section" data-carousel>
            <span class="welcome-section-mark welcome-section-mark--right" data-parallax="-0.05" aria-hidden="true">04</span>
            <span class="welcome-section-float welcome-section-float--gallery" data-parallax="0.05" aria-hidden="true"><i class="ph ph-users-three"></i></span>
            <div class="welcome-shell">
                <div class="welcome-gallery-head" data-aos="fade-up" data-parallax="0.012">
                    <div><p class="welcome-kicker welcome-kicker--left">Nuestra experiencia</p><h2>La vida sucede en<br><em>cada pequeño momento</em></h2></div>
                    <div class="welcome-carousel-controls" aria-label="Controles de galería">
                        <button type="button" data-carousel-prev aria-label="Imagen anterior"><i class="ph ph-arrow-left"></i></button>
                        <button type="button" data-carousel-next aria-label="Imagen siguiente"><i class="ph ph-arrow-right"></i></button>
                    </div>
                </div>
                <div class="welcome-carousel-viewport" data-carousel-viewport tabindex="0" aria-label="Galería de experiencias del centro">
                    <div class="welcome-carousel-track" data-carousel-track>
                        @foreach ($gallery as $photo)
                            <article class="welcome-gallery-card" data-carousel-slide>
                                <img src="{{ asset($photoBase.'/'.$photo['file']) }}" alt="{{ $photo['title'] }}" loading="lazy">
                                <div><p>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p><h3>{{ $photo['title'] }}</h3><span>{{ $photo['copy'] }}</span></div>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="welcome-carousel-dots" data-carousel-dots aria-label="Seleccionar imagen"></div>
            </div>
        </section>

        <section id="contacto" class="welcome-section welcome-contact">
            <span class="welcome-contact-word" aria-hidden="true">Conectar</span>
            <div class="welcome-contact-orbit welcome-contact-orbit--one" aria-hidden="true"></div>
            <div class="welcome-contact-orbit welcome-contact-orbit--two" aria-hidden="true"></div>
            <div class="welcome-shell welcome-contact-stage">
                <div class="welcome-contact-copy" data-aos="fade-right">
                    <p class="welcome-kicker welcome-kicker--left">Sigamos conectados</p>
                    <h2>La vida en Los Almendros,<br><em>más cerca de ti</em></h2>
                    <p>Compartimos instantes reales: actividades, celebraciones y esos pequeños momentos que hacen especial cada día.</p>
                    <div class="welcome-social-area">
                        <span class="welcome-social-hint">Encuéntranos</span>
                        <div class="welcome-social-links" aria-label="Redes sociales">
                            <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar Facebook">
                                <i class="ph-fill ph-facebook-logo" aria-hidden="true"></i><span>Facebook</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i>
                            </a>
                            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Visitar Instagram">
                                <i class="ph-fill ph-instagram-logo" aria-hidden="true"></i><span>Instagram</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i>
                            </a>
                            <a href="https://wa.me/" target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
                                <i class="ph-fill ph-whatsapp-logo" aria-hidden="true"></i><span>WhatsApp</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <figure class="welcome-contact-visual" data-aos="fade-left">
                    <div class="welcome-contact-image" data-parallax="0.018"><img src="{{ asset($photoBase.'/585367654_1351376833666902_8115277307804264176_n.jpg') }}" alt="Residente disfrutando de una actividad musical" loading="lazy"></div>
                    <figcaption><span>Momentos que importan</span><strong>Cuidado que acompaña cada historia.</strong></figcaption>
                    <i class="ph ph-heartbeat welcome-contact-leaf" aria-hidden="true"></i>
                </figure>
            </div>
        </section>
    </main>

    <footer class="welcome-footer">
        <div class="welcome-shell welcome-footer-grid">
            <a href="#inicio" class="welcome-brand welcome-brand--footer"><img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="Logo del Centro Geriátrico Los Almendros" width="62" height="62"><span><small>Centro Geriátrico</small>Los Almendros</span></a>
            <p>Cuidado integral, memoria viva y acompañamiento humano.</p>
            <a class="welcome-footer-portal" href="{{ auth()->check() ? url('/dashboard') : route('login') }}">Acceso institucional <i class="ph ph-arrow-right"></i></a>
        </div>
        <div class="welcome-shell welcome-footer-bottom"><span>© {{ date('Y') }} Centro Geriátrico Los Almendros</span><span>Atención geriátrica con calidez humana.</span></div>
    </footer>
    <script>{!! file_get_contents(resource_path('frontend/scripts/modules/pages-welcome-3.js')) !!}</script>
</body>
</html>
