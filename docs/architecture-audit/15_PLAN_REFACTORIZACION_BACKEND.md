# Plan de refactorización backend

## Secuencia por cortes

1. Proteger entradas existentes y agregar caracterización aislada: no empezar moviendo carpetas. UsuarioController/StoreUsuarioRequest, expediente familiar y documentos primero.
2. Introducir Policies de User/Resident/Prescription/Assessment/Document y scopes de lectura. Las Actions compartidas reciben Actor/User explícito; no asumir que siempre hay request autenticado.
3. Extraer AdmitResident/AssignBed desde PreadmisionWizard, PreadmisionesPanel y componentes de asignación. Preservar comportamiento válido, releer estado y locks dentro de transacción, documentos después del commit.
4. Separar identidad personal/laboral/acceso. Mantener adaptadores legacy y PK de usuario mientras se actualizan referencias/factories/morph maps.
5. Extraer RegisterVitalSigns/CreatePrescription/AdministerMedication. Normalizar IDs y pertenencia, añadir historial/versiones y evitar update destructivo.
6. Extraer PerformAssessment/CreateCarePlan/RegisterFall. Cálculos puros fuera de Livewire y contratos de versiones.
7. Reducir DashboardService/ReporteDataService a Queries especializadas y adaptadores de exportación. Un reporte nunca modifica el dato reportado.
8. Mover clases estabilizadas a Modules; resolver factories, rutas, imports, Policies y config por módulo. Mover namespace es el último paso mecánico de cada corte, no el objetivo arquitectónico.

## Objetos con exceso de responsabilidad

| Clase | Evidencia funcional | Extracción propuesta |
|---|---|---|
| UsuariosPanel | Crear/editar, familiar, adulto, rol, password, foto, exportes, correos, documentos | RegisterPerson, ProvisionAccount, UpdateAccess; Queries y Documents |
| PersonalInstitucionalForm | Datos/validaciones y flujo laboral extenso | Datos personales + vinculación laboral + provisión de acceso |
| TurnosAsignacionesPanel | Gestión/planificación/exportación | AssignSchedule y CoverageQuery |
| GeneradorPlanillaEnfermeriaService | 1112 líneas de generación operativa | Reglas puras de cobertura y orquestación separada; conservar resultados de referencia |
| SaludSignosPanel | 1304 líneas: datos, gráficos, reportes, creación/anulación | RegisterVitalSigns, CorrectVitalSigns, VitalSignsTrend |
| PreadmisionWizard | 957 líneas, documentos/correo/PDF y asignación | AdmitResident + documental recuperable + selección de cobertura |
| DashboardService | 927 líneas, varias áreas, alerts/cache/listas | Queries por widget con capacidades y fecha de actualización |
| AdultoMayorController | show carga colecciones; reportes/estado | ResidentOverviewQuery, TimelineQuery y Actions |
| EvaluacionGeriatrica | Mutadores espejos, estado mezclado con riesgo | Migración de aliases y servicio puro de scoring |

La longitud orienta la revisión, no es criterio automático para dividir. No sustituir un componente gigante por un servicio gigante ni extraer métodos privados que sigan compartiendo todo el estado mutable.

## Rutas y convenciones

Separar web.php en los cinco grupos definidos en 04, manteniendo auth/session y permisos declarados. Policies siguen siendo necesarias para objetos. Recurso usuarios no debe aplicar usuarios.ver a todas las mutaciones; asignar por acción y concesión. Rutas médico/psicología dejan de imponer profesiones por string y usan capacidades. Retirar aliases que prometen módulos ausentes después de actualizar menú/bookmarks.

## Deuda de repositorio

| Elemento observado o solicitado | Clasificación | Condición |
|---|---|---|
| AGENTS.md, README, manifests y locks | CONSERVAR | Actualizar documentación del motor y flujo tras aprobación |
| docs/auditoria.md, docs/auditoria_roles_permisos.md, RESUMEN_ARQUITECTURA.md | MOVER A docs/ (los dos primeros ya están) | Marcar fecha/histórico; no confundir con esta baseline |
| show.blade.php.bak | ELIMINAR POSTERIORMENTE o archivo histórico | Comparar con Git y comprobar que no contiene única versión útil |
| enfermeria${view}.blade.php | ELIMINAR POSTERIORMENTE si huérfano | Verificar referencias y origen de generación |
| fix*.py, scratch*, dumps SQL, test*.php manuales | No observados en raíz/inventario excluyendo vendor/node_modules/storage | No afirmar que siguen existiendo porque AGENTS.md los menciona |
| Scripts de reparación que se recuperen | MOVER A tools/ | Solo genéricos reproducibles; documentar precondiciones y dry-run |
| Backups/dumps con datos | IGNORAR en Git, almacenamiento privado | Política de respaldo/retención, nunca repo público |
| vendor, node_modules, caches y artefactos runtime | IGNORAR | Conservar instalación local; no borrar durante auditoría |
| tests/Feature/CasosPreadmisionTest.php no versionado | CONSERVAR | Cambio previo de la autora; revisar y ejecutar en entorno aislado luego |

No se eliminó/movió ninguno. El árbol de aplicación tiene utilidades como AuditarBaseDatosRememberMind; revisar cada comando antes de ejecutar: su nombre no garantiza solo lectura.

## Definition of Done de un corte

Una fuente de verdad; autorizaciones positivas/negativas; pruebas de invariantes; transacción y efectos externos definidos; errores seguros; rollback documentado; enlaces y reportes compatibles; código legacy retirado o ventana de retirada explícita. Ningún paso ejecuta fresh sobre la BD real.
