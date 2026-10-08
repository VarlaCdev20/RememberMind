@if($adulto)
    <x-ui.section-card :title="trim($adulto->nombres.' '.$adulto->ap_paterno.' '.$adulto->ap_materno)" subtitle="Sueño · Consulta del residente" icon="ph-moon-stars" class="rm-clinical-workspace__context">
        <div class="rm-clinical-workspace__actions">
            <p class="rm-clinical-form__note">Consulta de solo lectura. El sueño se documenta dentro del seguimiento diario completo.</p>
            <span class="rm-badge-pill">Solo consulta</span>
            <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->cod_residente) }}">Consultar ficha clínica</a>
        </div>
    </x-ui.section-card>
    <x-ui.section-card title="Historial reciente de sueño" subtitle="Hasta 12 registros guardados, del más reciente al más antiguo." icon="ph-clock-counter-clockwise">
        @if($registrosCuidado->isNotEmpty())
            <ol class="rm-clinical-workspace__history">
                @foreach($registrosCuidado as $registro)
                    <li class="rm-clinical-workspace__record">
                        <div class="rm-clinical-workspace__record-heading"><time>{{ $registro->fecha?->format('d/m/Y') ?? 'Fecha no registrada' }}</time><span class="rm-badge-pill">{{ $registro->estado ? mb_convert_case(str_replace('_', ' ', $registro->estado), MB_CASE_TITLE, 'UTF-8') : 'Estado no registrado' }}</span></div>
                        <h3 class="rm-section-title">{{ $registro->calidad ? mb_convert_case(str_replace('_', ' ', $registro->calidad), MB_CASE_TITLE, 'UTF-8') : 'Calidad no registrada' }}</h3>
                        <dl class="rm-clinical-workspace__details">
                            <div><dt>Horas de sueño</dt><dd>{{ $registro->horas_sueno !== null ? rtrim(rtrim(number_format((float) $registro->horas_sueno, 2, '.', ''), '0'), '.').' h' : 'No registradas' }}</dd></div>
                            <div><dt>Despertares</dt><dd>{{ $registro->despertares ?? 'No registrados' }}</dd></div>
                            @foreach(['insomnio' => 'Insomnio', 'somnolencia_diurna' => 'Somnolencia diurna', 'agitacion_nocturna' => 'Agitación nocturna'] as $campo => $etiqueta)
                                <div><dt>{{ $etiqueta }}</dt><dd>{{ $registro->$campo === null ? 'No registrado' : ($registro->$campo ? 'Sí' : 'No') }}</dd></div>
                            @endforeach
                        </dl>
                        @if(filled($registro->observacion))<p class="rm-clinical-form__note">{{ $registro->observacion }}</p>@endif
                    </li>
                @endforeach
            </ol>
        @else
            <x-ui.empty-state icon="ph-moon-stars" title="Sin registros de sueño" description="Aún no hay observaciones de sueño guardadas para este residente. Este acceso permite consultar el historial, sin registrar ni modificar datos." />
        @endif
    </x-ui.section-card>
@else
    <x-ui.empty-state icon="ph-user-circle" title="Selecciona un residente" description="Elige un residente de tu alcance para consultar sus registros de sueño." />
@endif
