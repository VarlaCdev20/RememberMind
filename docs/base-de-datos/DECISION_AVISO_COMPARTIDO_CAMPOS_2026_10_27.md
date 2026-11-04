---
title: "Aviso compartido de planificación sobre el evento existente"
status: ADOPTED_INSTALLED_LOCAL
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [planning_notice_scope, planning_notice_columns]
---

# Decisiones y sucesión

La propietaria rechazó una tabla nueva: «NO APRUEBO LA CREACION DE NINGUNA
TABLA MAS, PUEDES AMPLIAR CAMPOS EN TABLAS NO CREAR OTRAS». Después respondió
**«Aviso compartido por cambio»** a esta alternativa concreta: ampliar
`eventos_asignacion_personal`, conservar un único aviso por cambio para el
trabajador original y una bandeja compartida de Administración, con estado y
reintentos, sin entrega ni lectura individual por administrador.

Esta decisión adopta la ampliación descrita en el [diccionario43](../especificacion-funcional/vistas/gerencia/43-AVISOS-SIN-TABLAS-NUEVAS.md)
y sustituye su pregunta pendiente. La [propuesta42](../especificacion-funcional/vistas/gerencia/42-PROPUESTA-AVISOS-PLANIFICACION.md)
sigue rechazada y nunca instalada. La [prohibición de nuevas tablas](DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md)
permanece vigente; no queda un cupo disponible hasta110.

## Extensión específica

Seis columnas nullable en el evento existente, sin otra relación:

| Columna | Tipo | Regla |
|---|---|---|
| cod_usuario_afectado_aviso | VARCHAR(20) | FK restrictiva a usuarios.cod_usuario; cuenta del trabajador conservada en el INSERT, inmutable |
| estado_aviso_planificacion | VARCHAR(16) | PENDIENTE, DISPONIBLE o FALLIDO; NULL indica que no se conservó seguimiento |
| numero_intentos_aviso | INTEGER | 0 en PENDIENTE; positivo en DISPONIBLE/FALLIDO |
| fecha_hora_ultimo_intento_aviso | TIMESTAMP | Presente en DISPONIBLE/FALLIDO, no anterior al registro original |
| fecha_hora_disponible_aviso | TIMESTAMP | Solo DISPONIBLE; igual al instante del intento que lo publicó |
| categoria_fallo_aviso | VARCHAR(40) | Solo FALLIDO: FALLO_DISPONIBILIZACION; sin excepciones o SQL |

NULL permitido en todas ellas no significa combinaciones libres: los CHECK
exigen el grupo completo desconocido o un estado coherente. Dos índices:
`(cod_usuario_afectado_aviso, fecha_hora_registro)` y
`(estado_aviso_planificacion, fecha_hora_registro)`. Se conserva la PK original.
No se cambian cardinalidades ni las FK preexistentes.

Los atributos del acto original permanecen inmutables. La nueva función de
trigger solo permite evolucionar metadatos técnicos. No modifica la función
compartida de historia ni la protección de versiones de vínculos familiares.
Un evento desconocido no se completa retrospectivamente; DISPONIBLE es terminal.

DDL exacto, fuera del descubrimiento automático de migraciones:
`database/propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php` (artefacto local del corte, no incluido en esta rama).
El manifiesto de instalación conserva su SHA-256 y resultados de pruebas.

## Alcances de consulta y recuperación

No se crean permisos ni se conceden permisos nuevos:

- Administración/Superadministración: bandeja compartida disponible con el
  permiso existente `asignaciones_personal.ver` y cuenta activa.
- Trabajador: solo sus avisos disponibles, con cuenta y personal activos,
  rol laboral y coincidencia entre cuenta original y personal de la asignación.
  Esta lectura propia forma parte del alcance expresamente adoptado23A.
- Gerencia/Superadministración: recuperación de PENDIENTE/FALLIDO con
  `turnos.asignar`, rol vigente y fuera de previsualización.
- Administración: recuperación solo de sus propios actos con ese permiso.
  Poder leer la bandeja compartida no permite reintentar actos de otro emisor.
- Familiar: excluido. No se amplían competencias clínicas.

La consulta aplica el ámbito antes de paginar. El backend revalida cuenta,
rol, permiso y emisor después de adquirir el bloqueo del evento.

## Grano, historia e instalación

El evento determina una cuenta afectada singular y un estado actual singular
de publicación. No se introducen dependencias multivaluadas independientes.
No se afirma seguimiento individual de Administración ni historia de lecturas;
ese grano requeriría una relación distinta y está fuera de este alcance.
Activitylog conserva los actos de publicación/fallo, sin sustituir esa relación.

Inventario antes y después: **81 operativas +25 expertas =106 de negocio**,
más16 técnicas:122 físicas. Sin tablas nuevas, JSON, EAV ni listas en columnas.

La instalación local del27/10/2026 verificó respaldo, coincidencia del DDL
ensayado y contenido original idéntico dentro de una transacción.
La activación configura únicamente los nuevos eventos; no crea avisos de
historia desconocida. El [informe44](../especificacion-funcional/vistas/gerencia/44-INTEGRACION-AVISO-COMPARTIDO.md)
registra el estado realmente alcanzado.

Rollback: conserva filas y atributos anteriores; solo retira la ampliación
cuando los seis campos están NULL en todas las filas. Si hay seguimiento,
rechaza la reversión para preservar su historia. Deshabilitar la capacidad
detiene nuevas publicaciones sin borrar los avisos conservados.
