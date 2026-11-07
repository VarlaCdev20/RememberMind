<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Avisos administrativos" subtitle="Pendientes de documentación y cobertura del equipo" icon="ph-bell-ringing" />

    @if(count($avisosDocumentales))
        <x-ui.section-card title="Documentación laboral" icon="ph-files">
            <ul class="grid gap-3">
                @foreach($avisosDocumentales as $aviso)
                    <li>
                        <a class="rm-management-pending-item" href="{{ $aviso['url'] }}">
                            <i class="ph ph-file-text" aria-hidden="true"></i>
                            <span>
                                <strong>{{ $aviso['titulo'] }}</strong>
                                @if($aviso['persona'])<span>{{ $aviso['persona'] }}</span>@endif
                                <small>{{ $aviso['mensaje'] }} · Límite {{ $aviso['fecha'] }}</small>
                            </span>
                            <i class="ph ph-arrow-right" aria-hidden="true"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-ui.section-card>
    @else
        <x-ui.section-card title="Documentación laboral" icon="ph-files">
            <p class="rm-help">No hay avisos documentales en tu ámbito.</p>
        </x-ui.section-card>
    @endif

    <div class="rm-management-actions">
        @can('personal_institucional.ver')
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.personal.documentacion') }}"><i class="ph ph-folder-open" aria-hidden="true"></i>Ver documentación laboral</a>
        @endcan
        @can('jornadas.ver')
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index') }}"><i class="ph ph-calendar-check" aria-hidden="true"></i>Consultar cobertura</a>
        @endcan
    </div>
</div>
