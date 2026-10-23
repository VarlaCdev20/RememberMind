# Conciliación de tres perfiles DCL de prueba con fuentes originales

**Fecha de consulta:** 22/10/2026. **Base consultada:** instalación local `remembermind_dev`. Esta es una verificación de procedencia; no incorpora un juicio clínico ni modifica registros.

## Resultado

Los perfiles PET-01/PET-02 conservados para Elena Vargas Mamani, Hugo Romero Torrico y Mercedes Choque Rivera proceden de ejecuciones `PRUEBA_TECNICA` con versión `SIM-ORION-LOCAL-1`. `FuenteCasoSimulado` asigna los estados de dominio y C01–C05 desde la matriz `SIM-03A`; sus referencias se declaran sintéticas. Esas salidas históricas no se pueden transformar en valoraciones clínicas cambiando etiquetas o estados. La consulta institucional ordinaria las excluye mediante `AlcanceLecturaExperta`.

| Residente | Perfil técnico conservado | Aplicaciones observacionales | Aplicaciones funcionales | Aplicaciones cognitivas instrumentales | Valoraciones Petersen originales |
| --- | --- | ---: | ---: | ---: | ---: |
| Elena Vargas Mamani | PET-01, amnésico de dominio único | 57 | 10 | 0 | 0 |
| Hugo Romero Torrico | PET-02, amnésico multidominio | 57 | 8 | 0 | 0 |
| Mercedes Choque Rivera | PET-02, amnésico multidominio | 57 | 9 | 0 | 0 |

Cada residente tiene un PDF privado de transcripción histórica (C01, C03 y C04, respectivamente). Se comprobó que los tres archivos existen y tienen hash registrado. El texto de cada PDF conserva observaciones y advierte expresamente que no añade puntuaciones, diagnósticos ni firmas. Los hallazgos longitudinales y funcionales son fuentes reales de seguimiento, pero no equivalen a cinco mediciones objetivas ni a nueve juicios Petersen originales.

## Incorporación válida

1. Psicología registra una aplicación cognitiva realmente realizada por residente con instrumento, edición, idioma, fecha clínica, aplicador, preguntas/respuestas o tiempos originales, escolaridad, condiciones y documento/localizador si se transcribe. El perfil interpretativo debe corresponder exactamente al método y a la población mayor de 60 años; las aplicaciones observacionales no se reclasifican.
2. Medicina y Psicología, cada una dentro de sus competencias, registran los juicios originales de los cinco dominios y C01/C03/C04/C05 con soportes identificables. C02 se deriva; no se captura. La fecha y autor de cada nuevo juicio deben corresponder al acto real, no a la simulación de octubre.
3. Se ejecuta una **nueva** consulta institucional. Una salida orientativa sólo se publica si supera el contrato de admisibilidad, cinco dominios, C01–C05 y gobierno de reglas. El historial técnico queda separado e inmutable.

**Pendiente material:** no se localizaron en estos tres PDFs ni en `aplicaciones_instrumento` protocolos cognitivos con puntuaciones/respuestas originales, y `valoraciones_petersen_originales` tiene cero filas para los tres residentes. Sin esos originales y su interpretación atribuible, el resultado institucional correcto sigue siendo abstención; las observaciones se muestran como tales.

**Fuentes internas:** `CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md`, `PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md`, `SIMULACION_LOCAL_2026_10_17.md`, `app/Modulos/ApoyoDecision/Infraestructura/Simulacion/FuenteCasoSimulado.php`, `app/Modulos/ApoyoDecision/Aplicacion/Consultas/AlcanceLecturaExperta.php`.
