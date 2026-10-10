@if($adulto)
    @php($hidratacionInicial = ['subtipo' => '', 'cantidadMl' => null, 'tolerancia' => '', 'observacion' => ''])
    <x-ui.section-card :title="trim($adulto->nombres.' '.$adulto->ap_paterno.' '.$adulto->ap_materno)" subtitle="Hidratación · Registros del residente" icon="ph-drop" class="rm-clinical-workspace__context">
        <div class="rm-clinical-workspace__actions">
            <p class="rm-clinical-form__note">Cada aporte de líquido queda guardado como un nuevo registro.</p>
            @if($this->puedeMutarRegistro('registros_hidratacion.crear'))
                <button id="resident-register-trigger" type="button" class="rm-btn-primary" @click="$wire.$set('tipo', 'HIDRATACION', false); clinicalDiscardOpen = false; clinicalFormOpen = true">Registrar hidratación</button>
            @else
                <span class="rm-badge-pill">Solo consulta</span>
            @endif
        </div>
    </x-ui.section-card>

    <x-ui.section-card title="Hidratación reciente" subtitle="Hasta 12 registros guardados, del más reciente al más antiguo." icon="ph-clock-counter-clockwise">
        @if($registrosCuidado->isNotEmpty())
            <ol class="rm-clinical-workspace__history">
                @foreach($registrosCuidado as $registro)
                    <li class="rm-clinical-workspace__record">
                        <div class="rm-clinical-workspace__record-heading"><time>{{ $registro->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada' }}</time><span class="rm-badge-pill">{{ mb_convert_case(str_replace('_', ' ', $registro->estado), MB_CASE_TITLE, 'UTF-8') }}</span></div>
                        <strong class="rm-clinical-workspace__value">{{ $registro->cantidad_ml }} <small>mL</small></strong>
                        <dl class="rm-clinical-workspace__details"><div><dt>Tipo de líquido</dt><dd>{{ $registro->tipo_liquido ?: 'No registrado' }}</dd></div><div><dt>Tolerancia</dt><dd>{{ $registro->tolerancia ? mb_convert_case(str_replace('_', ' ', $registro->tolerancia), MB_CASE_TITLE, 'UTF-8') : 'No registrada' }}</dd></div></dl>
                        @if(filled($registro->observacion))<p class="rm-clinical-form__note">{{ $registro->observacion }}</p>@endif
                    </li>
                @endforeach
            </ol>
        @else
            <x-ui.empty-state icon="ph-drop" title="Sin registros de hidratación" description="Aún no hay aportes de líquido guardados para este residente." />
        @endif
    </x-ui.section-card>

    @if($this->puedeMutarRegistro('registros_hidratacion.crear'))
        <x-ui.modal-livewire id="clinical-hydration" title="Registro de hidratación" subtitle="Aporte de líquido y tolerancia" alpine-model="clinicalFormOpen" alpine-close="closeClinicalForm()" class="rm-clinical-form-modal" :show-validation="false">
            <x-slot:context>
                <div class="rm-resident-directory__register-context"><span class="rm-resident-directory__register-avatar" aria-hidden="true"><i class="ph-bold ph-drop"></i></span><div class="rm-resident-directory__register-identity"><strong data-clinical-resident>{{ trim($adulto->nombres.' '.$adulto->ap_paterno.' '.$adulto->ap_materno) }}</strong><span>Hidratación · Nuevo registro</span></div><div class="rm-clinical-form__personnel"><span>Profesional que registra</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong></div></div>
            </x-slot:context>
            <div x-show="clinicalDiscardOpen" x-cloak role="alert" class="rm-clinical-form__section" tabindex="-1" x-effect="if(clinicalDiscardOpen) $nextTick(() => $el.focus())"><h4 class="rm-section-title">¿Salir sin guardar?</h4><p class="rm-clinical-form__note">Los datos de este aporte de líquido se perderán.</p></div>
            <form id="clinical-hydration-form" wire:submit="guardarCuidado" @submit="prepareFeedback('hidratacion')" x-show="!clinicalDiscardOpen" class="rm-clinical-form" x-data="rmClinicalCapture(@js($hidratacionInicial))" @input="notifyDirty()" @change="notifyDirty()" aria-label="Registro de hidratación">
                <x-validation-errors />
                <p class="rm-clinical-form__note"><i class="ph-bold ph-lock" aria-hidden="true"></i> Fecha y hora automáticas al guardar.</p>
                <x-ui.form-section title="Aporte de líquido" icon="ph-drop" :columns="2" class="rm-clinical-form__section">
                    <x-ui.field label="Tipo de líquido" for="hidratacion-tipo" error="subtipo">
                        <input id="hidratacion-tipo" class="rm-input" wire:model="subtipo" type="text" maxlength="60" aria-invalid="{{ $errors->has('subtipo') ? 'true' : 'false' }}" @error('subtipo') aria-describedby="hidratacion-tipo-error" @enderror>
                    </x-ui.field>
                    <x-ui.field label="Volumen administrado" for="hidratacion-volumen" error="cantidadMl" :required="true" help="Registra el volumen real, entre 1 y 10 000 mL.">
                        <div class="rm-clinical-form__unit"><input id="hidratacion-volumen" class="rm-input" wire:model="cantidadMl" type="number" min="1" max="10000" step="1" inputmode="numeric" aria-label="Volumen administrado en mililitros" aria-invalid="{{ $errors->has('cantidadMl') ? 'true' : 'false' }}" aria-describedby="hidratacion-volumen-help @error('cantidadMl') hidratacion-volumen-error @enderror"><span aria-hidden="true">mL</span></div>
                    </x-ui.field>
                    <x-ui.field label="Tolerancia" for="hidratacion-tolerancia" error="tolerancia" class="rm-clinical-form__full">
                        <select id="hidratacion-tolerancia" class="rm-select" wire:model="tolerancia"><option value="">No registrada</option><option value="ADECUADA">Adecuada</option><option value="PARCIAL">Parcial</option><option value="RECHAZO">Rechazo</option><option value="NAUSEAS">Náuseas</option></select>
                    </x-ui.field>
                </x-ui.form-section>
                <x-ui.form-section title="Observaciones complementarias" icon="ph-note-pencil" :columns="2" class="rm-clinical-form__section">
                    <x-ui.field label="Observación complementaria" for="hidratacion-observacion" error="observacion" class="rm-clinical-form__full">
                        <textarea id="hidratacion-observacion" class="rm-textarea" wire:model="observacion" rows="3" maxlength="5000" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" @error('observacion') aria-describedby="hidratacion-observacion-error" @enderror></textarea>
                    </x-ui.field>
                </x-ui.form-section>
            <x-ui.clinical-clear-action /></form>
            <x-slot:footer>
                <template x-if="clinicalDiscardOpen"><div class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false">Seguir editando</button><button type="button" class="rm-btn-danger" @click="discardClinicalForm(@js($hidratacionInicial))">Salir sin guardar</button></div></template>
                <template x-if="!clinicalDiscardOpen"><div class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="closeClinicalForm()" wire:loading.attr="disabled" wire:target="guardarCuidado">Cancelar</button><button type="submit" form="clinical-hydration-form" class="rm-btn-primary rm-btn-primary--confirm" wire:loading.attr="disabled" wire:target="guardarCuidado"><span wire:loading.remove wire:target="guardarCuidado">Confirmar y registrar</span><span wire:loading wire:target="guardarCuidado">Registrando…</span></button></div></template>
            </x-slot:footer>
        </x-ui.modal-livewire>
    @endif
    @include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $adulto->cod_residente])
@else
    <x-ui.empty-state icon="ph-users" title="Selecciona un residente" description="Podrás consultar sus aportes de líquido y registrar hidratación cuando corresponda." />
@endif
