---
title: Solicitud histórica del stack de skills de sistema
status: HISTORICAL
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
supersedes: []
related_docs: ["../sistema/STACK_SKILLS_SISTEMA.md"]
related_modules: []
---

# Solicitud preservada

HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH.

Solicitud previa insertada en la guía UX; preservada íntegra, sin aprobar reglas clínicas o estructura por su contenido. La implementación de skills se describe en docs/sistema. No es documentación de producto.

## Texto original

# TAREA MAESTRA
# CREAR Y ORGANIZAR LAS SKILLS DE SISTEMA DE REMEMBERMIND

Quiero profesionalizar la forma en que Codex desarrolla RememberMind.

Esta tarea NO consiste en implementar una funcionalidad de producto.

Esta tarea consiste exclusivamente en:

- auditar las skills actuales;
- estudiar la documentación vigente del repositorio;
- comprender TODO el alcance funcional de RememberMind;
- crear o evolucionar las skills de sistema necesarias;
- definir cuándo se utiliza cada una;
- evitar duplicaciones;
- definir un pipeline de trabajo coherente;
- conseguir que futuros agentes trabajen con dominio, seguridad,
  trazabilidad, pruebas y arquitectura antes de modificar código.

NO modificar código funcional de RememberMind en esta tarea.

============================================================
0. CONTEXTO GENERAL
============================================================

RememberMind es un sistema de gestión residencial geriátrica
con seguimiento clínico, cognitivo, funcional e interdisciplinario,
más un aporte de sistema experto para detección temprana de
deterioro cognitivo.

No es:

- un CRUD genérico;
- una historia clínica aislada;
- un sistema únicamente de Enfermería;
- un sistema únicamente administrativo;
- un chatbot médico;
- un sistema experto autónomo que sustituye profesionales.

Integra varias áreas:

INSTITUCIONAL
- preadmisión;
- revisión;
- admisión;
- residentes;
- contactos;
- habitaciones;
- camas;
- ocupaciones;
- documentos;
- consentimientos;
- seguros;
- jornadas;
- asignaciones.

CLÍNICA
- atenciones;
- notas clínicas;
- antecedentes;
- diagnósticos;
- alergias;
- dispositivos;
- signos vitales;
- dolor;
- antropometría;
- estudios clínicos;
- resultados;
- informes;
- documentos clínicos;
- derivaciones;
- indicaciones.

CUIDADO CONTINUO
- controles cognitivos;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas;
- curaciones;
- incidentes;
- pase de turno;
- planes de cuidado;
- intervenciones;
- programaciones;
- ejecuciones.

MEDICACIÓN
- medicamentos;
- prescripciones;
- horarios;
- administraciones.

INTERDISCIPLINARIO
- Medicina/Geriatría;
- Enfermería;
- Psicología;
- Nutrición;
- Fisioterapia;
- Pedagogía.

INSTRUMENTOS
- instrumentos;
- preguntas;
- opciones;
- aplicaciones;
- respuestas.

VIDA RESIDENCIAL
- actividades;
- participantes;
- visitas;
- familiares/contactos.

SEGURIDAD Y CONTINUIDAD
- roles;
- permisos;
- Policies;
- jornadas;
- asignaciones;
- alertas;
- eventos de alerta;
- trazabilidad;
- auditoría.

SISTEMA EXPERTO
- adquisición del conocimiento;
- conceptualización;
- representación del conocimiento;
- base de conocimiento;
- hechos;
- criterios;
- variables;
- reglas;
- pesos;
- normalización;
- agregación multicriterio;
- motor de inferencia;
- clasificación de riesgo cognitivo;
- alertas preventivas;
- explicabilidad;
- trazabilidad del razonamiento;
- validación contra experto humano.

============================================================
1. PRIMERA REGLA: DESCUBRIR LA FUENTE DE VERDAD ACTUAL
============================================================

NO asumas automáticamente que un documento antiguo sigue vigente.

Antes de crear las skills:

1. leer AGENTS.md raíz;
2. leer AGENTS.md específicos de:
   - app;
   - database;
   - resources;
   - tests;
   si existen;
