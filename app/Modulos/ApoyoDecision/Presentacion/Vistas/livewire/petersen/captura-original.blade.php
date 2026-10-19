<div class="space-y-6 text-rm-primary" x-data="{ sucio: false }" x-on:input="sucio = true" x-on:change="sucio = true"
    x-on:petersen-captura-cerrada.window="sucio = false"
    x-on:beforeunload.window="if (sucio) { $event.preventDefault(); $event.returnValue = ''; }">
    @php($elementos = \App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel::ELEMENTOS)
    @php($tiposTodos = \App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel::TIPOS)
    <x-ui.section-card title="Valoraciones originales para Petersen" subtitle="Captura de juicios clínicos y sus fuentes; la clasificación se obtiene después" icon="ph-notebook">
        <p class="font-semibold">{{ $residente->nombre_completo }}</p>
        @if($profesional)<p class="text-sm">Profesional de la sesión: {{ $profesional->nombres }} {{ $profesional->apellido_paterno }} {{ $profesional->apellido_materno }}.</p>@endif
        <p class="text-sm mt-2">Registre lo que valoró y sus soportes originales. El estado del dominio es una interpretación clínica documentada; el sistema calcula C02 y el patrón. La información que no esté acreditada permanece pendiente.</p>
        @if($mensaje)<p role="status" class="mt-3">{{ $mensaje }}</p>@endif
        @if($puedeCrear && $solicitud === null)
            <div class="flex flex-wrap gap-2 mt-4">@foreach($elementos as $codigo => $nombre)
                <button type="button" wire:click="nuevo('{{ $codigo }}')" class="rm-btn rm-btn-secondary">Registrar {{ $nombre }}</button>
            @endforeach</div>
        @endif
    </x-ui.section-card>
    @if($solicitud !== null)
        <x-ui.section-card :title="($tipoRegistro === 'ANULACION' ? 'Anular: ' : ($tipoRegistro === 'RECTIFICACION' ? 'Rectificar: ' : 'Registrar: ')).$elementos[$form['elemento']]" icon="ph-pencil-line">
            <form wire:submit="guardar" wire:confirm="¿Registrar esta valoración original y sus soportes? El registro se conservará en el historial." class="space-y-4">
                @if($errors->any())<div role="alert" class="text-sm"><p>Revise la información antes de guardar:</p><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                @if($anterior)<p>Registro anterior: {{ $anterior }}. La corrección conservará ese original.</p>@endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label for="pet-fecha" class="block text-sm font-semibold">Fecha y hora del acto original</label><input id="pet-fecha" type="datetime-local" step="0.000001" wire:model="form.fecha_hora" class="rm-input w-full" @readonly($tipoRegistro === 'ANULACION') required><p class="text-sm">Indique cuándo se realizó la valoración.</p></div>
                    @if($tipoRegistro !== 'ANULACION')<div><label for="pet-estado" class="block text-sm font-semibold">Conclusión original del elemento</label><select id="pet-estado" wire:model="form.estado_original" class="rm-select w-full !leading-normal !min-h-11" required><option value="">Seleccione</option>
                        @if(str_starts_with($form['elemento'], 'COG-'))<option value="A">Alteración objetiva interpretada del dominio</option><option value="P">Rendimiento esperado acreditado del dominio</option><option value="I">No interpretable</option>
                        @else<option value="S">Acreditado con soporte explícito</option><option value="N">No acreditado con soporte explícito</option>@endif
                    </select></div>@endif
                </div>
                @if($anterior && $tipoRegistro === 'RECTIFICACION')<div><label for="pet-error-fecha" class="block text-sm font-semibold">Error de fecha original, si corresponde</label><input id="pet-error-fecha" wire:model="form.error_fecha_original" class="rm-input w-full"><p class="text-sm">Solo si corrige la fecha: explique el error identificado en el original.</p></div>@endif
                <div><label for="pet-justificacion" class="block text-sm font-semibold">{{ $tipoRegistro === 'ANULACION' ? 'Motivo de anulación' : 'Fundamento de la valoración original' }}</label><textarea id="pet-justificacion" wire:model="form.justificacion" class="rm-textarea w-full" rows="3" required></textarea></div>
                <details class="rounded-xl border border-rm p-4">
                    <summary class="font-semibold cursor-pointer">Referencia del acto original, si existe</summary>
                    <p class="text-sm mt-2">Vincule únicamente una atención o un documento original de este residente. El documento y su página o sección se indican juntos; no se crea una atención para completar estos campos.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div><label for="pet-atencion" class="block text-sm font-semibold">Código de atención, opcional</label><input id="pet-atencion" wire:model="form.cod_atencion" maxlength="20" class="rm-input w-full"></div>
                        <div><label for="pet-documento" class="block text-sm font-semibold">Código del documento clínico original</label><input id="pet-documento" wire:model="form.cod_documento_clinico" maxlength="20" class="rm-input w-full"></div>
                        <div class="md:col-span-2"><label for="pet-localizador" class="block text-sm font-semibold">Página o sección del documento</label><input id="pet-localizador" wire:model="form.localizador_fuente" maxlength="120" class="rm-input w-full"></div>
                    </div>
                </details>
                @if($tipoRegistro !== 'ANULACION')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@foreach($comprobaciones as $campo)
                        <div><label for="pet-{{ $campo }}" class="block text-sm font-semibold">{{ ucfirst(str_replace('_', ' ', $campo)) }}</label><select id="pet-{{ $campo }}" wire:model="form.{{ $campo }}" class="rm-select w-full !leading-normal !min-h-11"><option value="">Sin información acreditada</option><option value="1">Sí, documentado</option><option value="0">No, documentado</option></select></div>
                    @endforeach</div>
                    <fieldset class="rounded-xl border border-rm p-4 min-w-0"><legend class="font-semibold px-2">Soportes originales</legend>
                        <p class="text-sm">Seleccione registros del residente. Una referencia compartida no se convierte en corroboración independiente. En C05, identifique también las cinco valoraciones de dominio revisadas.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <div><label for="pet-tipo-fuente" class="block text-sm">Tipo de soporte</label><select id="pet-tipo-fuente" wire:model.live="tipoSoporte" class="rm-select w-full !leading-normal !min-h-11"><option value="">Seleccione</option>@foreach($tipos as $codigo => $nombre)<option value="{{ $codigo }}">{{ $nombre }}</option>@endforeach</select></div>
                            <div><label for="pet-fuente" class="block text-sm">Fuente original autorizada</label><select id="pet-fuente" wire:model.live="campoFuente" class="rm-select w-full !leading-normal !min-h-11"><option value="">Seleccione</option>@foreach($fuentes as $campo)<option value="{{ $campo }}">{{ str_replace(['cod_', '_'], ['', ' '], $campo) }}</option>@endforeach</select></div>
                            <div><label for="pet-buscar" class="block text-sm">Buscar por código de registro</label><input id="pet-buscar" wire:model.live.debounce.300ms="busquedaFuente" class="rm-input w-full"><p class="text-sm">Se muestran hasta 50 coincidencias.</p></div>
                            <div><label for="pet-registro" class="block text-sm">Registro original</label><select id="pet-registro" wire:model="registroFuente" class="rm-select w-full !leading-normal !min-h-11"><option value="">Seleccione</option>@foreach($opciones as $opcion)<option value="{{ $opcion['id'] }}">{{ $opcion['etiqueta'] }}</option>@endforeach</select></div>
                        </div>
                        <button type="button" wire:click="agregarSoporte" class="rm-btn rm-btn-secondary mt-3">Vincular soporte</button>
                        <ul class="space-y-2 mt-3">@foreach($form['soportes'] as $orden => $soporte)<li class="flex flex-wrap items-center gap-2 break-all" wire:key="pet-soporte-{{ $orden }}"><span>{{ $tiposTodos[$soporte['tipo_soporte']] ?? $soporte['tipo_soporte'] }} · {{ implode(' · ', array_values(array_diff_key($soporte, ['tipo_soporte' => true]))) }}</span><button type="button" wire:click="quitarSoporte({{ $orden }})" class="rm-btn rm-btn-ghost" aria-label="Quitar soporte {{ $orden + 1 }}">Quitar</button></li>@endforeach</ul>
                    </fieldset>
                @endif
                <div class="flex flex-wrap gap-3"><button type="submit" wire:loading.attr="disabled" class="rm-btn rm-btn-primary">Guardar valoración original</button><button type="button" wire:click="cancelar" wire:confirm="¿Descartar los cambios sin guardar?" class="rm-btn rm-btn-secondary">Cancelar captura</button></div>
                <p wire:loading role="status" class="text-sm">Validando y registrando…</p>
            </form>
        </x-ui.section-card>
    @endif
    <x-ui.section-card title="Historial de valoraciones originales" icon="ph-clock-counter-clockwise">
        <div class="space-y-3">@forelse($registros as $registro)
            <article class="rounded-xl border border-rm p-4 min-w-0" wire:key="pet-original-{{ $registro->getKey() }}"><h2 class="font-semibold">{{ $elementos[$registro->elemento] }} · {{ $registro->tipo_registro }}</h2><p class="text-sm break-words">{{ $registro->getKey() }} · {{ $registro->fecha_hora->format('d/m/Y H:i:s') }} · {{ $registro->personal?->nombres }} {{ $registro->personal?->apellido_paterno }}</p><p class="text-sm mt-2">{{ $registro->justificacion }}</p><p class="text-sm">Registrado el {{ $registro->registrado_en->format('d/m/Y H:i:s') }}. Estado original: {{ $registro->estado_original ?? 'Anulado mediante este registro' }}.</p>
                @if($solicitud === null && $registro->tipo_registro !== 'ANULACION')@can('rectificarOriginal', $registro)<div class="flex flex-wrap gap-2 mt-2"><button type="button" wire:click="corregir('{{ $registro->getKey() }}')" class="rm-btn rm-btn-secondary">Rectificar este registro</button><button type="button" wire:click="corregir('{{ $registro->getKey() }}', true)" class="rm-btn rm-btn-secondary">Registrar anulación</button></div>@endcan @endif
            </article>
        @empty<p>No hay valoraciones originales de Petersen registradas para este residente.</p>@endforelse</div>
        <div class="mt-4">{{ $registros->links() }}</div>
    </x-ui.section-card>
</div>
