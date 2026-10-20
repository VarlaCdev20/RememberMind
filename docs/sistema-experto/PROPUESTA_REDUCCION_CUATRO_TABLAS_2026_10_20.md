# Propuesta concreta: consolidar cuatro tablas expertas

Fecha: 20/10/2026. Estado: **PROPUESTA DE TRANSICIÓN NO APROBADA; SIN DDL**.
La propietaria indicó reducir y reutilizar, manteniendo recálculo íntegro.
Este documento concreta la primera reducción física posible. No autoriza ni
implementa por sí mismo la transición del modelo congelado.

## Alcance y conteo

Consolidar cuatro relaciones existentes en tres destinos que ya existen,
**sin crear tablas**:

| Relación que se retiraría después de trasladar y comprobar sus datos | Destino existente |
|---|---|
| `evidencias_cognitivas_instantanea` | `hechos_instantanea_experta`, bloque cognitivo fijo |
| `evidencias_iadl_instantanea` | `hechos_instantanea_experta`, bloque IADL fijo independiente |
| `funcionalidad_instantanea_experta` | `clasificaciones_dcl_instantanea`, bloque funcional fijo |
| `soportes_sinteticos_criterio` | `evidencias_criterio_evaluacion`, alternativa de soporte por hecho |

Resultado de esta transición, si se aprueba y se ejecuta satisfactoriamente:
**75 operativas + 29 expertas = 104 tablas de negocio**, frente a las 108
vigentes. La propuesta anterior de 25 tablas fue rechazada y nunca instalada.
No se cuenta como retirada una tabla vaciada o simplemente oculta del catálogo.

Esta reducción conserva las responsabilidades actuales y sus contratos
experimentales/observacionales. **No termina la persistencia del recálculo
institucional completo.** Ese requisito sigue vigente; las entradas y perfiles
faltantes deben resolverse reutilizando el modelo reducido. No se presenta
104 como el inventario final garantizado de toda la fase 6 ni se promete cero
extensiones futuras sin un diccionario suficiente.

## Fuentes y evidencia de grano

- [Baseline vigente](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md).
- [Conservación aprobada y diccionario de nueve relaciones](../base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).
- `database/migrations/2026_10_17_200000_incorporate_conservacion_experta_v2.php`:
  UNIQUE de cognitiva por hecho, funcionalidad por evaluación, clasificación
  por evaluación/funcionalidad y evidencia IADL por funcionalidad/hecho.
- `AlmacenInstantaneas::guardarClasificacion()`: escribe funcionalidad, IADL
  y clasificación en la misma transacción. `obtenerClasificacion()` rechaza
  funcionalidad sin clasificación como conjunto incompleto.
- `guardarSoportes()/cargarSoportes()`: combinan ambos tipos de soporte en una
  colección ordenada por criterio. El rol `SOPORTE_EXPERIMENTAL` ya se usa con
  evidencia institucional: no distingue por sí solo el origen del vínculo.
- [Investigación y reutilización](REUTILIZACION_Y_REDUCCION_EXPERTA_2026_10_20.md).

El bloque cognitivo es 0..1 por hecho. El bloque IADL también es 0..1 si hecho
y funcionalidad pertenecen a la misma evaluación; esa igualdad debe verificarse
antes y garantizarse después en SQL. **Ambos bloques pueden coexistir en el
mismo hecho.** No se agrega un discriminador que los haga excluyentes.

Funcionalidad y clasificación son dos bloques individuales de la misma raíz.
La etapa de cognición sin clasificación sigue siendo válida y no crea filas
funcionales ficticias. Una funcionalidad huérfana estructuralmente posible hoy
pero incompatible con el lector vigente **bloquea la transición**; no se
elimina ni se redefine como un nuevo estado válido. Este diseño no introduce
una fase independiente de guardado funcional.

## Diccionario de columnas propuestas

Se trasladan **29 columnas nuevas** a tres tablas existentes: 24 en hechos,
tres en clasificación y dos en vínculos. Se conservan tipos y longitudes de
los originales; no hay JSON, EAV, BLOB, listas serializadas ni filas por atributo.
PK/FK de origen son string(20); todos los índices y constraints nuevos forman
parte de la decisión estructural solicitada. `?` indica nullable.

### A. Hechos: bloque cognitivo (14 columnas)

