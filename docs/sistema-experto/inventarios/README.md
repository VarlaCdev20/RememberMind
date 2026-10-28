---
title: "Inventario instalado del sistema experto"
status: CURRENT
last_reviewed: 2026-10-28
owner: ApoyoDecision
source_of_truth: false
verification_scope: POSTGRESQL_READ_ONLY_CATALOG_METADATA
runtime_verified: false
---

# Inventario instalado: corte del 27/10/2026

Este corte es anterior a la [retirada del laboratorio](../RETIRADA_LABORATORIO_2026_10_27.md). Los conteos físicos se conservan como evidencia; las entradas técnicas ya no se ofrecen en el circuito operativo.

[Portal](../README.md) · [Mapa de componentes](../organizacion/MAPA_COMPONENTES.md) · [Candidatos](../instrumentos/CATALOGO_CANDIDATOS.md) · [Salida íntegra de metadatos](2026-10-27.json).

Se ejecutó [scripts/inventariar-experto.php](../../../scripts/inventariar-experto.php) sobre PostgreSQL local. La protección `transaction_read_only=on` se comprobó dentro de una transacción `REPEATABLE READ, READ ONLY`, cerrada con rollback. No se consultaron residentes, aplicaciones, respuestas, hechos ni evaluaciones; no se imprimieron conexión, usuarios o secretos. La hora exacta está en el JSON. Esta comprobación de metadatos no valida el motor ni una pantalla.

## Instrumentos catalogados

| Familia observada | Entradas | Estado y alcance |
|---|---:|---|
| Barthel (`BARTHEL_100`, `BARTHEL_LP_100`) | 2 | Activas, 10 preguntas cada una; dos identidades de catálogo con igual nombre/versión. No fusionarlas ni borrar datos por esa coincidencia. No son dos instrumentos cognitivos para detectar DCL. |
| Capturas sintéticas `COH30-COG-*` | 5 | Activas, versión `TEST-1`, 48 preguntas en conjunto. Son simulación, no una batería clínica instalada. |
| Registros de tarea `TEST-*` | 19 | Inactivos, versión `G08-TEST-v1`, una pregunta cada uno. Su tipo registrado es observacional. |
| **Total** | **26** | **87 preguntas de catálogo**, sin contar administraciones ni residentes. |

No aparecen entradas formales identificadas como MoCA, ACE-III, RUDAS u otra batería de los candidatos en este corte, fuera de Barthel como apoyo funcional. Las capturas sintéticas pueden contener mediciones de ejemplo, pero no acreditan otra identidad instrumental ni una administración real. El catálogo de candidatos y los métodos investigados son inventarios diferentes.

## Conocimiento registrado

| Conteo de metadatos | Cantidad |
|---|---:|
| Versiones | 6 |
| Reglas | 32 |
| Condiciones | 90 |
| Consecuencias | 32 |
| Nodos semánticos | 205 |
| Relaciones semánticas | 130 |

| Versión registrada | Estado | Reglas y revisión observadas |
|---|---|---|
| `MEM-INV-20261008` | INACTIVO | Sin reglas en el agrupamiento de este corte. |
| `ORION-V1-20261008` | INACTIVO | Sin reglas en el agrupamiento de este corte. |
| `SIM-ORION-LOCAL-1` | ACTIVO | 8, revisión NULL; laboratorio. |
| `ORION-COGNITIVO-1` | ACTIVO | 17, revisión PENDIENTE; contrato A–D registrado. |
| `ORION-PETERSEN-1` | ACTIVO | 4, revisión PENDIENTE; contrato E–H registrado. |
| `OBS-20261017-v1` | ACTIVO | 3, revisión NULL; alcance observacional. |

Los nombres son identidades técnicas preservadas. `ACTIVO` no significa validado clínicamente; revisión NULL no es aprobación. Las adopciones institucionales permiten el desarrollo técnico en su alcance y no deben transformarse en un bloqueo general por una firma pendiente.

## Configuración efectiva consultada

`perfiles_instrumentales`, `perfiles_interpretativos`, `contratos_conocimiento` y `lectura_clinica`: **0 entradas** en cada mapa de configuración. No hay una versión cognitiva fijada en la clave de configuración consultada.

Esto no demuestra ausencia de todo conocimiento ejecutable: `ConsultarConocimientoCognitivo` puede resolver la versión institucional desde la BDD, y existe una capacidad específica para perfiles sintéticos locales. No activar perfiles ni publicación automáticamente para llenar estas listas.

## Reproducir la lectura

```powershell
php scripts/inventariar-experto.php
```

La herramienta solo consulta una lista cerrada de catálogos en la conexión configurada. Rechaza motores fuera de PostgreSQL/SQLite, comprueba protección de lectura y devuelve error explícito si falla. Los errores no imprimen SQL ni credenciales. Las preguntas se cuentan por lote, sin consultas por instrumento. La salida es un corte nuevo, no una actualización automática de este documento ni de la instantánea fechada.

Pendientes: disponibilidad real de instrumentos en cada formulario/rol, licencias, baremos y edades de cada edición, historial completo y validación de resultados. No se consultaron expedientes para responder esas preguntas.

## Verificación de esta organización

- PHP 8.3: sintaxis de la herramienta correcta.
- PostgreSQL configurado: consulta completada, protección de lectura confirmada y JSON generado sin consultas a tablas clínicas.
- SQLite `:memory:` desechable y vacío: protección `query_only` confirmada; tablas ausentes informadas como tales, conteos desconocidos en NULL y sin catálogos inventados. Esta prueba no valida consultas con un esquema SQLite poblado.
- Motor no admitido, forzando MySQL únicamente en el proceso hijo: rechazo esperado con código de salida 1, antes de consultar catálogo; no se intentó migración o reinicio de base.
- 45 IDs de candidatos consecutivos y únicos; 198 enlaces relativos de los seis documentos revisados resueltos. Comprobación de los dos enlaces nuevos a secciones del mapa.
- Sin cambio de interfaz: build y QA visual no aplican. No se reejecutaron suites clínicas por esta organización documental/herramienta de lectura. No se declara aceptación del experto completo.

## Actualización del contrato de contexto —28/10/2026

La herramienta mantiene su corte de catálogo separado de la [revisión de idioma e informantes](../CONTEXTO_IDIOMA_INFORMANTES_2026_10_28.md). `contrato_entradas.contexto_evaluacion` verifica columnas/FK de escolaridad por atención, distingue el vínculo de contacto de la participación clínica y no deduce idioma desde el material. No necesita residentes ni consulta sus filas. Las cifras del27/10 conservadas arriba no se actualizan por esta ampliación del contrato.

El nuevo alcance se comprobó en SQLite (12 pruebas/435 aserciones), PostgreSQL desechable (11 correctas/399 aserciones y1 omitida específica de SQLite) y lectura protegida de metadatos en la base local. No se crean tablas, campos, catálogos o permisos ni se activan instrumentos o reglas.
