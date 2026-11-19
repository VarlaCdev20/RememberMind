@props(['area' => '', 'residente' => '', 'desde' => '', 'hasta' => '', 'alcance' => null, 'identificador' => null])

@use('App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria')
@php
    $tipoReporte = ConsultarReporteEnfermeria::tipoDeArea($area ?? '');
    $permisosReporte = match ($tipoReporte) {
        'TODOS' => array_values(ConsultarReporteEnfermeria::PERMISOS),
        'HERIDAS', 'CURACIONES' => ['heridas.ver', 'curaciones_herida.ver'],
        default => [ConsultarReporteEnfermeria::PERMISOS[$tipoReporte]],
    };
    $tituloReporte = ConsultarReporteEnfermeria::TIPOS[$tipoReporte];
    $modalReporte = 'exportar-enfermeria-'.($identificador ?? strtolower($tipoReporte));
@endphp
@if(auth()->user()?->can(['enfermeria.ver_dashboard', 'enfermeria.ver_ficha_paciente', ...$permisosReporte]))
    <div {{ $attributes->class(['contents']) }} x-data="rmNursingReportExport()" wire:key="{{ $modalReporte }}-{{ md5(json_encode([$tipoReporte, $residente, $desde, $hasta, $alcance])) }}">
        <x-ui.action-button variant="secondary" icono="ph-file-arrow-down" class="rm-report-export-button"
            @click="abrirExportacion()" aria-label="{{ $tipoReporte === 'TODOS' ? 'Exportar reporte general' : 'Exportar reporte de '.$tituloReporte }}" aria-haspopup="dialog"
            aria-controls="{{ $modalReporte }}" x-bind:aria-expanded="exportacionAbierta" />
        <template x-teleport="body">
            <x-ui.modal-livewire :id="$modalReporte" title="Exportar reporte" subtitle="Elige el período y el formato del archivo"
                icon="ph-file-arrow-down" alpine-model="exportacionAbierta" alpine-close="cerrarExportacion()"
                alpine-busy="descargandoExportacion" :show-validation="false" class="rm-report-export-dialog" surface="reading">
                <form id="{{ $modalReporte }}-form" class="rm-report-export-dialog__form" @submit.prevent="descargarExportacion($el)"
                    data-pdf-url="{{ route('admin.enfermeria.reportes.pdf', ['tipo' => $tipoReporte]) }}"
                    data-csv-url="{{ route('admin.enfermeria.reportes.csv', ['tipo' => $tipoReporte]) }}">
                    <div class="rm-report-export-dialog__context">
                        <h4>{{ $tituloReporte }}</h4>
                        <p>{{ $alcance ?? ($residente ? 'Residente seleccionado' : 'Residentes autorizados') }}</p>
                    </div>
                    <input type="hidden" name="tipo" value="{{ $tipoReporte }}">
                    <input type="hidden" name="codResidente" value="{{ $residente }}">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.field label="Desde" :for="$modalReporte.'-desde'" required>
                            <input class="rm-input" type="date" id="{{ $modalReporte }}-desde" name="desde"
                                value="{{ $desde ?: today()->subDays(6)->toDateString() }}" max="{{ today()->toDateString() }}" required>
                        </x-ui.field>
                        <x-ui.field label="Hasta" :for="$modalReporte.'-hasta'" required>
                            <input class="rm-input" type="date" id="{{ $modalReporte }}-hasta" name="hasta"
                                value="{{ $hasta ?: today()->toDateString() }}" max="{{ today()->toDateString() }}" required>
                        </x-ui.field>
                    </div>
                    <x-ui.report-export-formats :id="$modalReporte" />
                    <p class="rm-help">Incluye los registros autorizados de esta sección durante el período elegido.</p>
                    <p class="rm-field-error" x-show="errorExportacion" x-text="errorExportacion" role="alert" x-cloak></p>
                    <p class="rm-field-valid" x-show="resultadoExportacion" x-text="resultadoExportacion" role="status" x-cloak></p>
                </form>
                <x-slot:footer>
                    <x-ui.action-button variant="secondary" @click="cerrarExportacion()" x-bind:disabled="descargandoExportacion">Cerrar</x-ui.action-button>
                    <x-ui.action-button tipo="submit" icono="ph-download-simple" form="{{ $modalReporte }}-form" x-bind:disabled="descargandoExportacion" x-bind:aria-busy="descargandoExportacion">
                        <span x-text="descargandoExportacion ? 'Preparando archivo…' : 'Descargar ' + formatoExportacion.toUpperCase()">Descargar PDF</span>
                    </x-ui.action-button>
                </x-slot:footer>
            </x-ui.modal-livewire>
        </template>
    </div>
@endif