| Columna destino | Origen en cognitiva | Tipo |
|---|---|---|
| `cod_evidencia_cognitiva_instantanea` | PK original homónima | string(20)?, UNIQUE parcial no NULL |
| `cognitiva_dominio` | `dominio` | string(20)? |
| `cognitiva_subcomponente` | `subcomponente` | string(30)? |
| `cognitiva_hallazgo` | `hallazgo` | string(50)? |
| `cognitiva_instrumento_id_dominio` | `instrumento_id_dominio` | text? |
| `cognitiva_justificacion` | `justificacion` | text? |
| `cognitiva_orden` | `orden` | integer?, >= 0 |
| `cognitiva_referencia_instrumento` | `referencia_instrumento` | text? |
| `cognitiva_referencia_edicion` | `referencia_edicion` | text? |
| `cognitiva_referencia_subprueba` | `referencia_subprueba` | text? |
| `cognitiva_referencia_norma` | `referencia_norma` | text? |
| `cognitiva_referencia_idioma` | `referencia_idioma` | text? |
| `cognitiva_referencia_poblacion` | `referencia_poblacion` | text? |
| `cognitiva_referencia_revisor` | `referencia_revisor` | text? |

La PK original no desaparece: pasa a ser identidad alternativa única del bloque.
Si está NULL, las otras 13 columnas deben ser NULL. Si está presente, dominio,
hallazgo, justificación y orden son requeridos; los otros NULL se preservan.
UNIQUE parcial `(cod_evaluacion_experta,cognitiva_orden)` para bloques presentes,
con continuidad verificada al cerrar/reconstruir. No confundir orden cognitivo
con el orden general del hecho. El contenido del hecho no se reemplaza por su
caracterización; permanecen sus valores, fechas, autor y referencias originales.

### B. Hechos: bloque IADL (10 columnas)

| Columna destino | Origen en IADL | Tipo |
|---|---|---|
| `cod_evidencia_iadl_instantanea` | PK original homónima | string(20)?, UNIQUE parcial no NULL |
| `iadl_cod_funcionalidad_instantanea` | `cod_funcionalidad_instantanea` | string(20)?, FK al alias conservado en clasificación |
| `iadl_actividad_id_dominio` | `actividad_id_dominio` | text? |
| `iadl_estado` | `estado` | string(30)? |
| `iadl_oportunidad` | `oportunidad` | string(40)? |
| `iadl_nivel_previo` | `nivel_previo` | string(30)? |
| `iadl_causa_ayuda` | `causa_ayuda` | text? |
| `iadl_atribucion_cognitiva_simulada` | `atribucion_cognitiva_simulada` | boolean? |
| `iadl_revisor_sintetico_id` | `revisor_sintetico_id` | text? |
| `iadl_orden` | `orden` | integer?, >= 0 |

PK NULL exige el resto del bloque NULL. PK presente exige funcionalidad,
actividad, estado, oportunidad y orden. No traducir `CON_AYUDA` a `CON_APOYO`
ni `REAL_Y_ADECUADA` a `OBSERVADA`: se conservan literalmente los vocabularios
y el alcance simulados originales. NULL, false y cadena vacía no se confunden.

La FK compuesta `(iadl_cod_funcionalidad_instantanea,cod_evaluacion_experta)`
apunta al UNIQUE equivalente de clasificación; impide cruces de evaluación.
Se indexa la FK y se usa borrado restrictivo/sin cascada. UNIQUE parcial
`(iadl_cod_funcionalidad_instantanea,iadl_orden)` para bloques presentes.
La continuidad se comprueba al cerrar/reconstruir. La dependencia funcional
respecto del hecho debe estar demostrada antes de retirar la tabla original.

### C. Clasificación: bloque funcional (tres columnas)

| Columna nueva | Origen en funcionalidad | Tipo |
|---|---|---|
| `funcionalidad_independencia_relativa` | `independencia_relativa` | string(20), requerido |
| `funcionalidad_revisor_sintetico_id` | `revisor_sintetico_id` | text? |
| `funcionalidad_justificacion` | `justificacion` | text, requerido |

`cod_funcionalidad_instantanea` **ya existe en clasificación**: conserva su
valor y UNIQUE; deja de apuntar a una tabla retirada y se convierte en identidad
alternativa de su bloque funcional. Se agrega UNIQUE compuesto con
`cod_evaluacion_experta` como destino de IADL. PK de clasificación, FK a
evaluación, huellas, patrón NULL, diferencial y justificación clasificatoria
permanecen. Las dos justificaciones no se mezclan.

Los consumidores reconstruyen `VectorFuncionalidad` desde ese bloque y los
hechos con IADL, sobre la misma instancia lógica de memoria cognitiva. El
escritor debe insertar la raíz consolidada antes de sus hechos IADL o ajustar
el orden dentro de una transacción, sin hacer visible un conjunto parcial.
La asignación de una caracterización IADL a un hecho ya conservado requiere
una sola transición NULL→bloque completo dentro del guardado DCL autorizado,
bajo bloqueo de la cabecera. Las columnas previas del hecho y el bloque
cognitivo deben ser idénticos; no se permite otro UPDATE ni borrar el hecho.
Las guardas SQL y Models deben distinguir esa anexión única de una sustitución.

