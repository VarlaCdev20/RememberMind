import './utilities/bootstrap.js';
import './modules/operaciones-interactivas.js';

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
    rmChartNumber,
    rmChartColor,
    rmChartSemanticColors,
    rmGetOriginColor,
    rmGetOriginLabel,
    CLINICAL_ORIGINS_MAP,
    rmCreateGradient,
    rmCreateBarGradient,
    rmBaseChartOptions,
    rmDoughnutDefaults,
    rmInstallGlobalChartTheme,
} from '../styles/design-system/charts/chart-theme.js';

rmInstallGlobalChartTheme(Chart);

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
    rmSemanticChartConfig,
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
import { rmObserveChartCards } from '../styles/design-system/charts/chart-motion.js';
import { rmInstallChartInteractions } from '../styles/design-system/charts/chart-interactions.js';

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
    number: rmChartNumber,
    color: rmChartColor,
    semanticColors: rmChartSemanticColors,
    getOriginColor: rmGetOriginColor,
    getOriginLabel: rmGetOriginLabel,
    clinicalOrigins: CLINICAL_ORIGINS_MAP,
    createGradient: rmCreateGradient,
    createBarGradient: rmCreateBarGradient,
    baseOptions: rmBaseChartOptions,
    doughnutDefaults: rmDoughnutDefaults,
    installGlobalTheme: rmInstallGlobalChartTheme,

    // Presets canónicos
    presets: {
        semantic: rmSemanticChartConfig,
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', rmInstallChartInteractions, { once: true });
    document.addEventListener('DOMContentLoaded', rmObserveChartCards, { once: true });
} else {
    rmInstallChartInteractions();
    rmObserveChartCards();
}

import { iniciarEfectosAmbientales } from './components/efectos-ambientales.js';
iniciarEfectosAmbientales(AOS);

import redApoyoTree from './modules/red-apoyo-svg.js';
window.redApoyoTree = redApoyoTree;

// Tema institucional — Geriátrico Jardín de los Recuerdos
import './utilities/modo-oscuro.js';

import documentosAdulto from './modules/documentos-adulto.js';
window.documentosAdulto = documentosAdulto;

import './modules/signos-vitales-registro.js';
import './modules/dolor-registro.js';
import './modules/ingesta-registro.js';
import './modules/clinical-form-feedback.js';
import './modules/filter-selection.js';
import './modules/controles-institucionales.js';
import './modules/admisiones-interactivas.js';
import './modules/residentes-interactivos.js';
import './modules/habitaciones-interactivas.js';

import './modules/auth-login-parallax.js';
import './modules/app-depth-motion.js';

import './modules/hidratacion-registro.js';

import './modules/eliminacion-registro.js';

import './modules/movilidad-registro.js';
