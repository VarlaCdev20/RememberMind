/** One calm entrance per chart card, including cards added by Livewire. */
const observedCards = new WeakSet();
const enteredKeys = new Set();
const rowCounters = new WeakMap();

function motionKey(card) {
    const local = card.dataset.rmChartMotionKey
        || card.querySelector('canvas[id]')?.id
        || card.querySelector('h2, h3, h4')?.textContent?.trim();
    return local ? `${window.location.pathname}:${local}` : null;
}

export function rmObserveChartCards() {
    if (typeof document === 'undefined' || typeof window === 'undefined') return;
    if (!document.body || !('IntersectionObserver' in window)) return;

    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (!isIntersecting) return;
            observer.unobserve(target);
            const key = motionKey(target);
            target.classList.remove('rm-chart-motion-pending');
            if (key && enteredKeys.has(key)) return;
            if (key) enteredKeys.add(key);
            target.classList.add('rm-chart-entered');
        });
    }, { threshold: .08, rootMargin: '32px 0px' });

    const scan = () => {
        document.querySelectorAll('.rm-chart-card, .rm-admin-dashboard__analysis, .rm-admin-dashboard__alert-chart').forEach((card) => {
            if (observedCards.has(card)) return;
            observedCards.add(card);
            const key = motionKey(card);
            if (key && enteredKeys.has(key)) return;
            const parent = card.parentElement;
            if (parent) {
                const index = rowCounters.get(parent) || 0;
                card.style.setProperty('--rm-chart-enter-index', String(Math.min(index, 5)));
                rowCounters.set(parent, index + 1);
            }
            card.querySelectorAll('.rm-admin-dashboard__bar-group').forEach((group, index) => {
                group.style.setProperty('--rm-chart-bar-index', String(Math.min(index, 8)));
            });
            card.classList.add('rm-chart-motion-pending');
            observer.observe(card);
        });
    };

    scan();
    let scanQueued = false;
    const changes = new MutationObserver((records) => {
        if (!records.some(record => record.addedNodes.length)) return;
        if (scanQueued) return;
        scanQueued = true;
        window.requestAnimationFrame(() => {
            scanQueued = false;
            scan();
        });
    });
    changes.observe(document.body, { childList: true, subtree: true });
}
