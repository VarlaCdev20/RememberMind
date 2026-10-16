# Cuatro criterios cognitivos — formalización técnica V1

**Versión:** FC4-20261016-v1.0-TECNICA  
**Estado:** formalización documental y contratos consultables; **sin validación ni activación clínica**.

## Alcance

| Criterio | Subcomponentes | Evidencias candidatas | G07 | Plantillas G09 |
|---|---:|---:|---|---:|
| COG-ATE | 3 | 6 (EV-ATE-01…06) | EV-CA-0/1/2 + MOD-CA-1 | 3 |
| COG-EJE | 5 | 5 (EV-EJE-01…05) | EV-CE-0/1/2 + MOD-CE-1 | 5 |
| COG-LEN | 5 | 6 (EV-LEN-01…06) | EV-CL-0/1/2 + MOD-CL-1 | 5 |
| COG-VIS | 2 | 5 (EV-VIS-01…05) | EV-CV-0/1/2 + MOD-CV-1 | 2 |
| Total | **15** | **22** | **16** | **15** |

El archivo `FORMALIZACION_COG4_V1.json` especifica por criterio: subcomponentes y nombre, evidencias y fuentes operativas, condiciones propuestas de evaluabilidad, descriptores G09, antecedentes positivos AND, confusores, política de procedencia/episodios y trazabilidad. **Todos los instrumentos, mapeos y consecuencias clínicas siguen pendientes de ratificación.**

La clase `ContratosFormalesCuatroCriterios` es de **solo lectura** y rechaza catálogos que intenten marcar el conocimiento activo. No hereda `PaqueteConocimiento`, no se registra en el contenedor como servicio de inferencia y no contiene métodos para evaluar residentes. Ninguna migración, controlador médico, permiso, valor semántico persistido ni `PaquetesCandidatosCognitivos` ha sido modificado.

## Semántica de las compuertas

- `-0`: sin evidencia utilizable suficiente; no inferir normalidad.
- `-1`: señal candidata, sin evaluación integrada; no emitir resultado.
- `-2`: estado conceptual que **solo podría** habilitarse con instrumento/componente aprobado y contexto suficiente; este contrato no lo autoriza por sí solo.
- `MOD-*-1`: advertencia separada, no resta ni invalida automáticamente.

Los valores `ATE-R-*`, `EJE-R-*`, `LEN-R-*` y `VIS-R-*` son **identificadores documentales propuestos**; no se insertan en `valores_semanticos` ni crean diagnósticos, escalas, puntuaciones, categorías globales o resultados clínicos. No se reutiliza D-131 (reservado a COG-MEM).

## Verificación

Ejecutar con dependencias de desarrollo:

```bash
php vendor/phpunit/phpunit/phpunit tests/Unit/SistemaExperto/FormalizacionCuatroCriteriosTest.php
```

Las pruebas comprueban recuentos, integridad, ausencia de reglas ejecutables, de resultados clínicos, de firmas y de mapeos autorizados. **No sustituyen la validación profesional G12.** Tampoco sustituyen las 20 situaciones CR-* para dictamen ni los 34 VT-COGMEM definidos en el Anexo A.

## Gobierno y pendientes

| Compuerta | Trabajo pendiente para una activación real |
|---|---|
| G06 | Aprobar cada valor y mapeo observacional por tarea. |
| G07 | Ratificar cada estado -0/-1/-2 y modificador. |
| G08 | Identificar edición, licencia, idioma, normas, subprueba y respuestas. |
| G09 | Aprobar consecuencias, reglas positivas y política propia de resultados. |
| G10 | Adjudicar confusores, exclusión por tarea y episodios compartidos. |
| G11 | Cerrar trazabilidad bibliográfica, incluidos históricos FC-026/027/028 y FM-001. |
| G12 | Fijar expectativas profesionales ciegas y comparar salidas por versión. |
| G13 | Firmar actas y decidir alcance clínico institucional. |

**No editar el estado `INACTIVO` ni activar resultados sin estos controles.** Para una decisión `NO RESOLUBLE EN V1`, conservar los motivos y diferir el subcomponente sin forzar una conclusión.

Fuentes oficiales: [Anexo A](https://docs.google.com/spreadsheets/d/1lPFoZD6LlIoOkuWsLN3VTjvsgBowBl0mBQekjyOiyVA/edit), [Documento 15](https://docs.google.com/document/d/13jgBlNjOmE3GaaDGJr3YSjiuLkOXwNBxIecWIJSTF6I/edit), documentos G06/G07/G08/G09/G10/G12 del expediente y Documento Maestro D-108, D-123, D-131 y D-135.