3. leer docs/README.md;
4. leer docs/base-de-datos/README.md;
5. leer documentación vigente de arquitectura;
6. inspeccionar decisiones/versiones de la BDD;
7. distinguir documentación histórica de documentación vigente;
8. inspeccionar código actual cuando sea necesario;
9. inspeccionar tests actuales;
10. inspeccionar las skills existentes.

Clasificar documentación encontrada como:

VIGENTE
HISTÓRICA
PROPUESTA
DEPRECADA
CONFLICTIVA
NO RESUELTA

IMPORTANTE:

NO hardcodear en las nuevas skills:

"69 tablas"
"70 tablas"
"71 tablas"

como una verdad eterna.

Las skills deben decir:

"consultar el baseline vigente del repositorio".

Si dos documentos vigentes aparentan contradecirse:

NO elegir arbitrariamente.

Emitir:

SOURCE CONFLICT

Documento A:
...

Documento B:
...

Conflicto:
...

Impacto:
...

Decisión necesaria:
...

y detener cualquier cambio estructural relacionado.

============================================================
2. AUDITAR LAS SKILLS ACTUALES
============================================================

Las skills propias se encuentran en:

.agents/skills/

Actualmente existen varias skills de RememberMind y de proceso,
entre ellas:

remembermind-database-audit
remembermind-module-delivery
remembermind-release-check
remembermind-security-review
remembermind-ui-review

investigate-first
lean-build
migration
safe-refactor
surgical-patch
verify-and-stop

además de otras.

ANTES de crear nuevas:

leer TODOS los SKILL.md relevantes.

Construir una matriz:

SKILL
RESPONSABILIDAD ACTUAL
SOLAPAMIENTO
CALIDAD
DECISIÓN

Decisiones posibles:

KEEP
EVOLVE
MERGE
AUXILIARY
DEPRECATE LATER

NO borrar ninguna skill silenciosamente.

NO crear una skill nueva si una existente puede evolucionarse
sin perder claridad.

============================================================
3. SKILLS DE SISTEMA OBJETIVO
============================================================

Quiero evaluar, crear o evolucionar las siguientes responsabilidades:

1. remembermind-source-of-truth
2. remembermind-system-guardrails
3. remembermind-domain-architect
4. remembermind-geriatric-workflows
5. remembermind-authorization-guardian
6. remembermind-database-integrity
7. remembermind-clinical-record-integrity
8. remembermind-care-continuity
9. remembermind-alert-engine
10. remembermind-module-architect
11. remembermind-system-testing
12. remembermind-observability-audit
13. remembermind-performance-guardian
14. remembermind-expert-system-engineering
15. remembermind-expert-validation

IMPORTANTE:

Si una responsabilidad ya está correctamente cubierta por una
skill existente, NO duplicarla.

Ejemplo:

remembermind-module-delivery

puede evolucionar para cubrir
remembermind-module-architect

si eso es más limpio.

remembermind-database-audit

puede mantenerse como skill de auditoría
y complementarse con database-integrity,
o evolucionar si corresponde.

remembermind-security-review

puede permanecer como gate de revisión final,
mientras authorization-guardian actúa durante desarrollo.

Determinar la estructura más limpia después de auditar.

============================================================
4. FORMATO MÍNIMO DE CADA SKILL
============================================================

Cada SKILL.md de sistema debe tener:

---
name: ...
description: ...
---

# Purpose

# Use when

# Do not use when

# Mandatory sources

# Domain assumptions

# Workflow

# Invariants

# Failure conditions

# Escalation rules

# Tests required

# Definition of Done

La description debe ser MUY precisa.

Codex debe poder decidir correctamente
si la skill aplica o no.

NO usar descripciones vagas como:

"Skill para mejorar backend."

============================================================
5. SKILL
remembermind-source-of-truth
============================================================

PROPÓSITO:

Resolver qué reglas y documentos son vigentes
ANTES de modificar el sistema.

Debe utilizarse para:

- iniciar una tarea importante;
- detectar documentación contradictoria;
- trabajar en un módulo poco conocido;
- modificar dominio;
- modificar BDD;
- modificar autorización;
- trabajar en sistema experto.

WORKFLOW:

