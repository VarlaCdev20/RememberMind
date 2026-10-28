                @if($datos['conocimiento_documental'] || $datos['registros_cognitivos'])
                    <details class="rm-expert-supplement" wire:key="contexto-complementario-{{ $criterio }}">
                        <summary><i class="ph ph-folder-open" aria-hidden="true"></i> Información complementaria del expediente <span class="text-xs text-rm-secondary">Conocimiento y registros previos</span></summary>
                        <div class="space-y-3 mt-3">
                            @if($datos['conocimiento_documental'])
                                <x-ui.section-card title="Conocimiento disponible" subtitle="{{ $datos['conocimiento_documental']['version'] }} · definición documental" icon="ph-books">
                                    <h3 class="font-semibold">{{ $datos['conocimiento_documental']['nombre'] }}</h3>
                                    <p class="text-sm leading-relaxed mt-2">{{ $datos['conocimiento_documental']['definicion'] }}</p>
                                    <p class="text-sm text-rm-secondary mt-3">{{ $informe['complemento_textos']['documental'] }}</p>
                                    <details class="mt-3"><summary class="cursor-pointer min-h-11 text-sm font-medium">Fuente y límites documentados</summary><p class="text-xs text-rm-secondary break-words mt-2">{{ $datos['conocimiento_documental']['procedencia'] }}</p></details>
                                </x-ui.section-card>
                            @endif
                            @if($datos['registros_cognitivos'])
                                <x-ui.section-card title="Registros cognitivos disponibles" subtitle="Últimos cinco controles vigentes del expediente" icon="ph-clipboard-text">
                                    <p class="text-sm text-rm-secondary mb-4">{{ $informe['complemento_textos']['observaciones'] }}</p>
                                    <div class="space-y-3">
                                        @foreach($datos['registros_cognitivos'] as $registro)
                                            <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 min-w-0">
                                                <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ $registro['fecha'] }} · {{ $registro['autor'] }}</summary>
                                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                                    @foreach($registro['mediciones'] as $medicion)
                                                        <div class="min-w-0"><dt class="text-sm text-rm-secondary">{{ $medicion['nombre'] }}</dt><dd class="text-sm font-semibold break-words">{{ $medicion['valor'] }}</dd></div>
                                                    @endforeach
                                                </dl>
                                                <p class="text-sm mt-4 whitespace-pre-line break-words"><strong>Observación registrada:</strong> {{ $registro['observacion'] ?: 'Sin observación registrada' }}</p>
                                            </details>
                                        @endforeach
                                    </div>
                                </x-ui.section-card>
                            @endif
                                    </div>
                    </details>
                @endif
