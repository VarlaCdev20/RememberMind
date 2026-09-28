# Brechas y prioridades

H = hallazgo observado en código/esquema; D = dato agregado observado; R = riesgo cuya explotación requiere prueba aislada. No se realizaron ataques ni mutaciones en la BD real.

| ID / prioridad | Evidencia | Brecha e impacto | Destino y condición de cierre |
|---|---|---|---|
| G01 P0 H/R | routes/web.php: recurso usuarios con usuarios.ver; UsuarioController::store, StoreUsuarioRequest::authorize=true y rol validado como existente | Lectura habilita entrada de creación y rol sin límite de concesión; posible escalamiento | Policy create + grantRole, allowlist de concesión, test que usuario solo lector recibe 403 y no crea usuario |
| G02 P0 H/R | RolesAndPermissionsSeeder: FAMILIAR recibe adultos.ver; AdultoMayorController::show no filtra vínculo | Acceso a expediente ajeno/colecciones clínicas por URL si vista/flujo lo expone | Scope de residentes y recursos por campo; tests familiar vinculado/desvinculado, export y payload |
| G03 P0 D/H | Consulta READ ONLY: 1 grupo cod_cama con múltiples ACTIVO; asignacion_adulto_mayor sin UNIQUE activo | Ocupación ambigua y carrera de asignación | Conciliación humana, AssignBed transaccional, índice único parcial; carrera de dos solicitudes deja un ganador |
| G04 P0 H | StoreAdministracionMedicacionRequest exige integer para PK string; FK independientes de residente y medicación | Rechazo de códigos válidos; no garantiza pertenencia, tampoco unicidad por dosis | Identificador coherente y Action por prescription_item; tests de residente ajeno/reintento/orden suspendida |
| G05 P0 H/R | PreadmisionWizard líneas 344,381,400 escribe documentos en public; config/filesystems.php | Documentación sensible potencialmente accesible sin Policy vía storage | Disco privado, descarga autorizada; comprobar URL directa denegada y permisos por archivo |
| G06 P0 H | UsuarioController::generarPasswordInicial; UsuariosPanel::procesarPostGuardado y restablecerPasswordUsuario envían contraseña | Contraseñas predecibles o expuestas por correo/flash | Invitación de un uso con expiración, cambio obligatorio, sin texto de contraseña en correo/log |
| G07 P0 H/R | DecisionAdmisionModal::guardar: required sin enum; comprueba etapa en open pero no al guardar | Transición inválida/obsoleta y autorización de mutación no explícita | Validar enum, Policy y estado releído bajo bloqueo dentro de Action |
| G08 P1 H | GeneraCodigo, User::booted, AdultoMayor::booted | Generación basada en máximo+1 no atómica, doble hook | ID generado por BD o UUID, código público separado con UNIQUE; prueba concurrente |
| G09 P1 H | EvaluacionGeriatrica setters; migración compatibility_columns | puntaje/puntaje_total, estado/estado_eval y resultados duplicados | Un campo canónico por concepto; importación detecta discrepancias y conserva procedencia |
| G10 P1 H | FichaMedicaAdultoModal:160, SaludFichaPanel:251 | Edición sobreescribe hechos clínicos | Registro versionado/finalizado, corrección con motivo; consulta histórica reproduce versión original |
| G11 P1 H | adult_o: alergias/consentimiento/ubicación; preadmisiones y familiares | Identidad y hechos duplicados | persons, consents, admissions y bed_assignments; reconciliación antes del cambio |
| G12 P2 H | UsuariosPanel/PreadmisionWizard/SaludSignosPanel | UI coordina procesos/SQL/PDF/correo | Actions/Queries, archivos y correo después del commit con reintentos |
| G13 P2 H | AdultoMayorController::show y DashboardService | Carga amplia, informes y reglas mezclados | Read models limitados, permisos antes de cachear/serializar, índices verificados |
| G14 P3 H | resources/views y resources/js/app.js | HTML extenso, estado Alpine/Livewire, globals de gráficos | Componentes Vue por corte, design tokens compartidos, lifecycle de charts |
| G15 P4 H | No módulo ExpertSystem/integración Python en inventario app | Inferencias no trazables todavía | Puerto, snapshots y revisión humana; contrato probado sin Python |
| G16 P5 H | Rutas nutricion/fisioterapia/portal remiten a dashboard | Menú presenta alcance no implementado | Mostrar solo capacidades reales, desarrollar después de núcleo |

## Fuentes de verdad y dependencias

G01–G07 preceden cualquier acceso familiar/piloto. G08–G11 preceden la migración longitudinal y el experto. G12–G13 permiten cambiar la interfaz sin duplicar reglas. G14 no corrige G01 por sí solo. No convertir prioridades en una reescritura simultánea.

## Cosas que están bien

Hay FormRequests, múltiples comprobaciones backend en Livewire, transacciones en admisiones/usuarios, FK e índices en PostgreSQL, separación parcial de reportes, estado histórico, SoftDeletes y bitácora. Hay tokens semánticos, modo oscuro y componentes Blade reutilizables. Conservar estas decisiones y completar su aplicación; no calificarlas como ausentes por falta de uniformidad.

## Límites de evidencia

Los cuatro agregados de datos solo cubren pertenencia de medicación, repetición de cama ACTIVO y dos pares espejo de evaluaciones. Cero discrepancias en esos pares no demuestra ausencia de otros conflictos. La suite no se ejecutó y no hay evaluación visual de WCAG; sus verificaciones están especificadas en 16.
