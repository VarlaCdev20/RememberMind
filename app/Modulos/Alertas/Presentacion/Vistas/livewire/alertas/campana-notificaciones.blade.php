<div class="rm-notification-center"
    @unless($previsualizacion) wire:poll.visible.60s="verificarAlertas" @endunless
    x-data="{
        abierto: false,
        avisos: [],
        get actual() { return this.avisos[0] },
        critico(aviso) { return ['CRITICO', 'CRITICA'].includes(aviso?.nivel) },
        recibir(aviso) {
            if (this.avisos.some(item => item.id === aviso.id)) return
            this.avisos.push(aviso)
            this.avisos.sort((a, b) => Number(this.critico(b)) - Number(this.critico(a)))
        },
        abrirSeccion(seccion) {
            this.abierto = true
            this.avisos.shift()
            this.$nextTick(() => (this.$refs[seccion] ?? this.$refs.tituloAvisos).focus())
        },
        ocultar() {
            this.avisos.shift()
            if (!this.actual) this.$refs.botonAvisos.focus()
        }
    }"
    @click.outside="abierto = false"
    @keydown.escape.window="if (abierto) { abierto = false; $refs.botonAvisos.focus() }"
    @alerta-nueva="recibir($event.detail[0])"
    @avisos-retirados="avisos = avisos.filter(aviso => !$event.detail.ids.includes(aviso.id))">
    <button type="button" class="rm-topbar__action rm-topbar__notification" x-ref="botonAvisos" @click="abierto = !abierto; $nextTick(() => { if (abierto) $refs.tituloAvisos.focus() })" :aria-expanded="String(abierto)" aria-controls="notification-list-{{ $this->getId() }}" aria-label="Consultar avisos y alertas pendientes"><i class="ph-bold ph-bell" aria-hidden="true"></i>@if($conteoAbiertas)<span class="rm-notification-count">{{ $conteoAbiertas > 99 ? '99+' : $conteoAbiertas }}</span>@endif</button>
    <span class="sr-only" role="status" x-text="actual ? actual.titulo + '. ' + actual.mensaje : ''"></span>
    <aside x-cloak x-show="actual && !abierto" class="rm-notification-popup" aria-label="Aviso emergente"
        @keydown.escape.stop="ocultar()"
        :class="{ 'is-critical': critico(actual), 'is-review': ['REVISION', 'VENCIDA', 'ALTA', 'ALTO', 'MEDIA', 'MEDIO'].includes(actual?.nivel) }">
        <span class="rm-notification-popup__icon" aria-hidden="true"><i class="ph-bold" :class="actual?.icono"></i></span>
        <div class="rm-notification-popup__content">
            <span class="rm-notification-popup__category" x-text="actual?.categoria"></span>
            <strong x-text="actual?.titulo"></strong>
            <p x-text="actual?.mensaje"></p>
            <a x-show="actual?.url" class="rm-notification-popup__action" :href="actual?.url" x-text="actual?.accion"></a>
            <button x-show="!actual?.url" type="button" class="rm-notification-popup__action"
                @click="abrirSeccion(actual.seccion)" x-text="actual?.accion"></button>
            <small x-show="avisos.length > 1" x-text="'+' + (avisos.length - 1) + ' avisos nuevos'"></small>
        </div>
        <button type="button" class="rm-notification-popup__close" aria-label="Ocultar aviso"
            @click="ocultar()"><i class="ph-bold ph-x" aria-hidden="true"></i></button>
    </aside>
    <section x-cloak x-show="abierto" id="notification-list-{{ $this->getId() }}" class="rm-notification-list" aria-label="Avisos y pendientes autorizados">
        <header><h2 x-ref="tituloAvisos" tabindex="-1">Avisos y pendientes</h2><p>Actualizado {{ now()->format('d/m/Y H:i:s T') }} · {{ $previsualizacion ? 'Previsualización de solo lectura; actualiza la página para consultar cambios.' : 'Revisión cada 60 s mientras esta pantalla permanece activa.' }}</p></header>
        @isset($visitasFamiliares)
            <h3 x-ref="visitas" tabindex="-1">Próximas visitas</h3>
            @forelse($visitasFamiliares as $visita)
                <div class="rm-notification-item">
                    <strong>{{ $visita->estado === 'AUTORIZADA' ? 'Visita autorizada' : 'Visita programada' }}</strong>
                    <span>{{ $visita->nombres }} {{ $visita->apellido_paterno }}</span>
                    <time>{{ \Illuminate\Support\Carbon::parse($visita->fecha_hora_programada)->format('d/m/Y H:i') }}</time>
                </div>
            @empty
                <p>No tienes visitas próximas.</p>
            @endforelse
        @else
        @if($fallosCorreo > 0)
            <a class="rm-notification-item" href="{{ route('admin.usuarios.correos.index') }}">
                <strong>Envíos documentales fallidos</strong>
                <span>{{ $fallosCorreo }} en tu historial de correo</span>
            </a>
        @endif
        @if($derivacionesRecibidas->isNotEmpty())
            <h3 x-ref="derivaciones" tabindex="-1">Derivaciones recibidas</h3>
            @foreach($derivacionesRecibidas as $derivacion)
                <div class="rm-notification-item">
                    <strong>Derivación {{ $derivacion->getKey() }}</strong>
                    <span>Prioridad {{ mb_strtolower($derivacion->prioridad) }}</span>
                    <time>{{ $derivacion->fecha_hora?->format('d/m/Y H:i') }}</time>
                </div>
            @endforeach
        @endif
        @if($avisosDocumentales)<h3>Documentación del personal</h3><p>{{ count($avisosDocumentales) }} solicitudes requieren consulta. Cada solicitud/persona aparece una sola vez.</p>@foreach(array_slice($avisosDocumentales,0,10) as $aviso)<a class="rm-notification-item" href="{{ $aviso['url'] }}"><strong>{{ $aviso['titulo'] }}</strong>@if($aviso['persona'])<span>{{ $aviso['persona'] }}</span>@endif<span>{{ $aviso['mensaje'] }}</span><time>Límite: {{ $aviso['fecha'] }}</time></a>@endforeach<a class="rm-notification-item" href="{{ route('admin.personal.documentacion') }}">Ver documentación y seguimiento</a>@endif
        @if($cambiosLaborales)<h3>Cambios laborales y planificación</h3>@foreach(array_slice($cambiosLaborales,0,10) as $cambio)<a class="rm-notification-item" href="{{ $cambio['url'] }}"><strong>{{ $cambio['persona'] }}</strong><span>{{ $cambio['mensaje'] }}</span><time>{{ $cambio['fecha'] }}</time></a>@endforeach<p>Consultar el aviso no acredita revisión ni modifica las asignaciones.</p>@endif
        @if($avisosPlanificacion)
            <h3>Planificación · {{ $totalPlanificacion }} avisos en tu ámbito</h3>
            <p>Últimos cinco cambios. La disponibilidad no indica que hayan sido leídos.</p>
            @foreach($avisosPlanificacion as $aviso)
                <a class="rm-notification-item" href="{{ route('admin.avisos-planificacion.index') }}">
                    <strong>{{ $aviso['titulo'] }} · {{ $aviso['persona'] }}</strong><span>{{ $aviso['contexto'] }}</span>
                    <time>Con efecto {{ $aviso['efecto'] }}</time><span>{{ $aviso['estado_texto'] }}</span>
                </a>
            @endforeach
            <a class="rm-notification-item" href="{{ route('admin.avisos-planificacion.index') }}">Ver todos los avisos de planificación</a>
        @endif
        @if(app(\App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas::class)->paraUsuario(auth()->user())['foco'] === 'supervision')
            <a class="rm-notification-item" href="{{ route('admin.alertas-clinicas.index') }}">Ver avisos administrativos</a>
        @else
        <p>{{ $conteoAlertas }} alertas persistidas · {{ $conteoRecordatorios }} recordatorios de medicación</p>
        @if($puedeConsultarAlertas)
        <h3>Alertas registradas · Prioridad primero</h3>
        @forelse($alertas as $alerta)<a class="rm-notification-item {{ in_array($alerta->prioridad,['CRITICO','CRITICA']) ? 'is-critical' : '' }}" href="{{ route(auth()->user()?->hasRole('ENFERMEROS') ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index', ['alerta'=>$alerta->cod_alerta]) }}"><strong>{{ $alerta->prioridad }} · {{ $alerta->tipo }}</strong><span>{{ $alerta->residente?->nombre_completo }} · {{ $alerta->estado }}</span><time>{{ $alerta->fecha_hora?->format('d/m/Y H:i:s T') }}</time></a>@empty<p>Sin alertas pendientes en tu ámbito.</p>@endforelse
        <p>Se muestran hasta 15 alertas, ordenadas por prioridad.</p>
        <a class="rm-notification-item" href="{{ route(auth()->user()?->hasRole('ENFERMEROS') ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index') }}">Ver todas las alertas</a>
        @endif
        @if($puedeConsultarMedicacion)
        <h3>Medicación · Programación de hoy</h3><p>Un horario vencido no indica por sí solo una emergencia clínica.</p>
        @forelse($recordatoriosMedicacion as $recordatorio)<a class="rm-notification-item" href="{{ route(auth()->user()?->hasRole('ENFERMEROS') ? 'admin.enfermeria.medicacion' : 'admin.salud-seguimiento.administracion', ['adulto'=>$recordatorio['medicacion']->cod_residente]) }}"><strong>{{ $recordatorio['estado'] === 'VENCIDA' ? 'Vencida · comprobar registro' : 'Próxima dosis · revisar orden' }}</strong><span>{{ $recordatorio['adulto']?->nombre_completo }} · {{ $recordatorio['medicacion']->nombre_medicamento }}</span><time datetime="{{ $recordatorio['programada']->toIso8601String() }}">Programada: {{ $recordatorio['programada']->format('H:i') }} · {{ $recordatorio['programada']->format('d/m/Y H:i:s T') }}</time><span>{{ abs($recordatorio['minutos']) }} min {{ $recordatorio['minutos'] < 0 ? 'desde el horario programado' : 'para el horario programado' }}</span></a>@empty<p>Sin recordatorios de medicación en esta consulta.</p>@endforelse
        <p>Los avisos informan; el historial de eventos conserva las actuaciones. Revisar avisos no documenta atención ni administración. Confirma cada intervención en su módulo.</p>
        @endif
        @endif
        @endisset
    </section>
</div>
