<div
    class="mx-auto max-w-7xl space-y-5 text-parrafo"
    x-data="{
        confirmarAsignacion(id, accion) {
            const config = accion === 'finalizar'
                ? {
                    title: 'Finalizar asignacion clinica',
                    text: 'El acceso clinico del personal quedara cerrado desde hoy y el registro se conservara como historial.',
                    confirmButtonText: 'Si, finalizar',
                    method: 'finalizarAsignacion'
                }
                : {
                    title: 'Suspender asignacion clinica',
                    text: 'El acceso clinico del personal quedara suspendido desde hoy y el registro se conservara como historial.',
                    confirmButtonText: 'Si, suspender',
                    method: 'suspenderAsignacion'
                };

            if (!window.SwalAmandita) {
                if (confirm(config.title)) $wire[config.method](id);
                return;
            }

            window.SwalAmandita.fire({
                title: config.title,
                text: config.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: config.confirmButtonText,
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) $wire[config.method](id);
            });
        }
    }"
>
    <section class="overflow-hidden rounded-2xl border border-borde bg-fondo-panel shadow-sm">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#2F3E5C] via-[#8DA280] to-[#E27D60]"></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-borde bg-fondo-card px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">
                        <i class="ph-bold {{ $esVistaPersonal ? 'ph-user-focus' : 'ph-stethoscope' }}"></i>
                        {{ $esVistaPersonal ? 'Mis pacientes asignados' : 'Control operativo clinico' }}
                    </span>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-titulo sm:text-3xl">
                        {{ $esVistaPersonal ? 'Mis pacientes asignados' : 'Asignaciones clinicas' }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-parrafo/70">
                        {{ $esVistaPersonal
                            ? 'Consulta los adultos mayores con asignacion activa para acceder a su seguimiento clinico.'
                            : 'Administra la relacion medico-paciente del centro y conserva el historial operativo sin eliminar registros.' }}
                    </p>
                </div>

                @if($puedeCrear)
                    <button type="button" wire:click="abrirFormulario" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_24px_rgba(47,62,92,0.18)] transition hover:bg-boton-acento active:scale-95">
                        <i class="ph-bold ph-plus-circle"></i>
                        Nueva asignacion
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach([
            ['label' => 'Activas', 'valor' => $metricas['activas'], 'icono' => 'ph-pulse', 'clase' => 'text-estado-exito bg-estado-exitoBg'],
            ['label' => 'Pacientes', 'valor' => $metricas['adultos'], 'icono' => 'ph-users-three', 'clase' => 'text-parrafo bg-fondo-panel'],
            ['label' => 'Personal', 'valor' => $metricas['personal'], 'icono' => 'ph-user-gear', 'clase' => 'text-parrafo bg-fondo-panel'],
            ['label' => 'Finalizadas', 'valor' => $metricas['finalizadas'], 'icono' => 'ph-check-circle', 'clase' => 'text-apoyo bg-fondo-panel'],
            ['label' => 'Suspendidas', 'valor' => $metricas['suspendidas'], 'icono' => 'ph-pause-circle', 'clase' => 'text-boton-acento bg-estado-peligroBg'],
        ] as $metrica)
            <article class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $metrica['clase'] }}">
                        <i class="ph-bold {{ $metrica['icono'] }} text-lg"></i>
                    </span>
                    <p class="text-2xl font-black leading-none text-titulo">{{ $metrica['valor'] }}</p>
                </div>
                <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.14em] text-apoyo">{{ $metrica['label'] }}</p>
            </article>
        @endforeach
    </section>

    @if($mostrarFormulario && $puedeCrear)
        <section class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-titulo">Registrar asignacion clinica</h2>
                    <p class="mt-1 text-xs font-bold text-parrafo/60">Solo se permite una asignacion activa del mismo tipo para el mismo adulto mayor y personal.</p>
                </div>
                <button type="button" wire:click="cerrarFormulario" class="flex h-9 w-9 items-center justify-center rounded-xl border border-borde bg-fondo-card text-parrafo transition hover:bg-boton-acento hover:text-inverso">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <form wire:submit.prevent="guardarAsignacion" class="grid gap-4 lg:grid-cols-12">
                <label class="lg:col-span-3">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Adulto mayor</span>
                    <select wire:model="cod_am" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Seleccione...</option>
                        @foreach($adultosDisponibles as $adulto)
                            <option value="{{ $adulto->cod_am }}">{{ $adulto->cod_am }} - {{ trim($adulto->nombres . ' ' . $adulto->ap_paterno . ' ' . $adulto->ap_materno) }}</option>
                        @endforeach
                    </select>
                    @error('cod_am') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <label class="lg:col-span-3">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Personal de salud</span>
                    <select wire:model="cod_per_sal" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Seleccione...</option>
                        @foreach($personalSaludDisponible as $personal)
                            <option value="{{ $personal->cod_per_sal }}">{{ $personal->usuario?->name ?? 'Sin usuario' }} - {{ $personal->especialidad?->nombre ?? 'Salud' }}</option>
                        @endforeach
                    </select>
                    @error('cod_per_sal') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <label class="lg:col-span-2">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Tipo</span>
                    <select wire:model="tipo_asignacion" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        @foreach($tiposAsignacion as $tipo)
                            <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                    @error('tipo_asignacion') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <label class="lg:col-span-2">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Inicio</span>
                    <input type="date" wire:model="fecha_inicio" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                    @error('fecha_inicio') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <label class="lg:col-span-2">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Fin opcional</span>
                    <input type="date" wire:model="fecha_fin" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                    @error('fecha_fin') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <label class="lg:col-span-9">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Motivo u observacion</span>
                    <textarea wire:model="motivo" rows="2" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15" placeholder="Contexto clinico-administrativo de la asignacion..."></textarea>
                    @error('motivo') <span class="mt-1 block text-[10px] font-bold text-boton-acento">{{ $message }}</span> @enderror
                </label>

                <div class="flex items-end justify-end gap-2 lg:col-span-3">
                    <button type="button" wire:click="cerrarFormulario" class="inline-flex h-10 items-center justify-center rounded-xl border border-borde bg-fondo-card px-4 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-boton-acento">
                        <i class="ph-bold ph-floppy-disk"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-12">
            <label class="lg:col-span-4">
                <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Buscar</span>
                <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Paciente, personal, CI o codigo..." class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
            </label>

            <label class="lg:col-span-3">
                <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Adulto mayor</span>
                <select wire:model.live="filtroAdulto" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                    <option value="">Todos</option>
                    @foreach($adultosDisponibles as $adulto)
                        <option value="{{ $adulto->cod_am }}">{{ $adulto->cod_am }} - {{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>
                    @endforeach
                </select>
            </label>

            @if($puedeGestionar)
                <label class="lg:col-span-2">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Personal</span>
                    <select wire:model.live="filtroPersonal" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($personalSaludDisponible as $personal)
                            <option value="{{ $personal->cod_per_sal }}">{{ $personal->usuario?->name ?? 'Sin usuario' }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="lg:col-span-2">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Estado</span>
                    <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($estadosAsignacion as $estado)
                            <option value="{{ $estado }}">{{ ucfirst($estado) }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <div class="hidden lg:col-span-4 lg:block"></div>
            @endif

            <label class="lg:col-span-1">
                <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Tipo</span>
                <select wire:model.live="filtroTipo" class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-xs font-bold text-parrafo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                    <option value="">Todos</option>
                    @foreach($tiposAsignacion as $tipo)
                        <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="space-y-3">
        @forelse($asignaciones as $asignacion)
            @php
                $adulto = $asignacion->adultoMayor;
                $personal = $asignacion->personalSalud;
                $estadoClases = match($asignacion->estado) {
                    'activa' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                    'suspendida' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
                    default => 'bg-fondo-card text-apoyo border-borde',
                };
            @endphp
            <article class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm transition hover:border-borde-focus">
                <div class="grid gap-4 lg:grid-cols-[1.2fr_1.1fr_0.8fr_auto] lg:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $estadoClases }}">{{ ucfirst($asignacion->estado) }}</span>
                            <span class="rounded-full border border-borde bg-fondo-card px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-apoyo">{{ ucfirst($asignacion->tipo_asignacion) }}</span>
                        </div>
                        <h3 class="mt-2 truncate text-base font-extrabold text-titulo">
                            {{ $adulto?->nombres }} {{ $adulto?->ap_paterno }} {{ $adulto?->ap_materno }}
                        </h3>
                        <p class="mt-0.5 text-[11px] font-bold uppercase tracking-wide text-apoyo">{{ $asignacion->cod_am }} - {{ $adulto?->ci ?: 'CI S/D' }}</p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Personal asignado</p>
                        <p class="mt-1 truncate text-sm font-extrabold text-parrafo">{{ $personal?->usuario?->name ?? 'Sin usuario' }}</p>
                        <p class="truncate text-[11px] font-bold text-parrafo/55">{{ $personal?->especialidad?->nombre ?? 'Personal de salud' }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Periodo</p>
                        <p class="mt-1 text-xs font-bold text-parrafo">{{ $asignacion->fecha_inicio?->format('d/m/Y') }} - {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</p>
                        <p class="mt-1 text-[10px] font-bold text-parrafo/50">Asigno: {{ $asignacion->asignador?->name ?? 'Sistema' }}</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end">
                        @if($asignacion->estado === 'activa' && $adulto)
                            <a href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-boton-principal px-3 text-[10px] font-bold uppercase tracking-wider text-inverso transition hover:bg-boton-acento">
                                <i class="ph-bold ph-folder-open"></i>
                                Expediente
                            </a>
                        @endif

                        @if($puedeGestionar && $asignacion->estado === 'activa')
                            <button type="button" @click="confirmarAsignacion({{ $asignacion->id }}, 'finalizar')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card text-estado-exito transition hover:bg-estado-exitoBg" title="Finalizar">
                                <i class="ph-bold ph-check-circle"></i>
                            </button>
                            <button type="button" @click="confirmarAsignacion({{ $asignacion->id }}, 'suspender')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso" title="Suspender">
                                <i class="ph-bold ph-pause-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>

                @if($asignacion->motivo)
                    <div class="mt-3 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold leading-relaxed text-parrafo/65">
                        {{ $asignacion->motivo }}
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-borde bg-fondo-panel p-10 text-center">
                <i class="ph-bold ph-user-focus text-4xl text-parrafo/25"></i>
                <h3 class="mt-3 text-base font-extrabold text-titulo">No hay asignaciones para mostrar</h3>
                <p class="mt-1 text-xs font-bold text-parrafo/55">
                    {{ $esVistaPersonal ? 'Actualmente no tienes pacientes con asignacion activa.' : 'Ajusta los filtros o registra una nueva asignacion clinica.' }}
                </p>
            </div>
        @endforelse
    </section>

    <div class="flex justify-center">
        {{ $asignaciones->links() }}
    </div>
</div>
