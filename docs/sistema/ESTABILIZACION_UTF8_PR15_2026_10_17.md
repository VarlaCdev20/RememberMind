# Estabilización UTF-8 — PR #15 (17/10/2026)

## Diferencia frente a develop

- `develop` 155a1516: CI 38182405676, quality PASS (1095 pruebas, 15 omitidas).
- PR #15, 0fbc3638: CI 39056759580, quality FAIL exclusivamente por JsonException UTF-8 en Fase3SignosVitalesTest::test_panel_permite_intervencion_en_alertas_reconocidas_asignadas_y_pendientes.
- Ambas ejecuciones usan PHP 8.3.35 y PHPUnit 12.5.36. Composer/package lock, el test y las implementaciones de presentación implicadas coinciden en los dos commits originales.
- El residente se creaba con Faker es_ES. `substr(nombre, 0, 1)` corta un byte, no una letra UTF-8. Una inicial acentuada generaba HTML/estado inválido que Livewire no puede serializar.
- Reproducción determinista: fijar únicamente `Álvaro Íñiguez` en el test original provoca el mismo JsonException tanto en la rama del PR como en una copia aislada del SHA de develop. No es evidencia de una regresión semántica del motor.

## Corrección localizada

Usar mb_substr con UTF-8 explícito para iniciales del panel de alertas, detalle, notificaciones y drawers relacionados. En el resumen Livewire del residente usar también mb_strtoupper UTF-8. Mantener nombres completos, permisos y reglas de alertas.

La regresión conserva el ensayo original de intervención con nombre acentuado. Casos adicionales ejercitan vocales acentuadas, iniciales multibyte minúsculas y ASCII en AlertasPanel, CampanaNotificaciones, ubicación, gráficos y resumen del residente; se comprueban iniciales completas y HTML serializable.

## Límites y verificación

No se modifican motor-experto, conocimiento, pesos, clasificadores, fixtures expertos, esquema ni autorización. La prueba de develop es exclusivamente una reproducción en worktree; no se publica en develop.

- Reproducción previa: FAIL por Malformed UTF-8 en PR y develop, mismo método y mecanismo Livewire.
- Regresión original corregida: PASS, 1 prueba / 14 aserciones.
- Fase3SignosVitalesTest ampliado: PASS, 23 pruebas / 125 aserciones antes de añadir la comprobación de iniciales del resumen.
- Los 12 scripts independientes/de investigación del motor pasan, incluidos D-130, G08/G09, Petersen y puente de cinco dominios. Sin cambios de semántica.
- npm run build y composer validate --strict --no-check-publish: PASS.

La suite general y PostgreSQL se volverán a ejecutar en la CI del nuevo SHA. El PR continúa en borrador: una CI técnica no constituye aprobación clínica ni autorización para activar el prototipo.

Verificación final focal: Fase3SignosVitalesTest + Fase3AlertasTest + PanelAlertaPreparadoTest: 36 pruebas, 181 aserciones, PASS; incluye las iniciales del resumen. PHP lint y git diff --check: PASS.
