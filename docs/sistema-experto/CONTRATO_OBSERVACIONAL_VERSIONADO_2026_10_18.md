# Ejecución observacional versionada

Fecha: 18/10/2026. Contrato técnico `OBS-SNAP-2`. Reutiliza el diccionario y las ampliaciones aprobadas; no añade tablas, columnas, catálogos clínicos ni umbrales.

## Alcance que funciona

La ejecución lee la versión institucional descendiente de `OBS-20261017-v1`, sus reglas y las referencias de conocimiento conservadas en SQL. Ya no ignora el archivo, la sustitución o el rechazo de una regla. El motor recibe datos planos y permanece independiente de Laravel.

La familia conserva exactamente estos indicadores y sus significados descriptivos:

| Indicador original | Consecuencia permitida |
| --- | --- |
| `repite_preguntas = true` | Se documentó un episodio de repetición de preguntas. |
| `olvida_indicaciones = true` | Se documentó un episodio de olvido de indicaciones. |
| `cambio_cognitivo = true` | Existe un cambio cognitivo señalado que requiere corroboración. |

El gestor puede archivar, sustituir o añadir definiciones sobre esos indicadores, conservando su criterio y consecuencia. Esta ruta admite una condición afirmativa por regla. Una condición negativa, otra fuente, un criterio ajeno o una consecuencia incompatible se rechazan antes de publicar la sucesora. No interpreta instrumentos, recurrencias, afectación de dominios o DCL.

## Selección, autorización y concurrencia

- Cuenta activa, permiso explícito, Policy, profesional propio activo de Medicina/Psicología y residente autorizado; la autoría de los controles permanece con su profesional original.
- Selección por cadena de antecesoras desde la raíz observacional, sin escoger la versión más reciente de cualquier familia. Ciclos, ramificaciones ambiguas y versiones no disponibles se rechazan.
- La ejecución bloquea la versión utilizada; el gestor y la revisión usan el mismo bloqueo. Si la selección queda retirada mientras espera el bloqueo, vuelve a resolver la cadena antes de calcular.
- Solo una carga inicial necesita el contador de versiones; se toma antes de los demás bloqueos. La lectura ordinaria no crea conocimiento.
- Reintentar el UUID de una ejecución conservada devuelve esa historia exacta. No adopta una sucesora ni respuestas actuales para completar un reintento.

## Captura y conservación

Una evidencia por evaluación/mapeo/control, un hecho por control/campo y un estado original por control. Varias reglas pueden compartir esa evidencia mediante las relaciones de soportes existentes. Un criterio conserva una traza y, si corresponde, una consecuencia; no se duplica porque existan varias reglas coincidentes.

Se conservan todos los controles del alcance anterior al corte, también los anulados y NULL con sus causas de inadmisibilidad. Se excluyen fuentes futuras o de otros residentes. La ausencia de datos no se convierte en normalidad.

La huella incluye registros originales, corte, decisiones y conocimiento compilado: PK de versión/regla/condición/mapeo/criterio/valores, nombre, fundamento, autor, creación y antecesora de cada regla ejecutada. La selección ejecutada se recupera desde `evaluaciones_reglas`; quitar o añadir una regla a la traza altera la comprobación. Una aprobación o rechazo posterior no cambia esa selección causal.

La lectura comprueba conjuntos exactos de hechos, evidencias, criterios, trazas, reglas, condiciones, participaciones, soportes y resultados. No completa corrupción desde la fuente actual. La acción guarda y relee dentro de la misma transacción antes de confirmar éxito; cualquier fallo revierte el conjunto.

Si se archivan todas las reglas, la captura conserva los registros y devuelve cero decisiones. No genera hallazgos ni resultados DCL.

## Historia anterior

`OBS-SNAP-1` mantiene su lector y algoritmo originales. La raíz puede retirarse después sin impedir consultar sus ejecuciones. No se migra ni reinterpreta la historia anterior como si hubiera usado `OBS-SNAP-2`.

Las revisiones de conocimiento no sustituyen las dos cadenas de revisión profesional de una evaluación. Este incremento no amplía ese contrato ni afirma revisión humana cuando no existe.

## Recorrido y evidencia

1. Medicina/Psicología abre **Consultar reglas** desde resultados.
2. Consulta la versión observacional y crea una sucesora para archivar o sustituir una definición.
3. Desde el residente solicita **Ejecutar y guardar hallazgos observacionales**.
4. La nueva ejecución usa esa sucesora. El historial sigue recuperando versiones y datos originales.

Pruebas de aceptación: `SucesionObservacionalTest` cubre archivo, rechazo posterior, evidencia compartida, sustitución, cero reglas, rechazo de definición incompatible, lectura histórica SNAP-1, adulteración de conocimiento, pantalla y sucesión ambigua. `HallazgosObservacionalesTest` conserva cobertura de permisos, fuente ajena, NULL, anulaciones, rollback, corrupción e idempotencia. `GobiernoReglasConcurrenciaPostgresTest` incorpora ejecución/sucesión y ejecución/rechazo mediante procesos separados y barrera explícita, además de las tres carreras previas.

Resultados locales: 72 pruebas/282 aserciones en PostgreSQL desechable; dos casos adicionales de pantalla y ambigüedad, 2/11 en otra base PostgreSQL desechable. Regresión SQLite de gobierno/observaciones: 70/214; informe y sucesión: 78/631; casos adicionales de sucesión: 10/48. La prueba de condiciones distingue `NO_EVALUABLE` por ausencia de entrada y `NO_CUMPLE` por una entrada negativa: 2/17 junto con la prueba de interfaz existente. Los grupos se solapan y no deben sumarse como una suite única.

Build de frontend correcto. El navegador devolvió nuevamente «Local browser use is disabled in Work settings»: la apariencia/responsive no se declara verificada. La revisión independiente inicial identificó las brechas de archivo/historia y evidencia compartida; la segunda revisión del código final no se completó por el límite de uso del agente revisor. Se realizó revisión propia y pruebas reales; no se presenta ese intento como un gate independiente aprobado.

El cierre integral de cinco dominios, la interpretación de los otros componentes instrumentales y la conservación institucional de todo su contexto mantienen sus pendientes en [el checklist 1–8](CIERRE_OPERATIVO_1_A_8_2026_10_18.md). Este contrato no los declara resueltos.
