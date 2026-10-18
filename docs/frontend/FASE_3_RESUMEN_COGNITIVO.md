# Fase 3 — Memoria y estado cognitivo

Fecha de trabajo: 17/10/2026. Alcance: resumen de lectura en la ficha existente;
sin Git, migraciones, permisos, reglas expertas ni datos operativos modificados.

## SOURCE OF TRUTH

Área: ficha clínica y lectura cognitiva. Vigentes: instrucción de Fase 3;
AGENTS aplicables; baseline/diccionario y extensiones aprobadas; arquitectura
híbrida; captura cognitiva; Policy y lectores actuales. Se localizó fuera del
checkout la documentación de interfaz/evidencias del 08/10 y se consolidó en
[interfaz](../sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md) y
[evidencias](../sistema-experto/EVIDENCIAS_COGNITIVAS.md), preservando procedencia.

Conflicto resuelto: las ubicaciones Backend/Frontend de la copia documental son
históricas frente al ADR vigente. La skill domain-architect también conserva esa
indicación antigua en su workflow; se aplica la arquitectura híbrida de mayor
autoridad. Mapeos de memoria propuestos no se convierten en reglas clínicas.
La configuración de publicación clínica sigue vacía.

## DOMAIN DESIGN y UX FLOW

Caso de uso: consultar hechos cognitivos del residente en el período de la ficha.
Actor: médico autorizado o Enfermería dentro de su alcance existente.
Entrada: residente y 7/30/90 días. Policy: `consultarResultados` existente.
Application: `ConsultarResumenCognitivoResidente` coordina controles y lector
experto; `LecturaResultadosExperto::resumenMemoriaEnPeriodo` selecciona y verifica
la evaluación conservada. Presentation: getter Livewire y parcial Blade.
Modelos: ControlCognitivo, Personal, Residente, EvaluacionExperta y relaciones
del lector. Sin Action, transacción, evento o escritura: consulta pura.

Ficha → Resumen → Memoria y estado cognitivo → observación/fecha/autor/estado →
por qué/alcance → control original desplegable → Análisis cognitivo autorizado.
Se conserva el selector de Fases 1/2, la navegación y los tokens existentes.
Se retiró del indicador compacto el fallback NULL → «Sin cambio observado».

Etapas seleccionadas: fuentes, arquitectura, selector UX, redacción, pruebas,
revisión de seguridad, QA visual y cierre. No se requiere dirección artística
nueva, formulario, gráfica ni motion adicional. El flujo reutiliza la ficha y
el Design System; no se crea estética o paleta paralela.

## Verificación

Antes de editar: Fases 1/2, `EstadoEvolucionResidenteTest|EvolucionComparadaResidenteTest`:
31 pruebas / 170 aserciones PASS en SQLite `:memory:`.

La batería de Fase 3 es [ResumenCognitivoResidenteTest](../../tests/Feature/ResumenCognitivoResidenteTest.php).
Comprueba vacío, repetición, olvido, duplicidad del episodio, fechas diferentes,
insuficiencia, FALSE/NULL, contradicción literal, campos distintos, texto libre
escapado, ventana/estado/residente, permisos y revocación, asignación de Enfermería,
lectura experta verificable, ausencia de soporte instrumental y exclusión de pruebas.

Resultados ejecutados:

| Verificación | Resultado y alcance |
|---|---|
| `php artisan test --compact --filter=ResumenCognitivoResidenteTest` | PASS final: 19 pruebas / 83 aserciones, SQLite en memoria. |
| `scripts/probar-postgres.ps1 -Filtro ResumenCognitivoResidenteTest` | PASS: 19 pruebas / 83 aserciones, cluster y runner desechables locales; parada confirmada. |
| Regresión `ResumenCognitivoResidenteTest\|EstadoEvolucionResidenteTest\|EvolucionComparadaResidenteTest\|MedicoFichaUnificadaTest\|FichaCuidadosTest\|ObservacionCognitivaTest` | PASS: 80 pruebas / 414 aserciones; antes de añadir los dos casos finales de valor desconocido y cuenta/familia. Estos dos se verificaron en las baterías finales de Fase 3. |
| PostgreSQL Fases 1/2/3 | PASS: 48 pruebas / 249 aserciones; misma salvedad de los dos casos finales. |
| `node --test tests/Frontend/ficha-evolucion.test.js tests/Frontend/observacion-cognitiva.test.js` | PASS: siete pruebas. |
| `npm test -- --test-name-pattern='ficha'` | El argumento quedó después del glob y ejecutó la suite frontend completa: 217 PASS, un FAIL, dos omitidas. El fallo ajeno al cambio es `shell-visual-consistency.test.js:43`: input esperado RGB(231,226,222), renderizado RGB(231,227,223). No se modificaron CSS ni tokens en esta fase. |
| `npm run build` | PASS final, Vite 8.3.1. |
| Pint de los dos PHP nuevos | PASS. |
| `node tests/Support/resumen-cognitivo-visual-qa.mjs` | PASS: 70 combinaciones; siete estados × cinco anchos × dos temas. Contraste mínimo medido 6,06:1. |

