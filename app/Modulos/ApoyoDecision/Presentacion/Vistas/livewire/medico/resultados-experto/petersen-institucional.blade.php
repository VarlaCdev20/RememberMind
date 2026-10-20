@php($petersen = $perfilInstrumental['petersen_institucional'])
@php($nombresDominios = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Función visuoespacial'])
@php($nombresRequisitos = ['C01' => 'Cambio corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Independencia instrumental relativa', 'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad'])
@php($patrones = ['PET-01' => 'Patrón amnésico de dominio único', 'PET-02' => 'Patrón amnésico multidominio', 'PET-03' => 'Patrón no amnésico de dominio único', 'PET-04' => 'Patrón no amnésico multidominio'])
<x-ui.section-card title="Valoración orientativa de Petersen" subtitle="Derivada de las valoraciones originales y sus soportes al corte consultado" icon="ph-brain">
    <p class="font-semibold">{{ $petersen['patron'] ? $patrones[$petersen['patron']] : 'La información disponible no permite establecer un patrón.' }}</p>
    <p class="text-sm mt-2">Revisión del resultado: pendiente, por Medicina y Psicología de forma separada.</p>
    <p class="text-sm mt-2">Esta consulta se calcula con las fuentes disponibles. La evaluación completa todavía no se ha conservado como resultado histórico.</p>
    @foreach($petersen['impedimentos'] as $motivo)
        <p class="text-sm mt-2">{{ ['ABSTENCION_CONTRADICCION' => 'Hay interpretaciones contradictorias que deben aclararse.', 'ABSTENCION_EVALUACION_DIFERENCIAL' => 'La revisión diferencial contiene un impedimento o no acredita el requisito.', 'ABSTENCION_INFORMACION_INSUFICIENTE' => 'Faltan valoraciones originales o condiciones de interpretación suficientes.', 'ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE' => 'Los patrones de Petersen aún no tienen una versión de conocimiento vigente. Medicina o Psicología pueden registrarla o revisar su gobierno.', 'ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA' => 'La regla que corresponde a este perfil está archivada o rechazada. Consulte su gobierno antes de emitir un patrón.', 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO' => 'Un requisito no está acreditado; no se establece un patrón DCL.'][$motivo] }}</p>
    @endforeach
    @if($petersen['gobierno']['disponible'] ?? false)
        <details class="mt-4"><summary class="cursor-pointer min-h-11">Reglas de Petersen aplicadas</summary>
            <p class="text-sm">Versión: {{ $petersen['gobierno']['codigo_version'] }}. Esta traza corresponde al cálculo actual; su conservación íntegra continúa pendiente.</p>
            @foreach($petersen['gobierno']['reglas'] as $regla)
                <p class="text-sm mt-2">{{ $patrones[$regla['codigo']] }}: {{ !$regla['habilitada'] ? ($regla['estado'] !== 'ACTIVO' ? 'Archivada' : 'Rechazada') : ($regla['cumple'] ? 'Condiciones cumplidas' : 'Condiciones sin cumplir') }}. Revisión: {{ $regla['estado_revision'] ?? 'Sin revisión registrada' }}.</p>
            @endforeach
        </details>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div><h2 class="font-semibold">Cinco dominios</h2><dl class="space-y-2 mt-2">@foreach($petersen['dominios'] as $codigo => $d)<div><dt class="text-sm font-semibold">{{ $nombresDominios[$codigo] }}</dt><dd class="text-sm">{{ ['A' => 'Alteración objetiva interpretada con soporte', 'P' => 'Rendimiento esperado con soporte', 'U' => 'Información original insuficiente', 'I' => 'No interpretable', 'X' => 'Interpretaciones contradictorias'][$d['estado']] }}</dd></div>@endforeach</dl></div>
        <div><h2 class="font-semibold">Requisitos</h2><dl class="space-y-2 mt-2">@foreach($nombresRequisitos as $codigo => $nombre)<div><dt class="text-sm font-semibold">{{ $nombre }}</dt><dd class="text-sm">{{ ['S' => 'Acreditado con soporte', 'N' => 'No acreditado con soporte explícito', 'U' => 'Información original insuficiente', 'X' => 'Información contradictoria'][$petersen['requisitos'][$codigo]['estado']] }}</dd></div>@endforeach</dl></div>
    </div>
    <details class="mt-4"><summary class="cursor-pointer min-h-11">Consultar fundamentos, soportes y exclusiones</summary>
        @foreach(array_merge($petersen['dominios'], $petersen['requisitos']) as $codigo => $elemento)
            <div class="border-t border-rm pt-3 mt-3"><h3 class="font-semibold">{{ $nombresDominios[$codigo] ?? $nombresRequisitos[$codigo] }}</h3>
                @foreach($elemento['valoraciones'] as $valoracion)<p class="text-sm mt-2">{{ $valoracion['justificacion'] }}</p><p class="text-sm break-words">Autor: {{ $valoracion['origen']['autor'] }} · {{ $valoracion['origen']['fecha'] }} · {{ $valoracion['origen']['registro'] }}</p>
                    <ul class="list-disc pl-5">@foreach($valoracion['soportes'] as $soporte)<li class="text-sm break-words">{{ $soporte['tabla'] }} · {{ $soporte['registro'] }} · {{ $soporte['fecha'] }}</li>@endforeach</ul>
                @endforeach
                @foreach($elemento['motivos'] as $motivo)<p class="text-sm break-words mt-1">{{ str_replace('_', ' ', $motivo) }}</p>@endforeach
                @if(isset($elemento['derivado_de']))<p class="text-sm mt-2">Se obtiene de los estados sustentados del perfil, sin introducir C02 manualmente.</p>@endif
            </div>
        @endforeach
        @foreach($petersen['valoraciones_excluidas'] as $excluida)<p class="text-sm break-words mt-2">{{ $excluida['elemento'] }} · {{ $excluida['origen']['registro'] }}: {{ implode('; ', $excluida['motivos']) }}.</p>@endforeach
        @foreach($petersen['adquisicion']['exclusiones'] as $excluida)<p class="text-sm break-words mt-2">{{ $excluida['elemento'] }} · {{ $excluida['valoracion'] }}: {{ str_replace('_', ' ', $excluida['motivo']) }}.</p>@endforeach
    </details>
</x-ui.section-card>
