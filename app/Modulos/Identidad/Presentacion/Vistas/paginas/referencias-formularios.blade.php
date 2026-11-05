<x-sistema-layout>
    @php
        $references = [
            'adulto' => ['title'=>'Registro de Admisión', 'subject'=>'Adulto mayor · página independiente', 'revision'=>'ea7cca83', 'steps'=>['Identidad y fotografía','Datos médicos','Ubicación y dirección','Contacto responsable','Ingreso institucional','Confirmación y cierre']],
            'personal-pagina' => ['title'=>'Registro de Personal', 'subject'=>'Personal · página independiente', 'revision'=>'ea7cca83', 'steps'=>['Identidad y fotografía','Documento de identidad','Contacto','Rol e institución','Seguridad y acceso','Resumen y confirmación']],
            'personal-modal' => ['title'=>'Registrar Personal Institucional', 'subject'=>'Personal · modal antiguo', 'revision'=>'155a1516', 'steps'=>['Acceso','Datos personales','Dirección','Rol y perfil','Documentos del trabajador','Documentos institucionales','Resumen y confirmación']],
        ];
    @endphp
    <div class="rm-reference-gallery" x-data="{ reference: 'adulto', step: 1, total: 6 }">
        <x-ui.collection-header title="Referencias de formularios" subtitle="Compara los diseños anteriores antes de elegir cuál recuperar" icon="ph-layout">
            <x-slot:actions><a class="rm-btn rm-btn-secondary" href="{{ route('admin.personal-institucional') }}">Volver a Personal</a></x-slot:actions>
        </x-ui.collection-header>
        <x-ui.callout title="Galería local de referencia visual" variant="info">
            Apartados extraídos del código histórico, con los estilos disponibles del sistema actual. Los campos son de muestra: no guardan datos ni ejecutan acciones. Las secciones que dependían de catálogos o datos aparecen vacías; el acabado puede diferir del original.
        </x-ui.callout>
        <div class="rm-reference-gallery__choices" role="group" aria-label="Elegir diseño histórico">
            @foreach($references as $key=>$reference)
                <button type="button" class="rm-reference-gallery__choice" x-on:click="reference = '{{ $key }}'; step = 1; total = {{ count($reference['steps']) }}" x-bind:aria-pressed="reference === '{{ $key }}'" data-reference-choice="{{ $key }}">
                    <span>{{ $reference['subject'] }}</span><strong>{{ $reference['title'] }}</strong><small>{{ count($reference['steps']) }} apartados · {{ $reference['revision'] }}</small>
                </button>
            @endforeach
        </div>
        <x-ui.section-card title="Apartados del formulario" icon="ph-list-numbers">
            @foreach($references as $key=>$reference)
                <div class="rm-reference-gallery__steps" role="group" aria-label="Apartados de {{ $reference['title'] }}" x-show="reference === '{{ $key }}'" x-cloak>
                    @foreach($reference['steps'] as $title)
                        <button class="rm-btn rm-btn-secondary" type="button" x-on:click="step = {{ $loop->iteration }}" x-bind:aria-pressed="step === {{ $loop->iteration }}">{{ $loop->iteration }}. {{ $title }}</button>
                    @endforeach
                </div>
            @endforeach
        </x-ui.section-card>
        <section class="rm-reference-gallery__sheet" x-bind:class="reference === 'personal-modal' ? 'is-modal-reference' : ''" aria-label="Vista previa del diseño seleccionado">
            <header class="rm-reference-gallery__heading">
                <div>
                    @foreach($references as $key=>$reference)
                        <h2 x-show="reference === '{{ $key }}'" x-cloak>{{ $reference['title'] }}</h2>
                    @endforeach
                    <p role="status" aria-live="polite">Apartado <span x-text="step"></span> de <span x-text="total"></span> · Referencia histórica</p>
                </div>
                <span class="rm-reference-gallery__badge">Solo vista previa</span>
            </header>
            <div class="rm-reference-gallery__progress" aria-hidden="true"><span x-bind:style="'width:' + (step / total * 100) + '%' "></span></div>
            <div class="rm-reference-gallery__preview">
                @include('identidad::referencias.formularios-historicos')
            </div>
            <footer class="rm-reference-gallery__footer">
                <button type="button" class="rm-btn rm-btn-secondary" x-on:click="step--" x-bind:disabled="step === 1">Anterior</button>
                <span>No se registra ni recupera ningún formulario desde esta galería.</span>
                <button type="button" class="rm-btn rm-btn-primary" x-on:click="step++" x-bind:disabled="step === total">Siguiente apartado</button>
            </footer>
        </section>
    </div>
</x-sistema-layout>
