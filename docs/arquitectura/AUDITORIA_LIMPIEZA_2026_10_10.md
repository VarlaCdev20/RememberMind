---
title: "Auditoría de arquitectura y limpieza posterior"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: false
verified_against_commit: ed9dd523e8a5287f7fc6d7d90c75aaab6fea1dc8
verification_scope: STATIC_ARCHITECTURE_AND_TARGETED_SQLITE_POSTGRES_TESTS
runtime_verified: true
clinical_runtime_verified: false
supersedes: []
related_docs: ["README.md", "ARQUITECTURA_VIGENTE.md", "INFORME_MIGRACION_HIBRIDA.md"]
related_modules: [Alertas, Cuidados, Clinica]
---

# Auditoría de arquitectura y limpieza — 10/10/2026

Auditoría y limpieza ejecutadas en ARQUI-NUEVA sobre la entrega ed9dd523e8a5287f7fc6d7d90c75aaab6fea1dc8. Se retiraron **108 archivos versionados** (315813 bytes; aproximadamente 308 KiB), seis imports sin uso y se corrigió una operación clínica que podía persistir parcialmente. No se cambió el esquema, las factories, los roles, los permisos ni las reglas/umbrales clínicos. REINICIO se conserva sin push ni merge.

Esta auditoría conserva la evidencia de ese corte. La [limpieza posterior en PODEMOS-SEGUIR](LIMPIEZA_CARPETAS_2026_10_10.md) retira los 94 marcadores de subcarpetas anticipadas, deja 18 marcadores mínimos en las capas principales y retira los stubs después de comprobar que los 14 son idénticos a los del paquete instalado. Los conteos y decisiones de las secciones siguientes describen la revisión anterior.

## Hallazgos y resolución

| Hallazgo | Resolución |
|---|---|
| 101 .gitkeep en carpetas ya pobladas | Eliminados; los archivos reales conservan las carpetas. Los 94 marcadores necesarios para estructura vacía siguen presentes. |
| IncidentesEnfermeriaService sin consumidores | Retirado. Los flujos activos usan GestionarIncidentesPanel, GestionarIncidentesEnfermeria y RegistrarIncidenteEnFicha; no se conectó el servicio muerto ni se importaron sus reglas diferentes. |
| Tres FormRequest clínicos sin consumidores | Retirados StoreAtencionAdultoRequest, StoreCambioEstadoRequest y StoreObservacionAdultoRequest. Sin bindings/descubrimiento automático; las entradas activas usan sus validaciones actuales. Dos conservaban campos V1. |
| CSS desconectado | Retirado resources/frontend/styles/modules/pages-welcome-2.css: sin import/glob y sin uso de sus selectores. |
| Vista anterior desconectada | Retirado resources/views/welcome.blade.php; la ruta pública sigue renderizando pages.welcome. |
| Imagen duplicada | Retirado public/images/dashboard/manos.png; su SHA256 coincidía con enfermera-manos.webp, que se conserva. Ningún consumidor ni escaneo dinámico depende de la copia retirada. |
| Seis imports sin uso | Retirados en GestionarTareasPlan, PaseTurnoService, RegistrarCuidadosDeTurno, PaseTurnoPanel, SeguimientoDiarioPanel y PersonalInstitucionalForm. |
| Guías canónicas apuntaban a Backend/Frontend | Actualizados README arquitectónico y ARQUITECTURA_VIGENTE; los índices de producto/documentación/agentes/experto apuntan al estado real. |
| Documentos funcionales citaban el servicio muerto | La evidencia de incidentes se identifica correctamente; 63 menciones de rutas existentes se actualizaron al mapa modular en los tres contratos afectados, sin copiar reglas del servicio retirado. |
| Registro de incidente sin transacción exterior | Corregido en GestionarIncidentesEnfermeria: incidente, herida opcional y alerta se guardan en DB::transaction. Validación, autorización, jornada y payloads originales se conservan. |

El manifiesto LIMPIEZA_ARCHIVOS.json enumera cada eliminación con motivo, tamaño y hash anterior. Las rutas son relativas al repositorio. Los cuatro destinos PHP del inventario de migración ahora indican “Retirado tras auditoria”; el informe de migración anterior conserva su alcance histórico.

## Arquitectura comprobada

