# Consulta cognitiva y sistema experto

Documentación de lectura actualizada el 17/10/2026 para la arquitectura híbrida.
No autoriza inferencia clínica ni nuevas reglas de O.R.I.O.N.

- [Interfaz médica y de Enfermería](INTERFAZ_MEDICA_RESULTADOS.md): ficha, acceso,
  resultados conservados, publicación y procedencia de la documentación recuperada.
- [Evidencias cognitivas](EVIDENCIAS_COGNITIVAS.md): observaciones operativas,
  interpretación instrumental, resultados expertos y límites de cada estado.
- [Contrato del informe cognitivo](CONTRATO_INFORME_COGNITIVO.md): resultados médicos,
  estados experimentales segregados y responsabilidades de Application/Presentation.
- [Entrega de Fase 4](../frontend/FASE_4_INFORME_COGNITIVO.md): rediseño de la misma pantalla y evidencia de pruebas.
- [Entrega de Fase 3](../frontend/FASE_3_RESUMEN_COGNITIVO.md): archivos, mensajes,
  pruebas, QA y pendientes.
- [Motor técnico independiente](../../motor-experto/README.md).
- [Rediseño del informe neurocognitivo](REDISENO_INFORME_NEUROCOGNITIVO_2026_10_17.md): conclusión, fundamento, faltantes, autonomía y revisión en lenguaje natural; auditoría restringida.
- [Consulta operativa sin simulaciones](CONSULTA_OPERATIVA_SIN_SIMULACIONES_2026_10_17.md): aislamiento de las pruebas respecto a la pantalla normal y al historial del residente.
- [Revisión profesional aprobada](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md): firmas separadas de Psicología y Medicina, sin sobrescritura.
- [Matriz de interpretación operativa](MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md): material concreto para que los profesionales revisen conocimiento y soportes antes de habilitar inferencia operativa.
- [Arquitectura vigente](../arquitectura/README.md).

El conocimiento documental o conservado no equivale a publicación autorizada.
`config/sistema_experto.php:lectura_clinica` permanece vacío. Las pruebas de esta
fase son sintéticas; no activan G09, Petersen ni motores TEST_ONLY sobre residentes.
La Fase 4 está implementada como lectura. La revisión profesional separada fue
autorizada posteriormente por la propietaria e implementada según su decisión
específica; la inferencia operativa con interpretaciones clínicas nuevas sigue
pendiente del conocimiento y los soportes aprobados correspondientes.
