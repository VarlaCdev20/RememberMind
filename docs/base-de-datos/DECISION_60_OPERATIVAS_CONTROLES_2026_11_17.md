---
title: "Reducción a 60 tablas operativas mediante controles tipados"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-17
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [cognitive_iadl_observation_consolidation]
verification_scope: SQLITE_POSTGRESQL_LOCAL_INSTALLATION
runtime_verified: true
---

# Controles cognitivos e IADL como observaciones tipadas

La propietaria instruyó «lleva a 60 operativas y procede» después de revisar opciones de reducción. Esta entrega concreta dos retiradas operativas mediante una unión disjunta de tres fuentes. Las candidatas de consolidación de reglas/condiciones expertas quedan fuera de esta entrega.

| Fuente | Destino físico | Clase |
|---|---|---|
| registros_observaciones_cuidado | registros_observaciones_cuidado | MOVILIDAD, CONDUCTA y SUENO existentes |
| controles_cognitivos | registros_observaciones_cuidado | COGNITIVO |
| controles_funcionales_iadl | registros_observaciones_cuidado | IADL |

Inventario de destino: **60 operativas + 22 expertas = 82 tablas de negocio**, además de **16 técnicas: 98 físicas**. Son dos relaciones físicas menos que el corte anterior de 62 operativas/100 físicas. El núcleo sin extensiones explícitas de Gerencia queda en **93 físicas: 55 operativas + 22 expertas + 16 técnicas**. Los recuentos anteriores siguen siendo históricos.

## Grano y conservación de normalización

Una fila sigue representando una observación identificada, de una clase, sobre un residente y un momento o fecha. IADL conserva una actividad por registro; varias actividades, ocasiones y rectificaciones siguen siendo filas diferentes. No se concentran todas las actividades de una persona en una fila ni se cruzan sus colecciones con controles cognitivos o jornadas.

La PK física es cod_observacion_cuidado. Los códigos originales se copian sin renumerar; las claves generadas cod_control_cognitivo y cod_control_iadl identifican exclusivamente su subtipo. Las referencias a un control cognitivo o IADL no pueden apuntar a movilidad, sueño o al otro tipo. Las rectificaciones IADL mantienen su referencia tipada al control anterior.

Los datos propios de cada observación dependen de su identidad; no se copian atributos de residente, personal, cuenta, atención o jornada. Se mantienen esas FK y no se introducen JSON, EAV, listas ni productos entre relaciones uno a muchos. El análisis de 3FN/4FN está acotado a esta unión disjunta, no certifica todas las dependencias del sistema. Reducir tablas físicas no cambia las responsabilidades clínicas de los cinco tipos.

El CHECK conserva obligatoriedad y longitudes originales por rama y exige NULL en campos exclusivos de otra clase. Cognición mantiene sus variables originales y booleanos. IADL conserva autor personal, cuenta registradora, fecha clínica, fecha técnica, actividad, oportunidad, desempeño, ayuda, causa, nivel previo, fuente y estado. No se inventa una jornada ni una atención para IADL. El DEFAULT REGISTRADO se conserva en el modelo y en la inserción lógica; el rollback restituye también el DEFAULT SQL original.

La FK compuesta atención/residente existente en las observaciones y cognición se conserva en el destino; también impide mezclar un registro IADL con una atención de otro residente. Esto refuerza físicamente la invariancia institucional ya comprobada por el lector y las operaciones. Se verificó que los registros locales podían conservarse sin cambios bajo esa restricción. No se impone una atención cuando el contrato admite NULL.

## Integración, historia y autorización

ControlCognitivo y ControlFuncionalIadl conservan sus clases, códigos públicos, prefijos, casts y relaciones. Usan filtros de clase y ocultan campos ajenos al serializar. IADL sigue rechazando la sobrescritura mediante su modelo y se rectifica creando otra fila. Los modelos de movilidad, conducta y sueño también ocultan los campos de los nuevos tipos.

