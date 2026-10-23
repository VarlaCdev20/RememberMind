# Revisión independiente de BDD, autorización y arquitectura

Fecha: 21/10/2026. Auditoría de solo lectura del checkout y PostgreSQL local. Sin DDL, migraciones, seeders, reset, autenticación con cuentas reales ni cambios de producto. Se escriben únicamente este informe y su CSV solicitado. Hay trabajo simultáneo no confirmado; estas referencias corresponden al corte inspeccionado, no a un commit inmutable.

## Dictamen y fuentes

**120 tablas físicas = 104 de negocio (75 operativas + 29 expertas) + 16 técnicas. Diez roles instalados.** Las 104 tienen Model explícito. No se encontró fundamento para declarar prescindible alguna de ellas. Esto no demuestra mínimo matemático ni completitud de formularios: cero filas, cero FK entrantes o ausencia de CRUD propio no prueban desuso.

**Cero de diez roles pueden certificarse íntegramente completos mediante este gate acotado.** Es un límite de aceptación, no una afirmación de que diez roles sean inservibles. La presencia de dashboards, permisos y endpoints es evidencia de implementación; faltan recorridos y negativos integrales vigentes por cada rol. La raíz verifica suites y UX por separado.

Se aplicaron remembermind-source-of-truth, remembermind-database-audit y remembermind-security-review. Fuente vigente: núcleo congelado más decisiones aprobadas posteriores. El inventario actual está en [docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md:20](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md:20>); la reducción instalada y sus límites en `docs/base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md`. Los conteos 71/94/106/108 de snapshots anteriores no sustituyen 104.

`docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md` gobierna competencias y decisiones posteriores IADL/Petersen/gobierno. `docs/arquitectura/ADR_ARQUITECTURA_HIBRIDA.md` gobierna carpetas y excepción App/Models. `docs/seguridad/MATRIZ_ROLES_PERMISOS.md` es parcialmente útil, pero conserva enlaces a ubicaciones retiradas: no acredita las entradas actuales. `docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md` es propuesta pendiente, no autorización de estructura ni certificación de recálculo íntegro.

## Diez hallazgos prioritarios

| ID / prioridad | Evidencia | Impacto / siguiente corrección separada |
|---|---|---|
| S01 / P1 | [app/Providers/AppServiceProvider.php:79](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Providers/AppServiceProvider.php:79>), callback Gate::before, permite lectura a Superadministración sin comprobar ACTIVO; [app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php:11](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php:11>) sí rechaza cuenta inactiva. | Reproducción con User/Role/Residente sintéticos no persistidos: estado INACTIVO, Gate view=true, Policy before directa=false. El Gate global evita la Policy. HTTP cuenta.activa mitiga sus rutas; no llamadas internas. Rechazar inactivos antes del bypass y añadir negativo autónomo. |
| S02 / P1 | [app/Compartido/Infraestructura/Documentos/AlmacenamientoDocumentosUsuario.php:38](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Compartido/Infraestructura/Documentos/AlmacenamientoDocumentosUsuario.php:38>) escribe documentos en disco public; [app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/usuarios-panel.blade.php:502](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/usuarios-panel.blade.php:502>) enlaza Storage::url. | Una URL pública puede evitar reautorización si el enlace público habitual está habilitado. No se inspeccionaron archivos reales ni exposición del despliegue. Plan compatible hacia almacenamiento privado, descarga contextual y pruebas de revocación/enlaces antiguos. |
| S03 / P1 | [app/Modulos/Clinica/Aplicacion/Acciones/GestionarNotasClinicas.php:30](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Clinica/Aplicacion/Acciones/GestionarNotasClinicas.php:30>) actualiza contenido de la misma nota; [app/Models/NotaClinica.php:8](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Models/NotaClinica.php:8>) ya define relación anterior. | Se pierde el contenido original en la fila y no se crea rectificación enlazada. ModeloOperativo protege DELETE/PK, no este UPDATE. Preservar original mediante mecanismo de dominio, autor y justificación; no inventar tablas. |
| S04 / P1 | [app/Modulos/Clinica/Aplicacion/Acciones/GestionarAtenciones.php:26](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Clinica/Aplicacion/Acciones/GestionarAtenciones.php:26>): update/destroy/restore reciben User pero no lo usan para autorizar. | Dependencia de permisos/middleware de ruta. Falta contrato autónomo de cuenta, permiso, competencia, estado y autor. No se afirma explotación HTTP; mantener el alcance por residente y probar llamadas directas sin efectos. |
| S05 / P1 | [app/Modulos/Clinica/Aplicacion/Acciones/GestionarNotasClinicas.php:16](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Clinica/Aplicacion/Acciones/GestionarNotasClinicas.php:16>): mutaciones reciben User sin revalidar permiso/competencia/ámbito; create deriva personal pero eso no acredita autorización. | Nuevo consumidor interno podría escribir fuera de competencia. Reautorizar en backend; negativos de cuenta, revocación, profesional y recurso. No confundir esta deuda con ausencia de middleware HTTP. |
| S06 / P1 | [app/Modulos/Reportes/Aplicacion/Consultas/DashboardService.php:24](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Reportes/Aplicacion/Consultas/DashboardService.php:24>) cachea 60 s por usuario y roles; [app/Modulos/Reportes/Aplicacion/Consultas/RoleDashboardDataService.php:183](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Reportes/Aplicacion/Consultas/RoleDashboardDataService.php:183>) consulta recursos por personal sin comprobar cada permiso. | Reproducción READ ONLY con User/Personal no persistidos, sin roles/permisos: forRole(NUTRICIONISTA) ejecuta ocho SELECT de valoración/antropometría/atención/planes/alertas con autor sintético inexistente. Ninguna fila personal expuesta. Reautorizar antes de consultar y servir caché; invalidar por permisos/contexto. |
| S07 / P1 | [app/Modulos/Reportes/Aplicacion/Consultas/RoleDashboardDataService.php:281](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Reportes/Aplicacion/Consultas/RoleDashboardDataService.php:281>) filtra vínculo/contacto al generar el dashboard familiar, pero la caché anterior no incluye su estado. | Resultado previamente autorizado puede sobrevivir a revocación hasta TTL. Riesgo estático, no incidente sobre familiares reales. Revalidar vínculo antes de devolver caché y probar revocación inmediata. |
| D08 / P2 | [docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md:11](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md:11>): compuesto instrumental/Petersen preparado en memoria, sin escritor relacional íntegro. | Captura, DTO y replay en memoria no cierran recálculo histórico institucional. Faltan retención de entradas/contexto/perfiles/exclusiones/membresías/release; requiere diseño y autorización propios, sin JSON/EAV/archivo clínico de sustitución. |
| D09 / P2 | La sección [Integridad SQL pendiente](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md:83>) identifica relaciones_evidencias_evaluacion, evaluaciones_reglas, evaluaciones_condiciones_regla y evidencias_soporte_condicion. | FK simples prueban existencia, no toda igualdad de evaluación/versión/pertenencia. Models y lectores que rechazan corrupción no son garantía SQL universal. Gate específico y decisión estructural antes de nuevos constraints. |
| A10 / P2 | `docs/seguridad/MATRIZ_ROLES_PERMISOS.md` enlaza app/Backend, app/Frontend y app/Policies; [docs/arquitectura/ARQUITECTURA_VIGENTE.md:74](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/ARQUITECTURA_VIGENTE.md:74>) ya registra deuda de autorización autónoma. | Relocalizar símbolos, enlaces y estado por sección. No presentar snapshots históricos como PASS global ni reabrir problemas ya cerrados por decisiones posteriores. |

