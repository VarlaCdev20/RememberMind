# Roadmap incremental para una desarrolladora

## Reglas de ejecución

Propuesta, pendiente de aprobación. Una vertical activa a la vez; no migrar datos, reglas e interfaz de todos los módulos simultáneamente. Cada fase termina con evidencia revisable y posibilidad de recuperación. Mantener el sistema actual durante el trabajo; usar datos sintéticos y copia aislada para ensayos.

| Fase / prioridad | Trabajo | Dependencia | Salida verificable | Estimación semanas-persona |
|---|---|---|---|---:|
| 0 P0 | Baseline, backups privados y restauración en copia; pruebas mínimas | Aprobación | Versión reproducible, rollback ensayado | 1–2 |
| 1 P0 | Autorización, familiar, documentos, credenciales, medicación y cama | 0 | Casos negativos y carrera pasan; datos ambiguos conciliados | 3–4 |
| 2 P1 | Aprobar mapa de datos/roles, convenciones e invariantes | 1 | Diccionario y equivalencias firmados; sin UI nueva | 1–2 |
| 3 P1/P2 | Personas/Identity/Institution; conservar login legacy | 2 | Persona/usuario/profesional diferenciados, grants comprobados | 3–4 |
| 4 P1/P2 | Residents/Admissions/AssignBed y temporalidad | 3 | Reingreso, asignación atómica y documentos recuperables | 3–4 |
| 5 P1/P2 | Clinical/Medication con versiones/correcciones | 4 | Prescripción y dosis con pertenencia e idempotencia | 4–6 |
| 6 P2 | Assessment/Care y Queries longitudinales | 5 | Versión de instrumento, plan/ejecución y comparación histórica | 3–4 |
| 7 P3 | Spike Vue/TS/Inertia, compatibilidad CSS, shell y listado | 6 | Dos rutas en convivencia, misma sesión, build/test aislados | 2–3 |
| 8 P3 | Dashboard y expediente 360 de lectura | 7 | Una vista conceptual; props y timeline autorizados | 3–4 |
| 9 P3 | Formularios por corte: evaluación, admisión, clínica, medicación, turno | 8 | Paridad de flujo y retiro legacy en cada corte | 6–9 |
| 10 P3 | Consolidación, autenticación visual si conviene y regresión | 9 | Inventario de legacy retirado y exportes estables | 1–2 |
| 11 | Simulación integral y ajustes institucionales del núcleo | 10 | Aceptación por perfiles, incidencias críticas cerradas | 2–4 |

Suma base: **32–48 semanas-persona**, sin asumir dedicación de calendario completa. A 20 h/semana el tiempo calendario aproximadamente se duplica respecto a semanas-persona de 40 h; no es un compromiso de entrega. Incluye incertidumbre de aprendizaje y conciliación moderada, no datos gravemente corruptos. Reestimar tras fases 1 y 7.

Ampliación separada: P4 experto 4–7 semanas-persona y P5 sesiones/nutrición/visitas/social/actividades 4–7, más validación ampliada 2–4: **10–18 adicionales**. El piloto puede usar el núcleo sin motor experto. La investigación del algoritmo y evaluación clínica pueden superar esa estimación de integración.

## Migración de datos: expandir → reconciliar → conmutar → retirar

1. Respaldar BD y archivos; manifest/hash, permisos privados y restauración comprobada. No considerar un archivo SQL no restaurado como garantía.
2. Agregar estructuras nuevas mediante migraciones aditivas en copia; conservar PK legacy y correspondencias. Reproducir esquema desde cero solo en entorno desechable.
3. Importar por lotes idempotentes y tabla/artefacto de mapeo. Clasificar conflictos; no fusionar personas por similitud de nombres ni elegir el último puntaje automáticamente.
4. Comparar conteos, FK, intervalos, series y hashes. Las excepciones se revisan por responsable funcional. No inventar timestamps clínicos faltantes.
5. Cambiar el writer de un caso de uso y mantener un único origen canónico. Para el primer corte, preferir una pausa breve de escritura con delta final a implementar dual-write permanente para una sola desarrolladora.
6. Verificar lectura/exports/autorización y observar errores. Si hay fallo previo a nuevas escrituras, volver a código previo; si hay escrituras nuevas, aplicar corrección compatible o reconciliar delta antes de rollback. Un down destructivo no es rollback seguro.
7. Retirar columnas/tablas antiguas después de aceptación, backups y búsqueda de consumidores; nunca durante esta auditoría.

## Historia desde el inicio

No posponer longitudinalidad a una «fase 10» después de años de sobreescritura. La versión y fechas del hecho se incorporan en fases 4–6; la presentación timeline llega en 8. El experto depende de esa trazabilidad, no al revés.

## Puertas de decisión

Aprobar 12 módulos y fuentes de verdad; elegir seis roles base con plantillas o más roles de capacidad; validar permisos clínicos y familiares; confirmar alcance de tablas ampliadas; aceptar costo Vue y resolver shadcn/Tailwind mediante spike. El siguiente trabajo autorizado, si se aprueba, debería ser fase 0 y cierre P0, no instalación inmediata de Vue.
