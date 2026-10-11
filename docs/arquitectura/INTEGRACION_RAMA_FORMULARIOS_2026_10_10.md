# Integración de FORMULARIOS — 10/10/2026

Solicitud de la propietaria: incorporar los commits de las demás ramas a FORMULARIOS, separar el trabajo pendiente por tema, crear «FORMULARIOS BONITOS» y publicar la rama.

## Historial

Después de `git fetch origin`, se encontraron 35 commits únicos pendientes de ancestry. Las demás ramas ya estaban contenidas en FORMULARIOS. El merge `1bb88e66` incorpora diez puntas y sus ancestros, preservando exactamente el árbol previo `ddbdfa38ae6a034dd465d7b2d4a154804b680999` mediante estrategia `ours`.

**Esta es una integración de historial. No se aplican literalmente los parches obsoletos de esas ramas.** Se conserva la implementación V2 vigente y todo el trabajo local pendiente. No equivale a restaurar pantallas, tablas o comportamientos antiguos.

| Punta incorporada | Decisión |
| --- | --- |
| AGENTS.MD — 94cedc92 | Instrucciones ya incorporadas y evolucionadas desde 23f60eef. |
| DISEÑO_INTERFAZ — c6ee9824 | Conserva diseños y arquitectura actuales; no reintroduce sus tokens, componentes o contratos anteriores. Incluye codex/agents-md. |
| codex/restructuracion-checkpoint-20260925 — 014e8c7a | Checkpoint anterior a la arquitectura y contratos clínicos vigentes. |
| origin/REORGANIZAR-ADMINISTRACION — 2f4a85cd | No restaura app/Livewire, entidades V1 o voluntariado retirado. |
| origin/correcciones_base_datos — 769ea19b | No reintroduce ajustes parciales de BDD ni mappings V1 frente al baseline aprobado. |
| origin/crear_preadmisiones_demo — e5c92f2f | No restaura seeders demo o skills anteriores. |
| origin/feature/MEDICO — acc72891 | No restaura escritores app/Services ni formularios clínicos anteriores. |
| origin/feature/core-modules-integration — 76df8614 | Preserva buscador, módulos, rutas y frontend actuales frente a su implementación V1. |
| origin/feature/perfil — 9a9bd1af | `git cherry` confirma parche equivalente ya incorporado; commit dc05d9c2 del historial actual. |
| origin/refactor/dashboard-profesional — 06c0101f | No restaura hojas resources/css/paleta-colores; el sistema canónico está en resources/frontend/styles/design-system. |

La simulación con `git merge-tree` mostró conflictos de contenido, modify/delete y rename/delete con archivos retirados; se documentó localmente en storage/app/qa. Al finalizar, ninguna punta local/remota inspeccionada tiene commits fuera de HEAD. Los stashes anteriores no son ramas y se preservan sin reaplicar ni borrar.

## Trabajo pendiente separado por tema

- V2: limpieza de dependencias anteriores, flujos institucionales, autorización y pruebas relacionadas.
- Experto: implementación, interfaz de resultados, conocimiento, tests y migraciones ya presentes en el árbol de trabajo.
- Datos: carga sintética optativa de desarrollo y catálogo investigado, sin ejecutar seeders en esta operación Git.
- Documentos: fuentes vigentes, arquitectura, trazabilidad y gobernanza.
- Formularios: Dolor, Ingesta, Hidratación, Eliminación, Movilidad y componentes compartidos; título solicitado «FORMULARIOS BONITOS».

Se excluyen archivos temporales de storage/framework, secretos, credenciales de runtime, compilados y capturas de QA.

## Evidencia utilizada

La suite PHP completa del árbol actual antes de los últimos ajustes de Movilidad aprobó 984 tests, con 15 omitidos y 19745 assertions (`movilidad-php-full-final.txt`). Los archivos ejecutables cambiados después de esa ejecución se limitan a la captura/validación de Movilidad y sus componentes; su batería posterior aprobó 17 tests / 201 assertions. JavaScript: 147 tests aprobados. Build Vite aprobado. Diff check aprobado. El merge histórico no cambia archivos ejecutables.

Los resultados PostgreSQL, QA visual y límites de cada formulario permanecen en sus informes específicos; no se presentan como una nueva certificación integral ni de producción. Esta operación no ejecuta migraciones ni modifica datos clínicos.
