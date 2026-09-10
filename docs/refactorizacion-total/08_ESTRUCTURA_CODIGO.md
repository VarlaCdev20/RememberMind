# Estructura de código propuesta

Monolito Laravel modular por carpetas, conservando Blade + Livewire, Jetstream/Fortify/Sanctum y paquetes ya instalados. Sin microservicios, CQRS, Event Sourcing, DDD completo, repositorios universales ni interfaces por cada clase. Los módulos organizan responsabilidad, no crean un framework dentro de Laravel.

## Árbol objetivo

```text
app/
  Models/
    User.php                       # identidad de auth estable para paquetes
    Identidad/                     # Person, PersonIdentifier
    Institucion/                   # Area, StaffAssignment, Shift, Professional...
    Residentes/ Admisiones/ Clinica/ Medicacion/ Vgi/ Cuidados/
    Social/ Actividades/ SeguridadAsistencial/ Documentos/ Experto/ Reportes/
  Http/
    Controllers/<Modulo>/          # entrada HTTP, descargas, redirects
    Requests/<Modulo>/             # validación HTTP + autorización del caso
  Livewire/<Modulo>/               # estados UI, paginación y orquestación breve
    Forms/                         # validación/form state compartido si aporta
  Policies/<Modulo>/              # autorización contextual por agregado
  Actions/<Modulo>/               # solo casos con invariantes/transacción
  Queries/<Modulo>/               # lecturas complejas o compartidas
  Services/
    Documentos/                    # storage, PDF, envío e integraciones existentes
    Institucion/                   # generador de planilla y reglas de cobertura
  Rules/                           # validadores concretos reutilizables
  Enums/<Modulo>/                  # estados/decisiones cerrados y tipados
  Jobs/                            # exportación/correo después de commit
  Providers/                       # registro explícito de Policies
routes/
  web.php                          # auth, prefijo admin, carga grupos
  admin/<modulo>.php               # rutas y permiso por acción
resources/views/
  layouts/                         # sistema y auth existentes
  components/                      # controles Blade comunes + tokens
  modules/<modulo>/                # pantallas, parciales y paneles
  reports/                         # impresión/PDF reutilizable
  mail/                            # plantillas de envío
resources/css/design-of-system/    # conservar tokens y dark mode class
resources/js/                      # interacción Alpine/Livewire y gráficos
  app.js
config/
  navigation.php                  # menú por capacidades, sin rol=profesión
  clinical.php                    # configuración operativa validada, no historia
  reports.php                     # catálogo de reportes y campos permitidos
  permission.php                  # contrato Spatie y guard web
  fortify.php                     # login email/correo resuelto por migración
  database.php                   # PostgreSQL
  filesystems.php                # documentos privados
  ...
database/
  migrations/                    # evolución incremental y restricciones
  seeders/Catalogs/               # mínimos de producto idempotentes
  seeders/Demo/                   # nunca requeridos en producción
  factories/                     # datos ficticios coherentes para pruebas
tests/
  Feature/<Modulo>/              # HTTP + Livewire + scope + archivos
  Integration/Postgres/          # FKs, triggers, concurrencia, migración
  Unit/                          # scoring/horarios/pautas, cuando son funciones puras
```

No crear todas las carpetas vacías anticipadamente. Los nombres de los modelos y las 83 tablas se fijan en 03; usuarios mantiene App\Models\User para reducir cambios en contratos de autenticación. El resto se mueve de manera gradual con imports/morph map revisados. No introducir DTOs o interfaces si tipos simples/form objects bastan.

## Responsabilidades y límites

| Pieza | Hace | Evita |
|---|---|---|
| Modelo Eloquent | Relaciones/casts/scopes pequeños y estado local | Derivar profesión/cargo de rol; generar PK con MAX; coordinar módulos o enviar correo |
| Livewire | Estado del formulario, paginación, mensajes; autorizar acción, validar y llamar caso de uso | SQL de negocio complejo duplicado, 2500 líneas de escritura/exports/roles en panel |
| Controller | Validar/autorizar entrada HTTP, invocar Action/Query, responder/stream | Segunda implementación del mismo guardar de Livewire |
| FormRequest/Form | Reglas compartidas y normalización sin pérdida | authorize=true como único control; convertir PK string a int sin migración |
| Policy | Scope por persona/estancia/equipo/consentimiento/competencia | Confiar en menú oculto; bypass global técnico |
| Action | Transacción, locks, idempotencia, reglas multi-entidad y auditoría | CRUD trivial por costumbre, interfaz/repository por modelo |
| Query | Filtros/composición/agrupaciones complejas reutilizadas y scope | Reflejar todas las lecturas simples en clases ceremoniales |
| Service | Algoritmo de planilla o adaptación storage/PDF | Servicio universal que sabe todos los módulos |
| Job | Efecto externo reintentable con request key después de commit | Correo dentro de transacción; payload de clínica completo serializado |

## Actions justificadas

