@php
    $personaArchivo = $documentoVisible?->usuario?->personal;
    $nombrePersonaArchivo = $personaArchivo ? trim($personaArchivo->nombres.' '.$personaArchivo->apellido_paterno.' '.$personaArchivo->apellido_materno) : '';
    $estadosArchivo = ['CARGADO' => 'Cargado', 'PENDIENTE' => 'Pendiente de revisión', 'VALIDADO' => 'Validado', 'OBSERVADO' => 'Observado', 'VENCIDO' => 'Vencido registrado', 'ANULADO' => 'Anulado', 'ANULADA' => 'Anulada', 'REEMPLAZADO' => 'Reemplazado'];
@endphp
<x-ui.modal-livewire id="personal-documento-vista-previa" wire:model="vistaPreviaAbierta" :title="$documentoVisible?->nombre ?: 'Documento del personal'" :subtitle="$nombrePersonaArchivo" icon="ph-file-text" max-width="5xl" surface="reading" close-method="cerrarVistaPrevia" :show-validation="false">
    @if($documentoVisible)
    <div class="rm-staff-documents__viewer">
        <section class="rm-staff-documents__preview" aria-label="Vista previa del archivo">
            @if(!$archivoDisponible)
                <x-ui.empty-state title="No se encontró el archivo original" description="La ficha documental se conserva. Comunica esta incidencia a la persona que administra los archivos." icon="ph-file-x" />
            @elseif(in_array($documentoVisible->extension, ['jpg', 'jpeg', 'png', 'webp'], true))
                <img src="{{ route('admin.personal.documento.archivo', $documentoVisible) }}" alt="Vista previa de {{ $documentoVisible->nombre }}" decoding="async">
            @elseif($documentoVisible->extension === 'pdf')
                <iframe src="{{ route('admin.personal.documento.archivo', $documentoVisible) }}" title="Vista previa PDF de {{ $documentoVisible->nombre }}" referrerpolicy="same-origin"></iframe>
                <p>Si tu navegador no muestra el PDF, utiliza «Abrir original».</p>
            @else
                <x-ui.empty-state title="Vista previa no disponible para este formato" description="Utiliza Abrir original para consultar o descargar el documento con una aplicación compatible." icon="ph-file-text" />
            @endif
        </section>
        <aside class="rm-staff-documents__metadata" aria-labelledby="documento-ficha-titulo">
            <h3 id="documento-ficha-titulo">Ficha del documento</h3>
            <dl><div><dt>Persona</dt><dd>{{ $nombrePersonaArchivo }}</dd></div><div><dt>Estado registrado</dt><dd>{{ $estadosArchivo[$documentoVisible->estado] ?? 'Por revisar' }}</dd></div><div><dt>Formato</dt><dd>{{ mb_strtoupper($documentoVisible->extension) ?: 'No identificado' }}</dd></div><div><dt>Fecha de carga</dt><dd>{{ $documentoVisible->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}</dd></div><div><dt>Emisión</dt><dd>{{ $documentoVisible->fecha_emision?->format('d/m/Y') ?? 'No consta' }}</dd></div><div><dt>Vencimiento</dt><dd>{{ $documentoVisible->fecha_vencimiento?->format('d/m/Y') ?? 'No consta' }}</dd></div><div><dt>Última validación</dt><dd>{{ $documentoVisible->fecha_validacion?->format('d/m/Y H:i') ?? 'Sin validación registrada' }}</dd></div></dl>
            @if($documentoVisible->observacion)<details class="rm-staff-documents__notes"><summary>Observaciones conservadas</summary><p>{{ $documentoVisible->observacion }}</p></details>@endif
            @if($documentoVisible->motivo_anulacion)<p class="rm-help">Motivo de anulación: {{ $documentoVisible->motivo_anulacion }}</p>@endif
            <p class="rm-help">Consultar el archivo no lo valida ni modifica su estado.</p>
        </aside>
    </div>
    @endif
    <x-slot:footer>@if($archivoDisponible)<a class="rm-btn rm-btn-primary" href="{{ route('admin.personal.documento.archivo', $documentoVisible) }}" target="_blank" rel="noopener"><i class="ph ph-arrow-square-out" aria-hidden="true"></i>Abrir original<span class="sr-only"> en otra pestaña</span></a>@endif<button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarVistaPrevia">Cerrar vista previa</button></x-slot:footer>
</x-ui.modal-livewire>
