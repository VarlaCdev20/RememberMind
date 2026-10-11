---
name: remembermind-source-of-truth
description: "Resolver las fuentes, versiones y contratos vigentes de RememberMind antes de diseñar o modificar un flujo; clasificar historia, propuestas y conflictos. Usar al iniciar un módulo, auditar documentación o encontrar fuentes contradictorias; no para dictar reglas nuevas."
---

# Purpose

Entregar un contrato localizado con autoridad y evidencia antes de editar.

# Use when

Inicio de un módulo o flujo, discrepancias entre documentación/código/tests, auditoría documental o decisión de qué baseline/extensión aplica.

# Do not use when

Consultas triviales ya resueltas por un contrato localizado. No autoriza estructura o reglas clínicas ni realiza implementación.

# Mandatory sources

[Mapa de fuentes y formatos](references/source-map.md), AGENTS aplicables, índices general/BDD/arquitectura, baseline/diccionario y decisiones aprobadas del área; docs funcionales, código y tests pertinentes.

# Domain assumptions

La aprobación posterior explícita de la propietaria gobierna solo su alcance. Código y tests pueden divergir de la norma. El nombre del diccionario y el inventario de una versión no fijan para siempre el esquema.

# Workflow

1. Leer fuentes por autoridad; localizar secciones, fecha, estado y alcance.
2. Clasificar VIGENTE, HISTÓRICA, PROPUESTA, DEPRECADA, CONFLICTIVA o NO RESUELTA; permitir clasificación parcial.
3. Inspeccionar código/tests consumidores sin convertir implementación en autorización.
4. Emitir `SOURCE OF TRUTH` con Área, Documentos vigentes, Código relevante, Tests relevantes, Documentación histórica ignorada, Conflictos y Contrato que se aplicará.
5. Ante discrepancia emitir `SOURCE CONFLICT` con Documento A, Documento B, Conflicto, Impacto y Decisión necesaria. Resolver con autoridad documentada cuando exista; aislar lo pendiente.

# Invariants

No mezclar V1/V2 ni propuestas con decisiones aprobadas. No reintroducir reglas históricas o copiar conteos/versiones sin contrastar decisiones actuales.

# Failure conditions

Contrato sin fuente, aprobación supuesta, rutas inexistentes copiadas, conflicto omitido o tests presentados como norma superior.

# Escalation rules

Pedir decisión solo si la autoridad disponible no resuelve una cuestión material de dominio/estructura/permisos. Detener cambio estructural relacionado y trabajo dependiente; continuar tareas independientes ya autorizadas.

# Tests required

En auditoría de fuentes: comprobar enlaces y trazabilidad de un conflicto real o escenario. No ejecutar DB ni suites para acreditar una mera lectura; reportar tests localizados como no ejecutados si corresponde.

# Definition of Done

Contrato aplicable, estado de cada fuente relevante, conflictos resueltos o aislados y evidencia de código/tests identificados sin afirmaciones de ejecución ficticias.