1. identificar área afectada;
2. buscar documentación vigente;
3. buscar decisiones posteriores;
4. identificar documentos históricos;
5. inspeccionar implementación actual;
6. revisar tests;
7. establecer contrato aplicable;
8. señalar contradicciones.

OUTPUT ESTÁNDAR:

SOURCE OF TRUTH

Área:
...

Documentos vigentes:
...

Código relevante:
...

Tests relevantes:
...

Documentación histórica ignorada:
...

Conflictos:
...

Contrato que se aplicará:
...

NO implementar antes de resolver
conflictos que puedan cambiar el comportamiento esperado.

============================================================
6. SKILL
remembermind-system-guardrails
============================================================

PROPÓSITO:

Ser la constitución técnica y de dominio de RememberMind.

Debe impedir cambios que violen invariantes.

REGLAS FUNDAMENTALES:

- consultar baseline vigente;
- BDD congelada/versionada según documentación vigente;
- no modificar estructura sin aprobación;
- no inventar campos;
- no inventar relaciones;
- no introducir JSON/EAV para evitar el modelo;
- no alterar FK para simplificar UI;
- no saltar workflows institucionales;
- no sustituir permisos backend por visibilidad frontend;
- no borrar físicamente historia clínica ordinaria;
- no sobrescribir longitudinalidad;
- no atribuir actividad clínica a otro profesional;
- no convertir Superadmin en profesional clínico automáticamente.

TERMINOLOGÍA:

postulante/adulto mayor en preadmisión
≠
residente formalmente admitido

usuario
≠
personal

contacto
≠
usuario

No usar nombres ambiguos ni legacy
si la documentación vigente ya los reemplazó.

CUANDO DETECTE UN GAP:

NO modificar automáticamente.

Emitir:

DOMAIN GAP

Problema:
...

Contrato vigente:
...

Impacto:
...

Alternativas:
A.
B.
C.

Cambio estructural necesario:
SÍ / NO

Requiere decisión:
SÍ

============================================================
7. SKILL
remembermind-domain-architect
============================================================

PROPÓSITO:

Decidir dónde debe vivir cada responsabilidad
antes de escribir código.

STACK ACTUAL:

consultar versiones vigentes,
pero el proyecto utiliza Laravel + Livewire
y arquitectura por capas/modular.

PRINCIPIOS:

Blade/components
→ presentación

Livewire
→ estado interactivo de UI y coordinación

Form Request / validación
→ estructura de entrada cuando corresponda

Action
→ caso de uso

Service
→ reglas de negocio/dominio

Policy
→ autorización contextual

Model
→ persistencia, relaciones y comportamiento Eloquent apropiado

Job/Event/Listener
→ procesos desacoplados cuando exista necesidad real

NO:

- lógica clínica compleja dentro de Blade;
- Policies gigantes como motor de negocio;
- Models god-object;
- Livewire de miles de líneas;
- Services que solamente renombran una llamada Eloquent;
- Actions innecesarias sin caso de uso real.

ANTES DE IMPLEMENTAR:

producir:

DOMAIN DESIGN

Caso de uso:
...

Actor:
...

Entrada:
...

Policy:
...

Action:
...

Service:
...

Models:
...

Transaction boundary:
...

Events:
...

Tests:
...

============================================================
8. SKILL
remembermind-geriatric-workflows
============================================================

PROPÓSITO:

Modelar RememberMind como un centro geriátrico real,
no como tablas independientes.

Debe comprender los workflows institucionales
y asistenciales.

FLUJO DE INGRESO:

Preadmisión
→ revisión
→ aprobación/rechazo
→ admisión formal
→ cama
→ residente admitido

Nunca:

aprobar preadmisión
→ crear residente automáticamente

si el contrato vigente mantiene la separación.

ADMISIÓN debe comprobar:

- postulante válido;
- estado permitido;
- cama disponible;
- contacto/responsable;
- documentación requerida según contrato;
- transacción;
- ocupación;
- historial de estado;
- vínculos necesarios.

OPERACIÓN DIARIA:

jornada
→ personal asignado
→ residentes asignados
→ cuidados requeridos
→ medicación
→ controles
→ incidencias
→ alertas
→ continuidad
→ pase de turno

