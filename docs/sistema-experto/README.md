---
title: "Sistema experto"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: false
verified_against_commit: null
verification_scope: TECHNICAL_IMPLEMENTATION_AND_DISPOSABLE_DB_TESTS
runtime_verified: true
clinical_runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Índice de implementación técnica y propuestas. `runtime_verified` se limita al esquema y a pruebas técnicas sintéticas; no acredita validación ni activación clínica. [DEC-OPEN-005](../DECISIONES_PENDIENTES.md#dec-open-005) conserva los pendientes de validación profesional y derechos.

# Sistema experto

El núcleo técnico está implementado en `app/Backend/Modulos/SistemaExperto/`.
Las 23 tablas expertas se incorporaron, con autorización expresa, a
`remembermind_dev`, preservando las 71 operativas y los datos anteriores.
La versión documental `ORION-V1-20261008` contiene 55 nodos y 29 relaciones
inactivos. La propuesta observacional `MEM-INV-20261008` incorpora mapeos
pendientes de validación; no hay evaluaciones expertas clínicas generadas.
El piloto ejecutable continúa reservado a paquetes artificiales y la
persistencia técnica a bases desechables en entorno `testing`.

La autoridad del modelo experto es O.R.I.O.N.: Documento Maestro 4.16/4.19,
D-123/D-137 para persistencia y D-131/D-143/D-144 para el piloto. Este índice
enruta fuentes y no aprueba conocimiento ni reemplaza esos contratos.

## Implementación vigente

- [Implementación V1, fuentes, etapas, pruebas y límites](IMPLEMENTACION_V1.md).
- [Paquete investigado COG-MEM, mapeos propuestos y requisitos de activación](IMPLEMENTACION_V1.md#paquete-investigado-cog-mem).
- [Interfaz médica de resultados: consulta, evidencias, fundamento e historial](INTERFAZ_MEDICA_RESULTADOS.md).
- [Documento Maestro O.R.I.O.N.](https://docs.google.com/document/d/1d68S-EZTyYVKsYm-cOtaktYSNUBfDAi59FHexkEd88I).
- [Matrices O.R.I.O.N.](https://docs.google.com/spreadsheets/d/1lPFoZD6LlIoOkuWsLN3VTjvsgBowBl0mBQekjyOiyVA).
- Consulta administrativa: `/admin/sistema-experto`, desde Gestión del sistema,
  con cuenta activa, Superadministrador real y permiso explícito `auditoria.ver`.
  Muestra conocimiento y demostraciones artificiales en memoria; no lee
  evaluaciones ni resultados de residentes.
- Consulta médica: desde la ficha y carpeta cognitiva, en
  `/admin/medico/residente/{residente}/evaluacion-cognitiva/resultados-experto`.
  Solo lectura contextual; la publicación de conocimiento institucional y la
  persistencia de revisión profesional continúan pendientes de contratos.
- Consulta de Enfermería: desde **Mis residentes**, el selector de formularios
  y la ficha, en `/admin/enfermeria/pacientes/{residente}/resultados-experto`.
  Solo lectura de residentes asignados en la jornada vigente del directorio,
  con permisos existentes y personal activo. No habilita validación médica,
  publicación de conocimiento ni escritura en el sistema experto.

## Propuestas conservadas

1. [Arquitectura completa](ARQUITECTURA_COMPLETA_SISTEMA_EXPERTO.md)
2. [Árboles de decisión de todas las áreas](DISENO_ARBOLES_DECISION_TODAS_AREAS.md)
3. [Árboles de decisión del área médica](ARBOLES_DECISION_AREA_MEDICINA.md)

Estos tres documentos siguen siendo **PROPOSED**. Sus árboles, scores y
recomendaciones no gobiernan el motor cognitivo implementado. La activación
clínica, los instrumentos y sus derechos requieren decisiones específicas.