| Caso / Action | Invariantes y unidad atómica |
|---|---|
| VincularCuentaPersona | Persona única, email único, token de alta, no rol derivado de profesión |
| CambiarPermisosUsuario | Conjunto delegable, impedir autoescalada, invalidar caché/registrar diferencias |
| BloquearAccesoUsuario | Estado/acceso coherentes y revocación de sesiones/tokens |
| RegistrarAcreditacion | Profesión/especialidad compatibles, evidencia y vigencia |
| VersionarHorario / PublicarPlanilla | Intervalos/descanso/cobertura y clave de publicación idempotente |
| AprobarCaso / RegistrarDecision | Estado bloqueado, evidencia clínica, responsable y una estancia por caso |
| AsignarCama / TrasladarResidente / DarAlta | Bloqueo de cama/estancia; no solape; cerrar asignaciones relacionadas |
| FirmarNota / CorregirRegistroClinico | Sujeto inmutable, autor competente, nueva versión y motivo |
| FirmarPrescripcion / SuspenderPrescripcion | Competencia, pauta completa, versión vigente, no alterar dosis administradas |
| RegistrarAdministracion | Mismo sujeto que prescripción, momento/estado/omisión, idempotencia |
| FinalizarEvaluacion | Formulario versión conocida, score calculado servidor, firma y coherencia |
| ValidarPlan / RegistrarResultadoTarea | Versión del plan, ocurrencia, referencias clínicas coherentes |
| EmitirPase / RecibirPase | Snapshot y receptor, control de doble recepción |
| CerrarAlerta | Evidencia de acciones, estado y motivo; persistir cierre como acción |
| RegistrarAsistenciaVoluntario | Asignación opcional coherente y horas válidas, no duplicar |
| ReemplazarDocumento / ValidarDocumento | Binario privado nuevo, hash, cadena de versiones y eventos |
| EjecutarReglas / RevisarResultadoExperto | Versión aprobada, snapshot reproducible, revisión humana |

Crear/editar un tipo de actividad puede seguir Eloquent + Policy + validación directa, sin Action adicional. Un helper de corrección reutilizable no debe eliminar validaciones específicas de cada entidad.

## Queries que sí aportan

ExpedienteResidenteQuery compone paneles de lectura paginados; OcupacionEnFechaQuery reconstruye intervalos; EquipoEnTurnoQuery resuelve jornadas y alcance; AgendaMedicacionQuery interpreta solo pautas conciliadas; EvolucionVgiQuery distingue versiones; PendientesCuidadosQuery junta tareas/alertas; ChecklistDocumentalQuery calcula cumplimiento; ReporteSaludQuery/ReporteEquipoQuery agregan datos con el mismo scope que UI. Catálogos pequeños usan Eloquent directo. Todos los reportes deben filtrar antes de agregar, exportar y cachear.

## Reorganización del código actual

| Actual | Destino |
|---|---|
| UsuarioController + UsuariosPanel + UsuarioFormModal + PersonalInstitucionalForm | Persona/cuenta/vínculo separados; formularios compartidos y mismas Actions; conservar rutas usuarios |
| User::getPersonalSaludAttribute/getPersonalAdminAttribute y categorías virtuales | Relaciones reales a persona, perfil acreditado y vínculo; retirar alias solo cuando no haya consumidores |
| HorarioPersonalAdmin/Salud + TurnosAsignacionesPanel | StaffSchedule único, PublicarPlanilla y Query de calendario; conservar reglas de carga/descanso verificadas |
| AdultoMayorController + SaludSeguimiento + FichaPaciente + FichaClinicaIntegrada | Expediente compuesto con paneles compartidos, scope único, lista sin cargar todas relaciones |
| FichaMedicaAdultoModal/SaludFichaPanel + NotaEvolucion | Atención/nota/condiciones/alergias, una vía de firma/corrección |
| Tres familias de signos y dos de VGI/funcional | SignosForm y EvaluacionForm comunes, versión de instrumento explícita |
| TareasPlanPanel y PlanCuidadoPanel | Plan raíz/versión, tareas y ejecuciones separadas |
| Voluntariado con DB::table y Schema::hasColumn | Modelos estables + Queries; eliminar adaptaciones solo después de esquema reconciliado |
| Servicios DocumentosUsuario y tablas por titular | Módulo Documentos con versión, requisitos y autorización contextual |
| ReporteDataService/ChartData/ExportService | Queries por reporte y exportador existente compartido; retirar imports inexistentes |
| SidebarService por profesión/rol y alias de dashboard | Catálogo de navegación por capability + ruta real; portales contextuales funcionales |
| Datos clínicos guardados solo en activity_log | Records clínicos propios más auditoría; conservar extracción legada del mapa |

## Transacciones, archivos y fallos

Transacciones cortas de DB, bloqueos en orden estable (estancia→camas ordenadas por ID→asignaciones), retry controlado de deadlock y unicidad. lock_version detecta formulario obsoleto. Archivos primero temporales privados, después promoción controlada y reconciliación; no borrar original antes de confirmar DB. Job afterCommit con identificador idempotente para PDF/correo/export; no afirmar “enviado” hasta éxito y evento. Compensación de temporales huérfanos y monitor de fallos; nunca rollback de un correo ya emitido como si no hubiera ocurrido.

## Pruebas y calidad estructural

No fijar cobertura porcentual artificial. Cada invariante anterior necesita prueba observable, incluyendo llamada Livewire directa, HTTP y PostgreSQL para constraints/concurrencia. SQLite en memoria puede seguir para tests rápidos compatibles; no prueba ILIKE, rangos, locks o triggers PostgreSQL. Suite PostgreSQL usa BD desechable separada con guardia que rechaza host/nombre de producción. Probar migración con snapshot anonimizado y manifiesto; no tests que solo repiten getters ni snapshots masivos de HTML.

Migrar referencia a namespaces/clases exige morph map estable y adaptación de jobs/logs. Rutas api.php no se activan por defecto ni se introduce API clínica porque existan tokens Sanctum. Mantener frontend tecnológico y optimizar únicamente formularios, navegación, reutilización, accesibilidad y carga de datos.