Debe comprender también:

habitaciones
camas
ocupaciones
planes
intervenciones
programaciones
ejecuciones
actividades
visitas
documentación
consentimientos

No implementar módulos aislados
sin considerar sus relaciones operativas.

============================================================
9. SKILL
remembermind-authorization-guardian
============================================================

PROPÓSITO:

Proteger autorización y competencia profesional.

Toda operación sensible debe evaluarse como:

AUTENTICACIÓN
+
CUENTA ACTIVA
+
PERMISO EXPLÍCITO
+
POLICY CONTEXTUAL
+
REGLA DE NEGOCIO
+
ALCANCE / RELACIÓN / COMPETENCIA

Debe revisar tanto:

VISIBILIDAD

como:

ESCRITURA

ROLES:

consultar matriz vigente del repositorio.

Como concepto:

SUPERADMINISTRADOR
- visibilidad amplia;
- no obtiene competencia clínica automática.

ADMINISTRACIÓN
- institucional/operativa;
- no diagnostica por ser administrador.

MÉDICO/GERIATRA
- expediente interdisciplinario;
- competencia médica;
- prescripción según reglas vigentes.

ENFERMERÍA
- cuidado continuo;
- registra controles;
- administra medicación;
- no modifica la orden médica.

PSICOLOGÍA
- cognición, conducta, sueño e instrumentos
  dentro de su ámbito.

NUTRICIÓN
- antropometría, ingesta, hidratación,
  valoración e intervenciones nutricionales.

FISIOTERAPIA
- funcionalidad, movilidad, dolor
  en contexto de intervención.

PEDAGOGÍA
- seguimiento pedagógico,
  actividades y cognición permitida;
- no diagnostica.

FAMILIAR
- solo residentes relacionados;
- solo información expresamente autorizada.

REVISAR:

route
middleware
permission
Policy
query scope
record ownership
professional competence

NO confiar solo en:

@if
hidden button
disabled field
sidebar visibility

============================================================
10. SKILL
remembermind-database-integrity
============================================================

PROPÓSITO:

Proteger integridad relacional y transaccional
durante implementación.

NO sustituye remembermind-database-audit
si esa skill sigue siendo útil como auditor independiente.

Debe revisar:

- baseline vigente;
- PK/FK;
- tipos;
- longitudes;
- nullability;
- unique;
- índices;
- relaciones;
- transacciones;
- concurrencia;
- estado;
- portabilidad SQL/Laravel.

INVARIANTES IMPORTANTES:

- no dos ocupaciones activas de la misma cama;
- no dos ocupaciones activas del mismo residente;
- relaciones de contacto consistentes;
- consentimientos ligados correctamente;
- administración de medicación perteneciente
  a una prescripción del mismo residente;
- respuestas pertenecientes al instrumento aplicado;
- evitar duplicados donde el dominio lo prohíbe.

ÍNDICES:

NO agregar "por rendimiento" sin evidencia.

Deben responder a:

queries reales
joins reales
ordenamientos reales
históricos reales.

============================================================
11. SKILL
remembermind-clinical-record-integrity
============================================================

PROPÓSITO:

Proteger la integridad de la historia
y seguimiento longitudinal.

Aplica a:

- signos vitales;
- dolor;
- cognición;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- antropometría;
- heridas/curaciones;
- estudios;
- valoraciones profesionales;
- atenciones;
- notas;
- ejecuciones de cuidado.

PRINCIPIO:

nuevo evento clínico
→ nuevo registro longitudinal

NO:

"actualizar el estado actual"
para ocultar evolución.

CORRECCIÓN:

No borrado físico ordinario.

Usar mecanismo vigente según dominio:

estado
anulación
suspensión
cierre
corrección
registro compensatorio
enlace de corrección

La historia anterior debe conservarse.

TRAZABILIDAD CLÍNICA:

cuando corresponda:

cod_residente
cod_personal
cod_atencion
cod_jornada
fecha/hora de negocio
estado

No atribuir automáticamente
el registro a otro profesional.

============================================================
12. SKILL
remembermind-care-continuity
============================================================

