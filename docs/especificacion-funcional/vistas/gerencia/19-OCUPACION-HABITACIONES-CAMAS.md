---
title: "Gerencia: Ocupación, habitaciones y camas"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_OCCUPANCY_REVIEW
runtime_verified: false
---

# Ocupación de Gerencia

## 1. Objetivo y límites

La decisión **24A y B** adopta mapa y tabla sobre las mismas fuentes; no fija presentación inicial. Se conserva la preferencia de sesión si existe, sin tratarla como decisión institucional. «Mapa» significa agrupación por piso/habitación/cama; no supone disponer de un plano arquitectónico real.

Gerencia consulta ocupación y disponibilidad institucional. Registrar admisión, trasladar o dar de baja no se autoriza mediante esta documentación. Reutilizar las operaciones existentes únicamente con permiso, Policy y responsabilidad vigentes. [Reglas de ingreso](../../../modulos/ingreso-institucional/README.md), [Residentes/alojamiento](../../../modulos/residentes-alojamiento/README.md) y [baseline](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) mantienen su autoridad.

## 2. Composición y controles

| Zona | Distribución y contenido | Control |
|---|---|---|
| Cabecera | «Ocupación», explicación, fecha/hora de consulta legible | Volver al Inicio / Ver residentes |
| Indicadores | Camas registradas, ocupadas, disponibles y no habilitadas en el alcance consultado | Mostrar unidad y alcance, no mezclar camas con habitaciones |
| Filtros | Piso, habitación, disponibilidad, estado físico y búsqueda | Aplicar / Limpiar, filtros visibles también en tabla |
| Selector | Mapa / Tabla | Misma búsqueda, actualización y fuente, `aria-pressed` |
| Mapa | Grupos por piso; habitación con denominación física y camas | Cama seleccionable por teclado; estado textual además de color |
| Tabla | Piso, Habitación, Cama, Situación, Ocupante autorizado y Acción | Ordenación/paginación coherentes, Ver detalle |
| Detalle lateral | Ubicación, situación, condiciones físicas, asignación vigente e historia permitida | Cerrar, Ver ficha si autorizado; retorno de foco |
| Resumen auxiliar | Distribución de camas por situación | Barras con cantidades y tabla alternativa; opcional, sin duplicar paneles decorativos |

La leyenda usa disponible/salvia, ocupada/neutro y no habilitada/ámbar según tokens existentes; no implica severidad clínica. Un estado inválido o vínculo contradictorio se señala como incidencia de datos, no como disponibilidad favorable. No usar rojo para toda cama ocupada.

No se muestra porcentaje cognitivo, diagnóstico ni motivo médico de estancia en el mapa. El ocupante se identifica solo con lectura de residentes y Policy aplicables; permiso de habitaciones por sí solo no habilita nombres, fotos o historial nominal.

## 3. Datos nominales y significado

| ID | Dato | Fuente/interpretación |
|---|---|---|
| OC01 | Piso y denominación de habitación | habitaciones.piso, codigo/nombre; la denominación física no es la PK cod_habitacion |
| OC02 | Cama y tipo | camas.codigo/tipo; etiqueta física legible, PK interna |
| OC03 | Estado físico de habitación | habitaciones.estado; puede impedir disponibilidad aunque la cama esté vacía |
| OC04 | Estado físico de cama | camas.estado; separado de ocupación, conservar valor de origen |
| OC05 | Situación de ocupación | ocupaciones_cama vigentes y regla canónica de disponibilidad; no simple equivalencia de etiquetas |
| OC06 | Ocupante autorizado | ocupaciones_cama.cod_residente → residentes; comprobar ámbito antes de enviar identidad al frontend |
| OC07 | Fecha/hora de asignación | ocupaciones_cama.fecha_hora_asignacion, formato local legible |
| OC08 | Admisión de origen | ocupaciones_cama.cod_admision → admisiones, relación consistente con residente; no exponer código como explicación |
| OC09 | Historia de alojamiento | asignación, liberación, motivo y autor conservados; distinguir admisión inicial de traslado |
| OC10 | Capacidad declarada y camas registradas | habitaciones.capacidad frente al número real de camas; mostrar diferencia, no crear camas para igualar ambos |

Una cama **disponible** requiere habilitación física según [FormalizarAdmision](../../../../app/Modulos/Admisiones/Aplicacion/Acciones/FormalizarAdmision.php) y ausencia de ocupación activa. Una cama vacía no habilitada no entra en disponibles. Una habitación sin camas permanece visible como inventario incompleto, no como una plaza libre ficticia.

