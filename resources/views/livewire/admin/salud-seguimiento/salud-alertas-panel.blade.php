<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 shadow-sm border border-rose-100">
                    <i class="ph-bold ph-warning-circle text-2xl text-rose-500"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tight text-azul-profundo sm:text-2xl">
                        Alertas de seguimiento
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        Alertas orientativas generadas a partir de registros de salud y seguimiento.
                    </p>
                </div>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="mb-8 flex items-center gap-4 rounded-3xl border border-[#C7B5A3]/40 bg-white p-4 shadow-sm">
            <div class="w-full max-w-xs">
                <select wire:model.live="filtroTipo" class="w-full rounded-2xl border border-[#C7B5A3]/40 bg-[#F7F5F2] py-3 px-4 text-sm font-bold text-azul-profundo shadow-inner focus:border-terracota focus:ring-terracota">
                    <option value="">Todos los tipos de alerta</option>
                    <option value="Ficha Médica">Ficha Médica</option>
                    <option value="Medicación">Medicación</option>
                    <option value="Signos Vitales">Signos Vitales</option>
                    <option value="Valoración">Valoración Funcional</option>
                </select>
            </div>
        </div>

        {{-- LISTADO DE ALERTAS --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($alertas as $alerta)
                @php
                    $isCritica = $alerta['nivel'] === 'critica';
                @endphp
                <div class="flex flex-col rounded-3xl border {{ $isCritica ? 'border-rose-200 bg-rose-50/50' : 'border-amber-200 bg-amber-50/50' }} p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute top-0 right-0 h-16 w-16 bg-gradient-to-bl {{ $isCritica ? 'from-rose-200 to-transparent' : 'from-amber-200 to-transparent' }} opacity-50"></div>
                    
                    <div class="flex items-start justify-between mb-4 relative z-10">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $isCritica ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $alerta['tipo'] }}
                        </span>
                        <i class="ph-fill {{ $isCritica ? 'ph-warning-octagon text-rose-500' : 'ph-warning text-amber-500' }} text-2xl"></i>
                    </div>

                    <h3 class="text-lg font-black text-azul-profundo mb-1">{{ $alerta['adulto']->nombres }} {{ $alerta['adulto']->ap_paterno }}</h3>
                    <p class="text-[10px] font-bold text-terracota mb-4">{{ $alerta['adulto']->cod_am }}</p>

                    <p class="text-sm font-bold text-azul-profundo/80 mb-4 flex-1">
                        {{ $alerta['mensaje'] }}
                    </p>

                    <div class="rounded-xl bg-white/60 p-3 border {{ $isCritica ? 'border-rose-100' : 'border-amber-100' }} mb-4">
                        <p class="text-[10px] font-black uppercase text-azul-profundo/60 text-center">
                            {{ $alerta['accion'] }}
                        </p>
                    </div>

                    <a href="{{ $alerta['ruta'] }}" class="w-full rounded-xl {{ $isCritica ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-500 hover:bg-amber-600' }} py-2.5 text-center text-xs font-black uppercase tracking-wider text-white shadow-sm transition-all active:scale-95">
                        Ir al módulo relacionado
                    </a>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-[#C7B5A3]/60 bg-white py-10 text-center shadow-sm">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50">
                        <i class="ph-bold ph-check-circle text-4xl text-emerald-500"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-azul-profundo">Sin alertas de seguimiento</h3>
                    <p class="mt-1 text-sm font-bold text-[#2F3E5C]/50">Todos los expedientes están al día y dentro de los rangos normales.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
