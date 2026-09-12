import './utilities/bootstrap.js';

// Alpine lo proporciona Livewire; no iniciar una segunda instancia.

// GSAP
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);

// AOS
import AOS from 'aos';
import 'aos/dist/aos.css';

// Chart.js
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
Chart.register(ChartDataLabels);

// Asignar al objeto window para acceso global
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
window.Chart = Chart;
window.AOS = AOS;

// RememberMind Chart Design System - Imports
import {
    rmGetCss,
    rmIsDark,
    rmHexToRgba,
    rmChartPalette,
    rmChartPaletteAlpha,
    rmChartSemanticColors,
    rmGetOriginColor,
    rmGetOriginLabel,
    CLINICAL_ORIGINS_MAP,
    rmCreateGradient,
    rmCreateBarGradient,
    rmBaseChartOptions,
    rmDoughnutDefaults,
} from '../styles/design-system/charts/chart-theme.js';

import {
    rmDoughnutChartConfig,
    rmBarHorizontalChartConfig,
    rmAreaChartConfig,
    rmLineChartConfig,
    rmBarChartConfig,
    rmStackedBarChartConfig,
    rmPieChartConfig,
    rmRadarChartConfig,
    rmScatterChartConfig,
    rmGaugeConfig,
    rmSparklineChartConfig,
    rmMiniLineChartConfig,
} from '../styles/design-system/charts/chart-presets.js';

import {
    rmGetInstances,
    rmInitChart,
    rmDestroyChart,
    rmDestroyAllCharts,
    rmGetChart,
    rmHasChart,
    rmUpdateChartData,
    rmRefreshAllCharts,
    rmWatchLivewireData,
    rmOnThemeChange,
} from '../styles/design-system/charts/chart-livewire.js';

// Namespace único oficial: window.RMCharts
window.RMCharts = {
    // Gestor de instancias Map() único con clave y ciclo de vida estricto
    instances: rmGetInstances(),
    init: rmInitChart,
    destroy: rmDestroyChart,
    destroyAll: rmDestroyAllCharts,
    get: rmGetChart,
    has: rmHasChart,
    update: rmUpdateChartData,
    refreshAll: rmRefreshAllCharts,
    watchLivewire: rmWatchLivewireData,
    onThemeChange: rmOnThemeChange,

    // Utilidades de diseño y tokens
    getCss: rmGetCss,
    isDark: rmIsDark,
    hexToRgba: rmHexToRgba,
    palette: rmChartPalette,
    paletteAlpha: rmChartPaletteAlpha,
    semanticColors: rmChartSemanticColors,
    getOriginColor: rmGetOriginColor,
    getOriginLabel: rmGetOriginLabel,
    clinicalOrigins: CLINICAL_ORIGINS_MAP,
    createGradient: rmCreateGradient,
    createBarGradient: rmCreateBarGradient,
    baseOptions: rmBaseChartOptions,
    doughnutDefaults: rmDoughnutDefaults,

    // Presets canónicos
    presets: {
        doughnut: rmDoughnutChartConfig,
        barHorizontal: rmBarHorizontalChartConfig,
        area: rmAreaChartConfig,
        line: rmLineChartConfig,
        bar: rmBarChartConfig,
        stackedBar: rmStackedBarChartConfig,
        pie: rmPieChartConfig,
        radar: rmRadarChartConfig,
        scatter: rmScatterChartConfig,
        gauge: rmGaugeConfig,
        sparkline: rmSparklineChartConfig,
        miniLine: rmMiniLineChartConfig,
    },
};

import { iniciarEfectosAmbientales } from './components/efectos-ambientales.js';
iniciarEfectosAmbientales(AOS);

import redApoyoTree from './modules/red-apoyo-svg.js';
window.redApoyoTree = redApoyoTree;

// Tema institucional — Geriátrico Jardín de los Recuerdos
import './utilities/modo-oscuro.js';

import documentosAdulto from './modules/documentos-adulto.js';
window.documentosAdulto = documentosAdulto;
