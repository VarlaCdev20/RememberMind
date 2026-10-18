# Fase 4 — Informe médico de resultados expertos

Fecha de trabajo: 17/10/2026. Implementación de lectura en la pantalla existente
**Análisis cognitivo**. [Contrato completo de salida y fuentes](../sistema-experto/CONTRATO_INFORME_COGNITIVO.md).

## Fuentes, alcance y arquitectura

Se comprobaron los resultados de las tres fases previas y sus regresiones
relacionadas. Se conservan sus cambios no confirmados y el resto del trabajo
preexistente. No se modifican Git, migraciones, tablas, reglas, umbrales, permisos
ni configuración de activación. No se ejecuta Petersen sobre residentes.

SOURCE OF TRUTH: instrucción de Fase 4, AGENTS aplicables, ADR híbrido,
contrato físico D-137, decisión de conservación V2, interfaz y evidencias,
lector/lenguaje/Policy/modelos actuales. G09 se localizó en el worktree
`pr15-ci-utf8`, exclusivamente experimental. No se traslada su motor al checkout.
La configuración clínica permanece vacía.

DOMAIN DESIGN: consulta por médico o Enfermería autorizados, residente y
evaluación/criterio explícitos. Lector: recuperación e integridad conservadas.
Presentador Application: redacción y límites, sin inferencia o escritura.
Livewire: selección y recuperación de errores. Blade/CSS: presentación compartida.
No aplica Action/transacción/eventos: no hay mutaciones institucionales.

UX FLOW: ficha/Mis residentes → misma ruta de análisis → contexto → conclusión →
cinco áreas seleccionables → significado → evidencia/fundamento → historial →
retorno contextual. Skills aplicadas: fuentes, entrega, flujo UX, Design System,
redacción, pruebas, revisión de seguridad y QA. No se requiere imagen nueva,
otra paleta, formulario clínico, gráfica o animación adicional.

## Archivos de Fase 4

| Archivo | Cambio |
|---|---|
| [PresentarInformeCognitivo.php](../../app/Modulos/ApoyoDecision/Aplicacion/DTOs/PresentarInformeCognitivo.php) | Nuevo presentador de informe y catálogo segregado G09 para pruebas. |
| [LenguajeResultadosExperto.php](../../app/Modulos/ApoyoDecision/Aplicacion/DTOs/LenguajeResultadosExperto.php) | Traducciones de memoria y seis estados G09; sin nuevas reglas. |
| [LecturaResultadosExperto.php](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php) | Añade procedencia de criterio/evaluabilidad para el DTO y comentarios españoles. Conserva las verificaciones de lectura. |
| [AlcanceLecturaExperta.php](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/AlcanceLecturaExperta.php) | Excluye ejecuciones TEST_ONLY/EXPERIMENTAL y versiones G09-EXP de la vía asistencial. |
| [ResultadosExpertoResidente.php](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php) | Usa el presentador y relee la cuenta antes de reautorizar; conserva selección/fuente/drawer/reintento. |
| [resultados-experto-residente.blade.php](../../app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php) | Misma pantalla, cinco bloques y redacción de Application. |
| [evidencias.blade.php](../../app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/evidencias.blade.php) | Mantiene consulta de fuente; retira ID de la lectura principal. |
| [historial.blade.php](../../app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/historial.blade.php) | Bloque 5, con fecha/versión/estado y selección; ID interno no visible. |
| [complemento.blade.php](../../app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/complemento.blade.php) | Extrae el complemento existente y muestra límites de Application. |
| [vistas_modulares.php](../../config/vistas_modulares.php) | Registra el parcial del mismo propietario. |
| [expert-results.css](../../resources/frontend/styles/design-system/patterns/expert-results.css) | Composición de informe y grid de áreas con tokens vigentes. |
| [InformeCognitivoTest.php](../../tests/Feature/InformeCognitivoTest.php) | Contratos, integración Livewire/HTTP, fuentes, historial y seguridad. |
| [PresentacionG09AisladaTest.php](../../tests/Feature/PresentacionG09AisladaTest.php) | Seis estados técnicos y rechazo de uso asistencial/fuera de testing/desconocido. |
| [InformeCognitivoFixture.php](../../tests/Support/InformeCognitivoFixture.php) | Filas artificiales con traza conservada; ningún motor ejecutado. |
| [informe-cognitivo-visual-qa.mjs](../../tests/Support/informe-cognitivo-visual-qa.mjs) | QA del HTML real exportado con datos sintéticos. |
| [Contrato de informe](../sistema-experto/CONTRATO_INFORME_COGNITIVO.md), [interfaz](../sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md), [índice experto](../sistema-experto/README.md) y este documento | Contratos, trazabilidad, entrega y límites. |

