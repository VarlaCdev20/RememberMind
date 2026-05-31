<div>
    <!-- Tarjeta Principal y Botón -->
    <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-titulo">Signos Vitales</h3>
                <p class="text-sm text-apoyo">Registro hemodinámico, temperatura y métricas físicas.</p>
            </div>
            <button wire:click="abrirModalSignos('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2 text-sm font-bold text-inverso shadow-sm hover:bg-fondo-panel active:scale-95 transition">
                <i class="ph-bold ph-activity"></i> Registrar Signos
            </button>
        </div>

        @if(isset($signosList) && $signosList->isNotEmpty())
            <div class="mt-4 space-y-3">
                @foreach($signosList as $signo)
                    <div class="rounded-xl border border-borde-suave bg-fondo-card p-4 shadow-sm flex flex-col md:flex-row justify-between gap-4">
                        <div class="flex-1">
                            <span class="text-xs font-black uppercase text-apoyo">
                                {{ $signo->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($signo->hora)->format('H:i') }}
                            </span>
                            <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @if($signo->presion_arterial)
                                    <div class="bg-blue-50 text-blue-800 rounded-lg p-2 text-center border border-blue-100">
                                        <p class="text-[10px] font-black uppercase mb-0.5">PA</p>
                                        <p class="text-sm font-bold">{{ $signo->presion_arterial }}</p>
                                    </div>
                                @endif
                                @if($signo->frecuencia_cardiaca)
                                    <div class="bg-red-50 text-red-800 rounded-lg p-2 text-center border border-red-100">
                                        <p class="text-[10px] font-black uppercase mb-0.5">FC</p>
                                        <p class="text-sm font-bold">{{ $signo->frecuencia_cardiaca }} bpm</p>
                                    </div>
                                @endif
                                @if($signo->temperatura)
                                    <div class="bg-orange-50 text-orange-800 rounded-lg p-2 text-center border border-orange-100">
                                        <p class="text-[10px] font-black uppercase mb-0.5">Temp</p>
                                        <p class="text-sm font-bold">{{ $signo->temperatura }} °C</p>
                                    </div>
                                @endif
                                @if($signo->saturacion)
                                    <div class="bg-teal-50 text-teal-800 rounded-lg p-2 text-center border border-teal-100">
                                        <p class="text-[10px] font-black uppercase mb-0.5">SpO2</p>
                                        <p class="text-sm font-bold">{{ $signo->saturacion }}%</p>
                                    </div>
                                @endif
                            </div>
                            @if($signo->imc)
                                <p class="text-xs font-bold mt-2 text-titulo">
                                    Peso: {{ $signo->peso }}kg · Talla: {{ $signo->talla }}cm · 
                                    <span class="px-2 py-0.5 rounded-full {{ $signo->imc >= 25 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">IMC: {{ $signo->imc }}</span>
                                </p>
                            @endif
                        </div>
                        <div class="flex items-start shrink-0">
                            <button wire:click="abrirModalSignos('{{ $cod_am }}', {{ $signo->cod_signos_vitales }})" class="text-titulo bg-fondo-panel hover:bg-fondo-app px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="ph-bold ph-pencil-simple mr-1"></i> Editar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-4 text-center border-2 border-dashed border-borde-suave rounded-xl p-4 bg-fondo-card/50">
                <p class="text-sm font-bold text-apoyo">No hay registros de signos vitales recientes.</p>
            </div>
        @endif
    </div>

    <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Signos Vitales' : 'Registrar Signos Vitales' }}" maxWidth="3xl">
        <x-slot name="icon">
            <i class="ph-bold ph-activity text-terracota"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formSignos">
            @error('general')
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-600">
                    <i class="ph-bold ph-warning-circle mr-1"></i> {{ $message }}
                </div>
            @enderror

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Fecha y Hora -->
                <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-titulo">1. Momento del Registro</h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Fecha *</label>
                            <input type="date" wire:model="fecha"
                                class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('fecha') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Hora *</label>
                            <input type="time" wire:model="hora"
                                class="w-full rounded-xl border {{ $errors->has('hora') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('hora') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Presión y Frecuencia -->
                <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-titulo">2. Hemodinámica</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Presión Arterial (mmHg)</label>
                            <input type="text" wire:model="presion_arterial" placeholder="Ej. 120/80"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('presion_arterial') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Frecuencia Cardíaca (lpm)</label>
                            <input type="number" wire:model="frecuencia_cardiaca" placeholder="Ej. 75"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('frecuencia_cardiaca') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Temperatura y Saturación -->
                <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-titulo">3. Ventilación y Temperatura</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Temperatura (°C)</label>
                            <input type="number" step="0.1" wire:model="temperatura" placeholder="Ej. 36.5"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('temperatura') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Saturación O2 (%)</label>
                            <input type="number" wire:model="saturacion" placeholder="Ej. 98"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('saturacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Antropometría y Glucosa -->
                <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-titulo">4. Mediciones y Antropometría</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Glucosa (mg/dL)</label>
                            <input type="number" wire:model="glucosa" placeholder="Ej. 90"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('glucosa') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid gap-2 grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Peso (kg)</label>
                                <input type="number" step="0.1" wire:model.live="peso" placeholder="Ej. 65"
                                    class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Talla (m o cm)</label>
                                <input type="number" step="0.01" wire:model.live="talla" placeholder="Ej. 1.65"
                                    class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">IMC Calculado</label>
                            <input type="text" wire:model="imc" readonly
                                class="w-full rounded-xl border border-transparent bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo">
                        </div>
                    </div>
                </div>

                <!-- Dolor y Notas -->
                <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-titulo">5. Dolor y Observaciones</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Escala de Dolor (0-10)</label>
                            <input type="number" wire:model="dolor" min="0" max="10" placeholder="Ej. 0 (Sin dolor)"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
                            @error('dolor') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Observación</label>
                            <textarea wire:model="observacion" rows="3" placeholder="Paciente se encuentra estable..."
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus"></textarea>
                            @error('observacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-sm font-bold text-titulo transition hover:bg-fondo-panel">
                Cancelar
            </button>
            <button type="submit" form="formSignos" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-sm font-bold text-inverso transition hover:bg-fondo-panel active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $isEditing ? 'Actualizar Signos' : 'Guardar Signos' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
