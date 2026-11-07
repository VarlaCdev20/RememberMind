<section class="rm-document-library__pending" aria-labelledby="pendientes-expediente-titulo">
    <h3 id="pendientes-expediente-titulo"><i class="ph-bold ph-note" aria-hidden="true"></i>Pendientes de entrega @if($pendientesCarpeta)<span class="rm-badge rm-badge-neutral">{{ $pendientesCarpeta->total() }}</span>@endif</h3>
    @if(!$disponible)
        <p class="rm-help">El seguimiento documental todavía no está disponible en esta base.</p>
    @elseif($pendientesCarpeta->isEmpty())
        <p class="rm-help">No hay solicitudes pendientes de entrega. Los archivos recibidos pueden seguir pendientes de revisión.</p>
    @else
        <p class="rm-help">Documentos solicitados que faltan entregar o corregir.</p>
        <ul class="rm-document-library__pending-list" aria-label="Documentos que faltan entregar">
            @foreach($pendientesCarpeta as $fila)
                @php($destinoPendiente = $fila['destinatario'])
                @php($estadoPendiente = $fila['estado'])
                <li class="rm-document-pending-note" data-pendiente-documental="{{ $destinoPendiente->getKey() }}" wire:key="pendiente-carpeta-{{ $destinoPendiente->getKey() }}">
                    <span class="rm-document-pending-note__label"><i class="ph-bold ph-push-pin" aria-hidden="true"></i>DOCUMENTO SOLICITADO</span>
                    <div><strong class="rm-document-pending-note__title">{{ $destinoPendiente->solicitud->nombre_documento }}</strong>
                        <span class="rm-badge rm-badge-warning">{{ $estadoPendiente['situacion'] === 'Corrección solicitada' ? 'Pendiente de corrección' : 'Pendiente de entrega' }}</span>
                        <p class="rm-document-pending-note__deadline">Fecha límite: <strong>{{ $estadoPendiente['limite']?->format('d/m/Y') ?? 'Por revisar' }}</strong>@if($estadoPendiente['concesion']) · Plazo prorrogado @endif</p>
                        @if($estadoPendiente['aviso'])<p class="rm-help">{{ $estadoPendiente['aviso'] }}</p>@endif
                        @if($estadoPendiente['situacion'] !== 'Sin entrega vigente')<p class="rm-help">{{ $estadoPendiente['situacion'] }}</p>@endif
                    </div>
                    <button type="button" class="rm-btn rm-btn-secondary" wire:click="verPendienteCarpeta(@js($destinoPendiente->getKey()))" wire:loading.attr="disabled">Ver seguimiento</button>
                </li>
            @endforeach
        </ul>
        @if($pendientesCarpeta->hasPages())<x-ui.paginacion :paginator="$pendientesCarpeta" mode="livewire" label="pendientes" :show-per-page="false" />@endif
    @endif
</section>
