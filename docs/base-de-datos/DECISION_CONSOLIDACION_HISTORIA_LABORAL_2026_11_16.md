---
title: "Consolidación del historial laboral de personal"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-16
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [labor_history_consolidation]
verification_scope: SQLITE_POSTGRESQL_LOCAL_INSTALLATION
runtime_verified: true
---

# Unión del historial laboral de personal

La propietaria respondió «procede» después de la revisión de candidatas. Se aplica primero la unión recomendada de movimientos laborales y eventos de asignación. La alternativa cognitiva queda fuera de esta transición.

| Antes | Ahora |
|---|---|
| movimientos_laborales_personal | historial_laboral_personal, clase_acto LABORAL |
| eventos_asignacion_personal | historial_laboral_personal, clase_acto ASIGNACION |

Inventario instalado: **63 operativas + 22 expertas = 85 de negocio**, además de **16 técnicas: 101 tablas físicas**. Es una tabla menos que el corte anterior de 64 operativas/102 físicas. El núcleo sin extensiones explícitas de Gerencia continúa con 96 tablas físicas.

## Grano, 3FN y 4FN

Una fila conserva un solo acto institucional: un cambio de estado laboral o un evento de una asignación. La unión es disjunta; no se combinan los múltiples cambios laborales de una persona con sus múltiples eventos de asignación. Cada código determina los atributos de su acto, sin introducir colecciones independientes, productos entre relaciones, JSON ni EAV.

Los cambios laborales conservan cod_personal. Los eventos conservan cod_asignacion_personal y exigen cod_personal NULL: el titular se consulta a través de la asignación, evitando copiar un dato dependiente de ella. Los avisos permanecen como los metadatos escalares del mismo evento, sin crear destinatarios ni lecturas individuales. Este análisis se limita a la relación tipada fusionada y no certifica todas las dependencias del sistema.

PK física: cod_historial_laboral, varchar20. Los códigos originales se conservan en ella sin renumeración. Las columnas generadas cod_movimiento_laboral y cod_evento_asignacion permiten identificar y referenciar exclusivamente el subtipo correspondiente. Los modelos mantienen sus códigos y responsabilidades de dominio. No se fusionan los estados de cuenta y de personal.

El CHECK por clase conserva obligatoriedad, longitudes, estados y campos exclusivos. Las FK restringen el borrado de personal, usuario y asignación. La secuencia conserva UNIQUE por asignación/número y una FK compuesta al evento anterior de la misma asignación. El trigger sigue exigiendo el anterior inmediato: impide saltos, ramas y autoenlaces.

## Inmutabilidad y avisos

Los actos laborales son inmutables. En eventos de asignación se protege identidad, asignación, número, predecesor, tipo, estados, fechas, actor, motivo y cuenta afectada original.

Solo pueden evolucionar los cinco metadatos técnicos de un aviso conocido no terminal, conservando las restricciones existentes:

- PENDIENTE inicia con cero intentos;
- FALLIDO conserva instante e intento positivo y la categoría de fallo aprobada;
- DISPONIBLE conserva instante de disponibilidad y es terminal;
- no se reconstruye un aviso desconocido ni se reducen intentos;
- no se borran actos ordinariamente.

La auditoría corporativa sigue en Activitylog. Esta relación conserva historia de dominio, no incorpora un segundo registro técnico universal. Los permisos y la autorización de publicación/reintento no cambian: la consulta conserva sus filtros antes de paginar y los reintentos vuelven a autorizar al actor.

## Migración e integración

DDL explícito: database/propuestas/gerencia/2026_11_16_000004_consolidacion_historia_laboral.php. Depende de las extensiones35,37 y43; permanece fuera del descubrimiento automático del núcleo. En una instalación nueva autorizada se ejecuta después de ellas, mediante artisan migrate --path con esa ruta exacta.

Se reutiliza el soporte transaccional de las cuatro uniones previas, parametrizando el discriminador y las protecciones históricas. Cada copia se compara con EXCEPT en ambos sentidos. Se reorientan las referencias, se conservan índices y se reemplazan las funciones/triggers originales por los del destino. No quedan funciones activas consultando las tablas retiradas.

