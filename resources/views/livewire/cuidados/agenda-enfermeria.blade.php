<div class="space-y-5">
    <header class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-boton-acento">{{ $esSuperAdmin ? 'Supervisión institucional' : 'Puesto de trabajo' }}</p>
                <h1 class="mt-1 text-2xl font-black text-titulo">{{ $esSuperAdmin ? 'Agenda institucional de Enfermería' : 'Agenda priorizada de Enfermería' }}</h1>
                <p class="mt-1 text-sm text-apoyo">{{ $esSuperAdmin ? 'Alertas, medicación y cuidados de todos los residentes, ordenados por urgencia.' : 'Alertas, medicación y cuidados de sus residentes asignados, ordenados por urgencia.' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">{{ $esSuperAdmin ? 'Resumen global' : 'Mi turno' }}</a>
                <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis pacientes' }}</a>
                @if(!$esSuperAdmin && $turno && !$recepcion)
                    <button wire:click="recibirTurno" class="rm-btn-primary px-4 py-2 text-xs font-bold">Recibir turno</button>
                @elseif($recepcion)
                    <span class="rounded-xl bg-estado-exitoBg px-4 py-2 text-xs font-bold text-estado-exito">Recibido {{ $recepcion->fecha_hora_recepcion->format('H:i') }}</span>
                @endif
            </div>
        </div>
    </header>

    @if(!$esSuperAdmin && !$turno)
        <div class="rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-5 text-sm text-titulo">
            No existe un turno activo asignado para hoy. Solicite la asignación antes de registrar cuidados.
        </div>
    @endif

    <section class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
            <input wire:model.live.debounce.300ms="buscar" type="search" placeholder="Buscar residente, medicamento o tarea" class="rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm text-parrafo outline-none focus:border-borde-focus">
            <div class="flex flex-wrap gap-2">
                @foreach(['PENDIENTES' => 'Pendientes', 'VENCIDOS' => 'Vencidos', 'PROXIMOS' => 'Próximos', 'MEDICACION' => 'Medicación', 'ALERTAS' => 'Alertas', 'TAREAS' => 'Cuidados', 'TODOS' => 'Todos'] as $valor => $etiqueta)
                    <button wire:click="$set('filtro', '{{ $valor }}')" class="rounded-xl border px-3 py-2 text-xs font-bold transition {{ $filtro === $valor ? 'border-boton-principal bg-boton-principal text-inverso' : 'border-borde bg-fondo-card text-parrafo hover:border-borde-focus' }}">{{ $etiqueta }}</button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="space-y-3">
        @forelse($items as $item)
            @php
                $paciente = $item['paciente'];
                $clase = match($item['prioridad']) {
                    1 => 'border-estado-peligroBorde bg-estado-peligroBg',
                    2, 3, 4 => 'border-estado-advertenciaBorde bg-estado-advertenciaBg',
                    default => 'border-borde bg-fondo-panel',
                };
            @endphp
            <article class="rounded-2xl border {{ $clase }} p-4 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-xl text-boton-acento">
                            <i class="ph-bold {{ $item['tipo'] === 'MEDICACION' ? 'ph-pill' : ($item['tipo'] === 'ALERTA' ? 'ph-warning' : 'ph-check-square') }}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">{{ $item['tipo'] }} · {{ $item['estado'] }}</span>
                                <time class="text-xs font-bold text-titulo">{{ $item['fecha_hora']?->format('H:i') }}</time>
                            </div>
                            <h2 class="truncate text-sm font-black text-titulo">{{ $paciente?->nombres }} {{ $paciente?->ap_paterno }} — {{ $item['titulo'] }}</h2>
                            <p class="mt-1 text-xs text-parrafo">{{ $item['detalle'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold text-apoyo">HC {{ $paciente?->cod_am }} · Hab. {{ $paciente?->habitacion?->numero ?? 'Sin asignar' }}</p>
                        </div>
                    </div>
                    @if($paciente)
                        <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => $item['tipo'] === 'ALERTA' ? 'alertas' : ($item['tipo'] === 'MEDICACION' ? 'medicacion' : 'cuidado')]) }}" class="rm-btn-primary shrink-0 px-4 py-2 text-xs font-bold">Abrir y registrar</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-borde bg-fondo-panel p-10 text-center">
                <i class="ph-bold ph-check-circle text-4xl text-estado-exito"></i>
                <h2 class="mt-3 text-base font-black text-titulo">No hay actividades en este filtro</h2>
                <p class="mt-1 text-sm text-apoyo">La agenda se actualizará desde órdenes, planes y alertas reales.</p>
            </div>
        @endforelse
    </section>
</div>