### D. Vínculos de criterio: dos columnas y alternativa cerrada

| Columna nueva | Origen | Tipo |
|---|---|---|
| `cod_hecho_instantanea` | soporte sintético homónimo | string(20)?, FK restrictiva indexada a hechos |
| `cod_soporte_sintetico` | PK de soporte sintético | string(20)?, alias UNIQUE parcial no NULL |

`cod_evidencia_evaluacion` y `fecha_hora_vinculacion` pasan a nullable **solo
para la alternativa de soporte directo por hecho**. CHECK cerrado:

- Rama anterior: evidencia requerida, hecho y alias sintético NULL, fecha
  requerida; preserva literalmente rol/participación/justificación y sus
  restricciones anteriores.
- Rama directa: hecho y alias requeridos, evidencia NULL, rol
  `SOPORTE_EXPERIMENTAL`, participación `PRUEBA_TECNICA`, justificación y fecha
  NULL. No inventar fecha histórica: el original sintético no la tenía.
  `orden_conservacion` requerido y >= 0, igual al `orden` original.

Para los soportes trasladados se reserva una PK nueva real de
`evidencias_criterio_evaluacion` con el generador existente. La PK antigua se
conserva en `cod_soporte_sintetico`; no se trunca ni se reutiliza su prefijo
como PK de otra entidad. En el ensayo en memoria la PK nueva queda NULL para
dejar claro que **no es una fila lista para insertar**.

Se conservan FK e índice de criterio y evidencia, y su unicidad anterior.
UNIQUE parcial `(cod_evaluacion_criterio,cod_hecho_instantanea)` para la rama
directa. El contexto derivado de criterio, evidencia y hecho debe ser de la
misma evaluación, protegido también por trigger SQL. Para criterios SNAP-2,
un trigger que resuelve el contrato mediante el criterio y bloquea la cabecera
impone unicidad conjunta del orden y del mismo hecho lógico por ambas vías:
no duplicarlo mediante evidencia institucional y soporte directo. El contrato
de otra tabla no puede consultarse desde el predicado de un índice parcial.
La continuidad se comprueba al cerrar/reconstruir. Los D137 previos conservan
su contrato y orden nullable, sin imponerles nueva unicidad por conveniencia.

## Plan de transición aprobado solo si se acepta este diccionario

1. Nueva migración forward; no editar migraciones aplicadas. Verificar versión
   de esquema, contratos representables y todas las FK entrantes. Si aparece
   una referencia no contemplada, abortar sin borrar o corregir datos.
2. Medir por contrato antes de trasladar: filas, PK, NULL, tipos, órdenes,
   huellas de DTO recuperables y de columnas. Detectar huérfanos y cruces. La
   ausencia de clasificación con funcionalidad no se repara durante la copia.
3. Incorporar columnas, copiar bloques con alias y reservar PK reales de
   vínculos; actualizar la FK IADL hacia la raíz consolidada. Verificar conteos,
   columnas y reconstrucción inversa, sin cambiar el significado de una fila.
4. Cambiar Models/relaciones, escritor/lector del almacén y consumidores de
   observaciones, simulación y trazas pertinentes. Conservar contratos
   `ORION-SNAP-2` y `OBS-SNAP-1/2`, guardas, permisos y huellas históricas.
   Catálogo de códigos/secuencias conserva las identidades alternativas;
   no reiniciar contadores ni fabricar cuentas, residentes o conocimiento.
5. Ejecutar la aceptación de recuperación/replay antes y después con nuevas
   instancias y PostgreSQL real. Verificar rollback intermedio, reintentos,
   concurrencia de anexión única, relaciones SQL y rechazo de corrupción.
6. Solo tras comprobar equivalencia retirar las cuatro relaciones del esquema
   y del inventario, con PK/FK trasladadas. Actualizar baseline a 104 y tests
   del inventario como consecuencia de la transición aprobada, no para forzar
   una suite verde. Mantener fuentes históricas como tales, sin capa de
   compatibilidad permanente ni vistas contadas como eliminación ficticia.
7. Reversión: recrear las cuatro tablas con su definición anterior, reconstruir
   filas con los alias y columnas conservadas, restaurar FK y lectores, verificar
   huellas antes de quitar columnas nuevas. Si alguna fila nueva no puede
   representarse en el contrato anterior, el rollback falla explícitamente.
   No ejecutar `down()` ordinario de conservación para borrar historia.