Se inspeccionaron inicialmente 495 declaraciones y se comprobaron los candidatos contra rutas, providers, contenedor, aliases Livewire, Blade, config, tests, Composer y descubrimiento del framework. Después de la limpieza, el análisis AST resolvió nombres y referencias FQCN en **499 PHP**, con **491 declaraciones** y **22 PHP del motor**. El barrido no detectó Dominio → Laravel/Eloquent/capas exteriores, Aplicacion/Infraestructura → Presentacion ni motor-experto → App/Laravel/Livewire. No constituye una prueba de todas las llamadas dinámicas.

Conteo de archivos PHP por propietario y capa (excluye Blade; incluye registros de rutas):

| Propietario | Dominio | Aplicacion | Infraestructura | Presentacion |
|---|---|---|---|---|
| Admisiones | 0 | 6 | 0 | 7 |
| Alertas | 0 | 9 | 0 | 9 |
| ApoyoDecision | 0 | 9 | 5 | 5 |
| Clinica | 13 | 26 | 0 | 22 |
| Cuidados | 0 | 16 | 0 | 11 |
| Identidad | 0 | 15 | 5 | 13 |
| Institucion | 0 | 16 | 1 | 15 |
| Medicacion | 0 | 5 | 0 | 13 |
| Reportes | 0 | 5 | 31 | 19 |
| Residentes | 0 | 9 | 0 | 13 |
| Social | 0 | 9 | 0 | 12 |
| Valoraciones | 1 | 13 | 0 | 18 |

Las doce carpetas mantienen las cuatro capas. Una capa sin PHP operativo no se presenta como capacidad implementada. Los 96 modelos conservan sus FQCN por la excepción documentada; la comparación contra la base de migración no muestra diferencias estructurales ni cambios de migraciones/factories.

Dependencias entre propietarios detectadas en nombres PHP (no equivalen a ciclos de construcción del contenedor):

| Origen | Propietarios referenciados |
|---|---|
| Admisiones | Clinica |
| Alertas | Clinica, Cuidados, Medicacion |
| ApoyoDecision | Cuidados, Identidad |
| Clinica | Alertas, Cuidados, Identidad, Medicacion, Reportes, Residentes |
| Cuidados | Alertas, Clinica, Identidad, Institucion, Medicacion, Reportes, Valoraciones |
| Identidad | Cuidados, Institucion, Reportes |
| Institucion | Admisiones, Clinica, Identidad, Reportes, Residentes |
| Medicacion | Clinica, Cuidados |
| Reportes | Cuidados, Identidad |
| Residentes | Admisiones, Reportes, Social, Valoraciones |
| Social | Institucion |
| Valoraciones | Clinica, Cuidados, Identidad |

## Atomicidad: defecto reproducido y corregido

El consumidor RegistrosEnfermeria delega en GestionarIncidentesEnfermeria. Antes del cambio, una excepción tras insertar Herida o Alerta dejaba el Incidente persistido; los dos casos de rollback fallaron efectivamente. Después de envolver las tres escrituras, ambos fallos revierten todas las filas y conservan el nivel de transacción anterior. El caso positivo verifica residente, jornada, autor, gravedad y estados; el negativo de residente no asignado verifica 403 y ausencia de efectos.

No se añadieron nuevos estados, niveles de alerta, eventos clínicos ni guardas de permisos. Tampoco se fusionaron los distintos flujos de incidentes.

## Verificaciones de este corte

| Ejecución | Pruebas | Aserciones | Fallos | Errores | Omitidas |
|---|---|---|---|---|---|
| Regresión de atomicidad antes de corregir (fallo esperado) | 4 | 16 | 2 | 0 | 0 |
| Atomicidad corregida — SQLite | 4 | 20 | 0 | 0 | 0 |
| Arquitectura/rutas/vistas + flujos relacionados — SQLite | 80 | 4097 | 0 | 0 | 0 |
| Atomicidad corregida — PostgreSQL desechable | 4 | 20 | 0 | 0 | 0 |

Los conjuntos se solapan; no se suman para afirmar mayor cobertura. La regresión dirigida incluye contratos de 296 rutas, cuatro capas, aliases/vistas, frontera de presentación, incidentes, alertas, atenciones, paginación, enfermería y adaptadores V2.

