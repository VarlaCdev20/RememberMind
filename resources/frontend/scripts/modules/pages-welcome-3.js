document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const header = document.querySelector('[data-header]');
    const menuButton = document.querySelector('[data-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const cursorLight = document.querySelector('[data-cursor-light]');

    if (cursorLight && !reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        let cursorX = -240;
        let cursorY = -240;
        let cursorFrame = 0;

        const renderCursorLight = () => {
            cursorLight.style.setProperty('--cursor-x', `${cursorX}px`);
            cursorLight.style.setProperty('--cursor-y', `${cursorY}px`);
            cursorFrame = 0;
        };

        window.addEventListener('pointermove', (event) => {
            cursorX = event.clientX;
            cursorY = event.clientY;
            cursorLight.classList.add('is-visible');
            if (!cursorFrame) cursorFrame = window.requestAnimationFrame(renderCursorLight);
        }, { passive: true });

        document.documentElement.addEventListener('mouseleave', () => cursorLight.classList.remove('is-visible'));
        window.addEventListener('blur', () => cursorLight.classList.remove('is-visible'));
    }

    const syncHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 18);
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });

    const closeMenu = () => {
        if (!menuButton || !mobileMenu) return;
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Abrir menú');
        menuButton.querySelector('i')?.classList.replace('ph-x', 'ph-list');
        mobileMenu.hidden = true;
    };

    menuButton?.addEventListener('click', () => {
        if (!mobileMenu) return;
        const willOpen = menuButton.getAttribute('aria-expanded') !== 'true';
        menuButton.setAttribute('aria-expanded', String(willOpen));
        menuButton.setAttribute('aria-label', willOpen ? 'Cerrar menú' : 'Abrir menú');
        const icon = menuButton.querySelector('i');
        icon?.classList.toggle('ph-list', !willOpen);
        icon?.classList.toggle('ph-x', willOpen);
        mobileMenu.hidden = !willOpen;
    });
    mobileMenu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    window.addEventListener('resize', () => { if (window.innerWidth > 900) closeMenu(); });

    if (window.AOS) {
        window.AOS.init({
            duration: reduceMotion ? 0 : 720,
            once: true,
            offset: 70,
            easing: 'ease-out-cubic',
            disable: reduceMotion,
        });
    }

    const navLinks = [...document.querySelectorAll('.welcome-desktop-nav a')];
    const sections = navLinks
        .map((link) => document.querySelector(link.getAttribute('href')))
        .filter(Boolean);
    if ('IntersectionObserver' in window && sections.length) {
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                navLinks.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === '#' + entry.target.id));
            });
        }, { rootMargin: '-25% 0px -65%', threshold: 0 });
        sections.forEach((section) => sectionObserver.observe(section));
    }

    if (!reduceMotion) {
        const parallaxItems = [...document.querySelectorAll('[data-parallax]')];
        let ticking = false;
        const updateParallax = () => {
            const viewportCenter = window.innerHeight / 2;
            parallaxItems.forEach((item) => {
                const rect = item.getBoundingClientRect();
                const rate = Number(item.dataset.parallax || 0);
                const offset = Math.max(-48, Math.min(48, (rect.top + rect.height / 2 - viewportCenter) * rate));
                item.style.translate = '0 ' + offset.toFixed(2) + 'px';
            });
            ticking = false;
        };
        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(updateParallax);
        }, { passive: true });
        updateParallax();
    }

    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const viewport = carousel.querySelector('[data-carousel-viewport]');
        const track = carousel.querySelector('[data-carousel-track]');
        const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const dotsRoot = carousel.querySelector('[data-carousel-dots]');
        if (!viewport || !track || !slides.length || !dotsRoot) return;

        let index = 0;
        let timer;
        const visibleCount = () => window.innerWidth <= 600 ? 1 : window.innerWidth <= 900 ? 2 : 3;
        const maxIndex = () => Math.max(0, slides.length - visibleCount());
        const dots = slides.map((_, dotIndex) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.setAttribute('aria-label', 'Mostrar imagen ' + (dotIndex + 1));
            dot.addEventListener('click', () => goTo(Math.min(dotIndex, maxIndex()), true));
            dotsRoot.appendChild(dot);
            return dot;
        });

        const render = () => {
            index = Math.min(index, maxIndex());
            const first = slides[0];
            const gap = Number.parseFloat(getComputedStyle(track).gap) || 0;
            const step = first.getBoundingClientRect().width + gap;
            track.style.transform = 'translate3d(' + (-index * step) + 'px,0,0)';
            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
                dot.hidden = dotIndex > maxIndex();
            });
        };
        const restart = () => {
            window.clearInterval(timer);
            if (!reduceMotion) timer = window.setInterval(() => goTo(index >= maxIndex() ? 0 : index + 1, false), 4200);
        };
        const goTo = (nextIndex, userInitiated) => {
            index = Math.max(0, Math.min(nextIndex, maxIndex()));
            render();
            if (userInitiated) restart();
        };

        prev?.addEventListener('click', () => goTo(index <= 0 ? maxIndex() : index - 1, true));
        next?.addEventListener('click', () => goTo(index >= maxIndex() ? 0 : index + 1, true));
        viewport.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') { event.preventDefault(); goTo(index - 1, true); }
            if (event.key === 'ArrowRight') { event.preventDefault(); goTo(index + 1, true); }
        });
        carousel.addEventListener('mouseenter', () => window.clearInterval(timer));
        carousel.addEventListener('mouseleave', restart);
        window.addEventListener('resize', render);
        render();
        restart();
    });
});