RegistrosConsumo resuelve inserciones y consultas por identidad lógica; se reutiliza el catálogo existente de alias. Se adaptan seeders, consultas de autonomía, disponibilidad de fuentes, inventario de FK, reportes y pruebas. La selección de versiones vigentes de IADL conserva corte clínico/técnico y exclusión de originales rectificados. La proyección por defecto de IADL limita los campos a su contrato original, evitando que la columna cognitiva atencion oculte su relación atencion con el episodio clínico. La prueba verifica esa relación.

Los nombres originales de fuentes del conocimiento se conservan como identidades lógicas versionadas. Los adaptadores consultan modelos con alcance por tipo; no se reescriben fuentes, reglas ni huellas de versiones anteriores para aparentar otro origen. El mapper y el guardado institucional siguen conservando la identidad original de la fuente leída. No se modifica el motor independiente, las condiciones clínicas, pesos, severidades o metodología.

Los permisos de lectura, creación y rectificación mantienen sus reglas por rol, competencia, cuenta activa, vínculo y residente. Compartir tabla no concede a Enfermería la captura profesional IADL ni amplía acceso familiar. Los formularios y rutas mantienen sus clases de dominio existentes.

## Migración y rollback

Migración automática del núcleo: database/migrations/2026_11_17_000002_consolidate_controles_observaciones.php. El contrato físico de las tres fuentes se congela en esa migración; no se deduce un esquema nuevo de la presencia de datos.

Se reutiliza ConsolidacionTipada20261116: transacción, bloqueos PostgreSQL, comparación de columnas/FK, copia con EXCEPT en ambos sentidos, reorientación de referencias, índices y retiro de fuentes únicamente tras comprobar conservación. Las tres fuentes locales no tenían triggers propios.

SQLite requiere reconstruir tablas dependientes al reorientar FK. Durante esa reconstrucción se suspenden y restituyen dentro de la misma transacción los triggers propios y ajenos que dependen de la tabla reconstruida. No se anulan protecciones permanentemente. La prueba de ida/vuelta compara el conjunto completo de nombres/SQL de triggers antes y después y verifica referencias tipadas.

Primero debe revertirse esta consolidación y después las anteriores de sueño/observaciones. Las migraciones de esas dos uniones previas rechazan la reversión si aún existen los controles integrados. Se conservan las guardas generales de campos posteriores y cobertura completa de tipos. Despliegue/rollback requieren el código correspondiente y reiniciar procesos persistentes que mantengan modelos antiguos cargados.

## Ensayo local

Se comprobó el destino PostgreSQL local remembermind_dev y se ensayó ida/vuelta dentro de una transacción revertida.

| Fuente local | Registros conservados |
|---|---:|
| Observaciones de cuidado | 305 |
| Controles cognitivos | 20 |
| Controles IADL | 2 |
| Total | 327 |

El ensayo comprobó igualdad exacta de las fuentes restituidas, tablas dependientes, las 22 tablas expertas y asignaciones de roles/permisos. Se recuperaron las versiones existentes de conocimiento sin modificarlas. Después se creó y verificó el catálogo del respaldo privado union-controles60-20261117-775e491178b25e16.dump y se confirmó la instalación. Respaldo y datos quedan fuera de Git. Se registró la migración y se comprobaron 98 tablas físicas, seis versiones de conocimiento y cero FK pendientes de validación.


Inventario de filas instalado por clase: COGNITIVO20, CONDUCTA135, IADL2, MOVILIDAD135 y SUENO35. Los modelos recuperan20 controles cognitivos y2 IADL; no se fabricaron nuevas observaciones.

## Verificación final y límites

