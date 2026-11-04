<x-sistema-layout>
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Reportes de Gerencia" subtitle="Indicadores agregados de personal, ocupación y alertas" icon="ph-chart-bar">
        <x-slot:actions>@if($solicitud)<a class="rm-btn-primary" href="{{ route('admin.reportes.institucional.preparar', ['solicitud'=>$solicitud]) }}"><i class="ph-bold ph-download-simple" aria-hidden="true"></i> Preparar exportación</a>@else<span class="rm-help">La previsualización de rol permite consultar, sin exportar.</span>@endif</x-slot:actions>
    </x-ui.collection-header>
    <x-ui.callout variant="info" title="Situación actual"><p>Datos consultados el {{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}. Los conteos actuales no representan un cierre histórico.</p></x-ui.callout>
    <nav class="rm-management-tabs" aria-label="Secciones del reporte">@foreach(['resumen'=>'Resumen','personal'=>'Personal','ocupacion'=>'Ocupación','alertas'=>'Alertas'] as $clave=>$titulo)<a class="{{ $reporte['seccion'] === $clave ? 'rm-btn-primary' : 'rm-btn-secondary' }}" href="{{ route('admin.reportes.institucional.preview', ['seccion'=>$clave]) }}" @if($reporte['seccion'] === $clave) aria-current="page" @endif>{{ $titulo }}</a>@endforeach</nav>
    @include('reportes::paginas.gerencia.partials.indicadores')
    @include('reportes::paginas.gerencia.partials.distribuciones')
    <x-ui.section-card title="Resumen exportable" subtitle="PDF y CSV contienen estas mismas filas, unidades y referencia." icon="ph-table">
        @include('reportes::paginas.gerencia.partials.tabla-reporte')
        <details class="rm-management-definitions"><summary>Consultar definición, fuente y límites</summary><ul>@foreach($reporte['limites'] as $limite)<li>{{ $limite }}</li>@endforeach</ul><p>Fuentes autorizadas: personal, inventario y ocupaciones de camas, alertas persistidas. No se incluyen expedientes ni nombres.</p></details>
    </x-ui.section-card>
</div>
</x-sistema-layout>