PROPÓSITO:

Garantizar que lo relevante sobreviva
a cambios de turno, área y profesional.

Debe revisar:

- jornadas;
- asignaciones;
- controles recientes;
- cambios clínicos;
- medicación pendiente/administrada;
- planes activos;
- intervenciones;
- ejecuciones;
- incidentes;
- heridas;
- alertas abiertas;
- seguimientos;
- pase de turno.

CASO DE CONTINUIDAD:

evento
↓
registro
↓
acción/intervención
↓
seguimiento
↓
pendiente
↓
pase de turno
↓
siguiente profesional ve lo necesario

Detectar:

ORPHAN CLINICAL INFORMATION

cuando un dato existe en BDD
pero no llega al siguiente punto operativo
que necesita conocerlo.

============================================================
13. SKILL
remembermind-alert-engine
============================================================

PROPÓSITO:

Proteger el dominio completo de alertas.

Debe diferenciar:

condición clínica
≠
alerta

alerta
≠
notificación

alerta
≠
auditoría técnica

ALERTA:

representa situación vigente o gestionable.

EVENTO DE ALERTA:

representa evolución del ciclo de vida.

Flujo conceptual:

DETECCIÓN
↓
CREACIÓN
↓
RECONOCIMIENTO
↓
ASIGNACIÓN
↓
INTERVENCIÓN
↓
SEGUIMIENTO
↓
CIERRE / ANULACIÓN

Nunca sobrescribir los eventos anteriores.

Debe revisar:

- origen;
- residente;
- prioridad;
- estado;
- generación;
- responsable;
- timestamps;
- evidencia;
- intervención;
- resultado;
- cierre.

PREVIEW:

una evaluación en UI
NO debe crear efectos persistentes.

La decisión automática debe producirse
en backend al confirmar el caso de uso.

============================================================
14. SKILL
remembermind-module-architect
============================================================

PROPÓSITO:

Entregar módulos completos,
no pantallas aisladas.

ANTES de crearla revisar:

remembermind-module-delivery

Si esa skill puede evolucionarse,
NO duplicar.

Un módulo se considera completo solo si,
según aplique, incluye:

DOMAIN
- models;
- relaciones;
- reglas;
- estados.

AUTHORIZATION
- permission;
- Policy;
- scope.

APPLICATION
- Action;
- Service;
- transaction.

UI
- Livewire/controller;
- views/components.

DATA
- queries;
- histories;
- pagination.

AUDIT
- eventos relevantes.

TESTS
- happy path;
- permissions;
- invalid states;
- integration.

UX
- usar skills UX específicas,
  NO duplicar instrucciones visuales aquí.

Ejemplo:

"Módulo Hidratación"

NO es únicamente:

formulario + botón guardar.

Debe verificar:

registro
historial
residente
profesional
jornada
Policy
validación
continuidad
tests
visualización

============================================================
15. SKILL
remembermind-system-testing
============================================================

PROPÓSITO:

Probar reglas reales,
no solamente endpoints.

CLASIFICAR TESTS EN:

UNIT
FEATURE
INTEGRATION
AUTHORIZATION
WORKFLOW
REGRESSION

Crear pruebas sobre invariantes.

EJEMPLOS:

Preadmisión aprobada
NO crea residente.

Admisión formal correcta
crea/relaciona entidades necesarias.

Admisión falla en medio
→ rollback completo.

Cama ocupada
→ no puede reasignarse.

Enfermería intenta prescribir
→ denegado.

Administrador intenta diagnosticar
→ denegado.

Familiar solicita residente no vinculado
→ denegado.

Nuevo signo
→ registro histórico anterior intacto.

Corrección clínica
→ trazabilidad conservada.

Administración de medicamento de otro residente
→ imposible.

Alerta creada
→ evento inicial correspondiente.

Cierre de alerta
→ historia anterior conservada.

TESTS deben comprobar:

estado inicial
acción
resultado
efectos secundarios permitidos
efectos secundarios NO permitidos

============================================================
16. SKILL
remembermind-observability-audit
============================================================

PROPÓSITO:

Separar auditoría técnica
de trazabilidad de negocio/clínica.

