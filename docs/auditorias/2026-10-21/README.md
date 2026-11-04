# Auditoría integral de RememberMind — 21/10/2026

**Dictamen: NO LISTO PARA ACEPTACIÓN INTEGRAL.** Hay capacidades funcionales
implementadas y verificadas en alcances concretos; persisten defectos
reproducidos y dependencias clínicas/estructurales. La auditoría no cambia el
producto ni oculta pendientes tras una suite verde.

## Las dos partes solicitadas

1. [Administración, operación y atención por rol](PARTE_1_ADMINISTRATIVA.md):
   módulos, flujos, errores, roles, arquitectura, tablas, formularios,
   seguridad, integridad clínica y componentes visuales.
2. [Sistema experto](PARTE_2_SISTEMA_EXPERTO.md): hechos/conocimiento/red,
   catálogo de 21 reglas, 19 componentes, 29 tablas expertas, operaciones de
   inferencia, entradas/salidas, rutas/vistas, límites, corrección y validación.

La versión de lectura integrada está en [AUDITORIA.html](AUDITORIA.html).
Sus gráficos son diagramas propios; no capturas ni QA visual del sistema.

El alcance es una revisión amplia dirigida por riesgo: documentación vigente,
código, metadata y conteos locales, pruebas del repositorio y reproducciones
sintéticas. No equivale a inspeccionar manualmente cada método ni a completar
todas las pantallas con cada rol. Las matrices señalan qué se comprobó y qué
queda sin evidencia de aceptación.

## Resultado esencial

| Pregunta | Respuesta comprobada |
|---|---|
| ¿Cuántos módulos? | **12**, propietarios funcionales bajo `app/Modulos` |
| ¿Cuántos roles? | **10**, con 199 permisos de catálogo y 537 asignaciones rol–permiso locales |
| ¿Cuántas tablas? | **104 de negocio = 75 operativas + 29 expertas**; 16 técnicas adicionales, total físico 120 |
| ¿Cuántas tienen consumidores? | 104 Models y referencias de lectura; 103 referencias candidatas de escritura/carga, 96 de presentación. No equivale a 96 formularios aceptados |
| ¿Hay tablas vacías? | Cuatro de negocio en el corte; tienen contratos/consumidores. Vacía no significa inútil |
| ¿Cuántos roles están completos? | **Ninguno certificado globalmente con esta evidencia**. No significa cero roles funcionando |
| ¿Hay motor y reglas? | Sí, deterministas, versionados y con explicación en alcances concretos; no red neuronal entrenada |
| ¿Produce todo desde brutos? | **Todavía no**. Petersen recibe juicios intermedios humanos; faltan perfiles adoptados y puentes de interpretación |
| ¿Está completa la historia del compuesto? | **No**: preparación en memoria; falta conservación relacional institucional íntegra y su recorrido dependiente |
| ¿Puede borrarse una tabla por no tener formulario? | No. Varias son detalles internos del acto, conocimiento, evidencia, traza o revisión |
| ¿Se inspeccionaron pantallas reales por todos los roles? | No: navegador deshabilitado en Work. Revisión estática y pruebas de presentación separadas de QA manual |

## Prioridades que cambian la entrega

| Prioridad | Hallazgo | Evidencia y siguiente acción |
|---|---|---|
| P1 | Consultas agregadas y PDF sin todos los permisos/scope | Cuatro accesos indebidos reproducidos con datos sintéticos; aplicar proyección/autorización por recurso y residente |
| P1 | Dosis de texto ambiguo se transforma o rellena con 1 | Método real y rama HTTP comprobados sin persistir órdenes; entrada estructurada y rechazo de ambigüedad |
| P1 | Nota clínica sin cadena de rectificación; backend alternativo con guardas incompletas | Lectura estática; preservar versiones y reautorizar en la operación |
| P1/riesgo | Gate interno de inactivos, documentos públicos y caché de contexto | Gate reproducido sintético; exposición de despliegue y revocación cacheada requieren verificación específica |
| P2 | PDF de Enfermería falla con asignación; timeline y NULL incorrectos | Error de relación reproducido; reparar datos/relación y luego bytes PDF reales |
| P2 | Reportes/tendencias y dos entradas Social incompletas | Implementaciones vacías o catálogos de estado incompatibles localizados; cerrar recorrido real |
| Bloqueo experto | Juicios humanos intermedios y perfiles vacíos | Definir/adoptar derivaciones desde originales sin conclusión esperada ingresada |
| Bloqueo experto | Conservación compuesta institucional pendiente | Diccionario reducido legítimo y aprobado; writer/reader/replay antes de historial/revisión del compuesto |
| Aceptación | QA manual, casos independientes y operación | No reemplazarlos por número de tests ni por datos sintéticos abundantes |

No se suman los hallazgos de revisores como si todos fueran defectos distintos;
las revisiones se solapan y señalan su nivel de evidencia.

## Psicología, Medicina y Enfermería

[Cierre por profesión y validación](CIERRE_POR_PROFESION_Y_VALIDACION.md)
explica vistas y recorrido objetivo, qué originales aportar, tamizaje de
Enfermería como propuesta protocolizada, qué no debe convertirse en diagnóstico,
y cómo usar «Corregir regla» sin sobrescribir historia.

Se respeta la petición de evitar introducir la salida esperada como entrada.
La validación independiente puede realizarse fuera del flujo de captura;
corregir una regla no demuestra su corrección clínica por sí solo.
No se concedió IADL a Enfermería ni se adoptó un test o baremo nuevo.

## Evidencia y anexos

- [Pruebas, fallos y límites](EVIDENCIA.md).
- [Plan de cierre por lotes y criterio de aceptación](PLAN_CIERRE.md).
- [Inventario completo de las 120 tablas y consumidores](TABLAS_CONSUMIDORES.csv).
- [Reutilización de componentes y vistas](INVENTARIO_DISENO.md).
- [Red semántica: relaciones, recorrido y diferencia con la inferencia](RED_SEMANTICA_DETALLE.md).
- Revisiones independientes: [operación](REVISION_OPERATIVA.md),
  [BDD/seguridad](REVISION_BDD_SEGURIDAD.md), [experto](REVISION_EXPERTO.md).

La suite general terminó con **3473 pruebas, 63498 aserciones, 9 fallos,
48 omitidas y 0 errores**: 3416 aprobadas y dictamen técnico global FAIL.
Hubo cambios simultáneos en cinco archivos de interfaz durante el cierre:
esa ejecución no certifica un único snapshot ni el checkout final. EVIDENCIA
registra los archivos y una comprobación posterior dirigida por separado.
Esta última terminó con 63 pruebas/608 aserciones y dos fallos persistentes
de presentación; 61 aprobadas, sin errores ni omisiones.
La selección PostgreSQL pasó 169 pruebas/1695 aserciones. Frontend:
226 aprobadas, dos omitidas, cero fallos. Build PASS. Los nueve fallos y sus
límites están en EVIDENCIA. Los casos nuevos de reproducción son evidencia
de defectos, no reparaciones.

Esta auditoría no modificó código funcional, BDD, permisos ni configuración clínica; no se
ejecutó seed o reset operativo, no se autenticó con cuentas reales y no se
publicaron credenciales/datos clínicos. Los numerosos cambios locales previos
se conservaron. No se hizo push de esta auditoría.