- SQLite, estructura, códigos y regresión de migraciones: **29 pruebas y544 comprobaciones**, sin fallos.
- SQLite, versión final de las cuatro pruebas nuevas: **4 pruebas y63 comprobaciones**, sin fallos. Incluyen ida/vuelta con datos, FK entrantes tipadas, rechazo de mezcla por SQL, conservación de rectificaciones, rechazo de rollback antiguo, fallo en la tercera fuente sin unión parcial, igualdad de triggers y conservación de la lectura de ambas fuentes en el resultado institucional.
- PostgreSQL desechable, versión final de unión, adaptadores, fuentes y concurrencia IADL: **30 pruebas y180 comprobaciones**, sin fallos ni omisiones. Incluye la prueba nueva del resultado institucional y los procesos independientes de reintento/rectificación.
- PostgreSQL desechable, renumeración y rechazo de atención ajena: **3 pruebas y28 comprobaciones**, sin fallos. Se corrigió el recuento esperado de entidades físicas de68 a66, sin debilitar las comprobaciones de relaciones, auditoría y roles.
- SQLite, lectores finales IADL, rectificación, reporte y estructura/historia de Gerencia: **15 pruebas y105 comprobaciones**, sin fallos.
- La ejecución ampliada SQLite registró **255 aprobadas, tres fallidas y dos omitidas, con1608 comprobaciones**. Los tres fallos fueron expectativas antiguas: dos pruebas intentaban crear una atención ajena que ahora rechaza la FK física, y el rollback experto no revertía primero las uniones posteriores de componentes, vínculos y criterios. Se corrigieron las pruebas conservando el rechazo SQL y el orden de reversión; la reejecución focal de sus casos completó **10 pruebas y86 comprobaciones, sin fallos**. La ejecución ampliada inicial no se declara verde ni sus dos omisiones se computan como aprobadas. No se vuelve a ejecutar toda esa selección tras las correcciones focales.

El guardado institucional conserva el marcador de lectura de cada fuente lógica, sin inferir resultados nuevos. Las pruebas comprueban NULL/falso, procedencia, autor, residente, corte, observaciones sin episodio, cuenta/permisos y ausencia de acreditación automática de C03. El motor independiente y el conocimiento almacenado no se modifican. La comprobación del contrato congelado coincide exactamente con los tres orígenes capturados, normalizando únicamente la notación SQL de tipos/casts para portabilidad y conservando el DEFAULT IADL.

Revisión final de persistencia del alcance: **PASS** por conservación exacta, rollback poblado/atómico, claves tipadas y pruebas SQLite/PostgreSQL. Revisión de seguridad del cambio: **PASS** para separación de tipos y conservación de las fronteras existentes, con negativos funcionales. Verificación técnica de integración experta: **PASS** para las fuentes y la conservación afectadas; no es una evaluación clínica en uso real ni una auditoría global del motor.

Comandos: php artisan test --filter con las clases citadas y scripts/probar-postgres.ps1 -Filtro para los grupos PostgreSQL desechables. Los registros privados se identifican como controles60-migraciones-sqlite, controles60-union-final-sqlite, controles60-integracion-sqlite, controles60-regresiones-corregidas y controles60-lectores-finales; los informes PostgreSQL finales son remembermind-controles60-final-20261117 y remembermind-controles60-identidad-20261117. Ensayos, respaldo, datos y preimágenes no se incluyen en Git.

Las verificaciones corresponden al árbol compartido, conservando el trabajo anterior. El ajuste de ConservarResultadoInstitucional.php y las adaptaciones de GerenciaCamposAvisoEstructuraTest, instalar-resultado-institucional.php y el caso previo de ReporteEnfermeriaPeriodoTest permanecen junto a archivos/trabajo preexistentes fuera del commit propio. En las otras pruebas con cambios anteriores se prepara únicamente la adaptación de esta tarea.

No se ejecutó la CI global ni aceptación visual. No cambió frontend ni dependencias: no corresponde build. La revisión de procesos no encontró trabajadores PHP persistentes de cola/Octane/Horizon que reiniciar. Las deudas de medicación/textos del panel documentadas en la entrega anterior mantienen su alcance; no se corrigieron mediante esta reducción.
