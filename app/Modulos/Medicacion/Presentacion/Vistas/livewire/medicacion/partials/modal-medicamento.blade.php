<x-ui.modal-livewire id="medication-catalog" wire:model="modalMedicamentoAbierto"
    title="Ficha del medicamento" subtitle="Información del catálogo · solo lectura"
    maxWidth="sm" closeMethod="cerrarModalMedicamento" :showValidation="false"
    :draggable="true" :dismissOnBackdrop="true" class="rm-medication-catalog">
    @if($modalMedicamentoAbierto && $medicamentoFicha)
        <div class="rm-medication-catalog__content">
            <header class="rm-medication-catalog__identity">
                <div class="rm-medication-catalog__name">
                    <p class="rm-medication-catalog__eyebrow">Nombre genérico</p>
                    <h4>{{ $medicamentoFicha['nombre_generico'] ?? 'Medicamento no identificado' }}</h4>
                    <p>{{ $medicamentoFicha['forma_farmaceutica'] ?? 'Forma no registrada' }}</p>
                    <x-ui.status-badge :estado="$medicamentoFicha['estado'] ?? ''" />
                </div>
                <x-ui.medication-image id="medication-catalog-photo" :source="$medicamentoFicha['imagen'] ?? null" :name="$medicamentoFicha['nombre_generico'] ?? 'Medicamento'" />
            </header>
            <section class="rm-medication-catalog__section" aria-labelledby="medication-catalog-presentation">
                <h4 id="medication-catalog-presentation"><i class="ph-bold ph-pill" aria-hidden="true"></i> Presentación y uso</h4>
                <dl class="rm-medication-catalog__facts">
                    @foreach(['nombre_comercial' => 'Nombre comercial', 'concentracion' => 'Concentración', 'unidad' => 'Unidad', 'forma_farmaceutica' => 'Forma farmacéutica', 'via_predeterminada' => 'Vía predeterminada', 'control_especial' => 'Control especial'] as $campo => $label)
                        <div @class(['rm-medication-catalog__fact--wide' => in_array($campo, ['nombre_comercial', 'forma_farmaceutica'], true)])><dt>{{ $label }}</dt><dd>{{ $medicamentoFicha[$campo] ?? 'No registrado' }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section class="rm-medication-catalog__observation">
                <h4><i class="ph-bold ph-info" aria-hidden="true"></i> Dosis y horarios</h4>
                <p>La dosis, la frecuencia y los horarios se consultan en la prescripción médica.</p>
                @if(filled($medicamentoFicha['observacion'] ?? null))
                    <details class="rm-medication-catalog__references">
                        <summary>Ver notas y referencias del catálogo</summary>
                        <p>{{ $medicamentoFicha['observacion'] }}</p>
                    </details>
                @endif
            </section>
        </div>
    @endif
    <x-slot:footer><button type="button" wire:click="cerrarModalMedicamento" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i> Volver al detalle</button></x-slot:footer>
</x-ui.modal-livewire>
