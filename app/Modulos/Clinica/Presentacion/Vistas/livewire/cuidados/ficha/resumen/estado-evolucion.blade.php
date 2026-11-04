@php($estadoEvolucion = $this->estadoEvolucion)

<section class="mb-5 min-w-0 space-y-4 text-[var(--rm-text-primary)]" aria-labelledby="estado-evolucion-titulo">
    <div class="rm-clinical-card space-y-3">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-1">
                <h2 id="estado-evolucion-titulo" class="rm-clinical-card__title">
                    <i class="ph-bold ph-notebook text-[var(--rm-action-primary)]" aria-hidden="true"></i>
                    Estado y evolución del residente
                </h2>
                <p class="text-sm text-[var(--rm-text-primary)]">Resumen de los registros del período. No constituye un diagnóstico.</p>
                <a href="#evolucion-residente" class="inline-flex min-h-11 items-center text-sm font-semibold underline">Ver evolución entre períodos</a>
            </div>
            <div class="w-full space-y-1 sm:w-auto sm:min-w-48">
                <label for="periodo-estado-residente" class="block text-sm font-semibold">Período consultado</label>
                <select id="periodo-estado-residente" class="w-full min-h-11 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-input-bg)] text-sm"
                    wire:change="setPeriodoEstado($event.target.value)" wire:loading.attr="disabled" wire:target="setPeriodoEstado"
                    aria-describedby="periodo-estado-fechas periodo-estado-error" aria-invalid="{{ $errors->has('periodoEstado') ? 'true' : 'false' }}">
                    <option value="7d" @selected($periodoEstado === '7d')>Últimos 7 días</option>
                    <option value="30d" @selected($periodoEstado === '30d')>Últimos 30 días</option>
                    <option value="90d" @selected($periodoEstado === '90d')>Últimos 90 días</option>
                </select>
                <p id="periodo-estado-error" class="text-sm text-[var(--rm-danger-strong)]" role="alert">@error('periodoEstado'){{ $message }}@enderror</p>
            </div>
        </div>
        <p id="periodo-estado-fechas" class="text-sm text-[var(--rm-text-primary)]">Del {{ $estadoEvolucion['desde'] }} al {{ $estadoEvolucion['hasta'] }}.</p>
        <p class="text-sm leading-relaxed">
            Registros del período: <strong>{{ trans_choice('{1} :count incidente|[0,*] :count incidentes', $estadoEvolucion['total_incidentes']) }}</strong>
            ({{ $estadoEvolucion['total_caidas'] }} de tipo caída),
            <strong>{{ trans_choice('{1} :count control de signos vitales|[0,*] :count controles de signos vitales', $estadoEvolucion['total_signos']) }}</strong>,
            {{ trans_choice('{1} :count alerta|[0,*] :count alertas', $estadoEvolucion['total_alertas']) }} y {{ trans_choice('{1} :count nota clínica vigente|[0,*] :count notas clínicas vigentes', $estadoEvolucion['total_observaciones']) }}.
        </p>
        <p wire:loading wire:target="setPeriodoEstado" class="text-sm font-semibold" role="status">Actualizando el resumen del período…</p>
    </div>

    <div class="grid min-w-0 items-start gap-4 lg:grid-cols-2" wire:loading.class="opacity-60" wire:target="setPeriodoEstado">
        @foreach($estadoEvolucion['hallazgos'] as $hallazgo)
            <article class="rm-clinical-card min-w-0 space-y-3 break-words" wire:key="estado-{{ $periodoEstado }}-{{ $hallazgo['clave'] }}">
                <h3 class="rm-clinical-card__title">{{ $hallazgo['categoria'] }} <span class="text-sm font-normal">({{ $hallazgo['total'] }})</span></h3>
                @isset($hallazgo['clasificacion'])
                    @if($hallazgo['total'] > 0)
                        <p><span class="inline-flex rounded-lg border px-2 py-1 text-sm font-semibold {{ $hallazgo['clasificacion']['estado_badge'] }}">{{ $hallazgo['clasificacion']['label'] }}</span></p>
                    @endif
                @endisset
                <p class="text-sm font-semibold leading-relaxed">{{ $hallazgo['titulo'] }}</p>
                @if($hallazgo['explicacion'])
                    <p class="text-sm leading-relaxed text-[var(--rm-text-primary)]">{{ $hallazgo['explicacion'] }}</p>
                @endif
                @if($hallazgo['evidencias'])
                    <details class="border-t border-[var(--rm-border)]">
                        <summary class="min-h-11 cursor-pointer py-3 text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-focus-ring-color)]">¿Por qué se muestra este resultado?</summary>
                        <p class="mb-2 text-sm text-[var(--rm-text-primary)]">Evidencia del período. Despliegue cada registro para consultar sus datos originales.</p>
                        <div class="divide-y divide-[var(--rm-border)]">
                            @foreach($hallazgo['evidencias'] as $evidencia)
                                @include('clinica::livewire.cuidados.ficha.resumen.registro-original', ['contexto' => 'estado-'.$hallazgo['clave'], 'idOriginal' => 'estado-'.$hallazgo['clave'].'-'.$evidencia['ancla']])
                            @endforeach
                        </div>
                    </details>
                @endif
            </article>
        @endforeach
    </div>
</section>
