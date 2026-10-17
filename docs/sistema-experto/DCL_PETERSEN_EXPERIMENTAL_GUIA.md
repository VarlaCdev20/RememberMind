# O.R.I.O.N. — Clasificación Petersen experimental DCL

**Versión del contrato:** `DCL-PET-20261017-v0.1`  
**Estado:** implementado y probado **únicamente con casos sintéticos**. No habilitado para uso asistencial, no validado clínicamente, no fusionado en producción.

## Fuentes canónicas del proyecto

- [01 — Criterios clínicos de DCL](https://docs.google.com/document/d/1HUoV_3sWPO9F07zpD3UUkyamxBKM-1CuazZOgIdp9VU/edit)
- [02 — Evidencias y cobertura de BDD](https://docs.google.com/spreadsheets/d/1f2b5Y2ZhsjMbcUtbY3tKuV7n386Bl9cfXd_kYSPBy9w/edit)
- [03 — Contrato Petersen](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit)
- [03A — Matriz original, 30 casos y 32 configuraciones](https://docs.google.com/spreadsheets/d/1UwW3TsUZsnw6fEEXLVbyAB4_UddEWgjAeoZ_3By5x00/edit)
- [04 — Contrato de explicación](https://docs.google.com/document/d/1wZqJRreRVbQld2WakWssWhehq8ZZshgEQg7RYHg78Pk/edit)
- [05 — Validación G12-DCL](https://docs.google.com/document/d/1_o4E-r0wlKjvrFzkBfxCXSCNI7yff5cobEovZSbuyTY/edit)
- [EXP-01 — Desarrollo experimental antes de validación](https://docs.google.com/document/d/1jLV4WU1PMvNHk7hnERVCmVCT6QaXtBe2eIyQMKBACPc/edit)

## Implementación entregada

- `ClasificadorPetersenExperimental.php`: cinco criterios C01–C05 y cinco dominios MEM/ATE/EJE/LEN/VIS. Cada criterio tiene S/N/U/X/B (B solo en C04) y cada dominio A/P/U/I/X. Se exigen **soportes sintéticos explícitos** para estados S/N/A/P, un mismo caso y fecha de corte.
- PET-01 = MEM A, cero dominios adicionales A; PET-02 = MEM A, ≥1 adicional A; PET-03 = MEM P, exactamente un adicional A; PET-04 = MEM P, ≥2 adicionales A. Todo sujeto a compuertas C01…05 y todos los dominios evaluados A o P.
- Abstenciones: `ABSTENCION_CONTRADICCION`, `ABSTENCION_EVALUACION_DIFERENCIAL`, `ABSTENCION_INFORMACION_INSUFICIENTE`, `SIN_PATRON_CLASIFICABLE_POR_REQUISITO`, `REQUIERE_ADJUDICACION_CONTRATO`. No son diagnósticos.
- `ConstructorInformePetersenExperimental.php`: texto explicable por patrón o abstención y trazabilidad, con aviso visible de simulación.
- `dcl_petersen_experimental.php`: lee **directamente los valores importados** de dos archivos JSON derivados de Matriz 03A: `dcl_03a_30_casos.json` y `dcl_03a_32_configuraciones.json`.
- El flujo de CI `orion-g09-experimental.yml` comprueba sintaxis, ejecuta las suites G09 y la suite DCL de Petersen sobre PHP 8.3.

## Ejecutar la prueba

```sh
php motor-experto/tests/dcl_petersen_experimental.php
```

## Hallazgo de auditoría: discrepancia entre las fuentes

La **fila 00** de la pestaña `10_Cobertura_Exhaustiva` tiene como salida `REQUIERE_ADJUDICACION_C02_SIN_SOPORTE`, mientras que el Documento 03 y los casos `CAS-09` y `CAS-27` usan `REQUIERE_ADJUDICACION_CONTRATO` cuando C02=S pero todos los dominios son P. El código sigue el Documento 03 y los casos sintéticos, y **la prueba registra expresamente esta diferencia sin modificar ni normalizar la fuente original**. Queda pendiente el acuerdo de un único código documental para esa abstención.

La precedencia de discrepancia C02 frente a C02=N se codifica de acuerdo con `CAS-11`: una afirmación objetiva contradictoria con los dominios requiere adjudicación, no un descarte forzado.

## Limitaciones que NO están resueltas

Los soportes son **referencias inventadas y autocontenidas para verificar la lógica**, no pruebas interpretadas clínicamente. **No hay mapeo automático entre salida descriptiva D-131 (Memoria), resultados positivos G09 y estados DCL A/P**: el Documento 03 prohíbe tal inferencia sin evaluaciones específicas acreditadas. No existen todavía versiones normativas G08 autorizadas ni implementación de los requisitos funcionales IADL/diferencial en un caso real.

El clasificador permanece desacoplado de los datos de Laravel, sin escritura a historiales clínicos y sin integración asistencial. Esta etapa termina la **traducción a código y verificación de reglas documentales PET-01..04**, pero no prueba que sea clínicamente correcto clasificar a un residente.

## Próxima etapa

Conservar DCL como componente experimental segregado e integrar una interfaz de **casos de simulación de cinco dominios**, sin transformar implícitamente resultados D-131/G09 en estados A/P. Avanzar de forma paralela en G08 y en la especificación técnica de evidencia positiva de funcionalidad IADL, historial de cambio y evaluación diferencial. La comparación clínica con profesionales y la autorización G13 ocurrirán posteriormente.
