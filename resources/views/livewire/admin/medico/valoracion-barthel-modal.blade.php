<div>
@if($mostrar)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-8"
     x-data x-on:keydown.escape.window="$wire.cerrar()">

    <div class="relative w-full max-w-2xl rounded-[28px] border border-borde bg-fondo-card shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-borde px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-fill ph-person text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-titulo">Índice de Barthel</h3>
                    @if($adulto)
                    <p class="text-xs font-semibold text-apoyo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</p>
                    @endif
                </div>
            </div>
            {{-- Marcador total --}}
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-2xl font-black
                                {{ $totalBarthel >= 91 ? 'text-estado-exito' : ($totalBarthel >= 61 ? 'text-estado-info' : ($totalBarthel >= 41 ? 'text-estado-advertencia' : 'text-estado-error')) }}">
                        {{ $totalBarthel }}<span class="text-sm font-bold text-apoyo">/100</span>
                    </div>
                    <div class="text-[10px] font-bold text-apoyo">{{ $clasificacionBarthel }}</div>
                </div>
                <button wire:click="cerrar" class="flex h-8 w-8 items-center justify-center rounded-full text-apoyo hover:bg-fondo-panel hover:text-titulo transition">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
        </div>

        <div class="space-y-4 p-6">

            {{-- Fecha --}}
            <div class="flex items-center gap-4">
                <div class="w-48">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Fecha de valoración <span class="text-estado-error">*</span></label>
                    <input wire:model="fecha_valoracion" type="date"
                           class="w-full rounded-xl border @error('fecha_valoracion') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                    @error('fecha_valoracion')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
                </div>
                {{-- Barra visual del puntaje --}}
                <div class="flex-1">
                    <div class="mb-1 text-[10px] font-bold text-apoyo uppercase tracking-wider">Progreso</div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-fondo-panel">
                        <div class="h-full rounded-full transition-all duration-300
                                    {{ $totalBarthel >= 91 ? 'bg-estado-exito' : ($totalBarthel >= 61 ? 'bg-estado-info' : ($totalBarthel >= 41 ? 'bg-estado-advertencia' : 'bg-estado-error')) }}"
                             style="width: {{ $totalBarthel }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Ítems Barthel --}}
            <div class="rounded-2xl border border-borde overflow-hidden">
                @php
                $items = [
                    ['prop' => 'alimentacion',        'label' => 'Alimentación',           'opts' => [[0,'Dependiente'],[5,'Necesita ayuda'],[10,'Independiente']]],
                    ['prop' => 'bano',                'label' => 'Baño / Ducha',            'opts' => [[0,'Dependiente'],[5,'Independiente']]],
                    ['prop' => 'aseo_personal',       'label' => 'Aseo personal',           'opts' => [[0,'Necesita ayuda'],[5,'Independiente']]],
                    ['prop' => 'vestido',             'label' => 'Vestido / Desvestido',    'opts' => [[0,'Dependiente'],[5,'Necesita ayuda'],[10,'Independiente']]],
                    ['prop' => 'control_intestinal',  'label' => 'Control intestinal',      'opts' => [[0,'Incontinente'],[5,'Accidente ocasional'],[10,'Continente']]],
                    ['prop' => 'control_vesical',     'label' => 'Control vesical',         'opts' => [[0,'Incontinente'],[5,'Accidente ocasional'],[10,'Continente']]],
                    ['prop' => 'uso_retrete',         'label' => 'Uso del retrete / WC',   'opts' => [[0,'Dependiente'],[5,'Necesita ayuda'],[10,'Independiente']]],
                    ['prop' => 'traslados',           'label' => 'Traslados (silla-cama)', 'opts' => [[0,'Incapaz'],[5,'Gran ayuda'],[10,'Pequeña ayuda'],[15,'Independiente']]],
                    ['prop' => 'deambulacion',        'label' => 'Deambulación',            'opts' => [[0,'Inmóvil'],[5,'Independiente en silla'],[10,'Camina con ayuda'],[15,'Independiente']]],
                    ['prop' => 'escaleras',           'label' => 'Subir y bajar escaleras','opts' => [[0,'Incapaz'],[5,'Necesita ayuda'],[10,'Independiente']]],
                ];
                @endphp

                @foreach($items as $idx => $item)
                <div class="flex items-center gap-4 px-5 py-3 {{ $idx % 2 === 0 ? 'bg-fondo-panel/30' : '' }}">
                    <div class="w-44 shrink-0">
                        <div class="text-xs font-bold text-titulo">{{ $item['label'] }}</div>
                        <div class="text-[10px] font-semibold text-apoyo">
                            Puntos: {{ implode(' / ', array_column($item['opts'], 0)) }}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($item['opts'] as [$val, $desc])
                        <button wire:click="$set('{{ $item['prop'] }}', {{ $val }})"
                                class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition
                                       {{ $this->{$item['prop']} == $val
                                          ? 'bg-boton-acento text-white shadow-sm'
                                          : 'bg-fondo-panel text-apoyo hover:bg-borde hover:text-titulo' }}">
                            {{ $val }} — {{ $desc }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Ayudas técnicas y limitaciones sensoriales --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                    <div class="mb-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Ayudas para la movilidad</div>
                    <div class="space-y-2">
                        @foreach([['usa_baston','Usa bastón'],['usa_andador','Usa andador'],['usa_silla_ruedas','Usa silla de ruedas']] as [$prop, $label])
                        <label class="flex cursor-pointer items-center gap-2">
                            <div wire:click="$toggle('{{ $prop }}')"
                                 class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition
                                        {{ $this->{$prop} ? 'border-boton-acento bg-boton-acento text-white' : 'border-borde bg-fondo-card' }}">
                                @if($this->{$prop})<i class="ph-bold ph-check text-[10px]"></i>@endif
                            </div>
                            <span class="text-xs font-semibold text-titulo">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                    <div class="mb-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Limitaciones y supervisión</div>
                    <div class="space-y-2">
                        @foreach([['baja_vision','Baja visión'],['baja_audicion','Hipoacusia'],['dificultad_hablar','Dificultad para hablar'],['necesita_supervision','Necesita supervisión constante']] as [$prop, $label])
                        <label class="flex cursor-pointer items-center gap-2">
                            <div wire:click="$toggle('{{ $prop }}')"
                                 class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition
                                        {{ $this->{$prop} ? 'border-estado-advertencia bg-estado-advertencia text-white' : 'border-borde bg-fondo-card' }}">
                                @if($this->{$prop})<i class="ph-bold ph-check text-[10px]"></i>@endif
                            </div>
                            <span class="text-xs font-semibold text-titulo">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Riesgo de caída --}}
            <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-panel px-5 py-3">
                <div class="flex-1">
                    <div class="text-xs font-black text-titulo">Riesgo de caída calculado</div>
                    <div class="text-[10px] text-apoyo">Basado en puntaje Barthel y uso de dispositivos</div>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-black
                             {{ $riesgo_caida === 'ALTO' ? 'bg-estado-errorBg text-estado-error' :
                                ($riesgo_caida === 'MODERADO' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito') }}">
                    {{ $riesgo_caida }}
                </span>
            </div>

            {{-- Tabla de referencia --}}
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-apoyo hover:text-titulo transition list-none">
                    <i class="ph-bold ph-info text-sm"></i>
                    Tabla de clasificación Barthel
                    <i class="ph-bold ph-caret-down text-xs transition group-open:rotate-180"></i>
                </summary>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-5">
                    @foreach([
                        ['91-100','Independiente','bg-estado-exitoBg text-estado-exito'],
                        ['61-90','Dep. leve','bg-estado-infoBg text-estado-info'],
                        ['41-60','Dep. moderada','bg-estado-advertenciaBg text-estado-advertencia'],
                        ['21-40','Dep. severa','bg-boton-acento/10 text-boton-acento'],
                        ['0-20','Dep. total','bg-estado-errorBg text-estado-error'],
                    ] as [$rango, $clasif, $color])
                    <div class="rounded-xl {{ $color }} p-2 text-center">
                        <div class="text-sm font-black">{{ $rango }}</div>
                        <div class="text-[10px] font-bold">{{ $clasif }}</div>
                    </div>
                    @endforeach
                </div>
            </details>

            {{-- Observación --}}
            <div>
                <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Observaciones</label>
                <textarea wire:model="observacion" rows="2" placeholder="Observaciones sobre la valoración funcional..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-borde px-6 py-4">
            <div class="text-sm">
                <span class="font-black text-titulo">{{ $totalBarthel }}/100</span>
                <span class="ml-2 font-semibold text-apoyo">{{ $clasificacionBarthel }}</span>
            </div>
            <div class="flex gap-3">
                <button wire:click="cerrar" class="rm-btn-secondary h-10 px-5">Cancelar</button>
                <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
                        class="rm-btn-primary h-10 px-6 flex items-center gap-2">
                    <span wire:loading.remove wire:target="guardar"><i class="ph-bold ph-check text-sm"></i> Guardar Barthel</span>
                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
