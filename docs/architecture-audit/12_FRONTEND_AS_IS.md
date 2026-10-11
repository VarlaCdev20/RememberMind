# Frontend actual

## Diagnóstico

312 archivos en resources/views, 5 JavaScript y 8 hojas de sistema de diseño importadas desde app.css. La cantidad incluye PDFs, correo, componentes, páginas y un .bak: no son 312 pantallas. Existen Blade convencional, clases Livewire y archivos de componentes con prefijo ⚡. No declarar estos últimos duplicados solo por su nombre: verificar registro/uso antes de retirar.

| Área | Evidencia | Valor / problema | Decisión |
|---|---|---|---|
| Layouts | layouts/app, guest, sistema y navigation-menu | Shells alternativos y navegación distribuida | Un AppLayout, GuestLayout; transición conserva shells separados por ruta |
| Usuarios | usuarios-panel.blade.php 3207 líneas | Formularios, listados, detalles y exportes en una vista | Descomponer por caso de uso, no portar línea a línea |
| Personal | panel 1626 líneas y formulario parcial 1214 | Wizard y reglas visuales extensas | Separar persona, vínculo laboral y acceso |
| Áreas | panel 1511 líneas | Vista extensa para catálogo/operación | Conservar requisitos; componentes de tabla/formulario |
| Expediente | show, carpetas y parciales; fichas médica/enfermería | Reutilización parcial, múltiples experiencias para mismos hechos | Un ResidentHeader y secciones por permiso |
| CSS | design-of-system y tailwind.config.js | Tokens semánticos, tema oscuro, componentes | Conservar contrato semántico; auditar estilos no usados antes de simplificar |
| JS global | app.js | GSAP, AOS y Chart.js en window; animación requestAnimationFrame continua | Carga por página; lifecycle y movimiento reducido |
| Alpine | app.js no inicia Alpine porque lo aporta Livewire | Evita doble inicialización; comentario aún dice Livewire v3 | Conservar el principio durante convivencia |
| Red de apoyo | red-apoyo-svg.js y red-apoyo-three.js | SVG tiene import activo; Three requiere trazabilidad de uso | Preferir SVG accesible; candidato a retirar Three solo tras referencias/bundle |
| Gráficos | Chart.js + datalabels, código en vistas | Librería existente útil, integración acoplada a globals | Reutilizar Chart.js, reescribir wrapper Vue |
| PDFs/correo | múltiples layouts y plantillas Blade | Reutilizables fuera de Vue; variantes con guion/underscore | Consolidar tras comparar contenido y pruebas de exportación |
| Dependencia externa UI | layouts/sistema.blade.php:16 carga unpkg para iconos | Disponibilidad/CSP/versionado externo | Empaquetar iconos fijados en fase frontend |
| Artefactos | show.blade.php.bak; enfermeria${view}.blade.php | Backup y posible salida de generador | No eliminar ahora; verificar referencias y mover/retirar después |

## Acoplamiento y duplicación

Livewire controla datos y operaciones; Alpine controla paneles/modales y JS global controla gráficos. Migrar requiere reemplazar eventos (dispatch/listeners), wire:model, validaciones y ciclo de navegación. Blade no puede copiarse como Vue conservando directivas. Las constantes clínicas y cálculos deben extraerse primero a backend, no a Composables.

SaludSignosPanel combina creación/edición/anulación, reportes, alertas y métricas. Los métodos con int $id contrastan con códigos string de los modelos. La migración debe arreglar ese contrato antes de tiparlo en TypeScript.

Hay componentes Blade base (button/card/modal/input) y parciales de expediente. Reutilizar su contrato visual, textos validados y tokens; no mantener dos bibliotecas completas permanentemente. PDF y correo sí seguirán siendo Blade después de migrar páginas web.

## Auditoría UX: lo comprobado y lo pendiente

Comprobado: estructura con filtros, modales, múltiples dashboards y tokens de tema; páginas muy extensas y globals compartidos. Pendiente de probar en navegador: foco, contraste renderizado, reflujo tablet, lectores de pantalla, charts destruidos al navegar y comportamiento con red lenta. No se asigna una puntuación WCAG sin esas pruebas. Las condiciones de aceptación están en 08/16.

## Criterio de eliminación

Un archivo se retira solo si no hay ruta, include/component, import, referencia dinámica o exportador que lo use, y su reemplazo tiene pruebas. «No aparece en rg» por sí solo no prueba que una vista dinámica no se resuelva en runtime. No cambiar landing pública y flujo clínico simultáneamente.
