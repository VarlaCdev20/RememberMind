// Ayudas de captura: únicamente el texto elegido/escrito llega al campo existente.
export const freeTextSelect = (value, options) => ({
    value, options, choice: '', customText: '', search: '',
    init() {
        this.sync(this.value);
        this.$watch('value', next => this.sync(next));
    },
    sync(next) {
        const text = String(next ?? '');
        if (this.choice === '__custom' && text === this.customText) return;
        if (text === '' || this.options.includes(text)) {
            this.choice = text;
            if (text === '') this.customText = '';
        } else {
            this.choice = '__custom';
            this.customText = text;
        }
    },
    choose(next) {
        if (next !== '' && next !== '__custom' && !this.options.includes(next)) return;
        this.choice = next;
        this.value = next === '__custom' ? this.customText : next;
        if (next === '__custom') this.$nextTick(() => this.$refs.custom?.focus());
    },
    write(text) {
        this.customText = text;
        this.value = text;
    },
    matches(option) {
        const normalize = text => String(text).normalize('NFD').replace(/\p{Diacritic}/gu, '').toLocaleLowerCase('es');
        const label = normalize(option);
        return normalize(this.search).trim().split(/\s+/).every(word => label.includes(word));
    },
    get visibleOptions() { return this.options.filter(option => this.matches(option)); },
    chooseIllustration(option, trigger) {
        if (!this.options.includes(option) && option !== '__custom') return;
        this.choose(option);
        const panel = trigger?.closest('details');
        if (panel) panel.open = false;
        this.search = '';
        this.$dispatch('change');
        if (option !== '__custom') this.$nextTick(() => this.$refs.native?.focus());
    },
});

window.rmFreeTextSelect = freeTextSelect;
