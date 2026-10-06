# Documentación de RememberMind

## Documentación vigente

- [BDD operativa — baseline congelado y extensiones aprobadas](base-de-datos/README.md)
- [Arquitectura y estructura de carpetas vigente](arquitectura/README.md)
- [Roles y competencias vigentes](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md)
- [Stack de skills de sistema: responsabilidades y pipeline](sistema/STACK_SKILLS_SISTEMA.md)
- [Auditoría y transición de skills de sistema](sistema/AUDITORIA_SKILLS_SISTEMA.md)
- [Mapa funcional, actores y límites del sistema](sistema/MAPA_DOMINIO_FUNCIONAL.md)
- [Mapa de fuentes: vigentes, históricas, propuestas y conflictos](../.agents/skills/remembermind-source-of-truth/references/source-map.md)
- [Stack de skills UX/UI: selección, responsabilidades y pipeline](frontend/STACK_SKILLS_UX_UI.md)
- [Contrato visual UX/UI aprobado y límites del dominio](frontend/CONTRATO_VISUAL_UX_UI.md)
- [Auditoría y transición de las skills UX/UI existentes](frontend/AUDITORIA_SKILLS_UX_UI.md)

## Documentación histórica

Las carpetas `architecture-audit` y `refactorizacion-total` registran análisis y propuestas anteriores. Pueden servir como contexto histórico, pero no reemplazan la arquitectura vigente ni el baseline congelado de la BDD Operativa V2.1.

- [Auditoría de inventario V1](auditoria.md).
- [Auditoría histórica de roles y permisos](auditoria_roles_permisos.md).

## Sistema experto: propuesta

[Diseños del sistema experto](sistema-experto/README.md): documentación de apoyo futuro; no equivale a método, reglas o validación clínica aprobados.

## Autoridad y versiones

Cuando exista una contradicción sobre la base de datos, prevalecen:

1. `base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md`.
2. `base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md` (conservándose `base-de-datos/REMEMBERMIND_BDD_69_TABLAS.md` como histórico V2.0).

Aplicar primero la instrucción actual de la propietaria y las decisiones posteriores explícitamente aprobadas en su alcance. Consultar el índice BDD y sus extensiones vigentes; no inferir el inventario actual del número en un nombre de archivo.
