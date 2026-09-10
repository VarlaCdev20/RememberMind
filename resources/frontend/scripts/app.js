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

import { iniciarEfectosAmbientales } from './components/efectos-ambientales.js';
iniciarEfectosAmbientales(AOS);

// Livewire navigate handled via wire:navigate

import redApoyoTree from './modules/red-apoyo-svg.js';
window.redApoyoTree = redApoyoTree;

// Tema institucional — Geriátrico Jardín de los Recuerdos
import './utilities/modo-oscuro.js';
