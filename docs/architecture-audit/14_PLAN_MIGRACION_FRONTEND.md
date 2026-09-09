# Convivencia Livewire → Vue

## Orden corregido

No migrar el sistema experto primero: no existe aún su base longitudinal estable, y sería un primer corte demasiado incierto. Primero P0 y Actions comunes; después infraestructura Vue y una página de residentes de solo lectura. Luego dashboard y expediente; después entradas clínicas. El experto llega cuando las fuentes y versiones ya son confiables.

| Familia actual | Clasificación | Orden / condición |
|---|---|---|
| Login, reset, 2FA, perfil Jetstream | MANTENER TEMPORALMENTE EN LIVEWIRE | Último corte de identidad visual; mismos endpoints/sesión |
| Usuarios/PersonalInstitucional/RolesPermisos | MANTENER TEMPORALMENTE EN LIVEWIRE | Corregir autorización backend ya; migrar UI tras estabilizar personas |
| Áreas/turnos y voluntariado | MIGRAR DESPUÉS | Bajo retorno comparado con expediente; conservar funciones usadas |
| Nuevo shell + listado Residents | MIGRAR PRIMERO | Primera vertical simple, lectura autorizada y navegación |
| Dashboard general/médico/psicológico | MIGRAR PRIMERO | Tras Queries comunes, composiciones 08 |
| Expedientes general/médico/enfermería | MIGRAR PRIMERO | Lectura por sección y fuente canónica; no tres nuevas fichas |
| Psicología/evaluaciones | MIGRAR DESPUÉS | Primera entrada compleja tras PerformAssessment y versiones |
| Residentes/admisiones | MIGRAR DESPUÉS | Formularios tras AdmitResident, asignación y documentos consistentes |
| SaludSeguimiento/signos | MIGRAR DESPUÉS | Contrato de ID/cálculos unificado |
| Medicación | MIGRAR DESPUÉS | Solo con pruebas idempotencia, pertenencia y suspensión |
| Enfermería/planes/pase/tareas | MIGRAR DESPUÉS | Depende de turno y datos de planes establecidos |
| Nutrición/fisioterapia placeholders | REESCRIBIR | Implementación nueva de alcance aprobado |
| Portal familiar | REESCRIBIR | Policy de vínculo/consentimiento y Resource mínimo antes de UI |
| ExpertSystem | REESCRIBIR (nuevo) | Después de historia, snapshots y puerto probado |
| PDFs/correo Blade | MANTENER | No migrar a Vue; no son páginas interactivas |
| Duplicados huérfanos y aliases placeholder | ELIMINAR | Solo tras confirmar sustitución/referencias y aceptación |

## Convivencia técnica

Mismo guard web/Fortify, cookies, CSRF y permisos. Laravel decide por ruta si devuelve Blade/Livewire o Inertia. Las páginas legacy cargan su bundle y Alpine; las nuevas cargan Vue. Cruzar frontera con navegación completa (enlace normal), evitando wire:navigate sobre páginas Inertia o Link Inertia hacia respuestas Blade no compatibles. No montar Vue y Livewire como propietarios del mismo árbol DOM.

Una Action/Query es común durante el corte; no dos implementaciones de reglas. Registrar una ruta canónica y conmutación de renderer controlada por configuración. No dos writers independientes que mantengan tablas espejo. Si la persistencia cambió, la pantalla legacy llama al mismo caso de uso o queda de solo lectura.

## Contrato de cada retiro

| Antigua | Nueva | Pruebas mínimas | Retiro |
|---|---|---|---|
| AdultosMayoresPanel/listado | Residents/Index | Alcance, filtros, paginación, teclado | Quitar ruta/include legacy tras aceptación del corte |
| DashboardMedico/Psicologo | Dashboard/Clinical | Widgets autorizados, varios perfiles, datos vacíos | Retirar composiciones duplicadas; redirigir bookmarks |
| show + FichaClinicaIntegradaPanel + FichaPaciente | Residents/Show | Misma fuente, sección denegada no enviada, historia por fecha | Retiro por secciones; ninguna sección con dos writers |
| EvaluacionGeriatricaAreaModal | Assessment/Create | Versión, score servidor, borrador/finalizado, permiso | Quitar listeners y modal al activar formulario nuevo |
| PreadmisionWizard | Admissions/Create | Idempotencia, errores documentales, turnos y estado | Retirar wizard al cerrar reconciliación de datos |
| SaludMedicacion/Administración | Medication/Orders y Administrations | Orden ajena, doble clic/reintento, suspensión concurrente | Sin fallback a writer viejo ante errores clínicos |
| PlanCuidado/Tareas/PaseTurno | Care/* | Ejecución por turno, corrección, recepción | Quitar rutas/componentes y eventos huérfanos |

Una ventana de reversión corta (p.ej. una iteración, por acordar) puede conservar el código anterior sin estar publicado. La conmutación vuelve al renderer anterior solo si sigue usando el mismo contrato de datos. Si hubo escrituras en un modelo incompatible, detener el corte y conciliar; no alternar versiones silenciosamente. El retiro se verifica por route:list, referencias, navegación y pruebas de flujos, no por fechas arbitrarias.

## Costo orientativo de presentación

16–24 semanas-persona dentro del programa general para infraestructura, componentes y pantallas prioritarias, con una desarrolladora familiarizada con Laravel pero aprendiendo Vue/TS. No es adicional íntegro al roadmap: se solapa con sus fases de frontend. Medir dos pantallas representativas antes de comprometer calendario. Si el esfuerzo no es viable, mantener temporalmente Livewire usando la misma arquitectura backend.
