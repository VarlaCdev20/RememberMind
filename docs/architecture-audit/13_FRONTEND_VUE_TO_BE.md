# Vue + TypeScript + Inertia: propuesta

## Evaluación

Sí es una opción sostenible para una interfaz longitudinal con gráficos, filtros y componentes de dominio, conservando rutas, sesión, validación y autorización en Laravel. No exige SPA independiente, API pública, Vue Router, doble login o segundo repositorio. Su costo real es reescribir la presentación Livewire/Blade y aprender/mantener contratos TypeScript. Conservar Livewire sigue siendo una alternativa técnicamente válida si el calendario no permite ese costo.

Inertia 3 tiene adaptador oficial Vue y Laravel; su guía exige PHP 8.2+ y Laravel 11+, por lo que los mínimos declarados de este proyecto superan esos requisitos. Esto no verifica todavía todas las dependencias instaladas ni su combinación con Vite 8. [Guía oficial Inertia 3](https://inertiajs.com/docs/v3/getting-started/upgrade-guide). Laravel 13 se mantiene: no hay causa arquitectónica que justifique sustituirlo. [Laravel 13](https://laravel.com/framework/docs/13.x/releases).

shadcn-vue ofrece integración Laravel; usar sus componentes como base seleccionada, no instalar otro starter kit sobre la aplicación existente. Verificar y fijar versiones, requisitos Tailwind y accesibilidad de los componentes elegidos en un spike futuro. No asumir que la receta actual puede aplicarse sin cambios al Tailwind 3 existente. Si exige Tailwind 4, realizar ese cambio por separado o posponer shadcn-vue; nunca reconfigurar todos los tokens automáticamente. [Instalación oficial](https://shadcn-vue.com/docs/installation/laravel).

## Estructura inicial

```text
resources/js/
  app.ts
  Pages/
    Dashboard/
    Residents/
    Admissions/
    Clinical/
    Medication/
    Assessment/
    Care/
    Social/
    Safety/
    Expert/
    Administration/
  Components/
    ui/
    resident/
    clinical/
    assessment/
    charts/
    expert/
  Layouts/
    AppLayout.vue
    GuestLayout.vue
  Composables/
  Types/
  Utils/
```

Crear directorios cuando haya al menos un archivo usado. ClinicalLayout solo si agrega comportamiento propio; un contexto de expediente normalmente basta como componente dentro de AppLayout. No introducir Pinia por defecto: props Inertia, estado local y formularios cubren el inicio. Composables para filtros/paginación/interacción; no para reglas de prescripción o interpretación clínica.

## Contrato servidor–cliente

Resources/arrays explícitos, sin serializar el modelo completo. ResidentSummary incluye identificador público, nombre, estancia, ubicación y capacidades por sección; no incluye documentos, 2FA ni observaciones privadas por defecto. Consultas de cada sección reciben actor, residente y filtros validados. Filtros/paginación en URL, fechas ISO y unidades explícitas. Errores de Laravel asociados al formulario; normalizar errores de infraestructura a mensajes sin SQL/stack.

shared props mínimas: nombre público del usuario, contexto visual y capacidades de navegación. No compartir historia clínica ni todo el objeto User. Políticas y scopes se aplican también en cargas parciales/diferidas, descargas y búsqueda. Desactivar precarga indiscriminada de datos sensibles y revisar historial del navegador/caché al cerrar sesión.

## Sistema de diseño

ui: AppButton, AppCard, AppTable, AppModal, AppBadge, AppAlert y AppEmptyState solo cuando encapsulen un contrato de producto útil. Evitar envolver cada primitiva de shadcn sin valor. Tokens semánticos actuales se mapean a variables de la nueva base; temas usan class dark. Estados de riesgo tienen texto/icono y color semántico.

Dominio: ResidentHeader, ResidentSummaryCard, ClinicalTimeline, RiskIndicator, AssessmentCard, AlertCard y TrendChart. Chart.js inicialmente: está instalado y cubre tendencias; ECharts solo si un prototipo demuestra una necesidad concreta no resuelta. TrendChart maneja mount/unmount, resize, datos vacíos, serie con fechas y tabla accesible. No introducir ambas librerías durante la migración.

## Restricciones iniciales

Sin SSR necesario para la app autenticada; evaluar solo si hay un requisito real. Sin microfrontends, sin motor experto en navegador, sin cálculo clínico exclusivo en TS. Ningún cambio optimista da por administrada una dosis antes de respuesta confirmada. La interfaz muestra conflicto de versión/doble envío con instrucciones claras.
