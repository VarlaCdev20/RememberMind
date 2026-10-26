# Localización de cohorte y respaldo en Git — 22/10/2026

## Solicitud y método

La propietaria no dispone de la instalación o respaldo `remembermind_dev` y pidió buscarlo en las ramas. Revisión de solo lectura con la skill `remembermind-source-of-truth`: ramas locales y remotas, historial alcanzable, reflogs y objetos; `git fetch origin --no-tags` finalizó correctamente. No se cambió de rama ni se sobrescribieron cambios locales. No se utilizaron subagentes.

## Resultado

El commit `f855ccdf48c5ea2f323e2c69c67e4535cecfa746`, del 22/10/2026, se titula `feat(apoyo-decision): integrar resultados y cohorte cognitiva local`.

Está contenido en:

- `feature/apoyo-decision-informe-neurocognitivo`, rama actual.
- `feature/valoraciones-explorador-guias`.
- `origin/feature/apoyo-decision-informe-neurocognitivo`, confirmado después del fetch.

La prueba de ancestro confirmó que ya forma parte de HEAD. No requiere merge ni cherry-pick para recuperar ese código.

## Fuentes recuperables ya presentes

| Fuente | Contenido y alcance |
|---|---|
| `database/seeders/CohorteCognitivaInvestigacionSeeder.php` | Definición ejecutable de 30 casos sintéticos y su carga mediante operaciones institucionales |
| `scripts/cargar-cohorte-local.php` | Carga local optativa con destino exacto, respaldo previo, transacción y comprobación de preservación |
| `app/Modulos/ApoyoDecision/Infraestructura/Simulacion/CohorteCognitivaLocal.php` | Identidad nominal del lote y recuperación de sus bindings instrumentales |
| `docs/auditorias/cohorte-investigacion-2026-10-22/manifiesto.json` | Referencias y esperado técnico, separado de fuentes clínicas |
| `docs/auditorias/cohorte-nombres-2026-10-22/manifiesto.json` | Identidades ficticias de los casos |
| `docs/auditorias/cohorte-aplicacion-local-2026-10-22/resultados-locales.json` | Informe de ejecución histórico; declara `remembermind_dev`, pero no constituye un dump |

Se verificó mediante una llamada de lectura a `casos()` que el código actual define exactamente 30 casos, lote `COHORTE-INFERENCIA-30-V1`. No se invocó el seeder.

## Respaldo SQL histórico

La búsqueda de objetos de todas las referencias y reflogs encontró `remembermind_completo.sql` y `remembermind_estructura.sql`. El primero tiene objeto `37a72d0a79a2055417fdc847eb22804a56b103a0`, 188.220 bytes, incorporado por `831648f7` y retirado de la raíz por `2acf610c`.

Se inspeccionaron únicamente metadatos de su contenido, sin imprimir ni extraer filas personales: PostgreSQL, 38 sentencias CREATE TABLE, nombres V1 como `adulto_mayor`, sin marca `COH30-` y sin `versiones_modelo_experto`. Es un respaldo del modelo anterior, no la instalación experta buscada. No se restaura ni se mezcla con el esquema vigente.

No se encontró un dump de `remembermind_dev` ni archivos `cohorte30/carga.json` o `cohorte30/verificacion.json` en esos objetos. `git check-ignore` confirma que `storage/app/private/auditorias-locales/cohorte30/` está excluido de Git; las actas describen respaldos privados que no se publicaron.

## Conclusión y límite

**Código y casos sintéticos recuperables: sí, ya integrados. Base original y respaldo de esa instalación: no localizados.** Una rama conserva código y archivos versionados, no el contenido de una base externa por sí sola.

En el cierre de esta búsqueda, `RememberMind` no tenía versiones del conocimiento y el cargador requería instalarlas antes de cargar. La reconstrucción del lote no restituye los otros residentes ni la historia de aquella instalación.

La continuación debe usar las definiciones vigentes y preservar la procedencia sintética, los residentes existentes y las aprobaciones profesionales. No procede restaurar el dump V1 ni copiar resultados esperados al expediente para aparentar una inferencia. Esta búsqueda no modificó `.env`, configuración, permisos, estructura ni datos de PostgreSQL; no hubo commit, push ni despliegue.

Actualización del 23/10: la propietaria autorizó completar los seeders y requisitos faltantes. La [preparación local verificada](COMPLETAR_EXPERTO_LOCAL_2026_10_23.md) reconstruyó los 30 casos, instaló los contratos existentes y conservó los 73 residentes anteriores. No constituye recuperación del dump que no se encontró.