AUDITORÍA TÉCNICA:

usar la solución vigente del proyecto
(por ejemplo Activitylog si sigue vigente).

Auditar cuando corresponda:

- altas/bajas;
- cambios de estado;
- permisos;
- acciones sensibles;
- prescripciones;
- anulaciones/correcciones;
- documentos;
- seguridad.

TRAZABILIDAD CLÍNICA:

debe residir también
en las propias entidades clínicas.

NO copiar historias clínicas completas
al log técnico.

Debe permitir responder:

QUIÉN
QUÉ
CUÁNDO
SOBRE QUIÉN
QUÉ CAMBIÓ
QUÉ RESULTADO TUVO

sin filtrar información sensible innecesaria.

============================================================
17. SKILL
remembermind-performance-guardian
============================================================

PROPÓSITO:

Evitar que los módulos funcionen bien con
5 registros y colapsen con datos reales.

Aplicar especialmente a:

- dashboards;
- residentes;
- históricos;
- medicación;
- alertas;
- estudios;
- reportes;
- búsquedas;
- actividades;
- asignaciones.

Detectar:

N+1
queries dentro de loops
queries desde Blade
select * innecesario
eager loading excesivo
eager loading ausente
colecciones completas sin paginar
filtrado en memoria cuando debe ser SQL
count repetidos
queries duplicadas
gráficas cargando miles de puntos
payloads Livewire gigantes

OPTIMIZACIÓN:

1. medir/identificar;
2. corregir;
3. comprobar comportamiento;
4. no sacrificar integridad por rendimiento.

============================================================
18. SKILL
remembermind-expert-system-engineering
============================================================

PROPÓSITO:

Gobernar el aporte de sistema experto
para detección temprana de deterioro cognitivo.

IMPORTANTE:

El sistema experto
NO sustituye al profesional.

Produce apoyo a la decisión.

Debe separar claramente el sistema operativo geriátrico
del motor experto.

NO mezclar reglas expertas
con formularios Livewire
o Services clínicos ordinarios.

METODOLOGÍA:

consultar documentación vigente.

El trabajo académico contempla Buchanan:

IDENTIFICACIÓN
CONCEPTUALIZACIÓN
FORMALIZACIÓN
IMPLEMENTACIÓN
PRUEBA

ARQUITECTURA CONCEPTUAL:

FUENTES/EVIDENCIA
↓
HECHOS
↓
BASE DE CONOCIMIENTO
↓
CRITERIOS
↓
NORMALIZACIÓN
↓
PESOS
↓
REGLAS
↓
AGREGACIÓN MULTICRITERIO
↓
INFERENCIA
↓
CLASIFICACIÓN DE RIESGO
↓
EXPLICACIÓN
↓
RECOMENDACIÓN
↓
SEGUIMIENTO / ALERTA PREVENTIVA

Debe distinguir:

dato clínico
hecho
criterio
variable
peso
umbral
regla
resultado
explicación

NO permitir:

if/else médicos dispersos.

Toda regla experta debe tener,
cuando corresponda:

identificador
descripción
fuente
versión
variables
condición
resultado
justificación
validación

REDES SEMÁNTICAS:

si forman parte de la decisión vigente,
representar conceptos y relaciones
de forma explícita.

INSTRUMENTOS:

MMSE/MoCA/Pfeiffer/Barthel/Katz
u otros

solo según autorización metodológica/legal vigente.

No copiar contenido protegido
sin verificar derechos.

============================================================
19. EXPLICABILIDAD DEL SISTEMA EXPERTO
============================================================

Dentro de expert-system-engineering,
o en una skill separada si la auditoría lo justifica,
proteger explicabilidad.

Un resultado NO puede ser solamente:

RIESGO ALTO

Debe permitir entender:

resultado
puntaje
nivel
evidencias
criterios activados
contribuciones
reglas aplicadas
datos faltantes relevantes
recomendación

Ejemplo conceptual:

RIESGO COGNITIVO: ALTO

Puntaje:
78 / 100

Principales contribuciones:
- cambio cognitivo reciente;
- dependencia funcional;
- resultado de instrumento;
- cambios conductuales.

