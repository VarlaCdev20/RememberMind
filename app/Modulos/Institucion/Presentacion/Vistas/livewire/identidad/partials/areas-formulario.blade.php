@if($mostrarFormulario)
<div x-data="{
    inicial: @js(['nombre' => (string) $nombre, 'descripcion' => (string) $descripcion, 'estado' => (string) $estado]),
    async cerrarArea(origen) {
        if (document.getElementById('area-registro-guardar')?.disabled) return;
        const actual = { nombre: String($wire.nombre ?? ''), descripcion: String($wire.descripcion ?? ''), estado: String($wire.estado ?? '') };
        if (JSON.stringify(actual) !== JSON.stringify(this.inicial) &amp;&amp; !(await window.rmConfirmar({
            title: '¿Descartar los cambios del área?',
            text: 'Los datos que modificaste todavía no se han guardado.',
            confirmText: 'Descartar cambios',
            tone: 'neutral'
        }, origen))) return;
        await $wire.cerrarFormulario();
    }
}">
    <x-ui.modal-livewire
        id="area-registro-dialogo"
        wire:model="mostrarFormulario"
        :title="$isEdit ? 'Editar área' : 'Registrar área'"
        icon="ph-buildings"
        max-width="lg"
        surface="reading"
        class="rm-record-form-dialog no-print"
        close-method="cerrarFormulario"
        alpine-close="cerrarArea($event.target)"
        :show-validation="false"
    >
        <form id="area-registro-form" wire:submit="guardarArea" class="rm-record-form" novalidate>
            <x-ui.form-section title="Datos del área" icon="ph-identification-card" :columns="1">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <x-ui.field label="Nombre del área" for="area-registro-nombre" required error="nombre" class="sm:col-span-2" help="Hasta 80 caracteres.">
                        <input id="area-registro-nombre" class="rm-input uppercase" type="text" wire:model="nombre" maxlength="80" required
                            placeholder="EJ. ATENCIÓN Y CUIDADOS" autocomplete="off"
                            aria-invalid="{{ $errors->has('nombre') ? 'true' : 'false' }}"
                            aria-describedby="area-registro-nombre-help{{ $errors->has('nombre') ? ' area-registro-nombre-error' : '' }}">
                    </x-ui.field>
                    @if($modoEquipo)
                        <div class="rm-field">
                            <span id="area-registro-estado-label" class="rm-label">Estado del área</span>
                            <div class="rm-record-form__state" role="status" aria-labelledby="area-registro-estado-label">
                                <i class="ph-bold {{ $estado === 'ACTIVO' ? 'ph-check-circle' : 'ph-pause-circle' }}" aria-hidden="true"></i>
                                <strong>{{ $estado === 'ACTIVO' ? 'ACTIVA' : 'INACTIVA' }}</strong>
                            </div>
                            @error('estado')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <x-ui.field label="Estado del área" for="area-registro-estado" required error="estado">
                            <x-ui.selector id="area-registro-estado" label="Estado del área" wire:model="estado" teleport="#area-registro-dialogo"
                                :aria-invalid="$errors->has('estado') ? 'true' : 'false'"
                                :aria-describedby="$errors->has('estado') ? 'area-registro-estado-error' : null">
                                <option value="ACTIVO">ACTIVA</option>
                                <option value="INACTIVO">INACTIVA</option>
                            </x-ui.selector>
                        </x-ui.field>
                    @endif
                </div>
                <x-ui.field label="Descripción funcional" for="area-registro-descripcion" error="descripcion" hint="Opcional">
                    <textarea id="area-registro-descripcion" class="rm-input" wire:model="descripcion" rows="3"
                        placeholder="Describe las funciones y responsabilidades del área."
                        aria-invalid="{{ $errors->has('descripcion') ? 'true' : 'false' }}"
                        @if($errors->has('descripcion')) aria-describedby="area-registro-descripcion-error" @endif></textarea>
                </x-ui.field>
            </x-ui.form-section>
            <p class="rm-record-form__note">
                <i class="ph-bold ph-users-three" aria-hidden="true"></i>
                <span>{{ $modoEquipo && $isEdit ? 'Esta edición conserva el estado del área. El equipo se organiza desde Planificación.' : 'El equipo del área se organiza por jornada desde Planificación.' }}</span>
            </p>
        </form>
        <x-slot:footer>
            <x-ui.action-button variant="secondary" @click="cerrarArea($event.target)" loading="guardarArea">Cancelar</x-ui.action-button>
            <x-ui.action-button id="area-registro-guardar" tipo="submit" form="area-registro-form" icono="ph-check" loading="guardarArea">
                <span wire:loading.remove wire:target="guardarArea">{{ $isEdit ? 'Guardar cambios' : 'Registrar área' }}</span>
                <span wire:loading wire:target="guardarArea" role="status">Guardando…</span>
            </x-ui.action-button>
        </x-slot:footer>
    </x-ui.modal-livewire>
</div>
@endif