Son diez hallazgos de riesgo/completitud, no diez incidentes de producción. S01 y S06 se reprodujeron con objetos sintéticos sin persistencia; los restantes son lectura estática y fuentes. No se corrigieron por tratarse de auditoría independiente.

## Roles instalados y aceptación pendiente

Catálogo local: **199 permisos distintos y 537 asignaciones rol–permiso**. Grants agregados de role_has_permissions; no incluyen permisos directos por usuario ni prueban acceso contextual. No se consultaron usuarios asignados.

| Rol exacto | Grants locales | Implementación localizada | Falta para aceptar rol completo |
|---|---:|---|---|
| SUPERADMINISTRADOR | 191 | Gestión técnica y lectura global | S01/S02; lectura sin convertirla en competencia clínica. |
| GERENTE | 32 | Dashboard gerencial, personal, áreas y planificación | Recorrido completo de cobertura, reportes y límites frente a operación/clínica. |
| ADMINISTRADOR | 59 | Dashboard operativo, admisión, alojamiento, asignaciones y documentos | Errores, reintentos y continuidad; negativos frente a RRHH/escritura clínica. |
| MEDICO GENERAL/GERIATRA | 74 | Dashboard, expediente, prescripción, instrumentos, gobierno y Petersen | S03–S06; continuidad completa y conservación compuesta pendiente. |
| ENFERMEROS | 73 | Dashboard, agenda, cuidados, pase, administración y consulta cognitiva | Turnos/cierres/revocaciones por captura; nunca prescripción. |
| PSICOLOGO/A | 35 | Dashboard, instrumentos, observaciones, gobierno y Petersen | Recorrido documental/método/seguimiento y conservación íntegra. |
| NUTRICIONISTA | 23 | Dashboard de datos propios y endpoint profesional | S06; valoración→seguimiento→plan→informe y navegación. |
| FISIOTERAPEUTA | 23 | Dashboard de datos propios y endpoint funcional | Valoración→plan/intervención→seguimiento→informe y negativos. |
| PEDAGOGO | 20 | Dashboard de actividades/participación/seguimientos propios | Planificación→participantes→resultados→seguimiento→informe. |
| FAMILIAR | 7 | Dashboard con vínculo/contacto activos | S07; publicación documental individual no existe; vínculo no publica expediente. |

Los diez roles tienen representación en seeder y RolePreviewService. Hay cuatro dashboards especializados (Administración, Medicina, Enfermería, Psicología) y un dashboard compartido con variantes para Superadministración, Gerencia, Nutrición, Fisioterapia, Pedagogía y Familiar: no son seis roles sin dashboard. Presencia de una tarjeta no acredita proceso completo.