El parcial de fundamento existente se revisó y reutilizó sin cambiarlo. Las
modificaciones anteriores de inventario experto/conservación y de Fases 1–3 no
pertenecen a esta entrega.

## Verificación y limitaciones

Resultados finales ejecutados:

| Comando / gate | Resultado y alcance |
|---|---|
| `php artisan test --compact --filter='InformeCognitivoTest\|PresentacionG09AisladaTest\|ResumenCognitivoResidenteTest\|EstadoEvolucionResidenteTest\|EvolucionComparadaResidenteTest\|MedicoFichaUnificadaTest\|FichaCuidadosTest'` | **PASS: 89 pruebas / 460 aserciones**, SQLite `:memory:`. Incluye las tres fases previas y sus integraciones pertinentes. |
| `scripts/probar-postgres.ps1 -Filtro 'InformeCognitivoTest\|PresentacionG09AisladaTest\|ResumenCognitivoResidenteTest'` | **PASS: 50 pruebas / 247 aserciones**, PostgreSQL local con cluster/runner exclusivos desechables; parada confirmada. |
| Selección final de historial/fuente después de conectar el array del presentador | **PASS: una prueba / nueve aserciones**. |
| `npm run build` | **PASS**, Vite 8.3.1. |
| Pint de DTOs, Livewire y pruebas nuevos/modificados | **PASS**. |
| `git diff --check` y revisión de patrones de riesgo del alcance | **PASS**, sin staging, commit o cambio de rama. |
| `node tests/Support/informe-cognitivo-visual-qa.mjs` | **PASS: 110 combinaciones**, 11 estados × cinco anchos × dos temas. Contraste mínimo de texto visible medido: **4,69:1**. |

Estados comprobados: los tres resultados de memoria, ausencia de evaluación,
insuficiencia, preliminar, publicación bloqueada, integridad incompatible,
Enfermería, lectura técnica y error recuperable. Anchos 1440/1280/1024/768/390,
claro/oscuro, detalles abiertos/cerrados, jerarquía de cinco bloques, targets de
44 px, teclado/foco de disclosures, ausencia de literales inválidos, reflow de
tabla/fundamento y contraste. Se inspeccionaron las capturas de los cinco anchos.
No se añadió movimiento; el patrón mantiene su regla de movimiento reducido.

Artefactos sintéticos: `%TEMP%/remembermind-informe-cognitivo-qa/evidence/`;
capturas y `resultados.json`. Evidencia PostgreSQL:
`%TEMP%/remembermind-pg-4f4dbd42f0d54f4f926b237c2a370c77.junit.xml` y `.log`.

Los casos de memoria conservan el código original y comprueban ausencia de
escrituras de resultados, evidencias, diagnósticos y alertas. El historial
mantiene la evaluación exacta y su versión; un criterio de otra versión bloquea
la conclusión sin sustituir una evaluación anterior válida. La consulta de
fuente conserva el permiso instrumental y distingue dato actual de historial.
El fallo SQL se reporta y muestra recuperación segura, sin SQL privado en UI.

Revisión de seguridad de solo lectura al cierre: acceso reautorizado por Policy,
usuario fresco, permisos revocados, cuenta inactiva, preview, familiar,
Enfermería asignada/no asignada/jornada cerrada, IDOR de evaluación/evidencia y
exclusión experimental cubiertos. No se modifican competencias ni permisos.
Dictamen técnico acotado al informe: **PASS**; no es certificación global de
seguridad ni aprobación clínica independiente.

Los estados positivos se ejercitan con contratos artificiales en bases efímeras;
no habilitan publicación en la aplicación institucional. El contrato clínico
real continúa vacío. Las nueve pruebas de G09 verifican traducción y rechazo,
sin atribuirles validación de inferencia o de instrumentos.

El acceso automatizado a la pestaña del usuario fue rechazado por la configuración
«Local browser use is disabled in Work settings». No se extrae su sesión ni se
elude esa restricción. QA usa HTML real exportado por PHPUnit y un navegador
aislado local. La selección, fuente, drawer, reintento, acceso y revocación se
ejercitan con Livewire/HTTP en pruebas, no con una sesión real del residente.

No se declara validación clínica ni revisión humana del conocimiento. G09 se
verifica como **contrato de presentación**, sin ejecutar/copiar su motor. La
suite PHP global no forma parte del gate proporcional de esta pantalla.

Legacy: no se incorpora V1 ni otro módulo/motor/pantalla. El parcial histórico
`simulado.blade.php` no es consumidor de esta pantalla; queda fuera del cambio.
No se realiza commit ni push por la restricción expresa de no modificar Git.

**La Fase 5 requiere autorización de la propietaria.**
