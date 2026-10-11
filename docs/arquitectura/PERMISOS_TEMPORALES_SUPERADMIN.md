---
title: "Permisos temporales del superadministrador"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Excepción temporal documentada; guardia efectiva por entorno y configuración desplegada requieren verificación. Ver [TECH-004](../DEUDA_TECNICA.md#tech-004); no se cambian permisos en Fase 1.

# Permisos temporales del superadministrador

Durante la construcción de RememberMind, `SUPERADMINISTRADOR` recibe todos los permisos Spatie del guard `web`. El seeder conserva permisos nuevos ya existentes y los asigna al rol; el evento de creación asigna cada permiso `web` nuevo en el momento de crearlo. El panel de roles impide retirar permisos de este rol mientras dure esta etapa. La asignación persiste en `role_has_permissions`; para permisos clínicos de escritura, `can()` también exige el contexto temporal válido.

En entornos `local` y `testing`, `SUPERADMIN_CLINICAL_WRITE` habilita una excepción temporal a los controles de rol profesional. Su valor predeterminado es `true` solo en esos entornos. La excepción exige cuenta activa y personal propio activo, sin asignación de área ni turno al superadministrador. Cuando un acto clínico exige `cod_area`, se requiere que la solicitud indique un área activa existente. Los flujos de enfermería sobre residentes necesitan una jornada activa e inequívoca del residente, pero no una asignación del superadministrador a esa jornada. La autoría clínica se guarda con el `cod_personal` del usuario autenticado; no se crea ni selecciona otro personal para completarla. La previsualización de roles no habilita esta excepción.

Para suspender la excepción clínica, establecer `SUPERADMIN_CLINICAL_WRITE=false` y limpiar la caché de configuración. Al finalizar la construcción, revisar la matriz de permisos y sustituir la asignación global del seeder y el evento de creación por la matriz definitiva antes de retirar permisos al rol.
