const controlSelector = ':is(select, input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="reset"]))';
const filterTones = ['blue', 'violet', 'teal', 'green', 'orange'];

export function syncFilterControl(control) {
    if (!control?.matches?.(controlSelector) || !control.closest('.rm-filter-bar')) return;

    const active = control.tagName === 'SELECT'
        ? control.selectedIndex > 0
        : String(control.value ?? '').trim() !== '';

    if (control.getAttribute('data-rm-filter-active') !== String(active)) {
        control.setAttribute('data-rm-filter-active', String(active));
    }
}

export function syncFilterBars(root = document) {
    root.querySelectorAll('.rm-filter-bar').forEach(bar => {
        const controls = [...bar.querySelectorAll(controlSelector)];
        controls.forEach(syncFilterControl);

        const activeControls = controls.filter(control =>
            control.getAttribute('data-rm-filter-active') === 'true'
            && !control.disabled
            && control.getAttribute('aria-invalid') !== 'true'
        );
        controls.forEach(control => {
            if (!activeControls.includes(control)) control.removeAttribute('data-rm-filter-tone');
        });
        activeControls.forEach((control, index) => {
            const tone = filterTones[index % filterTones.length];
            if (control.getAttribute('data-rm-filter-tone') !== tone) {
                control.setAttribute('data-rm-filter-tone', tone);
            }
        });

        const chips = bar.querySelectorAll(
            '.rm-filter-bar__active .rm-filter-chip:not([class*="rm-filter-chip--"]), '
            + '.rm-filter-bar__active span.inline-flex:not(.rm-filter-bar__active-label):not([class*="rm-filter-chip--"])'
        );
        chips.forEach((chip, index) => {
            const tone = filterTones[index % filterTones.length];
            if (chip.getAttribute('data-rm-filter-tone') !== tone) {
                chip.setAttribute('data-rm-filter-tone', tone);
            }
        });
    });
}

if (typeof document !== 'undefined') {
    let scheduled = false;
    const scheduleSync = () => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(() => {
            scheduled = false;
            syncFilterBars();
        });
    };

    const updateFromInput = event => {
        syncFilterControl(event.target);
        scheduleSync();
    };
    document.addEventListener('input', updateFromInput, true);
    document.addEventListener('change', updateFromInput, true);
    document.addEventListener('livewire:navigated', scheduleSync);
    document.addEventListener('livewire:init', () => {
        window.Livewire?.hook('morph.updated', ({ el }) => {
            if (el?.closest?.('.rm-filter-bar') || el?.querySelector?.('.rm-filter-bar')) scheduleSync();
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleSync, { once: true });
    } else {
        scheduleSync();
    }
}
