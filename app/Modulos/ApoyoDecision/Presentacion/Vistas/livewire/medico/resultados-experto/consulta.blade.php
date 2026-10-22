@if($datos && $informe)
    @php($narrativa = $informe['narrativa'])
    <x-ui.modal-livewire wire:model="significadoAbierto" title="¿Qué significa este resultado?" :subtitle="$residente->nombre_completo.' · '.$informe['actual']['nombre']" maxWidth="md" surface="reading" class="rm-orion-significado" closeMethod="cerrarSignificado" :showValidation="false">
        <x-slot:icon><span class="rm-orion-area-icon"><i class="ph ph-book-open" aria-hidden="true"></i></span></x-slot:icon>
        <div class="rm-orion-reading">
            <section class="rm-orion-reading-summary"><h3>En palabras sencillas</h3><p class="font-semibold">{{ $narrativa['conclusion'] }}</p><p>{{ $narrativa['resumen'] }}</p></section>
            <section class="rm-orion-reading-support"><h3><i class="ph ph-files" aria-hidden="true"></i>Qué información está respaldada</h3><p>{{ $informe['actual']['conclusion'] }}</p><ul>@foreach($informe['actual']['respaldo'] as $texto)<li>{{ $texto }}</li>@endforeach</ul></section>
            <section class="rm-orion-reading-limits"><h3><i class="ph ph-info" aria-hidden="true"></i>Qué no puede concluirse</h3><p>{{ $informe['limite_global'] }}</p><ul>@foreach($informe['actual']['limites'] as $texto)<li>{{ $texto }}</li>@endforeach</ul></section>
            <section><h3>Qué puedes consultar después</h3><div class="rm-orion-toolbar">
                <button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary">Ver evidencias</button>
                <button type="button" wire:click="irASeccion('orion-desglose')" class="rm-btn rm-btn-secondary">Abrir desglose cognitivo</button>
                <button type="button" wire:click="irASeccion('orion-evolucion')" class="rm-btn rm-btn-secondary">Consultar evolución disponible</button>
                @if($puedeAuditar)<button type="button" wire:click="abrirConsulta('reglas')" class="rm-btn rm-btn-secondary">Ver reglas aplicadas</button>@endif
                @if($enfermeria)<button type="button" wire:click="abrirConsulta('cuidados')" class="rm-btn rm-btn-secondary">Cuidados autorizados</button>@endif
            </div></section>
        </div>
    </x-ui.modal-livewire>
    @unless($enfermeria)
        <x-ui.modal-livewire wire:model="informeCompletoAbierto" title="Informe clínico completo" :subtitle="$residente->nombre_completo" maxWidth="xl" surface="reading" class="rm-expert-report-modal" closeMethod="cerrarInformeCompleto" :showValidation="false">
            <x-slot:icon><span class="rm-orion-area-icon"><i class="ph ph-file-text" aria-hidden="true"></i></span></x-slot:icon>
            <p class="rm-expert-report-modal__intro">Esta lectura corresponde a la evaluación seleccionada. Revise el resultado, los fundamentos, las fuentes y la información que aún falta.</p>
            <div class="rm-expert-report-modal__content">
                @include('apoyodecision::livewire.medico.resultados-experto.informe-narrativo')
            </div>
            <x-slot:footer><button type="button" wire:click="cerrarInformeCompleto" class="rm-btn rm-btn-secondary">Cerrar informe</button></x-slot:footer>
        </x-ui.modal-livewire>
    @endunless
    <x-ui.drawer-livewire wire:model="consultaAbierta" title="Consulta de la evaluación" :subtitle="$residente->nombre_completo.' · '.$informe['actual']['nombre']" :badge="false" icon="ph-notebook" size="xl" :docked="true" surface="reading" class="rm-orion-consulta" closeMethod="cerrarConsulta">
        <p class="rm-orion-note">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($datos['evaluacion']['fecha'] ?? null, 'Sin evaluación histórica guardada') }}.</p>
        <div class="rm-orion-tabs" role="group" aria-label="Secciones de consulta">
            @foreach(['interpretacion' => 'Interpretación', 'registro' => ($enfermeria ? 'Registro' : 'Datos y evidencias')] as $clave => $titulo)
                <button type="button" wire:click="abrirConsulta(@js($clave))" aria-pressed="{{ $pestanaConsulta === $clave ? 'true' : 'false' }}" class="rm-btn rm-btn-secondary">{{ $titulo }}</button>
            @endforeach
            @if($enfermeria)<button type="button" wire:click="abrirConsulta('cuidados')" aria-pressed="{{ $pestanaConsulta === 'cuidados' ? 'true' : 'false' }}" class="rm-btn rm-btn-secondary">Cuidados</button>
            @elseif($puedeAuditar)<button type="button" wire:click="abrirConsulta('reglas')" aria-pressed="{{ $pestanaConsulta === 'reglas' ? 'true' : 'false' }}" class="rm-btn rm-btn-secondary">Reglas aplicadas</button>@endif
        </div>
        @if($pestanaConsulta === 'interpretacion')
            <div class="rm-orion-reading"><h3>{{ $informe['actual']['estado'] }}</h3><p>{{ $simulacion ? 'Esta lectura procede de un caso de prueba y no representa una valoración clínica del residente.' : $informe['actual']['significado'] }}</p><p>{{ $informe['actual']['por_que'] }}</p>
                @foreach($informe['actual']['limites'] as $texto)<p class="rm-orion-note">{{ $texto }}</p>@endforeach
            </div>
        @elseif($pestanaConsulta === 'registro')
            @if($simulacion)
                <p class="rm-orion-note">Instantánea de simulación. Las referencias artificiales no se abren como fuentes clínicas actuales.</p>
            @else
                @include('livewire.medico.resultados-experto.evidencias')
            @endif
            <p class="rm-orion-note">{{ $informe['limite_fuente'] }}</p>
            @if($enfermeria)
                <h3 class="rm-expert-section-title mt-4">Diagnóstico médico registrado</h3>
                <p class="text-sm">Se presenta separado del resultado experto.</p>
                @if($puedeVerDiagnosticos)
                    @forelse($diagnosticosMedicos as $diagnostico)<article class="rm-expert-source mt-3"><strong>{{ $diagnostico->nombre }}</strong><p>{{ $diagnostico->fecha_hora?->format('d/m/Y H:i') }} · {{ trim(($diagnostico->personal?->nombres ?? '').' '.($diagnostico->personal?->apellido_paterno ?? '')) }}</p></article>
                    @empty<p>No hay diagnósticos médicos registrados.</p>@endforelse
                @else<p>No dispone de permiso para consultar diagnósticos médicos.</p>@endif
            @endif
        @elseif($pestanaConsulta === 'cuidados' && $enfermeria)
            <h3 class="rm-expert-section-title">Plan de cuidados vigente</h3>
            <p class="text-sm">Solo consulta. Los cuidados se ejecutan desde la agenda de turno.</p>
            @if($puedeVerCuidados)
                @php($todosCuidados = $cuidadosPendientesHoy->concat($otrosCuidadosVigentes))
                @foreach(['PENDIENTE' => ['Pendientes de la jornada', 'ph-clock'], 'REALIZADA' => ['Realizados hoy', 'ph-check-circle'], 'NO_REALIZADA' => ['No realizados hoy', 'ph-minus-circle'], 'CANCELADA' => ['Cancelados hoy', 'ph-prohibit'], 'SIN_OCURRENCIA_HOY' => ['Otros cuidados vigentes', 'ph-heart']] as $estado => [$titulo, $icono])
                    @php($cuidados = $todosCuidados->where('estado', $estado))
                    @continue($cuidados->isEmpty() && $estado !== 'PENDIENTE')
                    <section class="rm-orion-care-group" data-state="{{ $estado }}"><h4><i class="ph {{ $icono }}" aria-hidden="true"></i>{{ $titulo }}<span>{{ $cuidados->count() }}</span></h4>
                        @forelse($cuidados as $cuidado)<article class="rm-orion-care-row"><div><strong>{{ $cuidado['intervencion']->nombre }}</strong><small>{{ $cuidado['plan']->nombre }}</small></div>
                            <span>{{ $cuidado['hora'] ?? 'Sin tarea hoy' }}</span></article>
                        @empty<p class="text-sm mt-2">No hay cuidados en este grupo.</p>@endforelse
                    </section>
                @endforeach
                <a class="rm-btn rm-btn-primary mt-4" href="{{ route('admin.enfermeria.agenda', ['residente' => $codResidente]) }}">Ir a mi agenda de turno</a>
            @else<p class="rm-orion-empty">No dispone de autorización para consultar la programación y ejecución de cuidados.</p>@endif
        @elseif($pestanaConsulta === 'reglas' && $exploracion)
            @if($nodoGrafo && ($nodo = collect($exploracion[$tipoGrafo]['nodos'])->firstWhere('id', $nodoGrafo)))
                <div class="rm-orion-reading"><h3>{{ $nodo['nombre'] }}</h3><p>{{ $nodo['tipo'] }} · {{ $nodo['estado'] }} · Versión {{ $nodo['version'] }}</p>
                    <dl class="rm-orion-definition">@foreach($nodo['datos'] as $nombre => $valor)<div><dt>{{ $nombre }}</dt><dd>{{ $valor }}</dd></div>@endforeach</dl></div>
            @else
                @include('apoyodecision::livewire.medico.resultados-experto.reglas-tabla')
            @endif
        @endif
    </x-ui.drawer-livewire>
@endif
