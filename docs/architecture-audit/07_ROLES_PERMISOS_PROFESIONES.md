# Identidad, profesiones y autorización

## AS-IS

El seeder y la consulta READ ONLY coinciden en 10 roles: SUPERADMINISTRADOR, ADMINISTRADOR, ENFERMEROS, MEDICO GENERAL/GERIATRA, PSICOLOGO/A, PEDAGOGO, NUTRICIONISTA, FISIOTERAPEUTA, VOLUNTARIO y FAMILIAR. No representan exactamente los 11 perfiles objetivo. Hay decisiones por hasRole/hasAnyRole y permisos con nombres duplicados (medicacion.* y salud.medicacion.*). AppServiceProvider::boot concede todas las capacidades al superadministrador mediante Gate::before.

## Modelo propuesto

Persona = identidad humana. Usuario = credenciales, estado y sesión, opcional por persona. Profesional = datos laborales/habilitación, opcional; profesión = formación/ocupación; especialidad = área de competencia; cargo = responsabilidad organizativa; área = ubicación funcional; rol = paquete de acceso; permiso = acción autorizable. Tener profesión no concede permisos automáticamente. Familiar y voluntario pueden existir sin usuario.

Proponer 6 roles base: technical_admin, oversight, operations, clinician, caregiver, family. Los 11 perfiles funcionales eligen composición de dashboard y **plantillas de permisos explícitas**, revisadas al alta por un administrador con facultad de concesión. Médico, psicólogo, enfermería, fisioterapeuta y nutrición usan clinician con permisos distintos; trabajo social usa operations con plantilla social y sin gestión de cuentas. Dirección usa oversight. Cuidador usa caregiver. Familiar usa family. El técnico no recibe bypass clínico permanente.

Las plantillas viven inicialmente en configuración versionada y se materializan en model_has_permissions de Spatie; no crear tablas ACL propias. El rol clinician solo concede un mínimo seguro, nunca prescripción/validación general. Pantalla de acceso muestra permisos efectivos, plantilla aplicada y diferencias; concesión/revocación auditada y revisión periódica. Un cambio de profesión no recalcula permisos silenciosamente.

Costo: permisos directos requieren disciplina. Si se acumulan excepciones o varias personas necesitan el mismo paquete especializado estable, convertir ese paquete en rol de capacidad (p.ej. prescriber), sin fingir que siguen siendo seis. Alternativa razonable: 9–11 roles de capacidades explícitas si la autora prefiere operación más simple a reducir el conteo. El mínimo propuesto es seis, no una restricción del modelo.

## Alcance contextual

Acceso efectivo = cuenta activa/habilitada + permiso + relación vigente con residente/área/turno + estado editable del registro + campos permitidos. No usar únicamente un rol como scope. Una asignación profesional es condición contextual, no habilitación automática. Las lecturas agregadas de Dirección omiten datos clínicos identificables salvo permiso adicional justificado.

## Matriz RBAC propuesta

V/C/E/L/N/A = ver/crear/editar/validar/anular/administrar. S = permitido dentro del alcance; P = propio borrador; D = disciplina asignada; G = agregado; F = campos/residente expresamente autorizados; — = denegado. Cada celda condicional requiere Policy. **Toda combinación actor–módulo no listada se deniega**, incluido cualquier permiso nuevo. Administrar nunca implica validar actos clínicos. Anular exige motivo y conserva registro.

