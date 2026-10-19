# O.R.I.O.N. — Contrato y ejecución del puente G08 → A/P/U/I/X → Petersen

**Versión:** `PTE-DCL-G08-TEST-01`
**Fecha:** 17/10/2026
**Estado:** implementación experimental TEST_ONLY, pruebas específicas satisfactorias. No validado clínicamente, no habilitado con residentes reales y PR #15 aún en borrador.

## Fuentes oficiales

- [DCL 01 — Condiciones generales C01–C05](https://docs.google.com/document/d/1HUoV_3sWPO9F07zpD3UUkyamxBKM-1CuazZOgIdp9VU/edit).
- [DCL 02 — Cobertura de datos y brechas IADL](https://docs.google.com/spreadsheets/d/1f2b5Y2ZhsjMbcUtbY3tKuV7n386Bl9cfXd_kYSPBy9w/edit).
- [DCL 03 — Contrato Petersen y diccionario A/P/U/I/X](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit).
- [DCL 03A — 30 casos y 32 configuraciones](https://docs.google.com/spreadsheets/d/1UwW3TsUZsnw6fEEXLVbyAB4_UddEWgjAeoZ_3By5x00/edit).
- [DCL 04 — Explicación del resultado](https://docs.google.com/document/d/1wZqJRreRVbQld2WakWssWhehq8ZZshgEQg7RYHg78Pk/edit).
- [G08 — Auditoría instrumental de 15 componentes](G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md).
- [Integración experimental anterior de cinco dominios](INTEGRACION_CINCO_DOMINIOS_EXPERIMENTAL.md).

## Componentes implementados

1. `AdaptadorEstadosPetersenExperimental`: recibe el perfil **TEST_ONLY** de cinco dominios y un conjunto explícito de referencias sintéticas, distinto de las salidas inferidas por los motores.
2. `IntegradorReferenciasDCLExperimental`: ejecuta el orquestador de Memoria + G09, consulta el adaptador, ejecuta `ClasificadorPetersenExperimental` y construye un informe explicable, conservando el perfil de origen.
3. `dcl_puente_referencias_experimental.php`: comprueba separadamente las cuatro salidas PET-01…PET-04 y abstenciones por ausencia, invalidez G08, A/P contradictorios, C03 sin IADL, C04 bloqueado, fecha, identidad, versión y procedencia.
4. `dcl_puente_integracion_cinco_dominios.php`: prueba el recorrido integrado mediante las 15 aplicaciones simuladas de G09 y Memoria D-131 en SQLite `:memory:`. Comprueba que el perfil cognitivo descriptivo no se sobrescribe aunque el clasificador de laboratorio reciba A/P ficticios.

## Semántica y compuertas

El Documento 03 fija cinco estados **por dominio**:

| Estado | Traducción permitida exclusivamente en la simulación |
|---|---|
| `A` | Referencia ficticia **positiva** de una alteración bajo condiciones sintéticas completas de instrumento, tarea, edición, norma, idioma, población, confusores y revisión |
| `P` | Referencia ficticia **positiva** de rendimiento esperado bajo las mismas condiciones; nunca se deriva del simple no cumplimiento de una regla |
| `U` | Sin referencia admisible disponible |
| `I` | Referencia presente, pero con interpretación o perfil G08 incompleto |
| `X` | Referencias ficticias admisibles con interpretaciones A y P incompatibles; requiere adjudicación |

Los requisitos **C01–C05 se documentan por separado**: C01 cambio longitudinal, C02 objetividad, C03 independencia funcional con IADL, C04 evaluación diferencial, C05 calidad/evaluabilidad. No se deriva ninguno exclusivamente de una salida cognitiva. El adaptador solo acepta identificadores `SIM-`, residentes `SINTETICO-` y versión exacta; contrasta identidad, fecha de corte, versión de conocimiento y origen sintético declarado.

**Importante:** los campos `SIM-BAREMO`, `SIM-PRUEBA` y `SIM-REVISOR` son literales inventados para comprobar la lógica. No corresponden a normas validadas, personal acreditado ni fuentes reales. El adaptador no puede acreditar clínicamente ninguna referencia: es un contrato de ensayo que presupone los campos simulados proporcionados por el llamador.

## Cómo reproducir

```bash
php motor-experto/tests/dcl_puente_referencias_experimental.php
php motor-experto/tests/dcl_puente_integracion_cinco_dominios.php
php motor-experto/tests/dcl_petersen_experimental.php
php motor-experto/tests/orion_cinco_dominios_experimental.php
```

Estas pruebas forman parte de `.github/workflows/orion-g09-experimental.yml`, con PHP 8.3 y extensión PDO SQLite. No crean ni consultan residentes reales ni alteran migraciones del esquema operacional.

## Límites y trabajo pendiente

- **G08: 0 de 15 perfiles interpretativos admitidos.** Falta validar edición exacta de instrumentos, normas para la población objetivo, algoritmo interpretativo, idioma, edad, escolaridad, derechos y confusores.
- Las referencias pueden ser construidas por el llamador del experimento; `procedencia=VERIFICADA` en el fixture significa **declaración ficticia de prueba**, no comprobación criptográfica, legal o profesional. En una integración futura deberá venir de un adaptador autenticado y verificable.
- C03/IADL y C04/diferencial solo se simulan mediante actas ficticias; faltan los contratos de datos operativos, revisión independiente y autorizaciones.
- A/P se conservan en la **entrada sintética del clasificador**; nunca se reescriben como resultados reales de D-131 o G09 ni se agregan en un índice cognitivo global.
- La regresión general del repositorio es un proceso de CI distinto; no debe declararse aprobada hasta que GitHub emita una conclusión positiva.

**Sin revisión clínica ni G13-DCL, la compuerta de uso asistencial permanece cerrada.**
