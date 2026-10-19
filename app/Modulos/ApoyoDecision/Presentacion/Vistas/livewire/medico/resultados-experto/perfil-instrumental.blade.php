@if(isset($perfilInstrumental['petersen_institucional']))@include('apoyodecision::livewire.medico.resultados-experto.petersen-institucional')@endif
<x-ui.section-card title="Observaciones y mediciones por dominio" subtitle="Cinco dominios y diecinueve componentes de la red cognitiva" icon="ph-tree-structure">
    <p class="text-sm text-rm-secondary">Cada observación conserva su fuente, fecha y autor. Los hallazgos instrumentales requieren interpretación específica, atribución de tarea y contexto suficiente.</p>
    <div class="space-y-3 mt-4">
        @foreach($perfilInstrumental['dominios'] as $codigo => $dominio)
            <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 min-w-0" wire:key="perfil-instrumental-{{ $codigo }}">
                <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ $dominio['nombre'] }} · {{ count($dominio['componentes']) }} componentes</summary>
                @if(isset($dominio['integracion']['estado_institucional']))
                    <p class="text-sm mt-3">{{ ['SIN_ANTECEDENTES_INSTRUMENTALES_SUFICIENTES' => 'No hay antecedentes instrumentales suficientes para emitir un hallazgo.', 'EVIDENCIA_CONTRADICTORIA' => 'Hay evidencia contradictoria; se suspende la conclusión.', 'HALLAZGOS_CANDIDATOS_CON_SOPORTE' => 'Hay hallazgos candidatos con reglas y evidencias de soporte.'][$dominio['integracion']['estado_institucional']] }}</p>
                    @if(isset($dominio['integracion']['compuerta_institucional']))<p class="text-sm mt-2">Evaluabilidad de Memoria: {{ $dominio['integracion']['compuerta_institucional']['estado'] }}.</p>@endif
                    @if(isset($dominio['integracion']['resultado_institucional']['codigo']))<p class="text-sm mt-2 font-semibold">Resultado de Memoria: {{ $dominio['integracion']['resultado_institucional']['codigo'] }}.</p>@endif
                    @foreach($dominio['integracion']['antecedentes_descartados_por_contexto'] ?? [] as $excluido)<p class="text-sm mt-2 break-words">Aplicación {{ $excluido['aplicacion'] }}: no contribuye al resultado instrumental porque falta su contexto de tarea.</p>@endforeach
                    @foreach($dominio['integracion']['conclusiones_candidatas'] as $hallazgo)
                        <p class="text-sm mt-2 break-words">Hallazgo candidato: {{ $hallazgo['codigo'] }} · {{ count($hallazgo['evidencias']) }} evidencias · {{ count($hallazgo['soportes_efectivos']) }} grupos de soporte. El conteo no acredita independencia clínica.</p>
                    @endforeach
                    <details class="mt-3"><summary class="cursor-pointer min-h-11 text-sm">Reglas, antecedentes y motivos de no emisión</summary>
                        @foreach($dominio['integracion']['traza']['reglas'] as $regla)
                            <div class="mt-3 text-sm break-words"><strong>{{ $regla['codigo_regla'] }}</strong> · {{ $regla['estado'] === 'CUMPLE' ? 'Antecedentes satisfechos' : 'Antecedentes insuficientes' }}
                                <ul class="list-disc pl-5">@foreach($regla['condiciones'] as $condicion)<li>{{ $condicion['variable_codigo'] }}: {{ $condicion['estado'] === 'CUMPLE' ? 'Con soporte coincidente' : 'Sin soporte coincidente en una misma aplicación' }} · {{ count($condicion['soportes']) }} evidencias.</li>@endforeach</ul>
                                @foreach($dominio['integracion']['traza']['rutas'] as $ruta)
                                    @if($ruta['regla'] === $regla['regla'])
                                        <details class="mt-2"><summary class="cursor-pointer min-h-11">Evidencia {{ $ruta['evidencia'] }} · Aplicación {{ $ruta['registro'] }}</summary>
                                            <p>Autor original: {{ $ruta['personal'] }} · Fecha: {{ $ruta['fecha_fuente'] }}.</p>
                                            <p>Pregunta: {{ $ruta['procedencia_instrumental']['cod_pregunta'] }} · Respuesta: {{ $ruta['procedencia_instrumental']['cod_respuesta'] ?? 'Sin respuesta' }}.</p>
                                            <p>Literal original: {{ is_bool($ruta['valor_original']) ? ($ruta['valor_original'] ? 'true' : 'false') : ($ruta['valor_original'] ?? 'Sin dato') }}.</p>
                                            @if(is_array($ruta['procedencia_instrumental']['perfil_interpretativo'] ?? null))
                                                <p>Método: {{ $ruta['procedencia_instrumental']['perfil_interpretativo']['metodo'] }} · Perfil: {{ $ruta['procedencia_instrumental']['perfil_interpretativo']['codigo'] }} · Versión: {{ $ruta['procedencia_instrumental']['perfil_interpretativo']['version'] }}.</p>
                                                <p>Fuente del método: {{ $ruta['procedencia_instrumental']['perfil_interpretativo']['fuente'] }}.</p>
                                                @foreach($ruta['procedencia_instrumental']['mediciones_interpretacion'] as $nombre => $medida)<p>Medida {{ $nombre }}: {{ $medida['valor_original'] }} · Pregunta {{ $medida['cod_pregunta'] }} · Respuesta {{ $medida['cod_respuesta'] }}.</p>@endforeach
                                            @endif
                                        </details>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                        <p class="text-sm mt-3">Versión: {{ $perfilInstrumental['inferencia_declarativa']['resultado']['version'] }}. La revisión profesional se registra por separado.</p>
                    </details>
                @else
                    <p class="text-sm text-rm-secondary mt-3">No hay una versión institucional vinculada para integrar este dominio.</p>
                @endif
                @foreach($dominio['componentes'] as $id => $componente)
                    <div class="border-t border-rm mt-4 pt-3 min-w-0">
                        <h3 class="font-semibold break-words">{{ $componente['nombre'] }}</h3>
                        @forelse($componente['observaciones'] ?? [] as $observacion)
                            <div class="mt-3 text-sm break-words">
                                <p class="font-semibold">{{ ['DIFICULTAD_DOCUMENTADA' => 'Dificultad documentada', 'SIN_DIFICULTAD_DOCUMENTADA' => 'No se documentó dificultad en este registro'][$observacion['codigo']] ?? 'Observación sin correspondencia utilizable' }}</p>
                                <p>{{ $observacion['fecha'] }} · Autor: {{ $observacion['autor'] }} · Aplicación: {{ $observacion['aplicacion'] }}</p>
                                <details class="mt-1"><summary class="cursor-pointer min-h-11">Consultar procedencia original</summary>
                                    <p>Pregunta: {{ $observacion['pregunta'] }} · Respuesta: {{ $observacion['respuesta'] ?? 'Sin respuesta' }} · Opción: {{ $observacion['opcion'] ?? 'Sin opción' }}.</p>
                                    <p>Literal conservado: {{ $observacion['literal_original'] ?? 'Sin dato' }} · {{ $observacion['representacion'] }} · {{ $observacion['admisibilidad'] }}.</p>
                                    @foreach($observacion['motivos'] as $motivo)<p>{{ $motivo }}</p>@endforeach
                                    <p>Esta observación no equivale a una puntuación ni satisface por sí sola una regla instrumental.</p>
                                </details>
                            </div>
                        @empty
                            <p class="text-sm text-rm-secondary mt-2">Sin observación original vinculada a este componente en el corte consultado.</p>
                        @endforelse
                        <p class="text-sm mt-2">{{ $componente['estado_medicion'] === 'MEDICIONES_DISPONIBLES' ? 'Hay mediciones normalizadas disponibles.' : 'Sin medición normalizada vinculada a este componente.' }}</p>
                        @foreach($componente['mediciones'] as $medicion)
                            <p class="text-sm break-words mt-2">Aplicación {{ $medicion['aplicacion'] }} · {{ $medicion['fecha'] }} · Método {{ $medicion['metodo'] }}</p>
                        @endforeach
                        <details class="mt-2"><summary class="cursor-pointer min-h-11 text-sm">Definición y límites del componente</summary>
                            <p class="text-sm break-words">{{ $componente['concepto']['definicion'] }}</p>
                            <p class="text-sm text-rm-secondary break-words mt-2">{{ $componente['concepto']['limites'] }}</p>
                        </details>
                        <details class="mt-2"><summary class="cursor-pointer min-h-11 text-sm">Campos y método de evaluación</summary>
                            <p class="text-sm mt-2 break-words">Referencia instrumental: {{ $componente['captura_requerida']['instrumento_referencia'] }}.</p>
                            <p class="text-sm mt-2">{{ $componente['captura_requerida']['metodos_disponibles'] ? 'Hay un método de cálculo disponible cuando la aplicación cumple sus condiciones.' : 'Campos documentados; falta un método de normalización compatible.' }}</p>
                            <dl class="mt-3 space-y-2">
                                @foreach($componente['captura_requerida']['campos'] as $campo => $descripcion)
                                    <div class="min-w-0"><dt class="text-sm font-semibold break-words">{{ str_replace('_', ' ', $campo) }}</dt><dd class="text-sm break-words">{{ $descripcion }}</dd></div>
                                @endforeach
                            </dl>
                            <p class="text-sm text-rm-secondary mt-3 break-words">{{ $componente['captura_requerida']['limite'] }}</p>
                            <a class="text-sm underline inline-flex items-center min-h-11 break-all" href="{{ $componente['captura_requerida']['fuente'] }}" target="_blank" rel="noopener noreferrer">Consultar la fuente del método (abre otra pestaña)</a>
                        </details>
                    </div>
                @endforeach
            </details>
        @endforeach
    </div>
</x-ui.section-card>
