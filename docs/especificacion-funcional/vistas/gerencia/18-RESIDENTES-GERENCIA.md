---
title: "Gerencia: Residentes y consulta administrativa"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_RESIDENT_DIRECTORY_REVIEW
runtime_verified: false
---

# Residentes de Gerencia

## 1. Alcance y decisiones adoptadas

La decisión **29B** establece tarjetas iniciales y tabla alternativa sobre los mismos residentes. La decisión **24A y B** establece mapa y tabla de ocupación; no selecciona cuál inicia. [Registro de decisiones](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md), [rol Gerencia](../../roles/gerencia.md) y [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) gobiernan el alcance.

Objetivo: consultar quién reside en la institución, su estado administrativo, alojamiento y contacto autorizado, y continuar al módulo pertinente. Gerencia no obtiene escritura clínica ni acceso a diagnósticos, tratamientos o resultados cognitivos por disponer del directorio. Esta especificación no amplía permisos.

El residente nace en la admisión formal con cama, después de una preadmisión aprobada. No habrá botón «Crear residente» que omita ese proceso. Las correcciones y cambios de estado pertenecen a sus operaciones autorizadas, no al cambio de vista.

## 2. Distribución y navegación

| Orden | Zona | Contenido y comportamiento |
|---|---|---|
| 1 | Cabecera | «Residentes», explicación administrativa breve; acceso a Ocupación si está autorizado |
| 2 | Resumen | Residentes del filtro, con alojamiento y sin alojamiento registrado; fecha de consulta y alcance del conteo visibles |
| 3 | Filtros | Nombre, estado administrativo, piso, habitación y con/sin alojamiento; Aplicar y Limpiar |
| 4 | Presentación | Tarjetas / Tabla; tarjetas al entrar por primera vez, conservando filtros y resultados al alternar |
| 5 | Resultados | Nombre, edad cuando se conoce, foto autorizada o iniciales, ubicación y estado textual; «Ver ficha» como acción principal |
| 6 | Pie | Total filtrado, página y tamaño de página; no anunciar el total institucional como si fuera el resultado filtrado |

La tabla usa columnas Residente, Edad, Ubicación, Estado administrativo y Acción. El contacto responsable se consulta en el detalle autorizado para evitar saturación. Los códigos técnicos no aparecen como nombres, subtítulos ni badges. La denominación física «Habitación 2 · Cama A» sí comunica ubicación; no equivale a exponer una PK `cod_*`.

Recorrido: Inicio → Residentes → buscar/filtrar → Ver ficha → Identidad administrativa / Alojamiento / Contactos autorizados / Historia administrativa → volver conservando búsqueda. Las secciones clínicas de otros roles no se incorporan por reutilizar su plantilla.

## 3. Datos visibles y fuentes

| ID | Dato | Fuente y límite |
|---|---|---|
| RG01 | Nombre completo | residentes.nombres y apellidos; no sustituir por código si falta, señalar dato incompleto |
| RG02 | Foto o iniciales | residentes.foto, acceso autorizado; fallback de iniciales, no fotografía inventada |
| RG03 | Edad | fecha_nacimiento y fecha de consulta; si falta, «Edad no registrada», nunca cero por defecto |
| RG04 | Estado administrativo | residentes.estado; etiqueta humana conservando el valor original; no equivale a estabilidad clínica |
| RG05 | Habitación y cama actuales | ocupaciones_cama activa → camas → habitaciones; no desde una ocupación histórica elegida por fecha |
| RG06 | Piso | habitaciones.piso; NULL: «Piso sin registrar», sin inventar planta |
| RG07 | Contacto responsable | residentes_contactos y contactos, vínculo vigente y principal, dentro de autorización; varios incompatibles requieren aclaración |
| RG08 | Registro de admisión | admisiones vinculadas; existencia de una fila no acredita por sí sola admisión formal activa íntegra |
| RG09 | Fecha de asignación de cama | ocupaciones_cama.fecha_hora_asignacion; no fecha de alta de usuario o nacimiento |
| RG10 | Conteos de consulta | misma consulta/filtros/scope; residentes distintos, sin multiplicar por contactos u ocupaciones inconsistentes |

No usar ausencia de documentos como «documentación completa», ni ausencia de evaluaciones como normalidad cognitiva. Documento de identidad y datos de contacto completos se limitan al detalle permitido y finalidad administrativa; no son requisitos de todas las tarjetas.

## 4. Botones y estados de interacción