El código de proyección no es el código de migración; no instala constraints,
no actualiza lectores y no guarda datos clínicos en archivos. La futura
implementación debe garantizar las reglas de esta sección tanto en SQL como
en los flujos autorizados.

## Comprobación disponible

```powershell
php artisan app:auditar-base-datos-remember-mind --evaluar-reduccion
```

`EvaluarReduccionConservacion` abre una transacción propia, PostgreSQL
REPEATABLE READ + READ ONLY; SQLite se verifica por ausencia de escrituras en
código/tests, sin atribuirle esa garantía PostgreSQL. Lee siete tablas de
origen y dos de contexto (criterios/evidencias), proyecta, restaura y compara
arrays canónicos estrictamente, sin perder tipos o valores. Solo devuelve
conteos, indicadores y una huella técnica; el comando no muestra payloads.
Una referencia entrante desconocida o una incoherencia bloquea el ensayo.
El inventario general enumera `public`; el preflight añade una comprobación
en `pg_catalog` que bloquea FK desde otros esquemas hacia las cuatro tablas
candidatas. Esa cobertura no se atribuye al comando de inventario por sí solo.

Límite: 100000 filas de origen por ensayo; no es un límite de bytes. Textos
grandes pueden requerir comparación por lotes antes de ejecutarlo en otro
entorno. No hay botón web ni exportación clínica. Los payloads permanecen en
memoria y la herramienta no crea un archivo clínico paralelo.

La lectura local inicial cotejó **490 filas** en las siete tablas. Un segundo
corte local, con nuevas escrituras ajenas al auditor entre ejecuciones,
cotejó **840 filas** (545 hechos, 55 cognitivas, 5 IADL, 5 funcionalidades,
5 clasificaciones, 75 soportes sintéticos y 150 vínculos). Ambos ensayos
reconstruyeron exactamente sus propias filas de origen; no se afirma que
ambos cortes fueran iguales ni que el auditor causara esas nuevas escrituras.

Las pruebas sintéticas incluyen los cinco casos existentes, ambos bloques en
el mismo hecho, ausencia de bloque, cero, false, NULL, bits IEEE-754 y
microsegundos, además de cruces, identidades duplicadas, FK entrante imprevista
y transacción ajena. La proyección inversa no certifica aún el lector sobre
el esquema nuevo: esa aceptación queda exigida antes del DROP.

Verificación final del código de ensayo, 20/10:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'PreflightReduccionExpertaTest|ProyeccionReduccionExpertaTest|InventarioReutilizacionExpertaTest|InventarioExpertoTest' --log-junit storage/app/private/reduccion-final-sqlite.xml
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'PreflightReduccionExpertaTest|ProyeccionReduccionExpertaTest|InventarioReutilizacionExpertaTest|InventarioExpertoTest' -Informe storage/app/private/reduccion-final-postgres.xml -Log storage/app/private/reduccion-final-postgres.log
```

- SQLite: 23 casos, **22 aprobados y uno exclusivo de PostgreSQL omitido**;
  783 aserciones. PostgreSQL desechable: **23 / 780, PASS**, incluida la FK
  entrante desde otro esquema. El cluster propio se detuvo y retiró al terminar.
- Revisión independiente: **16 / 185, PASS** antes de los últimos refuerzos de
  órdenes/FK externa, posteriormente cubiertos por la selección final anterior.
  Dictamen del mapa lógico/documental: PASS acotado apto para decisión concreta;
  no valida DDL, rollback físico, concurrencia ni recálculo institucional completo.
- Pint en los siete archivos PHP propios y enlaces locales de seis documentos:
  PASS. Build/QA visual no aplican porque no se cambia frontend. No se detectan
  dependencias V1 o depuración en el código incorporado.

Los fallos intermedios de pruebas se corrigieron sin debilitar reglas: comparar
contenido de filas en vez de identidad de objetos PHP; preparar el runner
desechable con transacción propia, evitando el rollback de migraciones que
correctamente rechaza eliminar historia; y limpiar las funciones PostgreSQL
del esquema exclusivo de pruebas. Las ejecuciones finales son posteriores.
Nada de esa preparación de tests se ejecuta sobre `remembermind_dev`.

## Decisión concreta pendiente

Aprobar o ajustar **estas cuatro consolidaciones, sus 29 columnas, cambios de
nulabilidad/FK/constraints y el plan de transición/reversión**, sin tablas
nuevas en este incremento. La ejecución posterior tendrá sus gates y pruebas.
No se solicita aprobar las tres candidatas de perfiles ni se atribuye a esta
decisión la conservación institucional completa todavía pendiente.
