---
title: "Avisos de planificación sin tablas nuevas"
status: ADOPTED
version: "1.1"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: false
runtime_verified: true
verification_scope: [disposable_schema_prototype_only]
---

# Decisión adoptada y antecedente de ensayo

**Actualización posterior del mismo27/10:** la propietaria respondió
«Aviso compartido por cambio». La pregunta funcional quedó resuelta.
La [decisión específica](../../../base-de-datos/DECISION_AVISO_COMPARTIDO_CAMPOS_2026_10_27.md)
adopta los seis campos, la consulta original del trabajador y la bandeja
compartida de Administración. El [informe44](44-INTEGRACION-AVISO-COMPARTIDO.md)
registra integración, pruebas e instalación efectiva. Los apartados siguientes
conservan el corte del ensayo anterior a esa respuesta: sus pendientes no
describen por sí solos el estado posterior.

El27/10/2026 la propietaria indicó: **«NO APRUEBO LA CREACION DE NINGUNA
TABLA MAS, PUEDES AMPLIAR CAMPOS EN TABLAS NO CREAR OTRAS»**.
Se rechaza la [propuesta42](42-PROPUESTA-AVISOS-PLANIFICACION.md), que nunca
se instaló. Se retiraron su DDL, Model, servicios, job y enlaces de bandeja
propuestos; no quedan activos un escritor o un permiso de la tabla rechazada.
Se conservaron sus logs únicamente como antecedentes del diseño descartado.

La alternativa mantiene **106 tablas de negocio +16 técnicas** y añade seis
campos nominales a `eventos_asignacion_personal`. El DDL se ensayó en bases
desechables; **no se instaló en la base institucional**.

Se preguntó a la propietaria si adopta **un aviso único por cambio, visible al
trabajador original y a una bandeja compartida de Administración**, sin entrega
o lectura individual de administradores. Esa respuesta sigue pendiente al
cerrar este corte. El contrato23A identifica Administración y afectados, pero
no resuelve expresamente la diferencia de conservación entre ambas opciones.
No se registra la pregunta como aprobación recibida ni se instala el DDL
antes de resolver el alcance de la integración.

## Por qué no se reparten destinatarios en columnas

El evento tiene un trabajador afectado y puede tener una cuenta original
asociada: una FK singular conserva ese hecho. Administración puede consultar
una bandeja institucional compartida bajo su competencia vigente.

Si se exige recordar recepción o lectura de cada cuenta administrativa para
cada evento, el grano es evento×cuenta, una relación multivaluada. Añadir
destinatario1/2/3, listas JSON, EAV, observaciones codificadas o columnas
repetidas no preserva correctamente ese grano. Esta alternativa no ofrece
ese seguimiento individual ni lo simula mediante Activitylog, jobs o caché.

## Diccionario mínimo preparado

Todos los campos son nullable y no se rellenan retrospectivamente.

| Columna en eventos_asignacion_personal | Tipo | Propósito |
|---|---|---|
| cod_usuario_afectado_aviso | varchar(20) NULL | FK usuarios.cod_usuario, restrictiva; cuenta original del trabajador, conservada en el INSERT del evento |
| estado_aviso_planificacion | varchar(16) NULL | PENDIENTE/DISPONIBLE/FALLIDO para la publicación compartida; NULL significa sin seguimiento conservado |
| numero_intentos_aviso | integer NULL | Contador de intentos efectivos de publicación; no contador de lecturas |
| fecha_hora_ultimo_intento_aviso | timestamp NULL | Momento del último intento de publicación |
| fecha_hora_disponible_aviso | timestamp NULL | Momento de disponibilidad en la bandeja, sin acuse individual |
| categoria_fallo_aviso | varchar(40) NULL | Categoría nominal FALLO_DISPONIBILIZACION; no SQL/excepción/contenido clínico |

Las columnas son atributos singulares de **un mismo evento/publicación**.
No se incorporan colecciones independientes ni una tabla genérica. Los cinco
metadatos de procesamiento pueden evolucionar; la FK original no cambia.
No se certifica4FN global por ejecutar CHECK: se justifica el grano de esta
ampliación y se conserva el límite de alcance compartido descrito.

### Restricciones y guardas ensayadas

- Grupo completamente NULL = sin seguimiento conservado; incluye historia
  anterior y eventos que se creen sin habilitar la futura función.
- Seguimiento presente exige estado y contador NO NULL explícitos, evitando
  que la lógica SQL UNKNOWN acepte grupos incompletos.
- PENDIENTE: contador0 y fechas/categoría NULL.
- DISPONIBLE: contador positivo, fechas de intento/disponibilidad presentes e
  iguales; sin categoría. No puede volver a pendiente/fallido ni recontarse.
- FALLIDO: contador positivo, último intento presente, disponibilidad NULL y
  categoría nominal explícitamente NO NULL.
- Último intento no anterior al registro original.
- Índice(FK afectado,fecha de registro) e índice(estado,fecha de registro).
- FK restrictiva a usuarios, con tipos y longitudes conservados.

DDL concreto:
[ampliación de campos fuera de migraciones automáticas](../../../../database/propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php).
No contiene CREATE TABLE ni cambia las PK o relaciones previas.

## Historia y reversión

La ampliación no modifica los bytes de la transición37 instalada.
`ger_historia_inmutable()` también protege versiones de vínculos y **no se
modifica**. Solo se sustituye el trigger de eventos por una función específica
que permite actualizar los cinco metadatos y mantiene inmutables todos los
atributos originales y la FK del afectado. El borrado ordinario sigue prohibido.