Consulta local selectiva: IADL.ver, reglas_expertas.ver y valoraciones_petersen.ver corresponden a Medicina/Psicología/Superadministración. Gestionar/revisar reglas, crear/rectificar Petersen y ejecutar_observaciones están en Medicina/Psicología. No en los otros siete roles ni en Superadministración. El permiso IADL no acredita C03.

## Por qué falta evidencia objetiva interpretable en el experto

Configuración runtime leída: perfiles_instrumentales=0, perfiles_interpretativos=0, lectura_clinica=0, contratos_conocimiento=0; version_cognitiva no configurada. Fuente base [config/sistema_experto.php:6](<C:/Users/CARLAENCINAS/Desktop/RememberMind/config/sistema_experto.php:6>) y siguientes. No se configuraron perfiles durante la auditoría.

Instrumentos: 21 (FUNCIONAL 2, OBSERVACIONAL 19); estados ACTIVO 2 / INACTIVO 19. Preguntas 39, opciones 98, aplicaciones 990, respuestas 7326. Petersen originales 0 y soportes 0. Solo agregados: no se deduce población, naturaleza real/sintética ni calidad clínica. Aplicaciones y respuestas existentes no equivalen a una medición objetiva interpretable con perfil/método/versión aprobados. No fabricar perfiles ni clasificadores para llenar una pantalla.

## Tablas, formularios y arquitectura

El [CSV de consumidores](TABLAS_CONSUMIDORES.csv) enumera las 120 tablas con grupo, propietario técnico, filas, Model y consumidores estáticos de lectura/escritura/presentación. El barrido localizó referencias candidatas de escritura/carga para 103 de 104 tablas, lectura/relación para 104 y presentación directa o dependiente para 96; **no son 96 formularios ni 103 escritores aceptados**. IADL es la única sin escritor de producto directo localizado. Las referencias de código son localización, no prueba de ejecución; columnas no localizadas se marcan explícitamente. No se cuenta CatalogoCodigos ni renumeración como uso funcional.

Las 104 tablas de negocio tienen Model explícito. Una admisión escribe varias tablas; instrumento captura aplicación/respuestas; gobierno administra regla/condiciones/consecuencia; conservar evaluación distribuye cabecera/evidencias/criterios/trazas. No se requiere un formulario por tabla. Catálogos y detalles internos pueden necesitar carga/edición controlada o formulario del agregado, no CRUD abierto. IADL tiene lectura/vinculación sin formulario independiente de escritura localizado; eso no autoriza nuevas responsabilidades. Tipos/componentes de estudio y vocabularios semánticos deben distinguir carga técnica de captura profesional.

Cien de104tablas de negocio tienen filas en el corte; cuatro están vacías: relaciones_evidencias_evaluacion, revisiones_evaluacion_experta, valoraciones_petersen_originales y soportes_valoracion_petersen. Las dos primeras tienen cero filas locales pero sí contratos y consumidores. No son prescindibles por ese contador. Las cuatro tablas consolidadas ya no existen físicamente; sus nombres como alias de identidad o en migración/reversa no significan tablas escondidas.

Doce propietarios: Admisiones, Alertas, ApoyoDecision, Clinica, Cuidados, Identidad, Institucion, Medicacion, Reportes, Residentes, Social y Valoraciones. Capas Dominio/Aplicacion/Infraestructura/Presentacion; rutas compuestas por propietario; app/Compartido para capacidades compartidas y motor-experto independiente. App/Models es excepción ADR deliberada para identidades/relaciones/factories/Activitylog/autenticación. No reorganizarlo por preferencia estética.

Nombres ejecutables AdultoMayor/adultos-mayores siguen en Controllers/rutas aunque usen Residente/cod_residente: deuda V2, no prueba de tabla V1 física. No aparecen adultos_mayores/adulto_mayor en el inventario. Esta auditoría no afirma barrido íntegro de todas las columnas ni eliminación global de legacy.

## Evidencia y límites

- Ejecutado `php artisan app:auditar-base-datos-remember-mind --contar-filas`: PostgreSQL local, 29/29 expertas + 91 adicionales. Metadata y conteos; sin registros clínicos.
- Ejecutadas consultas READ ONLY de metadata y agregados roles/permisos/tablas. Sin usuarios, contraseñas, tokens, expedientes ni ficheros reales.
- Ejecutados casos sintéticos S01/S06 sin autenticación ni escrituras. No son pruebas de una ruta HTTP explotable.
- Trazabilidad estática de Models, acciones, consultas, presentación, configuraciones y fuentes. Aquí no se ejecutaron suites ni QA visual: evidencia de raíz separada.

No se certifica producción, exposición de archivos del despliegue, restauración de respaldo, validación clínica, licencias, concurrencia global, mínimo de tablas o completitud de diez roles. Las correcciones deben ser otra fase con negativos y efectos comprobados. Mantener 104 tablas mientras no exista una nueva decisión estructural concreta.
