---
title: "Limpieza y conciliación documental"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [documentation_cleanup_evidence]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Limpieza documental — 25/10/2026

## Alcance autorizado y método

La propietaria solicitó retirar incoherencias, actualizar AGENTS/Markdown y dejar documentación funcional por módulo. Se editaron únicamente archivos Markdown; se preservaron los cambios concurrentes de PHP, Blade, JS, CSS, configuración y pruebas. No se alteraron BDD, permisos, reglas clínicas, historia, rama, commits ni despliegues.

La comparación inicial localizó **458 Markdown** y **206 enlaces locales a destinos inexistentes**, excluyendo dependencias, output y temporales. Se interpretaron correctamente los sufijos de línea; no se contaron como archivos ausentes. **Cero duplicados exactos de contenido.** Por ello se retiraron párrafos repetidos/obsoletos y referencias muertas, sin borrar archivos con decisiones o evidencia única.

## Fuentes conciliadas

- AGENTS raíz/pruebas: 100 tablas de negocio + 16 técnicas tras la decisión del 22/10; techo de diseño 110 separado de DDL. database/AGENTS ya contenía el inventario correcto.
- AGENTS backend/frontend: documentación de módulos y distinción entre objetivo visual y ruta integrada.
- Portal, estado actual y portada: corte del 25/10 con fallos reales; el estado anterior se preservó como [archivo histórico](../../historico/ESTADO_CORTES_HASTA_2026_10_11.md).
- BDD: índice único con fuentes y cadena de decisiones, sin duplicar diccionario ni alterar aprobaciones.
- Gobernanza: la restricción «solo documentación» de Fase 1 no se aplica a futuros desarrollos autorizados.
- DEC-OPEN-005: A–D/E–H y gobierno/captura ya decididos se separaron de mediciones, derechos, conservación y validación pendientes.
- Expertos: portada breve de contratos vigentes y catálogo exhaustivo separado; referencia SQL no instalable identificada como tal.
- Doce README de módulo: reemplazan el boilerplate de traslado por recorrido, actores, controles, entradas/salidas, fuentes y pruebas localizadas, con brechas explícitas.
- Mapa funcional y skill source-of-truth: arquitectura híbrida y estados actuales.
- Informe de mejora de ficha: aviso de integración divergente comprobada, conservando la evidencia histórica.

## Enlaces y ausencia de artefactos

Los destinos trasladados se conciliaron por nombre exacto de archivo y propietario comprobado. Las citas de líneas históricas no se usaron como números actuales después de mover el enlace. La vista show ambigua se resolvió explícitamente dentro de Residentes.

No se recreó `IMPLEMENTACION_V1.md`, el controlador retirado ni capturas faltantes. Se retiró su enlace roto conservando la referencia/ausencia; el experto remite a fuentes actuales. Un artefacto no disponible no se presenta como prueba.

## Verificación y límites

El resultado final de enlaces, verificaciones de contrato y manifiesto SHA-256 se conserva en `output/auditoria-integral-20261025/limpieza-documental/`. El comprobador verifica existencia de destinos de enlaces Markdown locales fuera de fences; no valida URLs externas, imágenes embebidas ni todo texto de código. Un segundo verificador comprueba anchors de encabezados/IDs Markdown según sus reglas locales; no sustituye un renderizador de navegador. El inventario inicial del 25/10 conserva su fecha y cifras; los nuevos documentos no se presentan como existentes antes de esta limpieza.

No se reejecutaron suites de producto ni build: esta modificación no cambia comportamiento ejecutable. Las cifras de pruebas del estado actual proceden de la auditoría previa, con fallos y omisiones conservados. No se declara corregido H01–H32 por modificar documentos.

## Documentación utilizable

[Portal](../../README.md) → [estado comprobado](../../ESTADO_ACTUAL.md) → [módulo/proceso](../../modulos/README.md) → fuente aprobada y código localizado → prueba/evidencia. Historia y entregas anteriores aportan contexto; no imponen una fase ni autorización pendiente ya resuelta.

## Resultado de las verificaciones

- **51 archivos Markdown propios** revisados/reescritos: incluye cuatro documentos nuevos y referencias de cortes anteriores. No se atribuye a esta tarea el diff previo de otros archivos.
- **206 incidencias iniciales** de destino local inexistente. El paso de reparación trasladó 174 enlaces a ubicaciones reales y dejó 14 ausencias explícitas; los demás casos se resolvieron al sustituir índices/mapas obsoletos.
- **Cero destinos inexistentes** en el escaneo final y **cero anchors no localizados** en el verificador complementario. No se afirma disponibilidad remota ni capturas ausentes.
- **Doce módulos** con propósito/actores, recorrido, entradas/salidas, autorización, persistencia, fuentes, interfaz, pruebas localizadas y brechas.
- **2.491 archivos no Markdown** comparados por SHA-256 sin alteración por esta tarea. Se preservó el código concurrente; no se implementaron las correcciones funcionales H01–H32.
- `git diff --check` forma parte del gate final; sin staging, commit, push o despliegue.

Comandos reproducibles desde la raíz:

```powershell
node output/auditoria-integral-20261025/auditar-enlaces-documentales.mjs despues
node output/auditoria-integral-20261025/verificar-documentacion.mjs
git diff --check
```

## Archivos propios de la limpieza

