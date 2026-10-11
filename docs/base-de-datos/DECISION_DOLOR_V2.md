---
title: "Extensión aprobada de valoraciones_dolor — Dolor V2"
status: CURRENT
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [approved_pain_schema_extension]
verification_scope: SQLITE_AND_ISOLATED_POSTGRESQL
runtime_verified: true
---

# Decisión Dolor V2 — 09/10/2026

La instrucción expresa de la propietaria autoriza tres columnas, la relación de
origen y sus índices en `valoraciones_dolor`. El resto del modelo congelado no
cambia. El inventario conserva **71 tablas operativas**; las 23 tablas expertas
aprobadas se cuentan separadamente.

## Contrato estructural

| Columna | Tipo | Nulabilidad | Uso |
|---|---|---|---|
| `cod_valoracion_origen` | `string(20)` | NULL | FK a la valoración inicial del mismo residente |
| `frecuencia` | `string(40)` | NULL | Descripción libre de esta valoración; sin catálogo inventado |
| `factores_alivio` | `text` | NULL | Lo referido/observado que disminuye el dolor |

Migración: `2026_10_09_100500_add_followup_fields_to_valoraciones_dolor_table.php`.
No añade una tabla, EVA posterior, hora de reevaluación, JSON, observaciones ni
catálogo de tipo de dolor.

### Integridad física

- `fk_dolor_origen_v2`: origen → `cod_valoracion_dolor`, DELETE RESTRICT.
- `fk_dolor_origen_residente_v2`: `(origen, residente)` →
  `(cod_valoracion_dolor, cod_residente)`, DELETE RESTRICT. Impide enlazar otra
  persona incluso por SQL directo.
- `idx_dolor_origen_v2`: índice de origen.
- `uq_dolor_codigo_residente_v2`: clave candidata para la FK compuesta; no
  cambia la PK string existente.
- Se reutiliza el índice existente que comienza por `(cod_residente,
  fecha_hora)`. Solo se crea `idx_dolor_residente_fecha_v2` si no existe uno.

SQLite reconstruye la tabla al alterar FK/columnas. La migración conserva las
definiciones de sus triggers existentes y las reinstala tras `up` y `down`.
No añade un motor clínico ni modifica esos triggers.

## Episodio y responsabilidad del dominio

Inicial A: origen NULL. Reevaluaciones B, C y D: origen A. Reevaluar B resuelve
A; el servidor no crea una cadena A→B→C. Cada captura crea una fila nueva,
autor real autenticado, fecha/hora del servidor y estado VIGENTE. A y los
registros anteriores permanecen intactos.

El servicio valida el origen existente, mismo residente, estado VIGENTE y
fecha no futura, además de autorización contextual; bloquea las filas de origen
durante la escritura transaccional. Detecta cadenas inválidas/cíclicas.
La FK protege existencia/coherencia de residente; vigencia, raíz, fecha y
competencia profesional son invariantes del servicio, no nuevas columnas.

`respuesta` conserva su columna `text`: NULL en la inicial y opcional en una
reevaluación. Es una descripción, nunca EVA posterior. `tipo_dolor` queda NULL
en este flujo mientras no exista vocabulario institucional aprobado.

## Normalización — 3FN en el alcance cambiado

Cada fila es una valoración. Frecuencia y factores de alivio dependen de su
PK; el enlace de origen evita copiar la valoración anterior. No se guardan
historiales JSON, listas serializadas ni otra persistencia del mapa corporal.
La clave compuesta repite la identidad necesaria para imponer coherencia
referencial, sin almacenar un segundo hecho clínico. No se atribuye una
auditoría de normalización a todas las tablas del sistema.

## Despliegue y reversión

`up` conserva las filas existentes. `down` elimina primero las FK, luego los
índices propios y las tres columnas. Conserva la PK, los índices anteriores y
los triggers. **Revertir descarta los valores de las tres columnas nuevas**;
tras capturar episodios reales se debe respaldar/exportar ese contenido antes
de una reversión de producción. No se ejecutó rollback en desarrollo.

Verificados `up`/`down` y restricciones en SQLite `:memory:` y PostgreSQL
aislado `remembermind_experto_test_20261009_dolorv2`. La aplicación de solo
esta migración a `remembermind_dev` conservó sus 5.160 filas previas.
El seed histórico canónico genera episodios sintéticos; no se hizo un reset
de desarrollo. Evidencia y límites del gate global:
[resultado Dolor V2](../frontend/FORMULARIO_DOLOR_V2_RESULTADO.md).
