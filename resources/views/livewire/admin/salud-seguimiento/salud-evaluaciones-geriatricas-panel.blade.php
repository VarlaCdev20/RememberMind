<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <nav class="mb-2 flex text-[10px] font-black uppercase tracking-widest text-meta" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.salud-seguimiento.index') }}" class="hover:text-parrafo">Salud y Seguimiento</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="ph-bold ph-caret-right mx-1"></i>
                            <span class="text-apoyo">Evaluaciones Geriátricas</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h2 class="text-2xl font-black tracking-tight text-parrafo flex items-center gap-2">
                <i class="ph-bold ph-list-magnifying-glass text-parrafo"></i> Evaluaciones Geriátricas
            </h2>
            <p class="mt-1 text-sm font-semibold text-apoyo">
                Valoración integral geriátrica, riesgo funcional, nutricional y seguimiento preventivo.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="inline-flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-xs font-black uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
                <i class="ph-bold ph-arrow-left"></i> Volver a Expediente
            </a>
            <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}' })" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-4 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-md transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
                <i class="ph-bold ph-plus-circle text-sm"></i> Registrar Evaluación
            </button>
        </div>
    </div>

    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        <div class="border-b border-borde bg-fondo-panel px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-user-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-meta">Adulto Mayor</p>
                    <h3 class="text-sm font-black text-parrafo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                </div>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-black uppercase tracking-wider text-parrafo">
                    {{ count($evaluaciones) }} Evaluaciones
                </span>
            </div>
        </div>

        <div class="p-6">
            @if(count($evaluaciones) > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($evaluaciones as $eval)
                        <div class="rounded-xl border border-borde bg-fondo-panel p-4 transition hover:border-borde hover:bg-fondo-panel">
                            <div class="mb-3 flex items-start justify-between gap-2 border-b border-borde pb-3">
                                <div>
                                    <h4 class="text-sm font-black text-parrafo leading-tight">{{ $eval->instrumento->nombre ?? 'Evaluación Geriátrica' }}</h4>
                                    <p class="text-[10px] font-bold text-meta mt-1">{{ \Carbon\Carbon::parse($eval->fecha_eval)->format('d M Y') }}</p>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo shrink-0">
                                    <i class="ph-bold ph-file-text"></i>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="font-bold text-apoyo">Puntaje Total</span>
                                    <span class="font-black text-parrafo">{{ $eval->puntaje_total ?? 'N/D' }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="font-bold text-apoyo">Nivel de Alerta</span>
                                    <span class="font-black {{ $eval->nivel_alerta === 'CRITICO' ? 'text-red-600' : ($eval->nivel_alerta === 'PRECAUCION' ? 'text-estado-advertencia' : 'text-estado-exito') }}">
                                        {{ $eval->nivel_alerta ?? 'N/D' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-borde flex justify-end gap-2">
                                <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}', eval_id: {{ $eval->id }} })" class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
                                    <i class="ph-bold ph-pencil-simple"></i> Editar
                                </button>
                                <!-- TODO: Implementar visor detallado si existe -->
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
                        <i class="ph-bold ph-list-magnifying-glass text-3xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-parrafo">No existen evaluaciones geriátricas registradas.</h3>
                    <p class="mt-2 text-sm font-semibold text-apoyo max-w-md">
                        Comience registrando la primera valoración multidimensional para llevar el seguimiento preventivo del adulto mayor.
                    </p>
                    <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}' })" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-6 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-md transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
                        <i class="ph-bold ph-plus-circle text-sm"></i> Registrar Evaluación
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Component --}}
    <livewire:admin.adultos-mayores.evaluaciones.evaluacion-geriatrica-modal :cod_am="$adulto->cod_am" />
</div>
