<x-ui.drawer-livewire
    wire:model="drawerDosisAbierto"
    title="Detalle de la dosis"
    subtitle="Detalle de la dosis seleccionada"
    badge=""
    icon="ph-pill"
    size="md"
    class="rm-medication-dose-drawer"
    close-method="cerrarDrawerDosis"
    :dismiss-on-escape="!$modalMedicamentoAbierto"
    alpine-close="if ($wire.modalMedicamentoAbierto) { $wire.cerrarModalMedicamento() } else if (!$wire.observacionEnfermeria.trim() || window.confirm('¿Descartar la observación sin guardar y cerrar?')) { $wire.cerrarDrawerDosis() }"
>
@if(!empty($dosisDetalle))
    @php
        $tieneAlergias = $dosisDetalle['seguridad']['tiene_alergias'] ?? false;
        $frecuencia = ucfirst(mb_strtolower(str_replace('_', ' ', $dosisDetalle['prescripcion']['frecuencia'] ?? 'No registrada')));
    @endphp
    <div class="rm-dose-detail">
        <section class="rm-dose-detail__identity" aria-label="Residente de la dosis">
            <span class="rm-dose-avatar" aria-hidden="true">{{ $dosisDetalle['residente']['iniciales'] ?? 'SR' }}</span>
            <div>
                <h3>{{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente no identificado' }}</h3>
                <p>{{ $dosisDetalle['residente']['edad'] ?? 'Edad no registrada' }} · {{ $dosisDetalle['residente']['habitacion'] ?? 'Sin habitación' }}</p>
                @if(($dosisDetalle['residente']['cama'] ?? 'Sin cama') !== 'Sin cama')<p>{{ $dosisDetalle['residente']['cama'] }}</p>@endif
                @if(!empty($dosisDetalle['residente']['nhc']) && $dosisDetalle['residente']['nhc'] !== 'No registrado')<p>NHC: {{ $dosisDetalle['residente']['nhc'] }}</p>@endif
            </div>
            <x-ui.status-badge :estado="$dosisDetalle['estado_dosis'] ?? ''"
                :label="match($dosisDetalle['estado_dosis'] ?? '') { 'RETRASADA' => 'Con retraso', 'ADMINISTRADA' => 'Administrada', 'OMITIDA' => 'Omitida', default => 'Pendiente' }"
                :variant="match($dosisDetalle['estado_dosis'] ?? '') { 'RETRASADA' => 'danger', 'ADMINISTRADA' => 'success', 'OMITIDA' => 'neutral', default => 'warning' }" />
        </section>

        <div class="rm-dose-detail__allergies {{ $tieneAlergias ? 'has-alert' : '' }}" @if($tieneAlergias) role="note" @endif>
            <i class="ph-bold {{ $tieneAlergias ? 'ph-warning-circle' : 'ph-info' }}" aria-hidden="true"></i>
            <div><strong>Alergias registradas</strong><p>{{ $dosisDetalle['seguridad']['alergias'] ?? 'Información no disponible; consulta la ficha clínica.' }}</p></div>
        </div>

        <section class="rm-dose-detail__section rm-dose-detail__section--medication" aria-labelledby="dose-medication-title">
            <h3 id="dose-medication-title"><i class="ph-bold ph-pill" aria-hidden="true"></i> Medicamento</h3>
            <div class="rm-dose-detail__drug">
                <div>
                    <strong>{{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento no identificado' }}</strong>
                    <p>{{ $dosisDetalle['medicamento']['concentracion'] ?? 'Concentración no registrada' }} · {{ $dosisDetalle['medicamento']['forma'] ?? 'Forma no registrada' }}</p>
                </div>
                <button type="button" wire:click="abrirModalMedicamento('{{ $dosisDetalle['cod_medicamento'] ?? '' }}')"
                    wire:loading.attr="aria-busy" wire:loading.class="opacity-60" wire:target="abrirModalMedicamento"
                    @click.capture="if ($el.getAttribute('aria-busy') === 'true') { $event.preventDefault(); $event.stopImmediatePropagation() }"
                    class="rm-btn rm-btn-secondary rm-dose-detail__catalog-button" aria-haspopup="dialog">
                    <i class="ph-bold ph-file-text" aria-hidden="true"></i> Ver ficha
                </button>
            </div>
            @error('medicamento')<p class="rm-dose-detail__error" role="alert">{{ $message }}</p>@enderror
        </section>

        <section class="rm-dose-detail__section rm-dose-detail__section--order" aria-labelledby="dose-order-title">
            <h3 id="dose-order-title"><i class="ph-bold ph-prescription" aria-hidden="true"></i> Prescripción médica <span>Solo lectura</span></h3>
            <dl class="rm-dose-detail__facts">
                <div><dt>Dosis prescrita</dt><dd>{{ $dosisDetalle['prescripcion']['dosis'] ?? 'No registrada' }}</dd></div>
                <div><dt>Vía</dt><dd>{{ $dosisDetalle['prescripcion']['via'] ?? 'No registrada' }}</dd></div>
                <div><dt>Frecuencia</dt><dd>{{ $frecuencia }}</dd></div>
                <div><dt>Hora programada</dt><dd class="rm-dose-detail__time"><i class="ph-bold ph-clock" aria-hidden="true"></i>{{ $dosisDetalle['programacion']['hora_programada'] ?? $dosisDetalle['hora'] ?? 'No registrada' }}</dd></div>
            </dl>
            <section class="rm-dose-detail__schedule" aria-labelledby="dose-schedule-title">
                <h4 id="dose-schedule-title">Horarios activos de la prescripción</h4>
                <p class="rm-dose-detail__note">La hora destacada arriba corresponde a la dosis consultada.</p>
                @if(!empty($dosisDetalle['programacion']['horarios_activos']))
                    <ul class="rm-dose-detail__schedule-list">
                        @foreach($dosisDetalle['programacion']['horarios_activos'] as $horario)
                            <li @class(['is-selected' => $horario['seleccionado']])>
                                <strong><i class="ph-bold ph-clock" aria-hidden="true"></i>{{ $horario['hora'] }}</strong>
                                <span>{{ $horario['dias'] }}</span>
                                @if($horario['seleccionado'])<span class="rm-dose-detail__schedule-current">Dosis consultada</span>@endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="rm-dose-detail__note">No hay horarios activos registrados. La frecuencia no genera horas automáticamente; consulta al profesional prescriptor.</p>
                @endif
            </section>
            <div class="rm-dose-detail__indication"><h4>¿Para qué se indicó?</h4><p>{{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Indicación no registrada' }}</p><p class="rm-dose-detail__note">Indicación médica de esta prescripción.</p></div>
            <dl class="rm-dose-detail__provenance">
                <div><dt>Prescrito por</dt><dd>{{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Prescriptor no registrado' }}</dd></div>
                <div><dt>Cargo / profesión registrada</dt><dd>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $dosisDetalle['prescripcion']['profesion_prescriptor'] ?? 'Profesión no registrada'))) }}</dd></div>
                @if(!empty($dosisDetalle['prescripcion']['especialidad_prescriptor']))<div><dt>Especialidad registrada</dt><dd>{{ $dosisDetalle['prescripcion']['especialidad_prescriptor'] }}</dd></div>@endif
            </dl>
        </section>

        <section class="rm-dose-detail__section rm-dose-detail__section--safety" aria-labelledby="dose-safety-title">
            <h3 id="dose-safety-title"><i class="ph-bold ph-shield-check" aria-hidden="true"></i> Seguridad antes de administrar</h3>
            <p class="rm-dose-detail__note">Verifica los cinco correctos antes de registrar la administración.</p>
            <ul class="rm-dose-detail__checks">
                @foreach(['Residente correcto', 'Medicamento correcto', 'Dosis correcta', 'Vía correcta', 'Hora correcta'] as $correcto)
                    <li><i class="ph-bold ph-circle" aria-hidden="true"></i>{{ $correcto }}</li>
                @endforeach
            </ul>
        </section>

        <dl class="rm-dose-detail__provenance"><div><dt>Última administración registrada</dt><dd>{{ $dosisDetalle['seguimiento']['ultima_admin'] ?? 'Sin administraciones previas' }}</dd></div></dl>

        @if(empty($dosisDetalle['ya_registrada']) && !$esModoConsulta)
            <section class="rm-dose-detail__section rm-dose-detail__section--capture" aria-labelledby="dose-observation-title">
                <h3 id="dose-observation-title"><i class="ph-bold ph-note-pencil" aria-hidden="true"></i> Observación de Enfermería</h3>
                <label for="obsEnfDrawer" class="rm-dose-detail__note">Opcional · Se conserva al consultar la ficha del medicamento.</label>
                <textarea id="obsEnfDrawer" wire:model="observacionEnfermeria" maxlength="200" rows="3"
                    placeholder="Describe lo observado antes de administrar…" class="rm-input" aria-describedby="dose-observation-count"></textarea>
                <p id="dose-observation-count" class="rm-dose-detail__counter" x-text="Array.from($wire.observacionEnfermeria || '').length + '/200 caracteres'">{{ mb_strlen($observacionEnfermeria) }}/200 caracteres</p>
            </section>
        @endif
    </div>
@else
    <x-ui.empty-state icon="ph-pill" title="Selecciona una dosis" description="Consulta una dosis de la agenda para ver su detalle." />
@endif
    <x-slot:footer>
        @if(!empty($dosisDetalle) && empty($dosisDetalle['ya_registrada']) && !$esModoConsulta)
            <div class="rm-dose-detail__actions">
                <button type="button" wire:click="abrirModalOmision" wire:loading.attr="disabled" wire:target="abrirModalOmision" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-prohibit" aria-hidden="true"></i> Registrar omisión</button>
                <button type="button" wire:click="abrirModalAdministrar" wire:loading.attr="disabled" wire:target="abrirModalAdministrar" class="rm-btn rm-btn-primary"><i class="ph-bold ph-pill" aria-hidden="true"></i> Administrar</button>
            </div>
        @elseif(!empty($dosisDetalle))
            <p class="rm-dose-detail__note">{{ !empty($dosisDetalle['ya_registrada']) ? 'Esta dosis ya tiene un registro. Puedes consultar su detalle.' : 'Solo consulta. El registro requiere turno activo y autorización.' }}</p>
        @endif
    </x-slot:footer>
</x-ui.drawer-livewire>
