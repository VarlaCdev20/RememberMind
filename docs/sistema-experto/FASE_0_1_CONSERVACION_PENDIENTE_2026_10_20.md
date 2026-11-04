# Fase 0.1: conservación compuesta pendiente de autorización estructural

Fecha: 20/10/2026. Estado: **PROPUESTA / NO IMPLEMENTADA / NO AUTORIZA DDL**.

La instrucción de Fase 0.1 autoriza corregir código y pruebas, pero prohíbe
modificar cualquier estructura operativa o experta sin autorización explícita.
El inventario vigente, según baseline y decisiones aprobadas, es 75 tablas
operativas y 29 expertas. La referencia a 69 tablas operativas no sustituye ese
inventario ni habilita cambios en las extensiones posteriores.

## Bloqueo verificable

La conservación observacional y experimental existentes se mantienen. La
evaluación compuesta instrumental/Petersen se prepara en memoria:

- `PrepararEvaluacionInstitucional::preparar` une ambas ramas en transacción,
  sin escritor relacional del compuesto.
- `InstantaneaEvaluacionInstitucional` exige `persistido=false` y
  `release_retenido=false`; su verificación rechaza cambiar esos indicadores.
- `InstantaneaPetersen` declara originales mutables y conservación completa
  falsa. Una valoración original inmutable no congela todas sus fuentes.
- `InstantaneaInstrumental::verificar` reproduce desde un DTO completo, sin
  consultar SQL/configuración actual. Esa capacidad requiere conservar el DTO
  íntegro; una FK o su huella no lo reconstruye.

| Información que debe sobrevivir | Representación disponible | Falta exacta |
|---|---|---|
| Conocimiento cognitivo y Petersen | Una versión en la cabecera | Dos ramas y estados de reglas adquiridos, sin volver a elegir reglas actuales. |
| Ejecutor y contexto | Cabecera parcial y ZIP del software por huella | Actor, nacimiento/contexto observados, huella compuesta y vínculo duradero al release recuperable. |
| Aplicaciones y respuestas | Filas operativas referenciables | Membresía y cierre exactos; estado, documento y localizador observados; inserciones posteriores no pueden entrar en la evaluación antigua. |
| Instrumentos, preguntas y opciones | Definiciones operativas mutables | Metadata adquirida y preguntas seleccionadas sin respuesta, incluidas ausencias y NULL. |
| Captura, interpretación y vínculos | Configuración dentro del DTO | Perfil/selector/medida/predicado/orden efectivamente usados; vínculos observacionales e instrumentales originales. |
| Valoraciones y soportes Petersen | Originales inmutables | Miembros, orden, exclusiones y fuentes consumidas por esa ejecución. |
| Fuentes clínicas/documentales | Originales con FK | Proyección mutable consumida, autor/estado/fecha y constancia de verificación documental. |

No hay columnas aprobadas para todos esos granos. Usar observaciones genéricas,
campos experimentales, JSON/EAV o archivos de historia clínica para guardarlos
alteraría sus contratos y aparentaría una conservación incompleta. No se ha
implementado ninguna de esas alternativas.

## Propuesta para decisión posterior

El [ensayo reducido existente](ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md)
contiene este candidato, todavía sin prueba completa de ida/vuelta:

1. `conocimientos_evaluacion_experta`: evaluación y rama.
2. `estados_reglas_evaluacion_experta`: rama y regla observada.
3. `autores_instantanea_experta`: etiqueta del profesional por evaluación.
4. `aplicaciones_instantanea_experta`: aplicación adquirida y atributos observados.
5. `perfiles_instrumentales_versionados`: perfil de captura o interpretación.
6. `entradas_perfil_instrumental`: selector o medición del perfil.
7. `predicados_perfil_instrumental`: predicado interpretativo ordenado.
8. `valoraciones_petersen_evaluacion`: juicio incluido.
9. `fuentes_petersen_instantanea`: proyección contextual mutable.
10. `soportes_petersen_evaluacion`: soporte original del juicio.
11. `atenciones_instantanea_experta`: atención observada.
12. `exclusiones_adquisicion_petersen`: exclusión ordenada.
13. `motivos_adquisicion_instrumental`: motivo de una aplicación.
14. `constancias_documentales_evaluacion`: comprobación documental.
15. `vinculos_declarativos_instantanea`: mapeo efectivamente adquirido.

El candidato también propone cuatro columnas en `evaluaciones_expertas`
(`cod_usuario_ejecucion`, `huella_release_motor`, `fecha_nacimiento_original`,
`huella_evaluacion_compuesta`) y tres de sellado en
`versiones_modelo_experto`. Además requiere sellado de `instrumentos`, sus
preguntas/opciones, `aplicaciones_instrumento` y respuestas, con triggers,
sucesión de ediciones y cierre de colecciones. Estas últimas son estructuras
operativas congeladas: **no se pueden instalar dentro de esta fase**.

Alternativa si las operativas deben permanecer intactas: diseñar copias expertas
tipadas de todo contenido mutable adquirido. Su diccionario, número de tablas y
costo serán distintos; no se reactiva la propuesta anterior de 25 tablas,
rechazada, por el mero hecho de citarla. Ninguna alternativa tiene autorización
estructural implícita en la petición de garantizar conservación.

Antes de pedir aprobación de un diseño definitivo se debe demostrar, en un
prototipo desechable, `guardar → recuperar → replay` igual al DTO canónico,
preservando NULL, ausencias, listas vacías, orden y preguntas sin respuesta.
Después se presenta un diccionario completo de columnas, PK/FK, índices,
checks/triggers, transición poblada, reversión, costos y respaldo del release.
No hay migración nueva ni DDL de este candidato en la entrega de Fase 0.1.

## Integridad SQL pendiente, separada del guardado compuesto

Las FK simples de `relaciones_evidencias_evaluacion`, `evaluaciones_reglas`,
`evaluaciones_condiciones_regla` y `evidencias_soporte_condicion` comprueban
existencia, pero no todas las igualdades de evaluación/versión o pertenencia
condición–regla. Eloquent previene mutaciones ordinarias y los lectores
rechazan corrupción; eso no equivale a inmutabilidad SQL universal.
Constraints/triggers adicionales para impedir físicamente esos cruces son otra
propuesta estructural pendiente. No se aplicaron sobre las tablas instaladas.

## Aceptación que continúa pendiente

- Guardado compuesto atómico e idempotente, recuperación íntegra y revisiones
  independientes posteriores, sin alterar resultados anteriores.
- Regresión de transcripción tardía, anulación, conciliación de documentos,
  respuesta añadida después, edición de instrumentos y cambios de configuración.
- Permisos revocados, fuentes cruzadas, rollback y reintento sin duplicados.
- Concurrencia PostgreSQL, corrupción y restauración del ejecutor original.
- Política de retención y respaldo del ZIP y comprobación de recuperación real.

Un sello nuevo conserva lo adquirido desde ese instante. No reconstruye con
certeza estados anteriores ya mutados cuando falta evidencia histórica. La
corrección del corte documental evita usar documentos posteriores, pero no
resuelve por sí sola esa ausencia histórica.

Esta dependencia permanece abierta en Fase 0.1. No se ha iniciado Fase 1 ni se
declara completa la conservación Petersen institucional.
