---
title: "Ficha clínica: colores unificados"
status: CURRENT
last_reviewed: 2026-10-14
verification_scope: FOCUSED_VISUAL_AND_FEATURE_TESTS
---

# Ficha clínica: colores unificados

La ficha compartida de todos los residentes utiliza los tokens del Design System para sus diez secciones, ventanas y menús. La unificación no depende del código ni de la identidad del residente.

## Implementado

- Superficies neutras cálidas; un acento de acción compartido en cabecera, pestañas, botones y títulos.
- Utilidades de color locales reemplazadas por tokens semánticos en las doce vistas de la ficha. Se conservan las decisiones y estados existentes.
- Rojo para peligro, ámbar para advertencias, verde para confirmaciones y azul para información. Texto pequeño con variantes de tinta contrastada.
- Categorías documentales sobre la misma superficie neutral; el tipo permanece identificado mediante texto e icono.
- Pestañas con estado accesible aria-current, foco visible y altura mínima de 44 px; se distribuyen en filas cuando falta espacio.
- Filtros de Medicación/Eventos y acciones documentales con reflow. Los hijos de las grillas pueden reducir su ancho sin ensanchar la página.
- Ajustes para modo oscuro. Los lectores longitudinales que aún transportan clases de color quedan normalizados dentro de la ficha, sin modificar sus datos.
- Los iconos PDF representan el formato con el acento compartido, sin rojo decorativo.
- Copy: «Código de residente» y «Medicación».

## Integración y seguridad

Se reutilizan clinical-detail.css y las vistas de Clinica; no se añade otro sistema de componentes ni una paleta por rol. Las rutas médicas y de Enfermería conservan la misma ficha. Sin cambios de base de datos, autorizaciones, prescripciones, autoría o clasificación clínica.

Rama: feature/clinica-ficha-colores, creada desde develop en el worktree adjunto. El checkout principal tiene trabajo previo y ediciones simultáneas de otro chat, confirmadas por la propietaria. Las correcciones finales de distribución y los aliases adicionales se conservan en esta rama; requieren integración coordinada con ese trabajo. No se sobrescribieron sus modificaciones ni se hizo push.

## Verificación

| Comprobación | Resultado |
| --- | --- |
| PHPUnit: MedicoFichaUnificadaTest, TabMedicacionEnfermeriaTest, FrontendArchitectureTest | 32 tests, 474 assertions, PASS en la rama |
| Mismas pruebas en checkout principal antes de separar los ajustes finales | 32 tests, 484 assertions, PASS |
| Render de las diez secciones con fixture sintética en SQLite de memoria | 1 test, 11 assertions, PASS |
| Build Vite de la rama, salida privada para preservar los assets compartidos | PASS |
| Chrome: diez secciones × cinco anchos × claro/oscuro | 100 casos, PASS para criterios de esta tarea |
| Foco, menú y apertura/cierre de Exportar en 1440/390, ambos temas | PASS |
| Literales visibles null, NaN, undefined, Infinity y [object Object] | Ninguna coincidencia |
| Errores JavaScript y desbordamiento de página en matriz final | 0 |
| Contraste mínimo acción / pestaña activa / muestras de estado | 5.49 / 5.04 / 4.58 |

Tras el último ajuste del formato PDF se revalidó Documentos en diez combinaciones de ancho/tema; las demás secciones no cambiaron.

Anchos: 1440, 1280, 1024, 768 y 390 px. Las vistas y snapshots proceden del componente Livewire real; el navegador utiliza el bundle real y Alpine para presentación, foco y overlays. Las llamadas de backend y permisos se verificaron con PHPUnit; esta prueba visual no acredita todas las mutaciones clínicas por HTTP ni una validación clínica integral.

Evidencia sintética local: C:/Users/CARLAENCINAS/.codex/ficha-colores-20261014/branch-final/resultados.json y la carpeta interacciones. Logs: worktree-tests-final.log, worktree-build.log y branch-export.log en el mismo directorio privado. No se exportaron datos reales.

## Pendientes anteriores, fuera de la unificación de colores

DocumentacionResidenteService incorpora documentos de ejemplo y fuerza cifras mínimas (24/12/5/4/3). Esto se observó en la revisión: los colores quedan verificados, pero esa sección no acredita información documental real. Corresponde corregir su fuente de datos en una tarea propia con las reglas y permisos documentales pertinentes.

Las tablas conservan los mecanismos de consulta existentes; esta tarea no certifica todos los estados de captura, impresión, exportación ni la integridad clínica global. Sin push o despliegue. Integración de la rama pendiente por edición concurrente del archivo compartido.
