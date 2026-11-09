<div class="rm-report-visuals" :aria-busy="cargandoGraficas">
    @unless($visorInicioGerencia ?? false)
        <div class="rm-report-visuals__toolbar">
            @include('reportes::paginas.gerencia.partials.selector-graficas')
            <p class="rm-help">El diseño se adapta a las categorías y unidades disponibles.</p>
        </div>
    @endunless
    <p class="rm-help" x-show="cargandoGraficas" role="status">Actualizando estadísticas…</p>
    <p class="rm-field-error" x-show="errorGraficas" x-text="errorGraficas" role="alert"></p>
    <button type="button" class="rm-btn rm-btn-secondary" x-show="errorGraficas" @click="reintentarGraficas()">Volver a intentar</button>
    <div class="rm-report-visuals__grid" x-show="!cargandoGraficas && !errorGraficas">
        <template x-for="grafica in graficasReporte" :key="grafica.id">
            <section class="rm-chart-card rm-report-chart" :aria-label="grafica.resumen?.titulo ?? grafica.titulo" :data-unidad="grafica.unidad">
                @if($visorInicioGerencia ?? false)
                <header class="rm-manager-home__chart-header">
                    <i class="ph-bold" :class="grafica.resumen.icono" aria-hidden="true"></i>
                    <h3 x-text="grafica.resumen.titulo"></h3>
                </header>
                <div class="rm-manager-home__chart-count" x-show="grafica.resumen.solo_cifra">
                    <template x-if="grafica.resumen.valor !== null">
                        <div>
                            <strong x-text="formatoGrafica(grafica.resumen.valor)"></strong>
                            <span x-text="grafica.resumen.unidad"></span>
                        </div>
                    </template>
                    <p class="rm-help" x-show="grafica.resumen.valor === null">Datos no disponibles</p>
                    <p class="rm-help" x-show="grafica.unidad === 'personas' && grafica.resumen.valor !== null">Con estado laboral activo.</p>
                    <p class="rm-help" x-show="grafica.unidad === 'avisos' && grafica.resumen.valor === 0">Sin avisos documentales en tu ámbito.</p>
                    <p class="rm-help" x-show="grafica.unidad === 'camas' && grafica.resumen.valor > 0 && !grafica.completa">Desglose de ocupación no disponible.</p>
                </div>
                @else
                <header class="rm-chart-header"><div class="rm-chart-heading"><h5 class="rm-chart-title" x-text="grafica.titulo"></h5><p class="rm-chart-subtitle" x-text="grafica.unidad"></p></div></header>
                @endif
                <div class="rm-report-chart__body" x-show="!grafica.resumen?.solo_cifra">
                <div class="rm-report-chart__plot" x-init="$nextTick(() => dibujarGraficas())">
                    <canvas :id="claveGrafica(grafica)" aria-hidden="true" x-show="!(grafica.composicion && grafica.total === 0)"></canvas>
                    <p class="rm-report-chart__empty" x-show="grafica.composicion && grafica.total === 0">Sin registros en estas categorías</p>
                    @if($visorInicioGerencia ?? false)
                    <div class="rm-manager-home__chart-center" x-show="grafica.resumen.porcentaje != null && tipoParaGrafica(grafica) === 'dona'">
                        <strong x-text="formatoGrafica(grafica.resumen.porcentaje) + ' %'"></strong>
                        <span>ocupación</span>
                    </div>
                    @endif
                </div>
                <ol class="rm-report-chart__legend">
                    <template x-for="(punto, indice) in grafica.serie" :key="indice">
                        <li><span class="rm-report-chart__marker" :style="{ background: colorGrafica(indice, grafica) }" x-text="indice + 1" aria-hidden="true"></span><span x-text="punto.etiqueta"></span><strong x-text="punto.valor === null ? 'No disponible' : punto.valor + porcentajeGrafica(punto, grafica)"></strong></li>
                    </template>
                </ol>
                </div>
                @if($visorInicioGerencia ?? false)
                <p class="rm-help" x-show="!grafica.resumen.solo_cifra" x-text="formatoGrafica(grafica.resumen.valor) + ' ' + grafica.resumen.unidad"></p>
                @else
                <p class="rm-help" x-text="grafica.nota"></p>
                @endif
                <p class="rm-help" x-show="!grafica.resumen?.solo_cifra && tipoGraficas !== 'auto' && !grafica.tipos.includes(tipoGraficas)">Para estos datos se mantiene la comparación en barras.</p>
                <p class="rm-help" x-show="!grafica.resumen?.solo_cifra && tipoParaGrafica(grafica) === 'radar'" x-text="'Escala: 0 a ' + grafica.maximo + ' ' + grafica.unidad + '. Los números de los ejes corresponden a la leyenda.'"></p>
            </section>
        </template>
    </div>
</div>