La reversión poblada reconstruye ambas relaciones y las protecciones de secuencia/avisos. Un despliegue o reversión requiere el código correspondiente al mismo esquema y reiniciar los trabajadores persistentes que mantengan modelos antiguos en memoria. Si se desea además revertir extensiones anteriores, primero se revierte esta unión y luego43,37 y35 en ese orden, respetando sus bloqueos por datos conservados. En SQLite se reconstruyen la FK del aviso y sus CHECK como restricciones de columna para conservar ese comportamiento histórico. Las claves candidatas de las tablas consolidadas usan índices explícitos para permitir reconstrucciones posteriores de Laravel sin nombres sqlite_autoindex reservados.

Se adaptaron modelos, conservación de eventos, consulta de avisos, detalle de horarios, disponibilidad documental, catálogo de códigos y preparación de pruebas/servidores sintéticos. Los nombres antiguos que permanecen en contratos lógicos ya no son consultas a tablas físicas retiradas. La búsqueda de FK entrantes en PostgreSQL se agrupa en una consulta de catálogo: se contrastaron seis referencias contra el recorrido anterior, con igualdad exacta de tablas, claves y orden de columnas. La consulta agrupada tardó14,56 ms en esa lectura local; no es una medida de toda la migración.

## Instalación local

Se realizó un ensayo de ida y vuelta antes de confirmar. Destino verificado: PostgreSQL local remembermind_dev. Se preservaron exactamente:

| Origen | Filas |
|---|---:|
| Movimientos laborales | 5 |
| Eventos de asignación | 203 |
| Total | 208 |

Se comprobaron huellas de contenido de los orígenes y tablas dependientes, roles y permisos. Antes de instalar se verificó el catálogo de un respaldo completo privado: union-historia-laboral-20261116-d567b68365152367.dump. El respaldo y los expedientes quedan fuera de Git.

Tras confirmar: 101 tablas físicas, 63 operativas, 22 expertas, 16 técnicas, cero FK pendientes de validación, modelos con5/203 actos y capacidades documental/de conservación de asignaciones disponibles. Se recuperaron también las seis versiones de conocimiento existentes sin modificarlas.

## Verificación y límites

- El ensayo poblado PostgreSQL conservó y restituyó los208 actos.
- Las pruebas nuevas cubren ida/vuelta con datos, códigos después de migrar, separación de tipos, inmutabilidad por SQL, progresión/terminalidad de avisos, secuencia y fallo intermedio de esquema sin unión parcial.
- SQLite:59 pruebas de altas, documentación, contraseñas iniciales y horarios,454 comprobaciones, sin fallos.
- SQLite:33 casos de historia nominal, uniones, estructura y avisos;239 comprobaciones;29 completados y cuatro de concurrencia reservados al runner PostgreSQL.
- PostgreSQL: el grupo focal actualizado de tres pruebas de unión y publicación posterior al commit completó4 pruebas y43 comprobaciones.
- SQLite: las tres pruebas nuevas se repitieron después de añadir rechazo de cambio de identidad e inserción con mezcla de tipos:34 comprobaciones.
- PostgreSQL desechable: el grupo ampliado final completó **33 pruebas y277 comprobaciones**, sin fallos ni omisiones. Incluye las cuatro uniones anteriores, historia nominal, avisos y concurrencia de planificación. Los procesos concurrentes prueban doble publicación, suspensión y revocación mientras esperan el bloqueo, y reversión que espera una inserción confirmada. La ejecución anterior se interrumpió para aplicar la corrección de consultas y no se declara verde.
- No hubo cambios visuales ni de dependencias; no corresponde un build frontend.
- Las verificaciones se realizaron sobre el árbol compartido, conservando los cambios previos. La CI global y el problema de transporte HTTP de fotografías de la entrega anterior conservan su estado pendiente. No forman parte de una certificación integral del producto.
