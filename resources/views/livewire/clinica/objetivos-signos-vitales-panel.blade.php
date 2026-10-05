<main class="rm-clinical-goals">
    <x-ui.page-header
    title="Objetivos de signos vitales"
    :subtitle="'Define rangos para ' . trim($residente->nombres.' '.$residente->apellido_paterno.' '.$residente->apellido_materno) . '. Cada cambio conserva su versión anterior.'"
    overline="Medicina · Objetivos Individuales"
    icon="ph-heartbeat"
    :date="now()">
    <a class="rm-btn rm-btn-secondary" href="{{ route('admin.medico.residentes') }}">
        <i class="ph-bold ph-arrow-left text-base" aria-hidden="true"></i>
        <span>Volver a residentes</span>
    </a>
</x-ui.page-header>

    <section class="rm-clinical-goals__card" aria-labelledby="objetivos-vigentes">
        <h2 id="objetivos-vigentes">Objetivos vigentes</h2>
        @forelse($objetivos as $objetivo)
            <div class="rm-clinical-goals__row" wire:key="objetivo-{{ $objetivo->cod_objetivo_signo }}">
                <div><strong>{{ $parametros[$objetivo->parametro] }}</strong><span>Definido {{ $objetivo->vigente_desde->format('d/m/Y H:i') }}</span></div>
                <div class="rm-clinical-goals__values">
                    <span>Objetivo: {{ $objetivo->min_objetivo ?? '—' }} a {{ $objetivo->max_objetivo ?? '—' }}</span>
                    <span>Crítico: {{ $objetivo->min_critico ?? '—' }} / {{ $objetivo->max_critico ?? '—' }}</span>
                </div>
            </div>
        @empty
            <p class="rm-clinical-goals__empty">Aún no hay objetivos individuales. Se aplican las reglas generales aprobadas; la saturación requiere valoración contextual.</p>
        @endforelse
    </section>

    @if($this->puedeGestionar())
        <section class="rm-clinical-goals__card" aria-labelledby="nuevo-objetivo">
            <h2 id="nuevo-objetivo">Definir o sustituir un objetivo</h2>
            <p class="rm-clinical-goals__hint">Los límites críticos pueden generar alertas automáticas al confirmar una lectura. Registra el motivo clínico del cambio.</p>
            <form wire:submit="guardar" class="rm-clinical-goals__form">
                <div class="rm-clinical-goals__field rm-clinical-goals__field--wide">
                    <label for="objetivo-parametro">Variable clínica</label>
                    <select id="objetivo-parametro" wire:model="parametro" aria-invalid="@error('parametro') true @else false @enderror">
                        @foreach($parametros as $codigo => $nombre)
                            <option value="{{ $codigo }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    @error('parametro') <span role="alert">{{ $message }}</span> @enderror
                </div>
                @foreach([['minObjetivo','min_objetivo','Mínimo objetivo'], ['maxObjetivo','max_objetivo','Máximo objetivo'], ['minCritico','min_critico','Mínimo crítico'], ['maxCritico','max_critico','Máximo crítico']] as [$propiedad,$error,$etiqueta])
                    <div class="rm-clinical-goals__field">
                        <label for="objetivo-{{ $error }}">{{ $etiqueta }}</label>
                        <input id="objetivo-{{ $error }}" type="number" step="0.01" min="0" wire:model="{{ $propiedad }}" aria-invalid="@error($error) true @else false @enderror">
                        @error($error) <span role="alert">{{ $message }}</span> @enderror
                    </div>
                @endforeach
                <div class="rm-clinical-goals__field rm-clinical-goals__field--wide">
                    <label for="objetivo-motivo">Motivo clínico <span aria-hidden="true">*</span></label>
                    <textarea id="objetivo-motivo" wire:model="motivo" rows="3" required aria-invalid="@error('motivo') true @else false @enderror"></textarea>
                    @error('motivo') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="rm-clinical-goals__actions">
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardar">Guardar objetivo médico</button>
                </div>
            </form>
        </section>
        @if($objetivos->isNotEmpty())
            <section class="rm-clinical-goals__card" aria-labelledby="retirar-objetivo">
                <h2 id="retirar-objetivo">Retirar un objetivo vigente</h2>
                <p class="rm-clinical-goals__hint">La versión queda en el historial. Al retirarla, se aplicarán las reglas generales aprobadas a las próximas lecturas.</p>
                <form wire:submit="retirar" class="rm-clinical-goals__form">
                    <div class="rm-clinical-goals__field rm-clinical-goals__field--wide">
                        <label for="objetivo-retiro">Objetivo que se retira</label>
                        <select id="objetivo-retiro" wire:model="codObjetivoRetiro" required>
                            <option value="">Seleccionar objetivo vigente</option>
                            @foreach($objetivos as $objetivo)
                                <option value="{{ $objetivo->cod_objetivo_signo }}">{{ $parametros[$objetivo->parametro] }} · {{ $objetivo->min_objetivo ?? '—' }} a {{ $objetivo->max_objetivo ?? '—' }}</option>
                            @endforeach
                        </select>
                        @error('codObjetivoRetiro') <span role="alert">{{ $message }}</span> @enderror
                    </div>
                    <div class="rm-clinical-goals__field rm-clinical-goals__field--wide">
                        <label for="motivo-retiro">Motivo clínico del retiro</label>
                        <textarea id="motivo-retiro" wire:model="motivoRetiro" rows="2" required aria-invalid="@error('motivo') true @else false @enderror"></textarea>
                        @error('motivo') <span role="alert">{{ $message }}</span> @enderror
                    </div>
                    <div class="rm-clinical-goals__actions">
                        <button type="submit" class="rm-clinical-goals__secondary-action" wire:loading.attr="disabled" wire:target="retirar">Retirar objetivo seleccionado</button>
                    </div>
                </form>
            </section>
        @endif
    @endif

    <section class="rm-clinical-goals__card" aria-labelledby="historial-objetivos">
        <h2 id="historial-objetivos">Historial</h2>
        <button type="button" class="rm-clinical-goals__history-button" wire:click="$toggle('mostrarHistorial')" aria-expanded="{{ $mostrarHistorial ? 'true' : 'false' }}">{{ $mostrarHistorial ? 'Ocultar versiones anteriores' : 'Ver versiones anteriores' }}</button>
        @if($mostrarHistorial)
            @forelse($historial as $objetivo)
                <div class="rm-clinical-goals__row" wire:key="historial-{{ $objetivo->cod_objetivo_signo }}">
                    <div><strong>{{ $parametros[$objetivo->parametro] }} · {{ $objetivo->estado === 'ANULADO' ? 'Retirado' : 'Sustituido' }}</strong><span>{{ $objetivo->vigente_desde->format('d/m/Y H:i') }} · {{ $objetivo->motivo }}</span></div>
                    <div class="rm-clinical-goals__values"><span>{{ $objetivo->min_objetivo ?? '—' }} a {{ $objetivo->max_objetivo ?? '—' }}</span></div>
                </div>
            @empty
                <p class="rm-clinical-goals__empty">No hay versiones anteriores.</p>
            @endforelse
        @endif
    </section>
</main>
