---
title: "Interfaz médica de resultados y resumen cognitivo"
status: CURRENT
version: "1.4"
last_reviewed: 2026-10-17
source_of_truth: false
clinical_runtime_verified: false
clinical_inference_activated: false
---

# Interfaz médica de resultados y resumen cognitivo

Documento adaptado a la arquitectura híbrida vigente. Procedencia: versión 1.2
del 08/10/2026 encontrada en
`C:/Users/CARLAENCINAS/Documents/Codex/2026-10-10/files-pasted-by-the-user-prompt/work/RememberMind/docs/sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md`.
Aquella copia conserva las verificaciones históricas; no se atribuyen sus cifras
de pruebas o QA al código actual. La instrucción de Fase 3 del 17/10/2026 gobierna
esta ampliación. No se restauran namespaces Backend/Frontend ni reglas antiguas.

## Navegación y autorización

La ficha conserva su pestaña Resumen. La sección **Memoria y estado cognitivo**
utiliza el período de **Estado y evolución del residente**: 7, 30 o 90 días.
No incorpora menú ni dashboard.

**Análisis cognitivo** abre la consulta existente:

- Medicina: `admin.medico.residente.resultados-experto`.
- Enfermería: `admin.enfermeria.pacientes.resultados-experto`.

La consulta usa [EvaluacionExpertaPolicy](../../app/Modulos/ApoyoDecision/Presentacion/Policies/EvaluacionExpertaPolicy.php):
cuenta activa, rol real permitido, permisos existentes de residente/control/ficha,
Policy del residente y personal activo cuando corresponde. Familia y preview
no reciben el resumen. Enfermería consulta sus residentes asignados según la
misma Policy; la vía médica conserva su autorización independiente. No se
modifican permisos ni Policies. Cada lectura del resumen obtiene el usuario
actual desde la base para respetar revocaciones.

La falta de autorización se presenta como consulta restringida; no se confunde
con ausencia de registros. El servicio directo rechaza el acceso antes de leer
los controles.

## Contenido de la ficha

Se muestran observación principal, fecha, autor disponible, estado del dato,
«¿Por qué?», «¿Qué significa?» y cada control original desplegable, con sus quince
indicadores y texto guardado. No se muestran PK, códigos de personal, códigos de
memoria ni códigos expertos. Los datos ausentes permanecen «No observado» o
«No registrada». Texto libre se escapa y no se analiza para extraer hechos.

El principal es el control más reciente que documenta repetición de preguntas,
olvido de indicaciones o dificultad descriptiva de memoria; si no hay uno,
se utiliza la observación más reciente. Su fecha se muestra explícitamente.
Los hechos de controles diferentes nunca se fusionan en una frase atribuida a
un solo control. Todos los originales del período permanecen disponibles.

## Publicación experta

[LecturaResultadosExperto](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php)
conserva la validación de publicación por versión, evaluabilidad, dominios,
traza, condiciones, soporte, participación y componente instrumental específico.
El resumen reutiliza esa lectura y
[LenguajeResultadosExperto](../../app/Modulos/ApoyoDecision/Aplicacion/DTOs/LenguajeResultadosExperto.php).
No reconstruye una inferencia desde los controles actuales.

El registro `config/sistema_experto.php:lectura_clinica` está vacío. Fase 3 no
habilita contratos. «Hallazgo sustentado» solo se presenta en el bloque separado
de resultado experto cuando un contrato explícito y la integridad de la lectura
lo permiten. La fecha y el solicitante son los de la evaluación, sin atribuirle
la autoría de los controles. Los hallazgos mixtos conservan el significado del
lector y no se convierten en contradicción operativa.

Se excluyen las lecturas de prueba, los estados/orígenes de prueba y las versiones
identificadas como TEST. Una evaluación exclusivamente V2 sin criterios D-137
no tiene contrato de lectura clínica en esta superficie. No se ejecutan G09,
Petersen ni motores TEST_ONLY. El enlace abre el historial existente; el usuario
puede seleccionar allí la evaluación por la fecha mostrada.

## Responsabilidades actuales

- [ConsultarResumenCognitivoResidente](../../app/Modulos/Clinica/Aplicacion/Consultas/ConsultarResumenCognitivoResidente.php):
  autorización, período, hechos, procedencia y discordancias literales.
- [FichaPaciente](../../app/Modulos/Clinica/Presentacion/Livewire/Paginas/FichaPaciente.php):
  entrega el período actual a Application y distingue acceso restringido.
- [Vista del resumen](../../app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha/resumen/memoria-estado-cognitivo.blade.php):
  presentación accesible con componentes y tokens existentes.
- [Contrato de evidencias](EVIDENCIAS_COGNITIVAS.md): separación de observación,
  interpretación instrumental y resultado experto.
- [Entrega y verificación de Fase 3](../frontend/FASE_3_RESUMEN_COGNITIVO.md).

La revisión profesional, la publicación institucional de conocimiento nuevo y
la activación clínica siguen fuera del alcance de esta fase.

## Fase 4 — Análisis cognitivo

La misma pantalla de resultados se reorganiza en conclusión, cinco áreas,
significado, evidencia/fundamento e historial. Conserva las rutas y las funciones
de selección, paginación y consulta de fuente. El lector no ejecuta inferencias.
La redacción y las limitaciones pertenecen al presentador de Application;
observaciones generales no se presentan como áreas evaluadas. Se retiran los
botones sin flujo funcional de exportación/nueva evaluación/revisión.

El [contrato de salida](CONTRATO_INFORME_COGNITIVO.md) documenta las tres salidas de
memoria y los seis estados G09, estos últimos limitados a pruebas aisladas sin
ruta asistencial. La [entrega de Fase 4](../frontend/FASE_4_INFORME_COGNITIVO.md)
registra archivos, evidencia y límites. No cambia la autorización, el esquema,
las reglas ni la configuración de publicación clínica.