Reglas/criterios considerados:
...

Datos insuficientes:
...

Recomendación:
requiere valoración profesional.

IMPORTANTE:

no afirmar diagnóstico
si el sistema solamente clasifica riesgo.

============================================================
20. SKILL
remembermind-expert-validation
============================================================

PROPÓSITO:

Validar que el sistema experto
no solamente "corra",
sino que produzca resultados justificables.

Debe evaluar:

BASE DE CONOCIMIENTO
- completitud;
- consistencia;
- contradicciones.

REGLAS
- reglas imposibles;
- reglas superpuestas;
- conflictos;
- prioridades.

MULTICRITERIO
- variables;
- normalización;
- pesos;
- agregación;
- sensibilidad.

UMBRALES
- justificación;
- casos frontera.

INFERENCIA
- expected vs actual.

EXPLICABILIDAD
- razones reconstruibles.

VALIDACIÓN HUMANA
- comparación con experto del dominio
  según metodología vigente.

MÉTRICAS:

si forman parte de la metodología vigente:

matriz de confusión
exactitud
sensibilidad
especificidad
concordancia
Kappa

No inventar resultados de validación.

============================================================
21. FUNCIONALIDAD POR ROL
============================================================

Las skills deben consultar siempre la matriz vigente,
pero comprender conceptualmente estas diferencias.

SUPERADMINISTRACIÓN

- usuarios;
- personal;
- configuración;
- áreas;
- turnos;
- seguridad;
- visibilidad administrativa amplia;
- auditoría.

ADMINISTRACIÓN

- personal;
- jornadas;
- preadmisiones;
- admisiones;
- residentes;
- contactos;
- habitaciones;
- camas;
- ocupaciones;
- documentos;
- consentimientos;
- seguros;
- asignaciones;
- actividades;
- visitas;
- operación institucional.

MÉDICO / GERIATRA

- antecedentes;
- diagnósticos;
- alergias;
- signos;
- dolor;
- estudios;
- indicaciones;
- prescripciones;
- seguimiento;
- valoraciones;
- planes;
- incidentes;
- alertas.

ENFERMERÍA

- residentes asignados;
- signos;
- dolor;
- administración de medicación;
- controles cognitivos permitidos;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas;
- curaciones;
- incidentes;
- pase de turno;
- ejecuciones de cuidados;
- alertas.

PSICOLOGÍA

- cognición;
- conducta;
- sueño;
- instrumentos;
- valoraciones psicológicas;
- atenciones;
- intervenciones;
- seguimiento.

NUTRICIÓN

- antropometría;
- ingesta;
- hidratación;
- eliminación;
- valoración nutricional;
- planes/intervenciones de nutrición.

FISIOTERAPIA

- funcionalidad;
- movilidad;
- dolor;
- restricciones;
- dispositivos;
- incidentes pertinentes;
- planes/intervenciones funcionales.

PEDAGOGÍA

- cognición permitida;
- conducta;
- actividades;
- seguimiento pedagógico;
- planes/intervenciones de su ámbito.

FAMILIAR

- residente relacionado;
- información expresamente permitida;
- documentos autorizados;
- consentimientos autorizados;
- actividades;
- visitas.

NO derivar autorización directamente de esta sección
sin consultar la documentación vigente.

============================================================
22. MEDICACIÓN COMO DOMINIO CRÍTICO
============================================================

Las skills deben respetar el flujo conceptual:

MEDICAMENTO
↓
PRESCRIPCIÓN
↓
HORARIO
↓
ADMINISTRACIÓN

No fusionar estos conceptos.

Qué existe:
medicamento.

Qué indicó el médico:
prescripción.

Cuándo corresponde:
horario.

Qué ocurrió realmente:
administración.

Enfermería puede documentar administración
según permisos.

No modificar orden médica
por conveniencia de UI.

Debe poder distinguir:

programada
administrada
omitida
retrasada
anulada/suspendida

solo si dichos estados existen
en el contrato vigente.

NO inventar estados.

============================================================
23. PLANES DE CUIDADO
============================================================

Respetar estructura vigente.

Conceptualmente:

