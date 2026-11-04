<div class="rm-handoff space-y-6" x-data="{
    captureDirty: false, receiptDirty: false, discardCapture: false, discardReceipt: false,
    contextOpen: window.matchMedia('(min-width: 1024px)').matches, returnFocusKey: null,
    rememberTrigger(event) {
        const button = event.target.closest('button');
        if (button?.getAttribute('wire:click')?.startsWith('abrir')) this.returnFocusKey = button.closest('article')?.getAttribute('wire:key');
    },
    restoreTrigger() {
        this.$nextTick(() => requestAnimationFrame(() => {
            const card = [...this.$root.querySelectorAll('article')].find(element => element.getAttribute('wire:key') === this.returnFocusKey);
            (card?.querySelector('button:not([disabled])') || this.$root.querySelector('button'))?.focus();
        }));
    }
}" @click.capture="rememberTrigger($event)" @pase-ventana-cerrada.window="restoreTrigger()" @pase-captura-abierta.window="captureDirty = false; discardCapture = false" @pase-recepcion-abierta.window="receiptDirty = false; discardReceipt = false" @resize.window="contextOpen = window.matchMedia('(min-width: 1024px)').matches">
    <x-ui.collection-header title="Pases de turno" subtitle="Continuidad de cuidados y comunicación entre jornadas." icon="ph-arrows-left-right" eyebrow="Continuidad asistencial">
        <x-slot:actions><div class="rm-handoff__tabs" role="group" aria-label="Vistas de pases">
            <x-ui.action-button :variant="$tabActivo === 'entrega' ? 'primary' : 'secondary'" icono="ph-handshake" wire:click="cambiarTab('entrega')" aria-pressed="{{ $tabActivo === 'entrega' ? 'true' : 'false' }}">Transferencia de guardia</x-ui.action-button>
            <x-ui.action-button :variant="$tabActivo === 'historial' ? 'primary' : 'secondary'" icono="ph-clock-counter-clockwise" wire:click="cambiarTab('historial')" aria-pressed="{{ $tabActivo === 'historial' ? 'true' : 'false' }}">Historial de pases</x-ui.action-button>
        </div></x-slot:actions>
    </x-ui.collection-header>
    @if(session()->has('mensaje'))<p class="rm-handoff__notice" role="status"><i class="ph-bold ph-check-circle" aria-hidden="true"></i>{{ session('mensaje') }}</p>@endif
    <section class="rm-handoff__context-bar rm-card" aria-label="Contexto del pase">
        <div><span>Jornada actual · saliente</span><strong>{{ $jornadaSaliente?->turno?->nombre ?? 'Fuera de jornada activa' }}</strong>@if($jornadaSaliente)<small>{{ $jornadaSaliente->fecha_jornada->format('d/m/Y') }} · {{ substr($jornadaSaliente->turno->hora_inicio, 0, 5) }}–{{ substr($jornadaSaliente->turno->hora_cierre, 0, 5) }}</small>@endif</div>
        <div><span>Profesional responsable</span><strong>{{ auth()->user()->name }}</strong></div>
        <div><span>Relevo · siguiente jornada</span><strong>{{ $jornadaEntrante?->turno?->nombre ?? 'Sin relevo planificado' }}</strong>@if($jornadaEntrante)<small>{{ $jornadaEntrante->fecha_jornada->format('d/m/Y') }} · {{ substr($jornadaEntrante->turno->hora_inicio, 0, 5) }}–{{ substr($jornadaEntrante->turno->hora_cierre, 0, 5) }}</small>@endif</div>
    </section>
    @if($errorConfiguracion)<div class="rm-handoff__notice" data-tone="warning" role="status"><i class="ph-bold ph-info" aria-hidden="true"></i><div><strong>{{ $errorConfiguracion }}</strong><p>La consulta del historial y de los pases recibibles sigue disponible. Si falta el relevo, solicita a coordinación revisar la planificación.</p></div></div>@endif

    @if($tabActivo === 'entrega')
        @if(count($pasesPendientesRecibir))
            <section class="space-y-3" aria-labelledby="handoff-receive-title"><x-ui.section-header id="handoff-receive-title" level="2" title="Pases pendientes de recibir" :subtitle="count($pasesPendientesRecibir).' pases dirigidos a ti o a residentes de tu jornada entrante.'" icon="ph-tray" />
                <div class="rm-handoff__grid">@foreach($pasesPendientesRecibir as $paseRec)
                    <article class="rm-card rm-handoff__resident" wire:key="recibir-{{ $paseRec->getKey() }}"><header><div><h3>{{ $paseRec->residente?->nombre_completo ?? 'Residente no disponible' }}</h3><p>{{ $paseRec->residente?->ubicacion_formateada ?? 'Ubicación no disponible' }}</p></div><x-ui.status-badge :estado="$paseRec->estado" /></header>
                        <p>De <strong>{{ $paseRec->personalSaliente?->usuario?->name ?? 'Profesional no disponible' }}</strong></p><p>{{ $paseRec->fecha_hora?->format('d/m/Y H:i') }} · {{ $paseRec->jornadaSaliente?->turno?->nombre }} → {{ $paseRec->jornadaEntrante?->turno?->nombre }}</p>
                        <p class="rm-handoff__excerpt">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($paseRec->resumen) }}</p>
                        <x-ui.action-button variant="primary" icono="ph-check-square" wire:click="abrirRevisarPase('{{ $paseRec->getKey() }}')" loading="abrirRevisarPase">Revisar pase</x-ui.action-button>
                    </article>
                @endforeach</div>
            </section>
        @endif
        <section class="space-y-3" aria-labelledby="handoff-outgoing-title">
            <div class="rm-handoff__section-heading"><x-ui.section-header id="handoff-outgoing-title" level="2" title="Residentes a entregar" subtitle="Prepara el resumen, comunica pendientes y confirma la entrega." icon="ph-users-three" />
                <div class="rm-handoff__tabs" role="group" aria-label="Filtrar residentes a entregar">@foreach(['todos' => 'Todos', 'criticos' => 'Con alerta / incidente', 'pendientes' => 'Por entregar'] as $filtro => $etiqueta)<x-ui.action-button :variant="$filtroEntrega === $filtro ? 'primary' : 'secondary'" wire:click="$set('filtroEntrega', '{{ $filtro }}')" aria-pressed="{{ $filtroEntrega === $filtro ? 'true' : 'false' }}">{{ $etiqueta }}</x-ui.action-button>@endforeach</div>
            </div>
            <div class="rm-handoff__grid">@forelse($residentesAEntregar as $item)
                @php($pase = $item['pase'])
                <article class="rm-card rm-handoff__resident" wire:key="entregar-{{ $item['residente']->getKey() }}"><header><div><h3>{{ $item['nombre_completo'] }}</h3><p>{{ $item['ubicacion'] }}</p></div><x-ui.status-badge :estado="$pase?->estado ?? 'SIN_INICIAR'" /></header>
                    <p><span class="rm-caption">Receptor designado</span><strong class="block">{{ $item['nombre_receptor'] }}</strong></p>
                    <div class="rm-handoff__indicators"><span><i class="ph-bold ph-bell" aria-hidden="true"></i>{{ $item['alertas_count'] }} alertas activas</span><span><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ $item['incidentes_count'] }} incidentes abiertos / en seguimiento</span></div>
                    @if($pase && ! $pase->esBorrador())<x-ui.action-button variant="secondary" icono="ph-eye" wire:click="abrirVer('{{ $pase->getKey() }}')" loading="abrirVer">Ver pase</x-ui.action-button>
                    @elseif($jornadaEntrante && auth()->user()->can($pase ? 'pases_turno.editar' : 'pases_turno.crear') && (! $pase || $pase->cod_personal_saliente === $personal?->getKey()))<x-ui.action-button variant="primary" icono="ph-note-pencil" wire:click="abrirPrepararPase('{{ $item['residente']->getKey() }}')" loading="abrirPrepararPase">{{ $pase ? 'Continuar borrador' : 'Preparar pase' }}</x-ui.action-button>
                    @else<p class="rm-help">{{ $jornadaEntrante ? 'Preparación disponible para el autor autorizado.' : 'La entrega estará disponible cuando exista un relevo planificado.' }}</p>@endif
                </article>
            @empty<div class="rm-handoff__empty"><x-ui.empty-state icono="ph-users-three" titulo="Sin residentes en esta vista" texto="Revisa el filtro o consulta el historial de tus pases. La preparación requiere una jornada activa con residentes asignados." /></div>@endforelse</div>
        </section>
    @else
        <section class="space-y-4" aria-labelledby="handoff-history-title"><x-ui.section-header id="handoff-history-title" level="2" title="Historial de pases" subtitle="Registros conservados con su autoría, entrega y recepción." icon="ph-clock-counter-clockwise" />
            <div class="rm-filter-bar rm-handoff__filters">
                <x-ui.field label="Buscar" for="handoff-search"><input id="handoff-search" class="rm-input" wire:model.live.debounce.350ms="searchHistorial" maxlength="100" placeholder="Residente o resumen"></x-ui.field>
                <x-ui.field label="Fecha del pase" for="handoff-date"><input id="handoff-date" class="rm-input" type="date" wire:model.live="filtroFechaHistorial"></x-ui.field>
                <x-ui.field label="Estado" for="handoff-state"><select id="handoff-state" class="rm-select" wire:model.live="filtroEstadoHistorial"><option value="">Todos los estados</option>@foreach(['BORRADOR' => 'Borrador', 'ENTREGADO' => 'Entregado', 'RECIBIDO' => 'Recibido', 'ANULADO' => 'Anulado', 'EMITIDO' => 'Emitido', 'GENERADO' => 'Generado', 'PENDIENTE' => 'Pendiente'] as $estado => $etiqueta)<option value="{{ $estado }}">{{ $etiqueta }}</option>@endforeach</select></x-ui.field>
                <x-ui.action-button variant="secondary" icono="ph-funnel-x" wire:click="limpiarHistorial">Limpiar filtros</x-ui.action-button>
            </div>
            <p class="rm-caption" role="status">{{ $historialPases->total() }} pases encontrados</p>
            <div class="rm-handoff__grid">@forelse($historialPases as $pase)
                <article class="rm-card rm-handoff__resident" wire:key="historial-{{ $pase->getKey() }}"><header><div><h3>{{ $pase->residente?->nombre_completo ?? 'Residente no disponible' }}</h3><p>{{ $pase->fecha_hora?->format('d/m/Y H:i') }}</p></div><x-ui.status-badge :estado="$pase->estado" /></header>
                    <p>{{ $pase->jornadaSaliente?->turno?->nombre }} → {{ $pase->jornadaEntrante?->turno?->nombre }}</p><p><span class="rm-caption">Profesional saliente</span><strong class="block">{{ $pase->personalSaliente?->usuario?->name ?? 'No disponible' }}</strong></p>
                    <p class="rm-handoff__excerpt">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($pase->resumen) }}</p><x-ui.action-button variant="secondary" icono="ph-eye" wire:click="abrirVer('{{ $pase->getKey() }}')" loading="abrirVer">Ver detalle</x-ui.action-button>
                </article>
            @empty<div class="rm-handoff__empty"><x-ui.empty-state icono="ph-clock-counter-clockwise" titulo="No hay pases para estos filtros" texto="Prueba otra fecha o limpia los filtros para consultar todo el historial autorizado." /></div>@endforelse</div>
            <x-ui.paginacion :paginator="$historialPases" mode="livewire" :show-per-page="false" label="pases" />
        </section>
    @endif

    <x-ui.modal-livewire id="handoff-prepare" wire:model="modalPreparar" title="Preparar pase de turno" subtitle="Resume la jornada y comunica lo que necesita conocer el relevo." maxWidth="xl" closeMethod="cerrarModalPreparar" alpineClose="if (captureDirty) discardCapture = true; else $wire.cerrarModalPreparar()" class="rm-handoff__modal rm-clinical-form-modal">
        @if($modalPreparar && $residenteSeleccionado)
            <x-slot:context><div class="rm-handoff__identity"><div><span class="rm-caption">Residente</span><h3>{{ $residenteSeleccionado->nombre_completo }}</h3><p>{{ $residenteSeleccionado->ubicacion_formateada }}</p></div><div><span class="rm-caption">Receptor designado</span><strong>{{ $nombreReceptorDesignado }}</strong></div></div></x-slot:context>
            <div x-show="discardCapture" x-cloak class="rm-handoff__notice" data-tone="warning" role="alert"><div><strong>Hay cambios sin guardar.</strong><p>Puedes seguir editando o cerrar y descartar esta captura.</p><div class="rm-handoff__tabs"><button type="button" class="rm-btn-secondary" @click="discardCapture = false">Seguir editando</button><button type="button" class="rm-btn-secondary" @click="captureDirty = false; discardCapture = false; $wire.cerrarModalPreparar()">Descartar cambios</button></div></div></div>
            @if(count($contextoClinico['alertas'] ?? []) || count($contextoClinico['incidentes'] ?? []))<p class="rm-handoff__notice" data-tone="warning"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ count($contextoClinico['alertas'] ?? []) }} alertas activas y {{ count($contextoClinico['incidentes'] ?? []) }} incidentes para revisar en el contexto.</p>@endif
            <div class="rm-handoff__capture-layout">
                <form id="handoff-form" class="space-y-5" wire:submit="confirmarEntrega" @input="captureDirty = true" @change="captureDirty = true">
                    <x-ui.form-section title="Resumen de la jornada" description="Describe hechos observados. El estado general es texto libre y no constituye una clasificación automática." :columns="1" step="1">
                        <x-ui.field label="Estado general descrito" for="handoff-general" error="estadoGeneral"><input id="handoff-general" class="rm-input" wire:model="estadoGeneral" maxlength="200" aria-invalid="{{ $errors->has('estadoGeneral') ? 'true' : 'false' }}" @error('estadoGeneral') aria-describedby="handoff-general-error" @enderror></x-ui.field>
                        <x-ui.field label="Resumen clínico de la guardia" for="handoff-summary" error="resumenTurno" required help="Obligatorio para entregar. Entre 5 y 5000 caracteres."><textarea id="handoff-summary" class="rm-textarea" wire:model="resumenTurno" rows="4" maxlength="5000" aria-invalid="{{ $errors->has('resumenTurno') ? 'true' : 'false' }}" @error('resumenTurno') aria-describedby="handoff-summary-error" @enderror></textarea></x-ui.field>
                    </x-ui.form-section>
                    <x-ui.form-section title="Continuidad de cuidados" :columns="1" step="2">
                        @foreach(['pendientesTurno' => 'Acciones o cuidados pendientes', 'vigilanciaTurno' => 'Puntos de vigilancia especial', 'recomendacionTurno' => 'Recomendaciones para el relevo'] as $campo => $etiqueta)<x-ui.field :label="$etiqueta" :for="'handoff-'.$campo" :error="$campo"><textarea id="handoff-{{ $campo }}" class="rm-textarea" wire:model="{{ $campo }}" rows="2" maxlength="3000" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @if($errors->has($campo)) aria-describedby="handoff-{{ $campo }}-error" @endif></textarea></x-ui.field>@endforeach
                    </x-ui.form-section>
                    <p class="rm-help">Guardar borrador permite continuar después. Confirmar entrega conserva el pase y bloquea su edición; no marca los pendientes como resueltos.</p>
                    @error('error_general')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                </form>
                <details class="rm-handoff__support" :open="contextOpen" @toggle="contextOpen = $el.open"><summary><i class="ph-bold ph-activity" aria-hidden="true"></i> Contexto clínico · solo lectura</summary><div class="space-y-4">@include('cuidados::livewire.cuidados.partials.pase-turno-contexto')</div></details>
            </div>
            <x-slot:footer><x-ui.action-button variant="secondary" wire:click="guardarBorrador" loading="guardarBorrador" icono="ph-floppy-disk">Guardar borrador</x-ui.action-button><x-ui.action-button tipo="submit" form="handoff-form" variant="primary" loading="confirmarEntrega" icono="ph-paper-plane-right">Confirmar entrega</x-ui.action-button></x-slot:footer>
        @endif
    </x-ui.modal-livewire>
    <x-ui.modal-livewire id="handoff-receive" wire:model="modalRevisar" title="Revisar y recibir pase" subtitle="Lee el resumen y los pendientes antes de confirmar la recepción." maxWidth="lg" closeMethod="cerrarModalRevisar" alpineClose="if (receiptDirty) discardReceipt = true; else $wire.cerrarModalRevisar()" class="rm-handoff__modal">
        @if($modalRevisar && $paseSeleccionado)
            <div x-show="discardReceipt" x-cloak class="rm-handoff__notice" role="alert"><div><strong>La observación aún no está guardada.</strong><div class="rm-handoff__tabs"><button type="button" class="rm-btn-secondary" @click="discardReceipt = false">Seguir revisando</button><button type="button" class="rm-btn-secondary" @click="receiptDirty = false; discardReceipt = false; $wire.cerrarModalRevisar()">Descartar y cerrar</button></div></div></div>
            <div class="space-y-4">@include('cuidados::livewire.cuidados.partials.pase-turno-detalle')
                @if($puedeRecibirSeleccionado)<form id="handoff-receive-form" wire:submit="confirmarRecepcion" @input="receiptDirty = true"><x-ui.field label="Observación de recepción" for="handoff-receive-note" error="observacionRecepcion" help="Opcional. La recepción quedará registrada a tu nombre con la hora del sistema."><textarea id="handoff-receive-note" class="rm-textarea" wire:model="observacionRecepcion" maxlength="3000" rows="3" aria-invalid="{{ $errors->has('observacionRecepcion') ? 'true' : 'false' }}"></textarea></x-ui.field></form>@endif
                @error('error_recepcion')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <x-slot:footer><x-ui.action-button variant="secondary" @click="if (receiptDirty) discardReceipt = true; else $wire.cerrarModalRevisar()">Cerrar</x-ui.action-button>@if($puedeRecibirSeleccionado)<x-ui.action-button tipo="submit" form="handoff-receive-form" variant="primary" icono="ph-check-circle" loading="confirmarRecepcion">Confirmar recepción</x-ui.action-button>@endif</x-slot:footer>
        @endif
    </x-ui.modal-livewire>
    <x-ui.modal-livewire id="handoff-detail" wire:model="modalVer" title="Detalle del pase de turno" subtitle="Consulta de solo lectura. El historial conserva sus registros." maxWidth="lg" closeMethod="cerrarModalVer" :showValidation="false" class="rm-handoff__modal">
        @if($modalVer && $paseSeleccionado)<div class="space-y-4">@include('cuidados::livewire.cuidados.partials.pase-turno-detalle')
            @if($mostrarAnulacion)<form id="handoff-annul-form" wire:submit="confirmarAnulacion" class="rm-handoff__notice" data-tone="warning"><div><strong>Anular este pase de turno</strong><p>El contenido y la recepción se conservarán en el historial. Esta acción no resuelve los pendientes comunicados.</p><x-ui.field label="Motivo de anulación" for="handoff-annul-note" error="motivoAnulacion" help="Opcional. Se conservará en la auditoría de la acción."><textarea id="handoff-annul-note" class="rm-textarea" wire:model="motivoAnulacion" maxlength="3000" rows="3"></textarea></x-ui.field></div></form>@endif
        </div>@endif
        <x-slot:footer><x-ui.action-button variant="secondary" wire:click="cerrarModalVer">Cerrar</x-ui.action-button>@if($modalVer && $puedeAnularSeleccionado)@if($mostrarAnulacion)<x-ui.action-button variant="secondary" wire:click="$set('mostrarAnulacion', false)">Volver a revisar</x-ui.action-button><x-ui.action-button tipo="submit" form="handoff-annul-form" variant="danger" loading="confirmarAnulacion">Confirmar anulación</x-ui.action-button>@else<x-ui.action-button variant="danger" icono="ph-prohibit" wire:click="solicitarAnulacion">Anular pase</x-ui.action-button>@endif @endif</x-slot:footer>
    </x-ui.modal-livewire>
</div>
