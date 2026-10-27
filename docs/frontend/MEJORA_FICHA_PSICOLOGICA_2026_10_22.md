# Ficha psicológica del residente — mejora de presentación

Fecha: 22/10/2026. Superficie: `/admin/psicologia/residente/{residente}/ficha`.

> **Estado de integración comprobado el 25/10/2026:** este informe conserva la entrega del 22/10. La ficha activa del árbol auditado no incluye los parciales `ficha-psicologica/cabecera` y `resumen` ni utiliza la raíz `.rm-resident-file`. No acredita que la mejora siga conectada hoy. Véanse H21/H32 de la [auditoría actual](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md). Los resultados y capturas que siguen son históricos; la reintegración y nueva QA están pendientes.

## Flujo y jerarquía

La ficha existente tenía ocho pestañas en una fila desplazable, texto pequeño
y las últimas valoraciones después del histórico. La cabecera mezclaba nombres
técnicos con la identificación del residente. La nueva distribución mantiene
el mismo componente Livewire, sus formularios y sus rutas.

1. Ruta de navegación y título de la ficha, con regreso al directorio.
2. Identidad del residente: iniciales, nombre completo, edad, ubicación y estado
   registrado. Acciones «Ver resultados cognitivos» y, cuando el backend lo
   autoriza, «Valoración cognitiva y funcional».
3. Ocho secciones visibles mediante botones con nombre, icono y estado pulsado.
   Se reutiliza `cambiarTab`; los destinos internos y el alias `sesiones`
   permanecen compatibles con la navegación existente.
4. Cuatro tarjetas de registros, con enlace a la sección del mismo residente:
   aplicaciones, valoraciones psicológicas, autonomía y atenciones de Psicología.
5. Últimos registros cognitivo y afectivo, con fecha y profesional. Una aplicación
   observacional conserva la etiqueta «Observación clínica». Un valor NULL
   muestra «Sin puntuación registrada».
6. Histórico y controles existentes, después de los registros recientes.

Los contadores proceden de las consultas existentes. «Aplicaciones registradas»
evita llamar pruebas instrumentales a todas las aplicaciones; «Atenciones
registradas» evita confundir el recuento de atenciones con sesiones confirmadas.
La cabecera no presenta códigos internos ni documento de identidad.

## Implementación y reutilización

| Archivo | Cambio |
|---|---|
| `app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/ficha-psicologica-residente.blade.php` | Integra cabecera y resumen; conserva los formularios y gráficos existentes; simplifica títulos y mensajes técnicos. |
| `app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/ficha-psicologica/cabecera.blade.php` | Contexto, acciones y navegación accesible. |
| `app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/ficha-psicologica/resumen.blade.php` | Tarjetas enlazadas y últimas valoraciones con autoría y fecha. |
| `resources/frontend/styles/design-system/patterns/resident-file.css` | Composición con tokens de color, tipografía, espaciado y radios existentes; adaptación según el ancho disponible. |
| `resources/frontend/styles/design-system/index.css` | Importa el patrón de ficha. Se preservó la importación concurrente de `expert-resident-workspace.css`. |
| `resources/frontend/styles/design-system/patterns/system-density.css` | Excluye esta composición del ajuste heredado que reducía controles a 40 px; la ficha aplica su mínimo de 44 px. |
| `tests/Feature/FichaPsicologicaResidenteTest.php` | Comprueba títulos, prioridad de información, rutas de tarjetas, estados vacíos, observaciones sin puntuación, nombres largos, fecha y autoría; conserva las pruebas de captura y acceso existentes. |
| `tests/Feature/NavegacionSistemaExpertoTest.php` | Alinea la expectativa del título con el listado vigente «Sistema experto por residente», conservando comprobaciones de búsqueda, permisos y enlaces. |

