<x-sistema-layout>
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Preparar exportación" subtitle="Revisa el contenido antes de descargar" icon="ph-file-arrow-down"><x-slot:actions><a class="rm-btn-secondary" href="{{ route('admin.reportes.institucional.preview', ['seccion'=>$reporte['seccion']]) }}">Volver al reporte</a></x-slot:actions></x-ui.collection-header>
    <div class="rm-management-export">
        <x-ui.section-card title="Contenido del archivo" icon="ph-file-text">
            <dl class="rm-management-context"><div><dt>Reporte</dt><dd>Resumen gerencial · {{ ucfirst($reporte['seccion']) }}</dd></div><div><dt>Referencia</dt><dd>{{ $reporte['referencia'] }}</dd></div><div><dt>Datos consultados</dt><dd>{{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}</dd></div><div><dt>Alcance</dt><dd>{{ $reporte['ambito'] }}</dd></div></dl>
            <form method="GET" x-data="{ formato: 'pdf' }" :action="formato === 'pdf' ? @js(route('admin.reportes.institucional.pdf')) : @js(route('admin.reportes.institucional.csv'))" action="{{ route('admin.reportes.institucional.pdf') }}">
                <input type="hidden" name="solicitud" value="{{ $solicitud }}">
                <fieldset class="rm-management-formats"><legend>Formato</legend><label><input type="radio" name="formato" value="pdf" x-model="formato" checked><strong>PDF</strong><span>Lectura e impresión</span></label><label><input type="radio" name="formato" value="csv" x-model="formato"><strong>CSV</strong><span>Análisis de los mismos datos</span></label></fieldset>
                <p class="rm-help">Incluye indicadores agregados. No incluye nombres ni expedientes. La preparación temporal caduca a los quince minutos.</p>
                <div class="rm-management-actions"><button class="rm-btn-primary" type="submit"><i class="ph-bold ph-download-simple" aria-hidden="true"></i><span x-text="formato === 'pdf' ? 'Descargar PDF' : 'Descargar CSV'">Descargar PDF</span></button><a class="rm-btn-secondary" href="{{ route('admin.reportes.institucional.preview') }}">Cancelar</a></div>
            </form>
        </x-ui.section-card>
        <x-ui.section-card title="Vista previa del contenido" subtitle="Mismos indicadores que se descargarán; no es un cierre histórico." icon="ph-eye">
            @include('reportes::paginas.gerencia.partials.tabla-reporte')
            <h3 class="rm-section-title mt-4">Definiciones y límites</h3><ul class="rm-management-limits">@foreach($reporte['limites'] as $limite)<li>{{ $limite }}</li>@endforeach</ul>
        </x-ui.section-card>
    </div>
</div>
</x-sistema-layout>
