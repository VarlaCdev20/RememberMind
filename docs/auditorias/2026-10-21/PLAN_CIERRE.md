# Plan de cierre derivado de la auditoría

Fecha: 21/10/2026. Plan de corrección, no implementación ni autorización de
nuevas reglas/permisos/estructura. Prioridad por riesgo y dependencia.

## Orden de ejecución

| Lote | Problema concreto | Trabajo propuesto | Criterio verificable de cierre | ¿Decisión de dominio/estructura? |
|---|---|---|---|---|
| 1. Lecturas y exportaciones | OP-01/02/03/07: recursos sin permiso y PDF fuera de asignación | Reusar scopes/Policies por recurso; proyectar antes de serializar; autorizar exportación y destino | Positivos y negativos permanentes por los diez roles, residente ajeno, permiso/vínculo revocado; sin marcador reservado en HTML/JSON/PDF | No para aplicar límites actuales; sí si se pretende ampliar contenido autorizado |
| 2. Fidelidad de órdenes | OP-05/06: dosis ambigua, unidad/fármaco/fecha divergentes | Una entrada estructurada de dosis/unidad/medicamento; revisar ambas ramas y consumidores; fallar ante texto no interpretable | Fracción/decimal/unidad preservados o rechazados claramente; agenda consume misma orden; rollback sin restos | Reusar contrato aprobado; nueva pauta/reinicio de orden exige criterio institucional |
| 3. Historia y acceso autónomo | OP-11/12, S01/S03–S05 | Rectificación enlazada; guardas en backend y veto inactivos antes de bypass | Original y correcciones recuperables; autor propio; llamada directa sin permiso no escribe; auditoría fallida revierte | No para respetar historia y autorización actuales |
| 4. Documentos y caché | S02/S06/S07 | Descarga privada contextual, reautorización antes de caché y de consulta, invalidación de contexto | URL directa ajena no entrega archivo; revocación inmediata no retorna contenido anterior | No amplía acceso; transición documental requiere plan y preservación de archivos |
| 5. Reportes y Social | OP-08/09/10 y demás riesgos comprobados al corregir | Reparar relación de turno, ADMITIDO y NULL; calcular la tendencia realmente definida; conectar entradas base a proceso existente | PDF real con datos sintéticos; timeline fiel; no ceros inventados; consultas con registros y vacías; visitas/ficha con recorrido completo | Indicadores/proceso nuevo requieren contrato; no inventar tendencia clínica |
| 6. Contratos técnicos | Fallos de arquitectura/paleta y documentación desactualizada | Cotejar ruta/Livewire nuevos con decisiones; actualizar snapshot solo tras revisión; conciliar tokens y documentación | Suite de arquitectura sin fallos, rutas autorizadas trazables, enlaces vigentes | No aprobar cambios funcionales solo porque hace falta verde |
| 7. Originales cognitivos | H03/H04 y Punto 1 | Adoptar protocolos/ediciones, derecho de uso, población y captura; configurar binding exacto | Medidas reales cotejadas, ausente/cero/No correctos, método aplicable, autor y contexto | **Sí:** catálogo/protocolo, responsabilidades o permisos nuevos |
| 8. Resultado derivado | H01–H03 | Especificar y probar medidas→hallazgo→dominio→requisitos sin conclusión esperada como entrada | Casos sustentados y abstenciones; ninguna salida copiada del juicio objetivo; explicación completa | **Sí:** sustituye parte del contrato E–H e interpretación clínica |
| 9. Conservación integral | H05/D08/D09 | Cerrar diccionario reducido probado, aprobarlo y construir writer/reader/replay institucional | Roundtrip exacto; mutación de fuentes/reglas; corrupción; carreras/rollback; release y runtime retenidos; transición/reversión pobladas | **Sí:** modelo congelado. 104→100 es objetivo/candidato, no DDL aprobado |
| 10. Corrección de conocimiento | H06/H07/H11 | Apartado «Corregir regla» sobre sucesoras/pruebas/auditoría existentes; explicación clínica por alcance | Solo cambio permitido; versión anterior intacta; nuevas emisiones usan sucesora; pruebas independientes congeladas | Sí cuando la corrección excede semántica/editor/permiso aprobados |
| 11. Aceptación por rol y operación | Evidencia incompleta de recorridos/QA/despliegue | Cinco anchos, claro/oscuro, teclado, estados, concurrencia y restauración; matriz de tareas de diez roles | Puertas de CIERRE_POR_PROFESION_Y_VALIDACION superadas y evidencia independiente | Clínica y despliegue se aceptan en su entorno; no por suite sintética |

Los lotes 1–6 permiten avanzar sin ampliar el modelo ni inventar metodología.
Los lotes 7–10 deben prepararse con contratos/diccionarios concretos y pruebas
antes de la decisión correspondiente. No convertir un pendiente en un botón
que guarda una simulación como evaluación institucional.

## Entregable mínimo por cada corrección

1. Hallazgo que corrige, fuentes y alcance por rol/estado.
2. Contrato de entrada/salida y preservación de historia.
3. Implementación en su propietario, reutilizando capacidades reales.
4. Prueba positiva, negativa, regresión y efectos prohibidos; PostgreSQL si
   requiere bloqueos/constraints/concurrencia.
5. Gate de seguridad/persistencia/experto según riesgo, separado de la edición.
6. QA del recorrido afectado, documentación y commit local específico.

No aplicar automáticamente todos los cambios en una auditoría de lectura.
Ninguna corrección exige regalar permisos, borrar las tablas vacías o adoptar
umbrales universales para aparentar que el sistema está terminado.
