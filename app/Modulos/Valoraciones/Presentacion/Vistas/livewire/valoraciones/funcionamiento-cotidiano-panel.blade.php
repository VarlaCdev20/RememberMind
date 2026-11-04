<div class="space-y-6 rm-captura-instrumental" x-data="{ sucio: false }" @input="sucio = true" @change="sucio = true" @funcionamiento-captura-cerrada.window="sucio = false" @beforeunload.window="if (sucio) { $event.preventDefault(); $event.returnValue = ''; }">
    <x-ui.section-card title="FUNCIONAMIENTO COTIDIANO" subtitle="Observaciones originales de actividades instrumentales de la vida diaria." icon="ph-person-simple-walk">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0"><h2 class="text-lg font-bold break-words">{{ $residente->nombre_completo }}</h2><p class="text-sm text-rm-secondary">{{ $residente->ubicacion_formateada }}</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ $retorno }}" class="rm-btn rm-btn-secondary">Volver a la ficha</a>
                @if($puedeCrear && !$abierto)<button type="button" wire:click="nuevo" class="rm-btn rm-btn-primary"><i class="ph-bold ph-plus" aria-hidden="true"></i> Registrar observación</button>@endif
            </div>
        </div>
        <p class="mt-3 text-sm text-rm-secondary">Registra una actividad y su contexto. La ayuda o la falta de oportunidad en la residencia no determinan por sí solas la capacidad de la persona. Este registro no genera una puntuación ni un diagnóstico.</p>
        @if($mensaje)<p class="mt-3 rm-help" role="status">{{ $mensaje }}</p>@endif
        @error('registro')<p class="rm-error mt-3" role="alert">{{ $message }}</p>@enderror
    </x-ui.section-card>

    @if($abierto)
        <x-ui.section-card :title="$anterior ? 'Rectificar una observación' : 'Nueva observación'" subtitle="Conserva lo observado o referido, con su fecha y procedencia." icon="ph-notebook">
            <p class="text-sm mb-4">Profesional que registra: <strong>{{ $profesional?->nombre_completo ?? trim(($profesional?->nombres ?? '').' '.($profesional?->apellido_paterno ?? '')) }}</strong>. La cuenta y la fecha de registro se conservan automáticamente.</p>
            @if($confirmarDescarte)
                <x-ui.form-section title="Captura sin guardar" description="Puedes volver a la captura o descartar los cambios. El historial registrado se conserva." :columns="1">
                    <div class="rm-captura__acciones"><button type="button" wire:click="volver" class="rm-btn rm-btn-secondary">Volver a la captura</button><button type="button" wire:click="descartar" class="rm-btn rm-btn-danger">Descartar captura</button></div>
                </x-ui.form-section>
            @elseif($revision)
                <div class="rm-captura__revision scroll-mt-28" tabindex="-1" x-init="$nextTick(() => { $el.focus({ preventScroll: true }); $el.scrollIntoView({ block: 'start' }); })">
                    <x-ui.form-section title="Revisar antes de guardar" description="Comprueba la actividad, la fecha y la información original. Los datos todavía no están guardados." :columns="1" icon="ph-checks">
                        <dl class="rm-captura__resumen">
                            <div><dt>Fecha y hora de la observación</dt><dd>{{ \Carbon\CarbonImmutable::parse($form['fecha_hora'])->format('d/m/Y H:i') }}</dd></div>
                            @foreach($campos as $campo => $nombre)<div><dt>{{ $nombre }}</dt><dd>{{ $opciones[$campo][$form[$campo]] }}</dd></div>@endforeach
                            <div><dt>Atención vinculada</dt><dd>{{ $atencionRevisada ? $atencionRevisada->fecha_hora?->format('d/m/Y H:i').' · '.str_replace('_', ' ', $atencionRevisada->tipo_atencion) : 'Sin atención vinculada' }}</dd></div>
                        </dl>
                        <div><h3 class="font-semibold">{{ $anterior ? 'Observación y motivo de la rectificación' : 'Observaciones y contexto' }}</h3><p class="whitespace-pre-line break-words">{{ ($form['observacion'] ?? '') !== '' ? $form['observacion'] : 'Sin observaciones adicionales' }}</p></div>
                        @if($anterior)<p class="rm-help">Se añadirá una rectificación vinculada. El registro original y su autor se conservan.</p>@endif
                    </x-ui.form-section>
                    <div class="rm-captura__acciones mt-4"><button type="button" wire:click="volver" class="rm-btn rm-btn-secondary">Volver a editar</button><button type="button" wire:click="guardar" class="rm-btn rm-btn-primary" wire:loading.attr="disabled" wire:target="guardar"><span wire:loading.remove wire:target="guardar">Confirmar y registrar</span><span wire:loading wire:target="guardar">Registrando…</span></button></div>
                </div>
            @else
                <form wire:submit="revisar" class="rm-captura__formulario">
                    @foreach([
                        ['titulo' => 'Actividad y procedencia', 'ayuda' => 'Identifica la actividad concreta, cómo se obtuvo la información y cuándo ocurrió.', 'campos' => ['actividad', 'fuente']],
                        ['titulo' => 'Oportunidad y desempeño', 'ayuda' => 'Distingue lo que pudo realizar de lo que no tuvo oportunidad de hacer. Selecciona cada respuesta explícitamente.', 'campos' => ['oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda']],
                        ['titulo' => 'Funcionamiento previo y contexto', 'ayuda' => 'Conserva el nivel previo y lo que limita la comparación. Si no se conoce, indícalo.', 'campos' => ['nivel_previo']],
                    ] as $grupo)
                        <x-ui.form-section :title="$grupo['titulo']" :description="$grupo['ayuda']" :step="$loop->iteration" icon="ph-notebook">
                            @foreach($grupo['campos'] as $campo)
                                <x-ui.field :for="'cotidiano-'.$campo" :label="$campos[$campo]" :error="'form.'.$campo" required>
                                    <x-ui.selector :id="'cotidiano-'.$campo" :label="$campos[$campo]" wire:model.live="form.{{ $campo }}" :aria-invalid="$errors->has('form.'.$campo) ? 'true' : 'false'" :aria-describedby="$errors->has('form.'.$campo) ? 'cotidiano-'.$campo.'-error' : null">
                                        <option value="">Selecciona una respuesta</option>
                                        @foreach($opciones[$campo] as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach
                                    </x-ui.selector>
                                </x-ui.field>
                            @endforeach
                            @if($loop->first)
                                <x-ui.field for="cotidiano-fecha" label="Fecha y hora de la observación o relato" error="form.fecha_hora" required :help="'Zona horaria: '.config('app.timezone')">
                                    <input id="cotidiano-fecha" type="datetime-local" wire:model="form.fecha_hora" max="{{ now()->format('Y-m-d\TH:i') }}" class="rm-input" required aria-describedby="cotidiano-fecha-help{{ $errors->has('form.fecha_hora') ? ' cotidiano-fecha-error' : '' }}" aria-invalid="{{ $errors->has('form.fecha_hora') ? 'true' : 'false' }}">
                                </x-ui.field>
                                <x-ui.field for="cotidiano-atencion" label="Atención vinculada, si corresponde" error="form.cod_atencion" help="Solo se muestran atenciones de este residente. No se crea una atención al guardar.">
                                    <x-ui.selector id="cotidiano-atencion" label="Atención vinculada" wire:model="form.cod_atencion" :aria-describedby="$errors->has('form.cod_atencion') ? 'cotidiano-atencion-help cotidiano-atencion-error' : 'cotidiano-atencion-help'" :aria-invalid="$errors->has('form.cod_atencion') ? 'true' : 'false'">
                                        <option value="">Sin atención vinculada</option>
                                        @foreach($atenciones as $atencion)<option value="{{ $atencion->getKey() }}">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }} · {{ str_replace('_', ' ', $atencion->tipo_atencion) }}</option>@endforeach
                                    </x-ui.selector>
                                </x-ui.field>
                            @endif
                            @if($loop->last)
                                <div class="sm:col-span-2">
                                    <x-ui.field for="cotidiano-notas" :label="$anterior ? 'Observación y motivo de la rectificación' : 'Observaciones y contexto'" error="form.observacion" :required="$anterior !== null" help="Describe ejemplos, quién aporta el relato y las limitaciones conocidas. Hasta 4000 caracteres.">
                                        <textarea id="cotidiano-notas" wire:model="form.observacion" rows="4" maxlength="4000" class="rm-textarea" aria-describedby="cotidiano-notas-help{{ $errors->has('form.observacion') ? ' cotidiano-notas-error' : '' }}" aria-invalid="{{ $errors->has('form.observacion') ? 'true' : 'false' }}"></textarea>
                                    </x-ui.field>
                                </div>
                            @endif
                        </x-ui.form-section>
                    @endforeach
                    <div class="rm-captura__acciones"><button type="button" wire:click="cerrar" class="rm-btn rm-btn-secondary">Cerrar captura</button><button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Revisar observación <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button></div>
                </form>
            @endif
        </x-ui.section-card>
    @endif

    <x-ui.section-card title="Historial de funcionamiento cotidiano" subtitle="Registros originales y rectificaciones, con su fecha y profesional." icon="ph-clock-counter-clockwise">
        <div class="space-y-4">
            @forelse($registros as $registro)
                <article class="rm-card p-4 min-w-0" wire:key="cotidiano-registro-{{ $registro->getKey() }}">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-bold">{{ $opciones['actividad'][$registro->actividad] ?? 'Actividad no identificada' }}</h3><p class="text-sm text-rm-secondary">{{ $registro->fecha_hora?->format('d/m/Y H:i') }} · {{ trim(($registro->personal?->nombres ?? '').' '.($registro->personal?->apellido_paterno ?? '')) ?: 'Autor no disponible' }}</p></div><span class="rm-badge">{{ $registro->sucesora_exists ? 'Original rectificado' : ($registro->estado === 'RECTIFICACION' ? 'Rectificación vigente' : 'Registro vigente') }}</span></div>
                    <dl class="rm-captura__resumen mt-3">@foreach($campos as $campo => $etiqueta)@if($campo !== 'actividad')<div><dt>{{ $etiqueta }}</dt><dd>{{ $opciones[$campo][$registro->$campo] ?? 'Dato no identificado' }}</dd></div>@endif @endforeach</dl>
                    @if($registro->observacion !== null)<p class="mt-3 text-sm whitespace-pre-line break-words">{{ $registro->observacion }}</p>@endif
                    @if($registro->sucesora_exists)<p class="rm-help mt-3">Se conserva como antecedente; tiene una rectificación posterior.</p>@elseif($puedeRectificar && !$abierto)<button type="button" wire:click="rectificar('{{ $registro->getKey() }}')" class="rm-btn rm-btn-secondary mt-3">Rectificar observación<span class="sr-only"> de {{ $opciones['actividad'][$registro->actividad] ?? 'esta actividad' }} del {{ $registro->fecha_hora?->format('d/m/Y H:i') }}</span></button>@endif
                </article>
            @empty
                <p class="text-sm text-rm-secondary py-4" role="status">No hay observaciones registradas. La ausencia de registros no describe el nivel de autonomía.</p>
            @endforelse
        </div>
        @if($registros->hasPages())<div class="mt-4">{{ $registros->links() }}</div>@endif
    </x-ui.section-card>
</div>
