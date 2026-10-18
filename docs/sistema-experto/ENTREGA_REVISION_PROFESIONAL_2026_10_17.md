# Entrega de revisión profesional — 17/10/2026

## Alcance implementado

La propietaria aprobó firmas separadas de Psicología y Medicina y una tabla
vinculada a la evaluación. La confirmación está al final de la pantalla
existente, después de evidencias, explicación e historial. Se conserva la
salida automática original y cada decisión profesional.

- `RevisarValoracionExperta`: decisión y justificación obligatorias, cuenta
  recién leída, competencia y permiso separados, autor derivado de la sesión.
- `ConsultarRevisionesValoracionExperta`: dos estados independientes, pendiente
  cuando no existe firma, historial y autorización de lectura renovada.
- `CadenaRevisiones`: una sola cadena completa por evaluación/disciplina;
  no acepta ciclos, referencias ajenas o nodos desconectados.
- `RevisionEvaluacionExperta`: dictámenes sin actualización o borrado. Las
  mismas protecciones se ejecutan también en SQL.
- Una raíz y una sucesora por revisión, FK compuesta para impedir correcciones
  de otra disciplina/evaluación, bloqueo de la evaluación y reintentos idénticos.
- UI: confirmar/rechazar, justificación, autoría, fecha, corrección e historial.
  La segunda disciplina permanece pendiente hasta su propia revisión.
- Ruta de Psicología conectada al mismo componente, sin duplicar la pantalla.

La revisión no crea diagnóstico, prescripción, alerta ni publicación a
familiares. Superadministración por sí sola no recibe competencia clínica.

## Incorporación local

Aplicada únicamente la migración aprobada
`2026_10_17_220000_create_revisiones_evaluacion_experta_table` a
`remembermind_dev`, después de un respaldo privado:

`storage/app/private/dev-backups/antes-revisiones-expertas-20261017-215920-320280cb.dump`

Tamaño: 1.746.553 bytes. SHA-256:
`259f2a838353dabe6f3a2f7724e051d0e073ae16fe48679a8cf6388c8829f53f`.

Se incorporaron solo los dos permisos aprobados a los roles existentes
correspondientes. No se reejecutó el seeder general sobre la base local ni
se cambiaron sus usuarios. Las cinco evaluaciones anteriores permanecen
intactas. La tabla nueva tiene cero dictámenes: no se fabricaron firmas.

El inventario vigente es **33 expertas + 73 operativas = 106 tablas de negocio**;
el diccionario específico está en la decisión aprobada de base de datos.

## Límite de activación

La revisión opera únicamente sobre versiones con contrato explícito
`revision_profesional=true`, áreas reconocidas, estado de ejecución permitido
y soporte íntegro. Rechaza simulaciones, pruebas y paquetes experimentales.
`config/sistema_experto.php:lectura_clinica` permanece vacío: no existe todavía
un paquete operativo con interpretaciones aprobadas para sustituir 03A.

Por ello las evaluaciones ficticias existentes conservan sus avisos y no
admiten firma profesional. El flujo de firma está implementado y probado,
pero no se presenta como inferencia clínica habilitada. Las entradas A/P y
C01–C05 actuales no pasan a ser reales al cambiar sus textos.

La [matriz de interpretación operativa](MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md)
prepara elementos concretos para revisión de los profesionales: fuentes,
componentes, métodos/versiones, calidad y soportes de C01–C05. No presupone
que un total global de instrumento determine los cinco dominios.

## Evidencias y pendientes

- PostgreSQL desechable, cierre final: revisiones e inventario, **20 pruebas /
  65 aserciones PASS** (`orion-revision-final.pg.xml` en TEMP).
- Concurrencia PostgreSQL: **1 prueba / 8 aserciones PASS**, dos profesionales
  y dos procesos con barrera; una firma guardada, otra rechazada, una auditoría
  (`orion-revision-race.pg.xml`).
- Primer grupo SQLite: 100 pruebas / 670 aserciones; un fallo del contador
  histórico 105. Se actualizó al inventario aprobado 106; contador e inventario
  se repitieron, **9 pruebas / 341 aserciones PASS**. Cierre del grupo completo:
  **100 pruebas / 989 aserciones PASS** (`orion-revision-final.sqlite.xml`).
- Protección adicional de revisión abierta y render final: **2 pruebas /
  16 aserciones PASS** en SQLite; ambas incluidas también en la ejecución final
  PostgreSQL. Cambiar de evaluación exige guardar o cancelar la revisión abierta.
- Build Vite: PASS (`orion-revision.build.log`).
- Gate independiente de persistencia y seguridad: PASS técnico, sin P1/P2
  pendientes; no equivale a aprobación de metodología clínica.
- Revisión visual: no ejecutada; el navegador está deshabilitado en Work.
- Sin operaciones de escritura Git o push, conforme a los reglamentos maestros.

Queda pendiente validar las interpretaciones operativas con Psicología y
Medicina, conectar sus fuentes reales y aprobar el paquete ejecutable
correspondiente. La confirmación por caso se encuentra implementada para ese
recorrido; no sustituye la aprobación del conocimiento que utiliza el motor.
