---
title: "Auditoría Dolor V2 — valoración inicial y reevaluación"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verification_scope: TARGETED_TESTS_SQLITE_POSTGRESQL_AND_RUNTIME_UI
runtime_verified: true
related_docs: [DECISION_DOLOR_V2.md, ../frontend/FORMULARIO_DOLOR_V2_RESULTADO.md]
related_modules: [Enfermería]
---

# Auditoría Dolor V2

Contrato actual: instrucción expresa de la propietaria del 09/10/2026 y
[decisión estructural aprobada](DECISION_DOLOR_V2.md). La tabla existente
`valoraciones_dolor` incorpora únicamente origen, frecuencia y factores de
alivio. El inventario de tablas operativas permanece en 71.

## Captura y persistencia

| Campo | Contrato |
|---|---|
| intensidad | Obligatoria, entero EVA 0–10; vacía al abrir; 0 válido |
| ubicación | Texto opcional, máximo 120 caracteres; mapa como atajo sobre la misma columna |
| duración | Valor positivo y unidad textual; se guardan juntos en string(80) existente |
| frecuencia | Texto opcional string(40), trim, sin SELECT ni catálogo |
| desencadenante | Texto opcional; circunstancia de aparición/aumento |
| factores_alivio | Texto opcional; lo observado/referido que reduce el dolor |
| intervención | Texto opcional de medida efectivamente realizada; no prescripción |
| respuesta | Texto opcional solo en reevaluación; prohibida en la inicial |
| cod_valoracion_origen | NULL inicial; raíz inicial autorizada en cada reevaluación |
| tipo_dolor | NULL; no existe vocabulario institucional aprobado |
| fecha_hora | now() de servidor al guardar; banda no editable y propiedad Locked |
| cod_personal | Personal activo del usuario autenticado, no input del cliente |
| cod_residente | Contexto autorizado y Locked; revalidado al guardar |
| cod_atencion | NULL; este popup no abre una atención |
| estado | VIGENTE |

No hay EVA posterior, hora de reevaluación ni observación general. Enviarlas,
atribuir fecha/autor o un tipo arbitrario se rechaza. Un error de validación o
persistencia conserva la captura y no emite éxito ficticio.

## Inicial, episodio y reevaluación

Cada guardado crea una fila nueva. Una inicial A tiene origen NULL;
reevaluaciones B/C/D apuntan a A. Reevaluar B resuelve A. El servicio comprueba
origen existente, mismo residente, VIGENTE, no futuro y competencia contextual,
y bloquea el origen dentro de la transacción. La self-FK compuesta impide
cruces por SQL directo y RESTRICT preserva registros referenciados.

Respuesta es descripción del cambio referido/observado desde la inicial,
no EVA posterior. Los datos previos se muestran en un contexto de solo lectura.
El guardado antiguo alternativo de Mis residentes se retiró; solo la captura
Dolor V2 puede llamar el guardado desde ese componente.

## Autorización y trazabilidad

Usuario autenticado activo, permiso valoraciones_dolor.crear, rol/competencia
ENFERMEROS, turno vigente, residente asignado, Policy contextual y personal
activo. La preparación/guardado de reevaluación exige además
valoraciones_dolor.ver. Administrador y Superadministrador no adquieren
escritura clínica por su rol. El servicio existente mantiene la continuidad
ante signos críticos pendientes. No se crean permisos, alertas por EVA ni
otro sistema de auditoría. La procedencia clínica permanece en cada fila.

## Presentación y gráfica

Identidad coral suave para EVA/mapa; confirmación institucional esmeralda.
Selección numérica «Intensidad seleccionada · Sin guardar», sin clasificación
leve/moderada/grave ni recomendaciones médicas. Este brief reemplaza la
presentación por rangos que se aprobó en el lote anterior; no modifica reglas
ajenas ni convierte ese color anterior en clasificación de este flujo.

El DTO plano incluye código/origen/fecha/intensidad y características reales.
Historial máximo siete filas VIGENTE no futuras; en reevaluación se limita al
episodio. Gráfica Y0–10, X temporal, histórico sólido, preview hueco con
conexión discontinua; como máximo seis guardadas + preview. Resultado guardado
no duplica una preview. El tooltip se recalcula cuando cambia EVA.

Ventana manual movable/resizable con ocho bordes, reset, Escape y devolución
de foco. Cerrar el popup conserva los campos. Cancelar/cambiar de residente/
reevaluar con cambios exige descarte; continuar conserva también las zonas.

## Verificación y límites

Pruebas dirigidas SQLite: 37 PASS, 1.420 aserciones (incluyen migración,
rollback experto y selector). PostgreSQL aislado final: 27 PASS, 193 aserciones,
incluido el rechazo del guardado alternativo. Seeder histórico: 3 PASS, 35 aserciones.
JS: 104 PASS. Build PASS. Runtime sintético: episodio 8→5→3 con filas distintas,
formulario/ventana en cinco tamaños y teclado/puntero. No se certifica una
release global: [resultado y gates finales](../frontend/FORMULARIO_DOLOR_V2_RESULTADO.md).

Suite PHP global final:935 PASS,15 omitidas,5 fallos previos ajenos a Dolor,
19.028 aserciones. Gate global FAIL; no es aceptación completa del lote.