| Actor | Módulo/recurso | Ver | Crear | Editar | Validar | Anular | Administrar |
|---|---|---|---|---|---|---|---|
| Dirección | Reports/indicadores | G | — | — | — | — | — |
| Dirección | Safety/alertas críticas agregadas | G | — | — | — | — | — |
| Dirección | Institution/políticas operativas | S | — | — | S | — | S |
| Administración | Residents/identificación | S | S | S | — | — | S |
| Administración | Admissions | S | S | P | S | S | S |
| Administración | Institution/camas/agenda | S | S | S | — | S | S |
| Administración | Identity/acceso ordinario | S | S | S | — | S | S |
| Administración | Documents/administrativos | S | S | P | S | S | S |
| Médico | Residents/360 clínico | S | — | — | — | — | — |
| Médico | Clinical | S | S | P | S | S | — |
| Médico | Medication/prescripción | S | S | P | S | S | — |
| Médico | Medication/administraciones | S | — | — | — | — | — |
| Médico | Admissions/decisión clínica | S | S | P | S | S | — |
| Médico | Assessment/Care | S | D | P | D | D | — |
| Médico | ExpertSystem | S | S | — | D | — | — |
| Médico | Safety | S | S | P | S | S | — |
| Psicólogo | Residents/360 necesario | S | — | — | — | — | — |
| Psicólogo | Assessment/cognición-afectividad | D | D | P | D | D | — |
| Psicólogo | Care/conducta y plan propio | D | D | P | D | D | — |
| Psicólogo | ExpertSystem/cognitivo | D | D | — | D | — | — |
| Psicólogo | Safety/observación | S | S | P | — | P | — |
| Enfermería | Residents/360 y Clinical/signos | S | D | P | D | D | — |
| Enfermería | Medication/prescripción | S | — | — | — | — | — |
| Enfermería | Medication/administración | S | S | P | — | S | — |
| Enfermería | Care/turno y tareas | S | S | P | D | S | — |
| Enfermería | Assessment/enfermería | D | D | P | D | D | — |
| Enfermería | Safety | S | S | P | S | S | — |
| Fisioterapia | Residents/360 necesario | S | — | — | — | — | — |
| Fisioterapia | Assessment/movilidad | D | D | P | D | D | — |
| Fisioterapia | Care/rehabilitación | D | D | P | D | D | — |
| Fisioterapia | Safety/caídas | S | S | P | — | P | — |
| Nutrición | Residents/360 necesario | S | — | — | — | — | — |
| Nutrición | Assessment/nutrición | D | D | P | D | D | — |
| Nutrición | Care/nutrición | D | D | P | D | D | — |
| Nutrición | Clinical/peso-ingesta pertinente | D | D | P | D | D | — |
| Nutrición | Safety/alerta nutricional | S | S | P | — | P | — |
| Trabajo Social | Residents/contactos y permisos informativos | D | D | P | D | D | — |
| Trabajo Social | Social/seguimiento-visitas-actividades | S | S | P | D | S | — |
| Trabajo Social | Documents/sociales | D | D | P | D | D | — |
| Trabajo Social | Safety | S | S | P | — | P | — |
| Cuidador | Residents/resumen mínimo asignado | S | — | — | — | — | — |
| Cuidador | Care/ejecuciones-observaciones | S | S | P | — | P | — |
| Cuidador | Safety/incidencias | S | S | P | — | P | — |
| Familiar | Residents/resumen publicado | F | — | — | — | — | — |
| Familiar | Social/visitas autorizadas | F | F | P | — | P | — |
| Familiar | Documents/compartidos | F | — | — | — | — | — |
| Superadmin técnico | Identity/concesión y operación | S | S | S | — | S | S |
| Superadmin técnico | Infraestructura/logs técnicos minimizados | S | — | — | — | — | S |
| Superadmin técnico | Clinical/Medication/Assessment/Expert | — | — | — | — | — | — |

La validación administrativa de admisión no sustituye aptitud clínica. Una ejecución de medicación finalizada no se edita: se corrige con registro enlazado y motivo; P solo aplica al borrador. Conceder permisos clínicos a personal técnico requeriría un acceso excepcional temporal, justificado y auditado, no Gate::before universal.

## Migración de accesos

Exportar solo metadatos de rol/permisos efectivos a un entorno privado; construir equivalencias de cada permiso legacy a acción objetivo. Revocar grants generales familiares, preservar acceso administrativo necesario, probar perfiles mínimos y combinados. No hacer syncPermissions masivo en producción sin diff revisable. Sembrar roles/permisos idempotentemente sin sobreescribir personalizaciones aprobadas. El acceso real debe probarse por HTTP, llamadas Livewire, exportaciones y Jobs solicitados por usuario.