QA de la sección: vacío, observación documentada, señal preliminar, información
insuficiente, contradicción, resultado sustentado artificial y acceso restringido.
Anchos 1440/1280/1024/768/390, temas claro/oscuro. Se comprobaron textos, ausencia
de códigos visibles/literales inválidos, reflow sin overflow horizontal, targets
de 44 px, apertura/cierre por teclado, foco al original y contraste sobre colores
renderizados. Se inspeccionaron las capturas de los cinco anchos. El enlace
secundario original tuvo contraste insuficiente en oscuro; la corrección utiliza
un enlace textual y los tokens existentes, sin alterar componentes compartidos.

Los artefactos sintéticos están en
`%TEMP%/remembermind-resumen-cognitivo-qa/evidence/`: capturas y `resultados.json`.
El QA utiliza exclusivamente Blade real exportado desde tests, sin sesión o base
operativa. La integración y el cambio de período se comprueban con Livewire en
PHPUnit; no se declara una sesión HTTP real del navegador ni prueba manual con
lector de pantalla. Loading usa el target Livewire existente; no se simula latencia
de transporte en el QA aislado. No se modifica movimiento ni se añade formulario.

Revisión de seguridad separada de la implementación: PASS acotado al resumen;
Policy antes de datos, usuario fresco, permiso revocado, cuenta inactiva,
familia, residente ajeno y Enfermería asignada/jornada cerrada comprobados.
No se editaron Policies, permisos, autenticación, reglas ni persistencia.
La revisión no certifica otras superficies o la seguridad global del sistema.

Suite PHP global: no ejecutada; se seleccionaron las regresiones relacionadas.

## Archivos de esta fase

Los archivos compartidos con cambios previos conservan el trabajo de Fases 1/2.
Este inventario se limita a las incorporaciones de Fase 3, sin atribuirse el resto
del árbol de trabajo.

| Archivo | Cambio |
|---|---|
| [ConsultarResumenCognitivoResidente.php](../../app/Modulos/Clinica/Aplicacion/Consultas/ConsultarResumenCognitivoResidente.php) | Nuevo: lectura autorizada, estados descriptivos y originales. |
| [LecturaResultadosExperto.php](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php) | Añade `resumenMemoriaEnPeriodo`; reutiliza el lector conservado. |
| [FichaPaciente.php](../../app/Modulos/Clinica/Presentacion/Livewire/Paginas/FichaPaciente.php) | Añade getter del resumen y delega a Application. |
| [tab-resumen.blade.php](../../app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha/tab-resumen.blade.php) | Integra el parcial y retira la consulta del antiguo indicador compacto. |
| [estado-cuidados.blade.php](../../app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha/resumen/estado-cuidados.blade.php) | Estado cognitivo con el mismo contrato, sin fallback de NULL a «Sin cambio». |
| [memoria-estado-cognitivo.blade.php](../../app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha/resumen/memoria-estado-cognitivo.blade.php) | Nuevo: sección, alcance, originales y navegación autorizada. |
| [ResumenCognitivoResidenteTest.php](../../tests/Feature/ResumenCognitivoResidenteTest.php) | Nuevo: 19 escenarios sintéticos, integración y seguridad. |
| [resumen-cognitivo-visual-qa.mjs](../../tests/Support/resumen-cognitivo-visual-qa.mjs) | Nuevo: QA reproducible del Blade exportado. |
| [INTERFAZ_MEDICA_RESULTADOS.md](../sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md) | Documentación recuperada y adaptada; conserva procedencia y separa evidencia histórica. |
| [EVIDENCIAS_COGNITIVAS.md](../sistema-experto/EVIDENCIAS_COGNITIVAS.md) | Nuevo: contrato y catálogo de mensajes con sus fuentes. |
| [README experto](../sistema-experto/README.md) | Índice de las fuentes actuales. |
| Este documento | Entrega, decisiones, evidencia y limitaciones. |

## Mensajes y procedencia

Catálogo completo: [estados y frases con fuente](../sistema-experto/EVIDENCIAS_COGNITIVAS.md#estados-y-frases-con-fuente).
Ejemplos verificados:

- «Se documentó repetición de preguntas en un control cognitivo»: `repite_preguntas = true`.
- «Se documentó olvido de indicaciones en un control cognitivo»: `olvida_indicaciones = true`.
- La frase conjunta requiere ambos TRUE en el mismo control.
- «No existen observaciones cognitivas disponibles para el período consultado»:
  consulta sin controles vigentes y no futuros en el período.
- «Existen respuestas diferentes para el mismo indicador y momento de registro»:
  valores explícitos discordantes del mismo campo y segundo, con ambos originales visibles.
- El alcance «no acreditan por sí solas una alteración cognitiva objetiva» procede
  del encargo de la propietaria; las cautelas de atribución proceden de los adaptadores.
- Título e interpretación de un resultado experto: `LenguajeResultadosExperto`,
  exclusivamente después de autorización e integridad del lector.
- «No dispone de autorización…»: rechazo de la Policy; no significa ausencia de historia.

## Límites

- No se activa publicación clínica; «Hallazgo sustentado» solo se verifica con
  contratos artificiales dentro de pruebas aisladas.
- No se infieren causas, episodios independientes, progresión, normalidad,
  gravedad o alteración objetiva Petersen.
- La contradicción es solo discordancia literal simultánea, no una regla clínica.
- La fuente instrumental actual no se reinterpreta. Las instantáneas exclusivamente
  V2 quedan fuera de la lectura D-137 vigente.
- No se ejecutan G09/Petersen/TEST_ONLY ni se escriben diagnósticos/alertas.
- No se realiza commit ni push por instrucción expresa. Fase 4 requiere autorización.
