const normalizar = text => String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

export function fechaLocal(date) {
    return `${String(date.getFullYear()).padStart(4, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

export function diasDelMes(year, month) {
    const first = new Date(0); first.setFullYear(year, month, 1);
    const last = new Date(0); last.setFullYear(year, month + 1, 0);
    const offset = (first.getDay() + 6) % 7;
    return [...Array(offset).fill(null), ...Array.from({ length: last.getDate() }, (_, i) => {
        const date = new Date(first); date.setDate(i + 1); return fechaLocal(date);
    })];
}

export function rmSelector(value = '') {
    return {
        open: false, search: '', options: [], active: 0, popupStyle: '', value,
        init() {
            this.$nextTick(() => {
                this.readOptions();
                this.observer = new MutationObserver(() => this.readOptions());
                this.observer.observe(this.$refs.native, { childList: true, subtree: true, attributes: true });
            });
        },
        destroy() { this.observer?.disconnect(); },
        readOptions() {
            this.options = Array.from(this.$refs.native.options, o => ({ value: o.value, label: o.textContent.trim(), disabled: o.disabled }));
        },
        get filtered() { return this.options.filter(o => normalizar(o.label).includes(normalizar(this.search))); },
        get multiple() { return this.$refs.native?.multiple || false; },
        selected(value) { return this.multiple ? (Array.isArray(this.value) && this.value.includes(value)) : String(this.value ?? '') === value; },
        get label() {
            const chosen = this.options.filter(o => this.selected(o.value));
            return chosen.length ? chosen.map(o => o.label).join(', ') : 'Seleccionar';
        },
        position() {
            const r = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(Math.max(240, r.width), window.innerWidth - 16);
            const height = Math.min(310, window.innerHeight - 24);
            const top = window.innerHeight - r.bottom > height ? r.bottom + 8 : Math.max(8, r.top - height - 8);
            this.popupStyle = `left:${Math.max(8, Math.min(r.left, window.innerWidth - width - 8))}px;top:${top}px;width:${width}px;max-height:${height}px`;
        },
        show() {
            if (this.$refs.native.disabled) return;
            this.readOptions(); this.search = ''; this.active = 0; this.position(); this.open = true;
            this.$nextTick(() => this.$refs.search.focus());
        },
        close() { this.open = false; this.$refs.trigger.focus({ preventScroll: true }); },
        choose(option) {
            if (option.disabled) return;
            this.value = this.multiple
                ? (this.selected(option.value) ? this.value.filter(v => v !== option.value) : [...(this.value || []), option.value])
                : option.value;
            this.$nextTick(() => {
                for (const o of this.$refs.native.options) o.selected = this.selected(o.value);
                this.$refs.native.dispatchEvent(new Event('change', { bubbles: true }));
                this.$refs.native.dispatchEvent(new FocusEvent('blur', { bubbles: true }));
            });
            if (!this.multiple) this.close();
        },
        key(event) {
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); this.close(); return; }
            if (event.key === 'Enter') { event.preventDefault(); const item = this.filtered[this.active]; if (item) this.choose(item); return; }
            if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const last = this.filtered.length - 1;
            this.active = event.key === 'Home' ? 0 : event.key === 'End' ? last : Math.max(0, Math.min(last, this.active + (event.key === 'ArrowDown' ? 1 : -1)));
            this.$nextTick(() => this.$refs.list.querySelectorAll('[role="option"]')[this.active]?.scrollIntoView({ block: 'nearest' }));
        },
    };
}

export function rmCalendario({ min = '', max = '', value = '' } = {}) {
    return {
        open: false, value, year: new Date().getFullYear(), month: new Date().getMonth(), popupStyle: '', min, max,
        get days() { return diasDelMes(this.year, this.month); },
        get monthLabel() { const date = new Date(0); date.setFullYear(this.year, this.month, 1); return date.toLocaleDateString('es-BO', { month: 'long' }); },
        get label() { return this.value ? new Date(`${this.value}T12:00:00`).toLocaleDateString('es-BO') : 'Elegir fecha'; },
        allowed(day) { return !!day && (!this.min || day >= this.min) && (!this.max || day <= this.max); },
        show() {
            this.min = this.$refs.native.dataset.min || '';
            this.max = this.$refs.native.dataset.max || '';
            const base = this.value || (this.max && this.max < fechaLocal(new Date()) ? this.max : fechaLocal(new Date()));
            const date = new Date(`${base}T12:00:00`);
            this.year = date.getFullYear(); this.month = date.getMonth();
            const r = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(320, window.innerWidth - 16);
            const top = window.innerHeight - r.bottom > 390 ? r.bottom + 8 : Math.max(8, Math.min(r.top - 390, window.innerHeight - 400));
            this.popupStyle = `left:${Math.max(8, Math.min(r.left, window.innerWidth - width - 8))}px;top:${top}px;width:${width}px;max-height:${window.innerHeight - top - 8}px`;
            this.open = true;
            this.$nextTick(() => this.$refs.year.focus({ preventScroll: true }));
        },
        close() { this.open = false; this.$refs.trigger.focus({ preventScroll: true }); },
        changeMonth(amount) {
            const date = new Date(0); date.setFullYear(this.year, this.month + amount, 1);
            this.year = date.getFullYear(); this.month = date.getMonth();
        },
        changeYear(value) { const year = Number(value); if (Number.isInteger(year) && year >= 1 && year <= 9999) this.year = year; },
        choose(day) {
            if (day && !this.allowed(day)) return;
            this.value = day;
            this.$nextTick(() => {
                this.$refs.native.value = day;
                this.$refs.native.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.native.dispatchEvent(new Event('change', { bubbles: true }));
                this.$refs.native.dispatchEvent(new FocusEvent('blur', { bubbles: true }));
            });
            this.close();
        },
        dayKey(event, day) {
            const shift = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[event.key];
            if (!shift) return;
            event.preventDefault();
            const target = new Date(`${day}T12:00:00`); target.setDate(target.getDate() + shift);
            this.year = target.getFullYear(); this.month = target.getMonth();
            this.$nextTick(() => this.$refs.grid.querySelector(`[data-date="${fechaLocal(target)}"]`)?.focus());
        },
        today() { return fechaLocal(new Date()); },
    };
}

if (typeof window !== 'undefined') {
    window.rmSelector = rmSelector;
    window.rmCalendario = rmCalendario;
}
