# O.R.I.O.N. — Laboratorio G09 de 15 candidatos

**Estado: PRUEBA TÉCNICA EXCLUSIVA. Sin autorización clínica.**
**Versión:** LAB-G09-20261015.
**Fuentes rectoras:** Documento Maestro D-108, D-125, D-131, D-135 y D-138; Documento 16, G06–G13; matriz G09 del 14/10/2026; Anexo A 2_Conceptos, 8_Evaluabilidad y 17_Casos_Prueba.

## Qué se ha implementado

Un laboratorio exclusivo de PHPUnit bajo tests/ con 15 plantillas del catálogo G09:

| Criterio | Subcomponentes | Etiquetas documentales G09 |
|---|---|---|
| COG-ATE | ATE-S01..03 | ATE-R-SOSTENIMIENTO, ATE-R-SELECCION, ATE-R-DISTRIBUCION |
| COG-EJE | EJE-S01..05 | EJE-R-INHIBICION, EJE-R-FLEXIBILIDAD, EJE-R-ACTUALIZACION, EJE-R-PLANIFICACION, EJE-R-RAZONAMIENTO |
| COG-LEN | LEN-S01..05 | LEN-R-DENOMINACION, LEN-R-FLUIDEZ, LEN-R-COMPRENSION, LEN-R-REPETICION, LEN-R-DISCURSO |
| COG-VIS | VIS-S01..02 | VIS-R-PERCEPCION, VIS-R-CONSTRUCCION |

Las etiquetas G09 son identificadores de referencia, **no valores clínicos**. Cada plantilla comprueba solamente la presencia de dos marcadores artificiales mediante AND: contexto LAB-CTX-VALIDO y hecho LAB-COMP-<subcomponente>. No existe mapeo desde datos de instrumentos, pacientes, observaciones, puntajes ni diagnósticos.

La compuerta LAB-EV-0/1/2 también es artificial, y **no** implementa las compuertas clínicas EV-CA/CE/CL/CV de G07. Cero o uno bloquean. Dos permite únicamente la comparación técnica.

## Aislamiento estricto

- Solo clases de tests/ (autoload-dev), sin rutas web, comandos de producción, migraciones, consultas SQL ni escrituras.
- Origen literal SINTETICO; actor SYN-ACTOR-*, caso LAB-CASO-*, evidencia SYN-EV-*, fuente SYN-SRC-* y episodio SYN-EP-*.
- Estructura de entrada estricta sin texto libre: se rechazan campos extra como cod_residente, valores observados, origen operacional y entradas de otro subcomponente.
- Rechazo de evidencia repetida por identidad o por combinación del mismo código, fuente y episodio.
- Cada ejecución produce traza de versión, criterio, subcomponente, etiqueta G09, compuerta, antecedentes, identificadores, procedencia y episodio.
- Las únicas salidas son COINCIDENCIA_TECNICA_SINTETICA, SIN_COINCIDENCIA_TECNICA y NO_EJECUTADO_POR_COMPUERTA_SINTETICA. resultado_clinico es siempre null, activacion_clinica es false y persistencia es NINGUNA.
- Ningún identificador sintético acredita independencia clínica G10. No se implementa D-131 para otros dominios.

## Ejecutar

Con dependencias de desarrollo instaladas, desde la raíz:

    php vendor/phpunit/phpunit/phpunit tests/Unit/SistemaExperto/LaboratorioCandidatosOrionTest.php

Los 15 casos se encuentran en tests/Fixtures/SistemaExperto/casos_candidatos_orion.php. Las pruebas abarcan los 15 positivos técnicos, conjunción incompleta, compuertas bloqueadas, rechazo de orígenes no sintéticos, evidencia ajena, campos clínicos, versiones no válidas y duplicados.

## Gobierno del conocimiento

No modificar PaquetesCandidatosCognitivos: los cuatro manifiestos siguen INACTIVO y sus reglas, mapeos, compuertas y dominios de resultado siguen vacíos. COG-MEM/D-131 se mantienen sin cambios. Los 15 casos técnicos no sustituyen los 20 CR-* ni los 26 patrones G12 pendientes de dictamen.

La activación clínica continúa condicionada a G04–G13, licencias/ediciones/idiomas, semántica aprobada, evidencia independiente, pruebas clínicas, actas y firmas profesionales. Este laboratorio no puede convertirse a conocimiento activo cambiando una bandera de estado.