- npm test: **147/147 PASS**, cero fallos u omisiones.
- npm run build: **PASS**, 97 módulos transformados. El aviso de tiempos de plugins es informativo.
- Composer validate y autoload optimizado: **PASS**, 10503 clases en autoload.
- Sintaxis PHP: **8 archivos modificados/nuevos PASS**; el AST adicional parseó los 499 PHP operativos indicados.
- Pint controlado: **8 archivos PASS**, sin formato global.
- view:clear / view:cache: **PASS** después de eliminar la vista desconectada.
- Motor independiente: **5 casos técnicos PASS** sin Laravel/Eloquent.
- Esquema/modelos: **96 identidades conservadas**, cero cambios de migraciones/factories.
- git diff --check: **PASS**.
- Enlaces locales de los documentos modificados: **305 comprobados**, ninguno roto; **108 eliminaciones comprobadas** y **94 marcadores conservados**, ninguno redundante.

PostgreSQL utilizó exclusivamente el cluster local desechable de la verificación anterior, puerto 58698 y runner sin superusuario. El bootstrap comprobó host, puerto, base y rol antes de ejecutar. No se consultó una base operativa. El servidor propio se cerró después de la prueba. Contraseñas/configuraciones privadas permanecen fuera de Git y de outputs.

No se repitió la suite global completa después de este corte. La suite completa y reejecución del informe de migración pertenecen a su versión anterior. La evidencia actual es el conjunto dirigido, el rollback SQLite/PostgreSQL y las comprobaciones estáticas/build registradas aquí; no se declara una certificación clínica o de producción.

## Archivos conservados deliberadamente

Los overrides Blade de paginación tienen nombres distintos que Laravel/Livewire resuelven dinámicamente; sus contenidos idénticos no justifican borrarlos. También se conservan comandos Artisan, componentes de layout, stubs make:livewire, ejemplos del Design System, exports/hojas y servicios con contratos probados. ExampleTest verifica la ruta pública; su nombre genérico no lo vuelve inútil.

El motor y ApoyoDecision tienen consumidores técnicos/transitivos. PersistirEvaluacionTecnica, BuilderExperto, el puerto, el contrato y el evento tras commit cumplen funciones aprobadas, aunque no exista activación clínica. Se conserva la estructura futura explícitamente solicitada. Docs históricos, baseline y evidencia de decisiones no se tratan como basura. No se encontraron backups, logs, bases SQLite, ZIP, build, vendor o node_modules versionados.

## Deuda abierta y límites

1. **Autorización directa:** app/Modulos/Clinica/Aplicacion/Acciones/GestionarAtenciones.php y GestionarNotasClinicas.php mantienen operaciones que reciben User y dependen de middleware para autorizar. Las rutas actuales conservan protección y alcance por residente; reutilizar los casos desde nuevas entradas requiere reforzar la autorización con pruebas específicas. Este corte no redefine permisos sensibles.
2. **Acoplamiento clínico:** Clinica, Cuidados, Alertas y Medicacion se referencian en ambos sentidos para permisos, contexto y coordinación reales. No se borran esas capacidades ni se introducen interfaces vacías para ocultar las dependencias.
3. **Excepciones Eloquent y contratos públicos:** continúan las 96 identidades, los alias y los nombres históricos necesarios; no se migra polimorfismo almacenado por limpieza de archivos.
4. **Interpretación y estados históricos:** las diferencias entre pantallas de signos y DEC-OPEN-001 permanecen; consolidarlas exige decisiones funcionales.
5. **Experto futuro:** inferencia clínica asíncrona, conocimiento nuevo y revisión profesional permanecen pendientes. La preparación técnica no se confunde con activación clínica.

## Git y entrega

Limpieza realizada exclusivamente en el worktree ARQUI-NUEVA. Los archivos ajenos y cambios del checkout Desktop se conservaron. REINICIO mantiene 7a6758e8bee25106c6282b9480d38e82ea72b0e3. Sin push ni merge.

La entrega de esta auditoría incluye AUDITORIA_ARQUITECTURA.md, LIMPIEZA_ARCHIVOS.json, VERIFICACIONES_AUDITORIA.json, DEPENDENCIAS_ARQUITECTURA.json, XML de pruebas y un parche separado contra ed9dd523e8a5287f7fc6d7d90c75aaab6fea1dc8. El paquete previo de migración conserva sus hashes y alcance; el parche de limpieza se revisa después de esa entrega. El commit final se indica en VERIFICACIONES_AUDITORIA.json.