Se comparan campos nullable mediante `IS DISTINCT FROM` en PostgreSQL y
`IS NOT` en SQLite. Un cambio NULL→valor de estado anterior/antecesor no puede
eludir la protección con comparaciones parciales. Tampoco se permite inicializar
seguimiento de un evento ya conservado sin él: los nuevos eventos deberán
incorporar el snapshot y estado inicial en su INSERT atómico.

Reversión: si los seis campos están NULL en todas las filas, restaura primero
el trigger original y retira solo los campos/índices añadidos. Conserva las
filas y sus relaciones originales. Si hay cualquier seguimiento, rechaza el
rollback para no destruirlo. No se borra historia para liberar el bloqueo.
El chequeo y retiro están en una misma transacción; PostgreSQL adquiere bloqueo
exclusivo antes de comprobar vacío, y SQLite reserva escritura sin tocar filas.
La revisión encontró y corrigió tanto esa ventana check-before-lock como el
paso seguimiento→NULL cuando no existe cuenta afectada. La prueba comprueba
la conservación de esos casos; todavía no se afirma un ensayo multiproceso
de reversión ni una prueba del futuro publicador concurrente.

## Prueba concreta ejecutada

[GerenciaCamposAvisoEstructuraTest](../../../../tests/Feature/GerenciaCamposAvisoEstructuraTest.php)
comprueba SQL real:

1. Ampliación sobre un evento existente: mismas tablas, mismos atributos
   originales y seis campos NULL; reversión sin pérdida de datos.
2. Historia desconocida sin inicialización posterior; originales nullable
   y borrado protegidos.
3. Evento nuevo con seguimiento: fallo con categoría NULL rechazado;
   progresión técnica válida, FK afectada y originales inmutables;
   reversión poblada rechazada.
4. Seguimiento sin cuenta afectada: ni PENDIENTE ni FALLIDO se pueden limpiar
   mediante NULL. Grupos parciales, FK inexistente y fecha incoherente rechazados.

| Motor desechable | Resultado |
|---|---|
| SQLite en memoria | **4 pruebas /31 aserciones, PASS** |
| PostgreSQL18 temporal | **4 pruebas /31 aserciones, PASS** |

Evidencias: `gerencia-avisos-campos-sqlite.xml/.txt` y
`gerencia-avisos-campos-postgres.xml/.txt`, bajo
`output/auditoria-integral-20261025/`.
El ensayo descartado de una tabla pasó12/84 en su corte, pero **no acredita
este flujo nuevo**, su concurrencia, UI o permisos. No se suman ambos diseños.
Regresión relacionada después de retirar el diseño rechazado:40 pruebas/288
aserciones SQLite PASS (`gerencia-avisos-campos-regresion.xml/.txt`); su corte
precede al último caso de seguimiento→NULL, cubierto por las cuatro pruebas
finales específicas. No se modificó una interfaz vigente, por lo que no se
atribuye un build o QA visual al flujo nuevo todavía sin conectar.

```powershell
./scripts/probar-sqlite.ps1 -Filtro GerenciaCamposAvisoEstructuraTest -Informe output/auditoria-integral-20261025/gerencia-avisos-campos-sqlite.xml -Log output/auditoria-integral-20261025/gerencia-avisos-campos-sqlite.txt
./scripts/probar-postgres.ps1 -Filtro GerenciaCamposAvisoEstructuraTest -Informe output/auditoria-integral-20261025/gerencia-avisos-campos-postgres.xml -Log output/auditoria-integral-20261025/gerencia-avisos-campos-postgres.txt
```

## Trabajo siguiente, sin falsa culminación

Tras definir la bandeja compartida o solo consulta:

- Conectar la captura singular al INSERT de eventos y publicar después del
  commit; un fallo de aviso no desdice la asignación confirmada.
- Reintentar el mismo evento bajo lock, sin crear otra asignación, volver a
  capturar destinatarios o incrementar un aviso ya disponible.
- Autorización de Administración con su permiso de lectura de asignaciones;
  reintento con permiso de asignación, rol/alcance vigente y sin preview.
  La consulta propia requiere su capacidad contextual expresamente resuelta;
  no se reutiliza `alertas.ver` como permiso laboral ni se conceden permisos
  propuestos por la tabla rechazada.
- Aplicar el scope SQL antes del límite; mostrar tipo de cambio, fecha
  efectiva y estado resultante. No usar motivo libre como texto del aviso.
- Probar rollback, fallo de cola/publicación, recuperación pendiente,
  revocación/vínculo cambiado, dos reintentos PostgreSQL y UI real.
- Documentar instalación concreta y activar únicamente el alcance aceptado.

PC14/PC15 **todavía no están cerrados**. El corte deja una ampliación física
ejecutable y ensayada, no un flujo completo anunciado como instalado.
No hubo cambios estructurales, grants ni datos nuevos en la base institucional.
Sin staging, commit, push o despliegue; trabajo simultáneo preservado.
El HEAD observado cambió externamente durante este trabajo de6d7b8c6d a
b17c04d1; no se cambió de rama ni se revirtió ese avance. Esta ejecución no
emitió comandos de staging/commit/push. `git diff --check` pasó y los diez
enlaces locales de los tres documentos nuevos no tienen destinos faltantes.
