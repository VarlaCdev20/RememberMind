<div class="rm-management-workspace space-y-5">
    <header class="rm-card p-5 space-y-2">
        <div class="rm-management-actions justify-between">
            <h1 class="rm-page-title">Avisos de planificación</h1>
            @if($disponible)@include('reportes::paginas.gerencia.partials.exportar-vista', ['origenReporte' => 'avisos-planificacion'])@endif
        </div>
        <p class="rm-help">Los horarios se asignan al guardar. Administración recibe avisos para coordinar la cobertura.</p>
        <p class="rm-help">Disponible significa que el aviso puede consultarse aquí. No confirma lectura ni asistencia al turno.</p>
    </header>
    @if($horarios !== null)
        <section id="mis-horarios" class="rm-card p-5 space-y-3" aria-labelledby="titulo-mis-horarios">
            <h2 id="titulo-mis-horarios" class="rm-section-title">Mis horarios</h2>
            @if($horarios->isEmpty())
                <p class="rm-help">Sin horarios asignados para hoy o para fechas próximas.</p>
            @else
                <x-ui.table-surface role="region" tabindex="0" aria-labelledby="titulo-mis-horarios">
                    <table class="rm-table">
                        <thead><tr><th scope="col">Turno</th><th scope="col">Área</th><th scope="col">Inicio</th><th scope="col">Fin</th></tr></thead>
                        <tbody>
                            @foreach($horarios as $horario)
                                <tr>
                                    <th scope="row">{{ $horario['turno'] }}</th>
                                    <td>{{ $horario['area'] }}</td>
                                    <td><time datetime="{{ $horario['inicio']->toIso8601String() }}">{{ $horario['inicio']->format('d/m/Y H:i') }}</time></td>
                                    <td><time datetime="{{ $horario['fin']->toIso8601String() }}">{{ $horario['fin']->format('d/m/Y H:i') }}</time></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-surface>
            @endif
            @if($horarios->hasPages()){{ $horarios->links() }}@endif
        </section>
    @endif
    @if($mensaje)<div class="rm-alert" role="status">{{ $mensaje }}</div>@endif
    @if(!$disponible)
        <section class="rm-card p-5"><p>El seguimiento de avisos todavía no está habilitado en esta base.</p></section>
    @else
        <section class="rm-card p-5 space-y-4" aria-label="Cambios autorizados de planificación">
            @if($avisos->contains('estado', 'DISPONIBLE') && auth()->user()->hasRole('ADMINISTRADOR'))
                @can('turnos.ver')
                    <a class="rm-btn rm-btn-secondary" href="{{ route('admin.asignacion-turno.index') }}">Ir a asignaciones de residentes</a>
                @endcan
            @endif
            <p class="rm-help">{{ $eventos->total() }} {{ $eventos->total() === 1 ? 'cambio' : 'cambios' }} en tu ámbito. Se muestran 15 por página.</p>
            @forelse($avisos as $aviso)
                <article class="rm-card p-4 space-y-3" wire:key="aviso-evento-{{ $aviso['clave'] }}">
                    <h2 class="rm-section-title">{{ $aviso['titulo'] }} · {{ $aviso['persona'] }}</h2>
                    <p>{{ $aviso['contexto'] }} · {{ $aviso['horario'] }}</p>
                    <dl class="rm-management-context md:grid-cols-2">
                        <div><dt>Situación tras este cambio</dt><dd>{{ $aviso['situacion_asignacion'] }}</dd></div>
                        <div><dt>Fecha efectiva del cambio</dt><dd>{{ $aviso['efecto'] }}</dd></div>
                        <div><dt>Registrado</dt><dd>{{ $aviso['registro'] }}</dd></div>
                        <div><dt>Preparación del aviso</dt><dd>{{ $aviso['estado_texto'] }}</dd></div>
                    </dl>
                    @if($aviso['estado']!=='DISPONIBLE')
                        <p class="rm-help">Intentos de preparación: {{ $aviso['intentos'] }}. Reintentar no vuelve a guardar la asignación.</p>
                        <button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar('{{ $aviso['clave'] }}')" wire:loading.attr="disabled" wire:target="reintentar">Reintentar aviso</button>
                    @endif
                </article>
            @empty
                <p>Sin avisos disponibles o pendientes en tu ámbito. Los eventos sin seguimiento conservado no se reconstruyen como avisos.</p>
            @endforelse
            {{ $eventos->links() }}
            <span wire:loading wire:target="reintentar" role="status">Preparando el aviso…</span>
        </section>
    @endif
</div>
