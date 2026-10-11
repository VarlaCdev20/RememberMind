<div x-data="{
    month: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    selected: new Date(),
    key(date) { return [date.getFullYear(), date.getMonth(), date.getDate()].join('-') },
    get title() { return this.month.toLocaleDateString('es-BO', { month: 'long', year: 'numeric' }) },
    get days() {
        const offset = (this.month.getDay() + 6) % 7;
        return Array.from({ length: 42 }, (_, index) => new Date(this.month.getFullYear(), this.month.getMonth(), index - offset + 1));
    },
    moveMonth(step) { this.month = new Date(this.month.getFullYear(), this.month.getMonth() + step, 1) },
    today() { this.selected = new Date(); this.month = new Date(this.selected.getFullYear(), this.selected.getMonth(), 1) },
    close() { this.$refs.calendar.close(); this.$refs.trigger.focus() }
}" x-on:livewire:navigating.window="$refs.calendar.close()">
    <button type="button" class="rm-topbar__action" x-ref="trigger" @click="$refs.calendar.showModal()" aria-label="Abrir calendario" aria-haspopup="dialog">
        <i class="ph-bold ph-calendar-blank text-xl" aria-hidden="true"></i>
    </button>
    <dialog class="rm-calendar" x-ref="calendar" aria-label="Calendario" @click="if ($event.target === $el) close()" @cancel.prevent="close()">
        <div class="rm-calendar__panel">
            <header class="rm-calendar__header">
                <h2 class="rm-section-title">Calendario</h2>
                <button type="button" class="rm-topbar__action" @click="close()" aria-label="Cerrar calendario" autofocus><i class="ph-bold ph-x" aria-hidden="true"></i></button>
            </header>
            <div class="rm-calendar__header">
                <button type="button" class="rm-topbar__action" @click="moveMonth(-1)" aria-label="Mes anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button>
                <p class="rm-card-title" aria-live="polite" x-text="title"></p>
                <button type="button" class="rm-topbar__action" @click="moveMonth(1)" aria-label="Mes siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button>
            </div>
            <div class="rm-calendar__week" aria-hidden="true"><span>L</span><span>M</span><span>X</span><span>J</span><span>V</span><span>S</span><span>D</span></div>
            <div class="rm-calendar__days">
                <template x-for="day in days" :key="key(day)">
                    <button type="button" class="rm-calendar__day"
                        :class="{ 'is-outside': day.getMonth() !== month.getMonth(), 'is-selected': key(day) === key(selected), 'is-today': key(day) === key(new Date()) }"
                        :aria-label="day.toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })"
                        :aria-pressed="(key(day) === key(selected)).toString()" :aria-current="key(day) === key(new Date()) ? 'date' : null"
                        @click="selected = day" x-text="day.getDate()"></button>
                </template>
            </div>
            <footer class="rm-calendar__footer">
                <p class="rm-body" aria-live="polite" x-text="selected.toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })"></p>
                <button type="button" class="rm-calendar__today" @click="today()">Hoy</button>
            </footer>
        </div>
    </dialog>
</div>
