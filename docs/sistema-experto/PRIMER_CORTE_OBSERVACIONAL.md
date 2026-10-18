---
title: "Primer corte funcional de hechos observacionales"
status: EXPERIMENTAL
version: "OBS-20261017-v1"
clinical_activation: false
schema_changed: false
---

# Primer corte funcional de hechos observacionales

## Objetivo
Mostrar los primeros resultados verificables a partir de datos de controles cognitivos ya existentes, sin esperar la formalización clínica completa de los cinco dominios. El piloto es estrictamente descriptivo y de solo lectura.

## Consulta y reglas
- Ruta existente: /admin/medico/residente/{residente}/evaluacion-cognitiva/resultados-experto.
- También disponible en la consulta existente de Enfermería, exclusivamente bajo su Policy y alcance de residentes asignados.
- Fuente: controles_cognitivos, mismo residente, estado VIGENTE, fecha no futura.
- Muestra: últimos 30 controles ordenados de forma estable. Un registro adicional permite saber cuándo hay truncamiento.
- OBS-COG-001: SI el control vigente marca repite_preguntas = verdadero, ENTONCES reportar observación de repetición de preguntas.
- OBS-COG-002: SI el control vigente marca olvida_indicaciones = verdadero, ENTONCES reportar observación de olvido de indicaciones.
- OBS-COG-003: SI el control vigente marca cambio_cognitivo = verdadero, ENTONCES reportar que se señaló cambio cognitivo en un control.
- Las reglas incluyen identificador, versión, condición explícita, estados CUMPLE/NO_CUMPLE/NO_EVALUABLE, total de registros interpretables y referencias a los controles positivos (identificador, fecha y autor).
- Falso significa únicamente que no se marcó el indicador en ese registro. NULL no equivale a falso ni a normalidad.
- Un solo indicador positivo satisface su regla descriptiva; la salida no se presenta como cambio longitudinal corroborado ni alteración cognitiva objetiva.

## Alcance y limitaciones
- NO emite DCL, patrones de Petersen, Alzheimer, demencia, gravedad, probabilidades, alteración objetiva ni recomendaciones de tratamiento.
- NO crea evaluaciones expertas ni alertas, NO modifica controles, conocimientos o tablas, NO publica un contrato de inferencia clínica.
- El conjunto de reglas vive provisionalmente como lógica determinista aislada y versionada en código. NO implica que las tablas reglas_expertas estén alimentadas ni que exista todavía una pantalla editorial.
- La consulta reutiliza los permisos médicos/de Enfermería definidos en EvaluacionExpertaPolicy::consultarResultados.
- No concluye ausencia de dificultad desde NO_CUMPLE y se abstiene cuando los campos están sin registrar.
- No pretende verificar todos los 65 tipos de evidencias documentales; es el primer corte completo de tres hechos del esquema vigente.

## Prueba y aceptación
Pruebas en tests/Feature/SistemaExperto/HechosObservacionalesTest.php:
1. Caso con evidencia verdadera, falsa y NULL produce los tres estados; la vista muestra la fuente y no escribe evaluación experta.
2. Registros anulados, futuros y de otro residente no participan.
3. Sin controles todos los indicadores son NO_EVALUABLE.

Ejecutar en una base de datos **desechable** con entorno testing:
php artisan test --filter=HechosObservacionalesTest
php artisan test --filter=ResultadosExpertoResidenteTest

Las pruebas no reemplazan validación clínica ni justifican cargar reglas clínicas. Ningún cambio a develop/producción se hará sin revisión del PR.

## Siguiente iteración
1. Gestionar definiciones versionadas en las tablas expertas existentes (alta/borrador/ensayo/suspensión/archivo), con autoría y motivos. No editar retrospectivamente versiones utilizadas.
2. Registrar pruebas por regla/versión con resultados reproducibles y evidencia exacta.
3. Implementar comparación con evaluación médica independiente, indicando coincidencia, discrepancia y datos no comparables. Concordancia no es validez clínica por sí sola.
4. Incorporar ventana/recurrencia de observaciones y signos vitales como **módulos descriptivos separados**, con criterios definidos y fuentes verificadas.
5. Ampliar progresivamente evaluación cognitiva por dominio y continuar Petersen SOLO en laboratorio hasta la autorización clínica del alcance sucesor.
