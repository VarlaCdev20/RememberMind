// Distribución de consultas existentes; no modifica captura, datos ni permisos.
export function focusClinicalHistory(dialog) {
    const history = dialog?.querySelector('[data-clinical-history]');
    if (!history) return;
    if (history.tagName === 'DETAILS') history.open = true;
    history.setAttribute('tabindex', '-1');
    history.focus({ preventScroll: true });
    history.scrollIntoView({ block: 'start', behavior: 'auto' });
}

export function consultationPlacement(viewportWidth, bounds) {
    if (viewportWidth < 1280) return null;
    const left = bounds.left - 32, right = viewportWidth - bounds.right - 32;
    const available = Math.max(left, right);
    if (available < 600) return null;
    return { side: left >= right ? 'left' : 'right', width: Math.min(1160, available) };
}

function installConsultationLayout() {
    const selector = '.rm-signos__graph-popup[popover], .rm-clinical-consultation-panel[popover]';
    const shells = new Set();
    const observed = new Set();
    const sizes = new ResizeObserver(() => schedule());
    let frame = null, active = null;
    const sync = () => {
        frame = null;
        const windows = [...document.querySelectorAll(selector)].filter(el => el.matches(':popover-open'));
        for (const popup of observed) if (!windows.includes(popup)) { sizes.unobserve(popup); observed.delete(popup); }
        for (const popup of windows) if (!observed.has(popup)) { sizes.observe(popup); observed.add(popup); }
        if (active && windows.includes(active)) windows.push(active);
        const layouts = new Map();
        for (const popup of windows) {
            const shell = popup.closest('.rm-modal-shell');
            if (!shell) continue;
            const layout = consultationPlacement(window.innerWidth, popup.getBoundingClientRect());
            if (layout) layouts.set(shell, layout);
        }
        for (const shell of shells) if (!layouts.has(shell)) {
            delete shell.dataset.clinicalConsultationSide;
            shell.style.removeProperty('--rm-consultation-form-width');
            shells.delete(shell);
        }
        for (const [shell, { side, width }] of layouts) {
            shell.dataset.clinicalConsultationSide = side;
            shell.style.setProperty('--rm-consultation-form-width', `${width}px`);
            shells.add(shell);
        }
    };
    const schedule = () => { if (frame === null) frame = requestAnimationFrame(sync); };
    document.addEventListener('toggle', event => {
        if (!event.target.matches?.(selector)) return;
        if (event.newState === 'open') active = event.target;
        schedule();
    }, true);
    window.addEventListener('resize', schedule);
    new MutationObserver(records => {
        if (records.some(record => record.type === 'childList' || record.target.matches?.(selector))) schedule();
    }).observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['style', 'aria-modal'] });
    schedule();
}

if (typeof document !== 'undefined') {
    window.rmFocusClinicalHistory = focusClinicalHistory;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', installConsultationLayout, { once: true });
    else installConsultationLayout();
}