- [.agents/skills/remembermind-source-of-truth/references/source-map.md](../../../.agents/skills/remembermind-source-of-truth/references/source-map.md)
- [AGENTS.md](../../../AGENTS.md)
- [CODEX_SETUP.md](../../../CODEX_SETUP.md)
- [README.md](../../../README.md)
- [app/AGENTS.md](../../../app/AGENTS.md)
- [app/Modulos/Admisiones/README.md](../../../app/Modulos/Admisiones/README.md)
- [app/Modulos/Alertas/README.md](../../../app/Modulos/Alertas/README.md)
- [app/Modulos/ApoyoDecision/README.md](../../../app/Modulos/ApoyoDecision/README.md)
- [app/Modulos/Clinica/README.md](../../../app/Modulos/Clinica/README.md)
- [app/Modulos/Cuidados/README.md](../../../app/Modulos/Cuidados/README.md)
- [app/Modulos/Identidad/README.md](../../../app/Modulos/Identidad/README.md)
- [app/Modulos/Institucion/README.md](../../../app/Modulos/Institucion/README.md)
- [app/Modulos/Medicacion/README.md](../../../app/Modulos/Medicacion/README.md)
- [app/Modulos/Reportes/README.md](../../../app/Modulos/Reportes/README.md)
- [app/Modulos/Residentes/README.md](../../../app/Modulos/Residentes/README.md)
- [app/Modulos/Social/README.md](../../../app/Modulos/Social/README.md)
- [app/Modulos/Valoraciones/README.md](../../../app/Modulos/Valoraciones/README.md)
- [docs/CHANGELOG.md](../../CHANGELOG.md)
- [docs/DECISIONES_PENDIENTES.md](../../DECISIONES_PENDIENTES.md)
- [docs/DEUDA_TECNICA.md](../../DEUDA_TECNICA.md)
- [docs/ESTADO_ACTUAL.md](../../ESTADO_ACTUAL.md)
- [docs/GOBERNANZA_DOCUMENTAL.md](../../GOBERNANZA_DOCUMENTAL.md)
- [docs/README.md](../../README.md)
- [docs/TRAZABILIDAD.md](../../TRAZABILIDAD.md)
- [docs/administrador/01-ADMISIONES.md](../../administrador/01-ADMISIONES.md)
- [docs/arquitectura/ARQUITECTURA_VIGENTE.md](../../arquitectura/ARQUITECTURA_VIGENTE.md)
- [docs/arquitectura/README.md](../../arquitectura/README.md)
- [docs/auditorias/2026-10-25/LIMPIEZA_DOCUMENTAL.md](LIMPIEZA_DOCUMENTAL.md)
- [docs/base-de-datos/README.md](../../base-de-datos/README.md)
- [docs/frontend/MEJORA_FICHA_PSICOLOGICA_2026_10_22.md](../../frontend/MEJORA_FICHA_PSICOLOGICA_2026_10_22.md)
- [docs/frontend/VALORACIONES_INTERACTIVAS_2026_10_22.md](../../frontend/VALORACIONES_INTERACTIVAS_2026_10_22.md)
- [docs/historico/ESTADO_CORTES_HASTA_2026_10_11.md](../../historico/ESTADO_CORTES_HASTA_2026_10_11.md)
- [docs/historico/README.md](../../historico/README.md)
- [docs/modulos/README.md](../../modulos/README.md)
- [docs/modulos/alertas/README.md](../../modulos/alertas/README.md)
- [docs/modulos/ingreso-institucional/README.md](../../modulos/ingreso-institucional/README.md)
- [docs/modulos/jornadas-asignaciones/README.md](../../modulos/jornadas-asignaciones/README.md)
- [docs/modulos/residentes-alojamiento/README.md](../../modulos/residentes-alojamiento/README.md)
- [docs/modulos/signos-vitales/README.md](../../modulos/signos-vitales/README.md)
- [docs/seguridad/MATRIZ_ROLES_PERMISOS.md](../../seguridad/MATRIZ_ROLES_PERMISOS.md)
- [docs/seguridad/MODELO_AUTORIZACION.md](../../seguridad/MODELO_AUTORIZACION.md)
- [docs/sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md](../../sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md)
- [docs/sistema-experto/INDICE_DOCUMENTAL.md](../../sistema-experto/INDICE_DOCUMENTAL.md)
- [docs/sistema-experto/README.md](../../sistema-experto/README.md)
- [docs/sistema/AUDITORIA_SKILLS_SISTEMA.md](../../sistema/AUDITORIA_SKILLS_SISTEMA.md)
- [docs/sistema/ESTADOS_Y_CICLOS_DE_VIDA.md](../../sistema/ESTADOS_Y_CICLOS_DE_VIDA.md)
- [docs/sistema/FLUJOS_GERIATRICOS.md](../../sistema/FLUJOS_GERIATRICOS.md)
- [docs/sistema/INVARIANTES_NEGOCIO.md](../../sistema/INVARIANTES_NEGOCIO.md)
- [docs/sistema/MAPA_DOMINIO_FUNCIONAL.md](../../sistema/MAPA_DOMINIO_FUNCIONAL.md)
- [resources/AGENTS.md](../../../resources/AGENTS.md)
- [tests/AGENTS.md](../../../tests/AGENTS.md)