| Acción | Efecto | Autorización y estado |
|---|---|---|
| Buscar / Aplicar | Consulta con filtros válidos, reinicia página | Cuenta activa, lectura y Policy/contexto; no modifica fuentes |
| Limpiar | Restablece filtros de esta consulta | No borra registros ni altera estado |
| Tarjetas / Tabla | Cambia presentación sobre el mismo conjunto | `aria-pressed`, foco visible; no duplicar consulta de negocio |
| Ver ficha | Abre residente seleccionado | Revalidar Policy por residente; no confiar en el identificador de Livewire |
| Ver alojamiento | Detalle del alojamiento e historia permitida | Lectura física y de identidad cuando corresponda; no traslada |
| Ver contacto | Abre contacto/vínculo autorizado | No concede autorización de información o salida por implicación |
| Ver ocupación | Abre [mapa/tabla](19-OCUPACION-HABITACIONES-CAMAS.md) | No dirigir al dashboard administrativo completo si Gerencia carece de su permiso |
| Volver | Recupera listado y filtros pertinentes | No recupera datos de otro residente desde un estado de componente anterior |

Estados: cargando con skeleton discreto; vacío institucional distinto de búsqueda sin resultados; error con reintento sin falsa confirmación; edad/ubicación no registradas con texto; permiso revocado con denegación segura. No conservar datos sensibles del detalle anterior después de una denegación.

## 5. Componentes existentes y diseño

Reutilizar [resident-card](../../../../resources/views/components/ui/resident-card.blade.php) con `variant="management"`, `primaryLabel="Ver ficha"`, nombre/edad/ubicación y destino autorizado. `code` sigue siendo identidad interna para enlaces y claves, sin imprimirse como contenido. [resident-card-identity](../../../../resources/views/components/ui/resident-card-identity.blade.php) ya representa nombre/foto/edad sin mostrar la PK. La modalidad `row` no es automáticamente una tabla semántica: la alternativa tabular debe tener encabezados y celdas accesibles.

Usar [card](../../../../resources/views/components/ui/card.blade.php), [status-badge](../../../../resources/views/components/ui/status-badge.blade.php), [empty-state](../../../../resources/views/components/ui/empty-state.blade.php) y overlays compartidos cuando correspondan, conservando sus API. No pasar `alertCount=0` para aparentar ausencia de alertas cuando no se consultó una fuente autorizada. No usar un contexto vacío que produzca «Sin cuidado programado» en una ficha gerencial.

La indicación de la propietaria del 26/10 es mejorar el diseño y reutilizar diseños/componentes institucionales. Mantener crema/capuchino/salvia, tipografía y tokens actuales, jerarquía compacta, acciones de 44 px, foto pequeña y superficies consistentes; sin nueva paleta ni branding generado. A 390 px una tarjeta por fila y filtros apilados; a 768 px ajustar columnas según ancho útil; en escritorio rejilla flexible. La tabla conserva etiquetas y desplazamiento local si es necesario, nunca desbordamiento de toda la página. QA pendiente en 1440, 1280, 1024, 768 y 390 px.

## 6. Código localizado y brechas

- [DirectorioResidentesService](../../../../app/Modulos/Residentes/Aplicacion/Consultas/DirectorioResidentesService.php): consulta, métricas, filtros, tarjetas/tabla y alojamiento; no hace autorización por sí mismo. El llamador debe autorizar antes de consultar y serializar.
- [ResidenteAdministrativoController](../../../../app/Modulos/Residentes/Presentacion/Http/Controllers/ResidenteAdministrativoController.php): detalle con fuentes por pestaña. Su existencia no autoriza todas sus secciones a Gerencia.
- [ResidentePolicy](../../../../app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php): comprobar acceso contextual también al detalle.
- El directorio elige un responsable mediante mínimo código de vínculo entre principales: evita duplicados de filas, pero no resuelve una contradicción institucional entre dos contactos principales. Presentar situación pendiente; no convertir el mínimo técnico en prioridad funcional.
- H04 de la [auditoría](../../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md): el acceso de Ocupación termina en un grupo administrativo y puede devolver 403. Requiere destino y middleware de lectura apropiados; no otorgar acceso global para evitar el error.

Estas fichas no corrigen esos archivos ni acreditan el recorrido en ejecución. La aceptación conjunta está en [20](20-ACEPTACION-RESIDENTES-OCUPACION.md).

**Avance posterior del 26/10:** [H04 corregido en código y pruebas](34-CORRECCION-ACCESO-OCUPACION-H04.md). La consulta de Ocupación ya abre directamente y mantiene filtros/detalle en la ruta autorizada. Esta corrección puntual no resuelve las otras brechas del directorio o la aceptación visual del rol.