El total físico se divide en situaciones excluyentes solamente si la integridad está verificada. Duplicaciones activas o vínculos huérfanos requieren aviso; no redondear o ajustar conteos para esconderlos. Si se presenta tasa, definir denominador explícito (todas las camas o habilitadas), período y fuente; no introducir una tasa por defecto en esta especificación.

## 4. Acciones y continuidad

La consulta no requiere formulario de nueva captura. Filtros y detalle son suficientes para la función gerencial aprobada. «Ver ficha», «Ver historial» y «Volver» requieren lectura contextual vigente. Refresh manual actualiza consulta actual, sin aparentar una instantánea histórica.

Si el actor posee por otra autorización el permiso `ocupaciones_cama.gestionar`, el traslado utiliza [CambiarAlojamientoResidente](../../../../app/Modulos/Admisiones/Aplicacion/Acciones/CambiarAlojamientoResidente.php): residente y admisión activa válidos, ocupación anterior esperada, destino disponible al confirmar, fecha coherente, transacción y bloqueos. No ofrecer esta mutación a Gerencia solo por esta ficha ni recrearla en el mapa.

No «liberar» una cama ocupada cambiando su etiqueta física. No deshabilitar habitación/cama ocupada sin resolver la operación autorizada. Un conflicto concurrente conserva el alojamiento anterior y explica que debe volver a elegir destino; no anuncia guardado.

## 5. Fuentes existentes y revisión estática

- [MapaHabitacionesService](../../../../app/Modulos/Institucion/Aplicacion/Consultas/MapaHabitacionesService.php): autorización física, identidad condicionada, resumen por situaciones, pisos, mapa/tabla y detalle. Ya reutiliza el filtro canónico de disponibilidad.
- [HabitacionPolicy](../../../../app/Modulos/Institucion/Presentacion/Policies/HabitacionPolicy.php): crear/editar requieren permisos específicos; lectura no implica esas operaciones.
- [OcupacionCama](../../../../app/Models/OcupacionCama.php), [Cama](../../../../app/Models/Cama.php) y [Habitacion](../../../../app/Models/Habitacion.php): entidades distintas; no fusionarlas por conveniencia visual.
- [Rutas de Institucion](../../../../app/Modulos/Institucion/Presentacion/Rutas/web.php) y [rutas raíz](../../../../routes/web.php): integrar destino gerencial sin el 403 H04 ni ampliar el dashboard administrativo completo.

Brechas para implementar/verificar: diferencias de estados ACTIVA/ACTIVO y DISPONIBLE entre consumidores; DEC-OPEN-001 no se resuelve renombrando catálogos aquí. El servicio etiqueta cada evento del historial como admisión formal aunque el detalle actual distingue un traslado por ocupación anterior: comprobar origen evento por evento antes de mostrar la historia como exacta. El historial se limita actualmente a doce filas: informar límite o paginar; no llamarlo completo sin navegación adicional. La autorización global de directorio no sustituye el ámbito de cada identidad histórica.

## 6. Diseño reutilizable y accesibilidad

Mismo shell, filtros, tarjetas de métricas, botones, tabla y drawer institucionales que las otras vistas; [metric-card](../../../../resources/views/components/ui/metric-card.blade.php), [drawer-livewire](../../../../resources/views/components/ui/drawer-livewire.blade.php) y [empty-state](../../../../resources/views/components/ui/empty-state.blade.php). Un mapa no debe esconder el nombre y estado detrás de hover. Botón de cama con nombre accesible, estado y selección; Escape cierra detalle, foco vuelve al origen. Especificar nombre del residente únicamente si autorizado.

Usar texto marrón/gris contrastado, superficies neutras, salvia y ámbar del Design System; sin copiar colores o marca generados en las imágenes de propuesta. A 390 px grupos verticales y drawer de ancho disponible; conservar tabla alternativa. A 768–1440 px rejilla adaptable al ancho útil, no al ancho total que incluye sidebar. Respetar movimiento reducido, carga, vacío, error y permiso revocado. No atribuir QA responsive/contraste a esta lectura estática.

## 7. Estado de entrega

Diseño y contrato de consulta documentados; no se han creado rutas, permisos, columnas o formularios productivos en esta tarea. Los [24 casos conjuntos](20-ACEPTACION-RESIDENTES-OCUPACION.md) son criterios futuros, no resultados ejecutados.

**Implementación posterior del 26/10:** el [hito H04](34-CORRECCION-ACCESO-OCUPACION-H04.md) corrige la ruta existente, conserva navegación/filtros y evita enlaces administrativos no autorizados. 56 pruebas PHP/361 aserciones, dos JS y build correctos. No altera datos/permisos/BDD ni cierra las brechas de historia/estados o QA visual completa.
