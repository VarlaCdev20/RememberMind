@inject('exportacionGerencia', 'App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReportePorVistaGerencia')
@if($exportacionGerencia->puedePreparar(auth()->user(), $origenReporte))
    @php($modalExportacion = 'exportacion-reporte-'.$origenReporte)
    <div class="contents" x-data="rmReportExport()" data-exportacion-url="{{ route('admin.reportes.institucional.preparar', ['vista' => $origenReporte] + ($parametrosReporte ?? [])) }}" wire:key="exportacion-{{ $origenReporte }}">
        <x-ui.action-button variant="secondary" size="sm" icono="ph-file-arrow-down" class="rm-report-export-button" @click="abrirExportacion($event.currentTarget)"
            data-rm-export-vista="{{ $origenReporte }}" aria-label="Exportar reporte" aria-haspopup="dialog" aria-controls="{{ $modalExportacion }}" x-bind:aria-expanded="exportacionAbierta">
        </x-ui.action-button>
        <template x-teleport="body">
            <x-ui.modal-livewire :id="$modalExportacion" title="Exportar reporte" subtitle="Elige el formato y el contenido del archivo" icon="ph-file-arrow-down"
                alpine-model="exportacionAbierta" alpine-close="cerrarExportacion()" alpine-busy="descargandoExportacion"
                :show-validation="false" class="rm-report-export-dialog" surface="reading">
                <p class="rm-help" x-show="cargandoExportacion" role="status">Consultando el contenido disponible…</p>
                <div class="rm-alert rm-alert-danger" x-show="errorExportacion" x-cloak role="alert"><p x-text="errorExportacion"></p></div>
                <button type="button" class="rm-btn rm-btn-secondary mt-3" x-show="errorExportacion && !datosExportacion && !cargandoExportacion"
                    @click="abrirExportacion()">Volver a intentar</button>

                <form id="{{ $modalExportacion }}-form" @submit.prevent="descargarExportacion()" x-show="datosExportacion && !cargandoExportacion" x-cloak class="rm-report-export-dialog__form">
                    <div class="rm-report-export-dialog__context">
                        <h4 x-text="datosExportacion?.titulo"></h4>
                        <p x-text="datosExportacion?.referencia"></p>
                        <p class="rm-help" x-text="datosExportacion?.ambito"></p>
                    </div>
                    <fieldset class="rm-management-formats" :disabled="descargandoExportacion">
                        <legend>¿En qué formato?</legend>
                        <label><input type="radio" name="formato-{{ $origenReporte }}" value="pdf" x-model="formatoExportacion"><strong>PDF</strong><span>Lectura e impresión</span></label>
                        <label><input type="radio" name="formato-{{ $origenReporte }}" value="csv" x-model="formatoExportacion"><strong>CSV</strong><span>Hoja de cálculo</span></label>
                    </fieldset>
                    <fieldset class="rm-report-export-dialog__parts" :disabled="descargandoExportacion" aria-describedby="{{ $modalExportacion }}-alcance">
                        <legend>¿Qué parte quieres exportar?</legend>
                        <label class="rm-report-export-dialog__all">
                            <input type="checkbox" :checked="datosExportacion && partesExportacion.length === datosExportacion.partes.length" @change="seleccionarTodasExportacion($event.target.checked)">
                            <span>Todo el resumen de esta vista</span>
                        </label>
                        <div class="rm-report-export-dialog__options">
                            <template x-for="parte in (datosExportacion?.partes || [])" :key="parte.id">
                                <label>
                                    <input type="checkbox" :value="parte.id" x-model="partesExportacion">
                                    <span x-text="parte.etiqueta"></span>
                                    <small x-text="parte.valor === null ? 'No disponible' : parte.valor + ' ' + parte.unidad"></small>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <details class="rm-report-export-dialog__charts" open @toggle="if ($event.target.open) $nextTick(() => dibujarGraficas())">
                        <summary><i class="ph-bold ph-chart-pie-slice" aria-hidden="true"></i> Gráficas del reporte</summary>
                        <p class="rm-help">El PDF conserva el diseño elegido. El CSV contiene los valores.</p>
                        @include('reportes::paginas.gerencia.partials.visor-graficas')
                    </details>
                    <p id="{{ $modalExportacion }}-alcance" class="rm-help">Incluye los indicadores seleccionados dentro del alcance de esta consulta.</p>
                    <p class="rm-help" x-show="partesExportacion.length === 0">Selecciona al menos una parte para descargar.</p>
                </form>
                <p class="rm-field-valid mt-3" role="status" x-show="resultadoExportacion" x-text="resultadoExportacion" x-cloak></p>

                <x-slot:footer>
                    <button type="button" class="rm-btn rm-btn-secondary" @click="cerrarExportacion()" :disabled="descargandoExportacion">Cerrar</button>
                    <button type="submit" class="rm-btn rm-btn-primary" form="{{ $modalExportacion }}-form"
                        :disabled="cargandoExportacion || descargandoExportacion || !datosExportacion || partesExportacion.length === 0" :aria-busy="descargandoExportacion">
                        <i class="ph-bold ph-download-simple" aria-hidden="true"></i>
                        <span x-text="descargandoExportacion ? 'Preparando archivo…' : 'Descargar ' + formatoExportacion.toUpperCase()">Descargar PDF</span>
                    </button>
                </x-slot:footer>
            </x-ui.modal-livewire>
        </template>
    </div>
@endif