Se reutilizan `rm-resident-banner`, `rm-tabs`, `rm-tab-item`, `ui.metric-card`,
`ui.section-card` y `rm-btn`. Las pestañas usan `aria-pressed` y `aria-controls`;
son botones de navegación ordinarios, sin declarar un tablist que exigiera otro
contrato de teclado. Las confirmaciones tienen `role="status"` y cierre con
nombre accesible. Se preservan permisos, persistencia y cálculos del backend.

## Verificación técnica

```powershell
php artisan test --filter='FichaPsicologicaResidenteTest|NavegacionSistemaExpertoTest' --compact
powershell -NoProfile -File scripts/probar-postgres.ps1 -Filtro FichaPsicologicaResidenteTest -Informe storage/logs/ficha-psicologica-pg.junit.xml -Log storage/logs/ficha-psicologica-pg.log
node --test tests/Frontend/espacios-layout.test.js tests/Frontend/controles-institucionales.test.js
npm run build
```

- SQLite desechable, ejecución final: **17 pruebas, 132 aserciones,
  satisfactorias**, incluyendo ficha y navegación. La primera ejecución detectó
  una expectativa de título desactualizada en `NavegacionSistemaExpertoTest:56`.
  Se corrigió esa expectativa para comprobar el título real del listado;
  no se alteraron producto, permisos ni invariantes para obtener el resultado.
- PostgreSQL desechable: **12 pruebas de ficha, 83 aserciones**, más **5 pruebas
  de navegación, 49 aserciones**, satisfactorias. Total: **17 pruebas, 132
  aserciones** en dos ejecuciones. Clústeres temporales detenidos al terminar;
  sin cambios de datos institucionales. Evidencia adicional de navegación:
  `storage/logs/ficha-psicologica-navegacion-pg.junit.xml` y su log asociado.
- Frontend: **9 pruebas satisfactorias**.
- Build Vite: satisfactorio, 4,50 segundos en la última ejecución.
- Servidor Vite local: respuesta HTTP 200 de la entrada CSS; contiene los estilos
  de identidad y navegación de la ficha, sin requerir reinicio del servidor.

## Verificación visual acotada

Work rechazó el acceso al navegador de la usuaria. La verificación se realizó
en Chrome headless con perfil temporal sobre HTML real renderizado por Laravel
y datos sintéticos de PHPUnit, usando el CSS compilado. No se accedió a la
sesión institucional ni se enviaron formularios desde esa copia estática.

Se generaron capturas en **1440, 1280, 1024, 768 y 390 px**, en claro y oscuro,
para expediente vacío y expediente con registros y nombre largo. No se detectó
desbordamiento horizontal de página, componente o navegación, ni literales
`null`, `NaN`, `undefined`, `Infinity` o `[object Object]` visibles. Los controles
principales comprobados miden al menos 44 px de alto. El foco de navegación
es visible con contorno de 2 px. La autoría y la ausencia de puntuación siguen
presentes con datos.

Capturas y mediciones locales: `%TEMP%/remembermind-ficha-psicologia-20261022/`.
Resumen: `qa-resultados.json`. Ensayo visual local:
`storage/app/private/qa-ficha-psicologica.mjs`. Logs de pruebas y build en
`storage/logs/ficha-psicologica-*`; ambos directorios privados están fuera de Git.

**Aceptación acotada:** presentación del resumen y adaptación comprobadas;
interacciones Livewire verificadas mediante pruebas de backend. La copia visual
estática no acredita interacción de formularios o actualización dinámica de
gráficos en la sesión real. Esos formularios y gráficos se conservaron y no se
declara una auditoría global de ellos. No se declara certificación WCAG a partir
de estas comprobaciones.

## Git y continuidad

Se preservó el árbol compartido, incluidos la vista principal y su prueba,
que ya existían localmente sin seguimiento antes de este cambio. Sin commit,
push, migraciones o despliegue. Se cerró la discrepancia del título del listado
con la corrección de su prueba y se conservó el resto del trabajo concurrente.