PLAN
↓
INTERVENCIÓN
↓
PROGRAMACIÓN
↓
EJECUCIÓN

Plan:
objetivo.

Intervención:
qué hacer.

Programación:
cuándo.

Ejecución:
qué ocurrió.

NO introducir una entidad "tarea"
si el modelo vigente no la define.

============================================================
24. ESTUDIOS Y DOCUMENTOS
============================================================

Distinguir:

DOCUMENTO ADMINISTRATIVO

de:

DOCUMENTO CLÍNICO

y:

ESTUDIO CLÍNICO.

Estudio clínico:

tipo
↓
componentes
↓
resultados
↓
informe
↓
documentos

No crear una tabla por cada tipo de laboratorio/imágenes
sin aprobación del dominio.

Archivos grandes:

no asumir almacenamiento BLOB
si la arquitectura vigente utiliza storage privado + metadata.

============================================================
25. CONSENTIMIENTOS
============================================================

Las skills deben consultar el contrato vigente.

NO inventar campos visuales/persistentes
que no existan en el modelo.

Verificar:

quién firma
relación con residente
estado
revocación/anulación
trazabilidad

según baseline vigente.

============================================================
26. ACTIVIDADES, VISITAS Y VIDA RESIDENCIAL
============================================================

RememberMind NO es solamente clínico.

Las skills deben reconocer:

actividades
participación
visitas
contactos
vida cotidiana
planes interdisciplinarios

como parte real del sistema.

No ocultar todo dentro de "historia clínica".

Mantener separación entre:

operación residencial
actividad social
documentación
clínica
cuidado.

============================================================
27. REPORTES Y BÚSQUEDAS
============================================================

Cuando se implemente reporting:

- respetar permisos;
- no filtrar datos sensibles;
- evitar N+1;
- usar filtros explícitos;
- fechas coherentes;
- paginación;
- exportación solo autorizada;
- datos reproducibles.

Los reportes clínicos
no deben cambiar el dato fuente.

============================================================
28. RELACIÓN ENTRE SKILLS DE SISTEMA Y UX
============================================================

Ya existen/se crearán skills UX especializadas.

NO duplicar UX dentro de estas skills.

Orden conceptual:

SOURCE OF TRUTH
↓
GUARDRAILS
↓
DOMAIN/WORKFLOW
↓
AUTHORIZATION
↓
DATA INTEGRITY
↓
UX FLOW/DESIGN
↓
IMPLEMENTATION
↓
TESTS
↓
AUDIT
↓
VISUAL QA
↓
RELEASE

Cuando una tarea tenga UI:

usar también las skills UX correspondientes.

Ejemplo:

Módulo Signos Vitales

SYSTEM
source-of-truth
system-guardrails
domain-architect
authorization-guardian
clinical-record-integrity
alert-engine

UX
ux-flow-architect
reference-fidelity
warm-geriatric-art-direction
design-system
clinical-form-ux
data-visualization-ux
motion
responsive
ux-writing
visual-QA

============================================================
29. ORDEN DE TRABAJO RECOMENDADO
============================================================

Para una funcionalidad nueva:

1. investigate-first
2. remembermind-source-of-truth
3. remembermind-system-guardrails
4. remembermind-domain-architect
5. skill de workflow correspondiente
6. authorization-guardian
7. data/clinical integrity
8. diseñar UX si corresponde
9. implementar
10. system-testing
11. observability/security review
12. performance si aplica
13. visual QA si aplica
14. remembermind-release-check
15. verify-and-stop

NO saltar directamente de:

"quiero una funcionalidad"

a:

"editar Blade/Livewire".

============================================================
30. USO DE SUBAGENTES
============================================================

Para tareas complejas:

NO permitir que varios agentes
modifiquen simultáneamente los mismos archivos.

Usar preferentemente:

ORCHESTRATOR
        │
        ├── DOMAIN REVIEWER
        ├── SECURITY REVIEWER
        ├── DATABASE REVIEWER
        └── UX REVIEWER cuando corresponda
                │
                ↓
         PLAN CONSOLIDADO
                ↓
         ONE IMPLEMENTER
                ↓
         TEST / REVIEW AGENTS
