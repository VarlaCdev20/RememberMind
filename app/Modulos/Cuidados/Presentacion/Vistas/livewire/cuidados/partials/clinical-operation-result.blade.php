<x-ui.modal-livewire id="clinical-operation-result" title="Resultado del registro" alpine-model="clinicalFeedbackOpen" alpine-close="closeClinicalFeedback()" class="rm-clinical-form-modal rm-clinical-form-result" :show-validation="false">
    <x-ui.resultado-operacion-clinica resident="" title="Registro guardado correctamente" message="El registro se incorporó al historial clínico del residente." />
    <p class="rm-clinical-form__note text-center" x-text="feedbackTitle"></p>
    <p class="rm-clinical-form__note text-center"><strong x-text="feedbackResident"></strong><br><span x-text="feedbackProfessional"></span></p>
    @if($clinicalResultResidentCode && auth()->user()?->can('enfermeria.ver_ficha_paciente'))
        <p class="text-center"><a class="rm-clinical-form__history-link" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $clinicalResultResidentCode]) }}">Consultar ficha clínica</a></p>
    @endif
    <x-slot:footer><button type="button" class="rm-btn-primary" @click="closeClinicalFeedback()">Volver al residente</button></x-slot:footer>
</x-ui.modal-livewire>
