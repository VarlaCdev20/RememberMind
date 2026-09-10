# CRUDs, estados y vistas Blade/Livewire

## Contrato común de siete operaciones

Crear/listar/ver/editar/activar/anular/eliminar son capacidades diferentes; no todo registro admite todas. La tabla de cada entidad aplica el contrato íntegro siguiente, junto con la regla específica que aparece debajo. Historial: lectura de versiones anuladas autorizada, estado visible y motivo; una “restauración” clínica crea nueva versión firmada que referencia la anulada, no borra la anulación.

### Perfil M

- **Crear:** Registrar catálogo/perfil validado.
- **Listar:** Filtrar/paginar vigentes e inactivos.
- **Ver:** Detalle y usos.
- **Editar/corregir:** Corregir metadata con auditoría; nunca cambiar significado de catálogo usado.
- **Activar/desactivar:** Sí, con motivo; bloquear nuevas referencias.
- **Anular:** Solo duplicado/registro inválido con motivo.
- **Eliminar:** Solo borrador sin referencias; datos usados se archivan.

### Perfil A

- **Crear:** Crear cuenta vinculada a persona, invitación segura.
- **Listar:** Solo alcance autorizado.
- **Ver:** Perfil sin secretos.
- **Editar/corregir:** Datos de cuenta con verificación; roles por acción distinta.
- **Activar/desactivar:** Bloquear/revocar sesiones y tokens.
- **Anular:** Invalidar alta errónea con evidencia.
- **Eliminar:** No física si autor de historial; anonimización requiere política aprobada.

### Perfil T

- **Crear:** Abrir intervalo validando competencia y no solapamiento.
- **Listar:** Calendario e historial.
- **Ver:** Detalle, vigencias y responsable.
- **Editar/corregir:** Cerrar anterior y crear intervalo corregido; conservar fuente.
- **Activar/desactivar:** Finalizar/reactivar mediante nuevo intervalo.
- **Anular:** Anular intervalo erróneo con motivo y referencias preservadas.
- **Eliminar:** Solo borrador sin efectos ni referencias.

### Perfil H

- **Crear:** Registrar hecho con autor, instante y request_key si aplica.
- **Listar:** Vigentes por defecto; anulados visibles en historial autorizado.
- **Ver:** Detalle, procedencia y cadena de corrección.
- **Editar/corregir:** Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id.
- **Activar/desactivar:** No aplica al hecho; el estado clínico se cambia con nueva observación.
- **Anular:** Sí con motivo, actor y fecha; nunca ocultar evidencia.
- **Eliminar:** Prohibida física para hechos; únicamente borradores vacíos sin efectos.

### Perfil V

- **Crear:** Crear nueva versión desde anterior.
- **Listar:** Todas las versiones con estado.
- **Ver:** Versión exacta usada y evidencia.
- **Editar/corregir:** Solo borrador; publicada/validada inmutable.
- **Activar/desactivar:** Retirar de nuevas operaciones sin alterar usos anteriores.
- **Anular:** Revocar versión con motivo sin borrar usos.
- **Eliminar:** Solo borrador nunca referenciado.

### Perfil W

- **Crear:** Iniciar flujo válido y autorizado.
- **Listar:** Bandejas por estado y alcance.
- **Ver:** Detalle e historial de transiciones.
- **Editar/corregir:** Datos de borrador; transición explícita con auditoría y lock_version.
- **Activar/desactivar:** Cerrar/reabrir solo transición autorizada.
- **Anular:** Cancelar/anular con motivo, conservar relaciones.
- **Eliminar:** Solo borrador sin efectos; en otro caso cancelar.

### Perfil S

- **Crear:** Solo importador idempotente.
- **Listar:** Conciliación restringida.
- **Ver:** Fuente/target/hash y problemas.
- **Editar/corregir:** Nueva ejecución de conciliación; no edición de negocio.
- **Activar/desactivar:** No aplica.
- **Anular:** Marcar intento descartado, no borrar evidencia.
- **Eliminar:** Retención aprobada, jamás desde CRUD de producto.

## Contrato por entidad

Rutas propuestas bajo /admin y nombre admin.<recurso>.*; conservar rutas antiguas mediante alias/redirects solo de GET durante transición. Las siete rutas admin.usuarios.* existentes conservan nombre y permisos usuarios.ver/crear/editar. DELETE usuarios permanece denegado cuando existe trazabilidad; activar/bloquear son acciones explícitas. En formularios compartidos la misma Action se llama desde HTTP y Livewire.

### Person — persons

Identidad personal única, tenga o no cuenta. Módulo Identidad y Seguridad.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **No fusionar automáticamente por nombre; normalización conserva grafía original**.

Vista/componente: resources/views/modules/identidad/persons/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Identidad\\PersonIndex / PersonForm o panel integrado en PersonasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### PersonIdentifier — person_identifiers

Identificación oficial por país y tipo. Módulo Identidad y Seguridad.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(country,type,number,complement normalizado) solo identificadores verificados activos; vacíos no son números**.

Vista/componente: resources/views/modules/identidad/person_identifiers/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Identidad\\PersonIdentifierIndex / PersonIdentifierForm o panel integrado en PersonasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### User — users

Cuenta de acceso independiente del empleo. Módulo Identidad y Seguridad.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear cuenta vinculada a persona, invitación segura | Solo alcance autorizado | Perfil sin secretos | Datos de cuenta con verificación; roles por acción distinta | Bloquear/revocar sesiones y tokens | Invalidar alta errónea con evidencia | No física si autor de historial; anonimización requiere política aprobada |

Regla específica: **U(person_id), U(lower(email)); correo de acceso separado del contacto; Fortify configurable**.

Vista/componente: resources/views/modules/identidad/users/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Identidad\\UserIndex / UserForm o panel integrado en PersonasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Institution — institutions

Centro y zona horaria operativa. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(code); inicialmente un centro, sin prometer multitenencia completa**.

Vista/componente: resources/views/modules/institucion/institutions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\InstitutionIndex / InstitutionForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Area — areas

Unidad organizativa real. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(institution_id,slug); sin roles que impliquen pertenencia**.

Vista/componente: resources/views/modules/institucion/areas/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\AreaIndex / AreaForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Position — positions

Cargo laboral independiente del permiso. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(institution_id,name)**.

Vista/componente: resources/views/modules/institucion/positions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\PositionIndex / PositionForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Profession — professions

Profesión acreditable. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(name)**.

Vista/componente: resources/views/modules/institucion/professions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\ProfessionIndex / ProfessionForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Specialty — specialties

Especialidad de una profesión. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(profession_id,name)**.

Vista/componente: resources/views/modules/institucion/specialties/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\SpecialtyIndex / SpecialtyForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Professional — professionals

Perfil profesional de una persona. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(person_id); no exige usuario ni empleo activo**.

Vista/componente: resources/views/modules/institucion/professionals/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\ProfessionalIndex / ProfessionalForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ProfessionalQualification — professional_qualifications

Acreditación profesional con vigencia. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear nueva versión desde anterior | Todas las versiones con estado | Versión exacta usada y evidencia | Solo borrador; publicada/validada inmutable | Retirar de nuevas operaciones sin alterar usos anteriores | Revocar versión con motivo sin borrar usos | Solo borrador nunca referenciado |

Regla específica: **Especialidad debe corresponder a profesión; renovaciones son filas nuevas; no inferir titulación desde rol**.

Vista/componente: resources/views/modules/institucion/professional_qualifications/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\ProfessionalQualificationIndex / ProfessionalQualificationForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### StaffAssignment — staff_assignments

Vínculo laboral y ubicación temporal. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **Una asignación principal por persona e institución en cada instante**.

Vista/componente: resources/views/modules/institucion/staff_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\StaffAssignmentIndex / StaffAssignmentForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Shift — shifts

Catálogo único de turnos. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(institution_id,name); madrugada/noche explícitas**.

Vista/componente: resources/views/modules/institucion/shifts/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\ShiftIndex / ShiftForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### StaffSchedule — staff_schedules

Versión de horario semanal de un vínculo. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **Día ISO1..7; periodos no solapados por persona; cambios cierran vigencia anterior**.

Vista/componente: resources/views/modules/institucion/staff_schedules/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\StaffScheduleIndex / StaffScheduleForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### StaffingSlot — staffing_slots

Plaza rotativa de planilla. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(area_id,code); plaza no es cama ni cuenta**.

Vista/componente: resources/views/modules/institucion/staffing_slots/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\StaffingSlotIndex / StaffingSlotForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### StaffingSlotAssignment — staffing_slot_assignments

Titularidad y reemplazos de plaza. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **Evitar dos titulares simultáneos; conservar reemplazos**.

Vista/componente: resources/views/modules/institucion/staffing_slot_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\StaffingSlotAssignmentIndex / StaffingSlotAssignmentForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### RosterAssignment — roster_assignments

Turno fechado publicado, descanso o apoyo. Módulo Institución y Personal.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **U(generation_key) si existe; no guardar simulaciones como trabajo realizado**.

Vista/componente: resources/views/modules/institucion/roster_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Institucion\\RosterAssignmentIndex / RosterAssignmentForm o panel integrado en AreasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Resident — residents

Expediente institucional persistente entre ingresos. Módulo Residentes.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(person_id), U(public_code); no guardar cama ni fecha única de ingreso aquí**.

Vista/componente: resources/views/modules/residentes/residents/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Residentes\\ResidentIndex / ResidentForm o panel integrado en ResidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ResidentStatusChange — resident_status_changes

Evolución administrativa o asistencial explícita. Módulo Residentes.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Dimensión distingue expediente, estancia y condición; no codificar diagnóstico como estado de cuenta**.

Vista/componente: resources/views/modules/residentes/resident_status_changes/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Residentes\\ResidentStatusChangeIndex / ResidentStatusChangeForm o panel integrado en ResidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Consent — consents

Otorgamiento y revocación verificable. Módulo Residentes.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Revocar agrega fila; booleanos heredados no prueban firma; identidad del representante verificable**.

Vista/componente: resources/views/modules/residentes/consents/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Residentes\\ConsentIndex / ConsentForm o panel integrado en ResidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### AdmissionCase — admission_cases

Solicitud previa, evaluación y decisión. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **U(request_key); no copiar datos personales vivos; documentación completa se calcula**.

Vista/componente: resources/views/modules/admisiones/admission_cases/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\AdmissionCaseIndex / AdmissionCaseForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### AdmissionDecision — admission_decisions

Decisiones de aprobación, rechazo y derivación. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Caso bloqueado al decidir; decisión clínica firmada y administrativa diferenciadas en decision**.

Vista/componente: resources/views/modules/admisiones/admission_decisions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\AdmissionDecisionIndex / AdmissionDecisionForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Admission — admissions

Estancia concreta, alta y reingreso. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **U(admission_case_id) no nulo; un ingreso abierto por residente; histórico importado puede carecer de caso**.

Vista/componente: resources/views/modules/admisiones/admissions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\AdmissionIndex / AdmissionForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Room — rooms

Espacio físico. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(institution_id,code); capacidad autorizada distinta de cantidad de camas**.

Vista/componente: resources/views/modules/admisiones/rooms/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\RoomIndex / RoomForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Bed — beds

Plaza física habilitada. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(room_id,code); ocupación derivada, mantenimiento no significa ocupada**.

Vista/componente: resources/views/modules/admisiones/beds/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\BedIndex / BedForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### BedAssignment — bed_assignments

Ocupación histórica por estancia. Módulo Admisiones y Ocupación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **Excluir intervalos solapados por cama y por estancia; habitación deriva de cama**.

Vista/componente: resources/views/modules/admisiones/bed_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Admisiones\\BedAssignmentIndex / BedAssignmentForm o panel integrado en CasosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### EncounterType — encounter_types

Catálogo de atención interdisciplinaria. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(name); conserva tipo_atenciones_adulto**.

Vista/componente: resources/views/modules/clinica/encounter_types/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\EncounterTypeIndex / EncounterTypeForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Encounter — encounters

Atención, consulta, interconsulta o sesión. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Una tabla de atenciones para todas las profesiones; no deduplicar solo por fecha**.

Vista/componente: resources/views/modules/clinica/encounters/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\EncounterIndex / EncounterForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ClinicalNote — clinical_notes

Nota clínica firmada y antecedentes narrativos. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Corrección agrega versión enlazada; borrador editable antes de firma**.

Vista/componente: resources/views/modules/clinica/clinical_notes/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\ClinicalNoteIndex / ClinicalNoteForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Diagnosis — diagnoses

Condición longitudinal con grado de certeza. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **No transformar un false heredado en ausencia clínica confirmada**.

Vista/componente: resources/views/modules/clinica/diagnoses/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\DiagnosisIndex / DiagnosisForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Allergy — allergies

Alergia o intolerancia documentada. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Distinguir desconocido, negación explícita y alergia; texto no equivale a catálogo validado**.

Vista/componente: resources/views/modules/clinica/allergies/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\AllergyIndex / AllergyForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### VitalSign — vital_signs

Medición con unidades y momento. Módulo Clínica.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **IMC derivado kg/m²; distinguir cero de no medido; no duplicar signos en nota JSON**.

Vista/componente: resources/views/modules/clinica/vital_signs/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Clinica\\VitalSignIndex / VitalSignForm o panel integrado en AtencionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Prescription — prescriptions

Orden firmada de tratamiento. Módulo Medicación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Prescriptor interno acreditado o externo identificado; suspension no borra administraciones**.

Vista/componente: resources/views/modules/medicacion/prescriptions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Medicacion\\PrescriptionIndex / PrescriptionForm o panel integrado en PrescripcionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### PrescriptionItem — prescription_items

Línea y pauta de un fármaco. Módulo Medicación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Pauta versionada; texto ambiguo requiere conciliación antes de generar dosis**.

Vista/componente: resources/views/modules/medicacion/prescription_items/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Medicacion\\PrescriptionItemIndex / PrescriptionItemForm o panel integrado en PrescripcionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### MedicationAdministration — medication_administrations

Dosis dada u omitida, independiente de la orden. Módulo Medicación.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **U(request_key); una versión vigente por administration_group; corrección no crea nueva dosis**.

Vista/componente: resources/views/modules/medicacion/medication_administrations/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Medicacion\\MedicationAdministrationIndex / MedicationAdministrationForm o panel integrado en PrescripcionesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### AssessmentDomain — assessment_domains

Dimensión geriátrica, no área laboral. Módulo Valoración Geriátrica Integral.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(name)**.

Vista/componente: resources/views/modules/vgi/assessment_domains/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Vgi\\AssessmentDomainIndex / AssessmentDomainForm o panel integrado en InstrumentosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### AssessmentInstrument — assessment_instruments

Identidad estable del instrumento. Módulo Valoración Geriátrica Integral.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(acronym); Barthel e ingreso de enfermería como instrumentos explícitos**.

Vista/componente: resources/views/modules/vgi/assessment_instruments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Vgi\\AssessmentInstrumentIndex / AssessmentInstrumentForm o panel integrado en InstrumentosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### InstrumentVersion — instrument_versions

Formulario y reglas inmutables publicadas. Módulo Valoración Geriátrica Integral.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear nueva versión desde anterior | Todas las versiones con estado | Versión exacta usada y evidencia | Solo borrador; publicada/validada inmutable | Retirar de nuevas operaciones sin alterar usos anteriores | Revocar versión con motivo sin borrar usos | Solo borrador nunca referenciado |

Regla específica: **U(assessment_instrument_id,version); no cambiar puntos de corte retrospectivamente**.

Vista/componente: resources/views/modules/vgi/instrument_versions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Vgi\\InstrumentVersionIndex / InstrumentVersionForm o panel integrado en InstrumentosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Assessment — assessments

Valoración funcional, cognitiva o de ingreso. Módulo Valoración Geriátrica Integral.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Solo un score y un estado de documento; riesgo no es estado; respuestas validadas según versión**.

Vista/componente: resources/views/modules/vgi/assessments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Vgi\\AssessmentIndex / AssessmentForm o panel integrado en InstrumentosIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### CareAssignment — care_assignments

Equipo responsable de residente por intervalo. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **Asignación asistencial independiente de cama y de tarea; varios profesionales compatibles**.

Vista/componente: resources/views/modules/cuidados/care_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\CareAssignmentIndex / CareAssignmentForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### CarePlan — care_plans

Raíz estable del plan interdisciplinario. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Plan general o funcional/nutricional sin tabla por profesión**.

Vista/componente: resources/views/modules/cuidados/care_plans/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\CarePlanIndex / CarePlanForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### CarePlanVersion — care_plan_versions

Objetivos e indicaciones de una versión. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear nueva versión desde anterior | Todas las versiones con estado | Versión exacta usada y evidencia | Solo borrador; publicada/validada inmutable | Retirar de nuevas operaciones sin alterar usos anteriores | Revocar versión con motivo sin borrar usos | Solo borrador nunca referenciado |

Regla específica: **U(care_plan_id,version); una versión vigente; JSON nutrición con esquema validado**.

Vista/componente: resources/views/modules/cuidados/care_plan_versions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\CarePlanVersionIndex / CarePlanVersionForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### CareTask — care_tasks

Tarea programada de una versión. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Residente deriva del plan; reprogramación auditada; tarea no equivale a ejecución**.

Vista/componente: resources/views/modules/cuidados/care_tasks/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\CareTaskIndex / CareTaskForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### CareTaskExecution — care_task_executions

Resultado de una ocurrencia de tarea. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **U(request_key); repetición genera ocurrencia distinta, nunca sobrescribe el resultado**.

Vista/componente: resources/views/modules/cuidados/care_task_executions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\CareTaskExecutionIndex / CareTaskExecutionForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### DailyObservation — daily_observations

Seguimiento por turno y observación eventual. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Esquema versionado para alimentación, hidratación, movilidad, higiene, sueño, orientación, conducta; no diagnostica**.

Vista/componente: resources/views/modules/cuidados/daily_observations/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\DailyObservationIndex / DailyObservationForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Handover — handovers

Pase de turno emitido y recibido. Módulo Cuidados.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Snapshot histórico de pendientes, no lista operativa editable; receptor distinto comprobado**.

Vista/componente: resources/views/modules/cuidados/handovers/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Cuidados\\HandoverIndex / HandoverForm o panel integrado en EquipoResidentePanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### PersonContact — person_contacts

Parentesco y responsabilidad contextual. Módulo Social y Familia.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **No autoriza solo por ser familiar; consentimiento vigente adicional; sin vínculos reflexivos**.

Vista/componente: resources/views/modules/social/person_contacts/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Social\\PersonContactIndex / PersonContactForm o panel integrado en RedApoyoPanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Visit — visits

Visita programada, ingreso y salida. Módulo Social y Familia.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Función hoy de vista base; privacidad de visitantes; no convertir voluntariado automáticamente**.

Vista/componente: resources/views/modules/social/visits/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Social\\VisitIndex / VisitForm o panel integrado en RedApoyoPanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### SocialFollowup — social_followups

Evaluación social y seguimiento de red. Módulo Social y Familia.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Función anunciada pero ficha social sin persistencia actual**.

Vista/componente: resources/views/modules/social/social_followups/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Social\\SocialFollowupIndex / SocialFollowupForm o panel integrado en RedApoyoPanel. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ActivityType — activity_types

Catálogo de actividades. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(name)**.

Vista/componente: resources/views/modules/actividades/activity_types/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\ActivityTypeIndex / ActivityTypeForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Activity — activities

Evento programado individual o colectivo. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **No fusionar eventos antiguos por coincidencia horaria sin evidencia**.

Vista/componente: resources/views/modules/actividades/activities/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\ActivityIndex / ActivityForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ActivityParticipation — activity_participations

Inscripción, asistencia y resultado por residente. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **U(activity_id,resident_id) vigente; planificar no confirma asistencia**.

Vista/componente: resources/views/modules/actividades/activity_participations/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\ActivityParticipationIndex / ActivityParticipationForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### VolunteerProfile — volunteer_profiles

Vinculación voluntaria sin duplicar identidad. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(person_id); profesión declarada no es acreditación**.

Vista/componente: resources/views/modules/actividades/volunteer_profiles/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\VolunteerProfileIndex / VolunteerProfileForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### VolunteerAvailability — volunteer_availabilities

Disponibilidad semanal temporal. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Abrir intervalo validando competencia y no solapamiento | Calendario e historial | Detalle, vigencias y responsable | Cerrar anterior y crear intervalo corregido; conservar fuente | Finalizar/reactivar mediante nuevo intervalo | Anular intervalo erróneo con motivo y referencias preservadas | Solo borrador sin efectos ni referencias |

Regla específica: **No solapamiento vigente; cambio crea nueva vigencia**.

Vista/componente: resources/views/modules/actividades/volunteer_availabilities/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\VolunteerAvailabilityIndex / VolunteerAvailabilityForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### VolunteerAssignment — volunteer_assignments

Colaboración programada con o sin evento. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Excepción de disponibilidad exige permiso y motivo**.

Vista/componente: resources/views/modules/actividades/volunteer_assignments/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\VolunteerAssignmentIndex / VolunteerAssignmentForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### VolunteerAttendance — volunteer_attendances

Asistencia real o ausencia justificada. Módulo Actividades y Voluntariado.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **La asignación es opcional para legado o asistencia extraordinaria; coincidencia no implica FK**.

Vista/componente: resources/views/modules/actividades/volunteer_attendances/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Actividades\\VolunteerAttendanceIndex / VolunteerAttendanceForm o panel integrado en TiposActividadIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Incident — incidents

Hecho de seguridad asistencial. Módulo Seguridad Asistencial.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Diferente de alerta de riesgo; no crear incidente solo desde booleano sin descripción**.

Vista/componente: resources/views/modules/seguridad_asistencial/incidents/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\SeguridadAsistencial\\IncidentIndex / IncidentForm o panel integrado en IncidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Alert — alerts

Riesgo que requiere atención y cierre. Módulo Seguridad Asistencial.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Único dedup_key mientras abierta; cierre exige acción y motivo**.

Vista/componente: resources/views/modules/seguridad_asistencial/alerts/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\SeguridadAsistencial\\AlertIndex / AlertForm o panel integrado en IncidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### AlertAction — alert_actions

Acción, atención, cierre o reapertura. Módulo Seguridad Asistencial.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **La bitácora de acciones determina atendido/cerrado; no duplicar campos en alerta**.

Vista/componente: resources/views/modules/seguridad_asistencial/alert_actions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\SeguridadAsistencial\\AlertActionIndex / AlertActionForm o panel integrado en IncidentesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### DocumentType — document_types

Catálogo documental reutilizable. Módulo Documentos.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(code); requisitos no son permisos; revisión de roles antiguos a perfiles nuevos**.

Vista/componente: resources/views/modules/documentos/document_types/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Documentos\\DocumentTypeIndex / DocumentTypeForm o panel integrado en TiposDocumentoIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### Document — documents

Identidad del documento y titular. Módulo Documentos.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Titular obligatorio: persona o caso, ambos solo si coinciden; no duplicar archivo al aprobar caso**.

Vista/componente: resources/views/modules/documentos/documents/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Documentos\\DocumentIndex / DocumentForm o panel integrado en TiposDocumentoIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### DocumentVersion — document_versions

Archivo privado e inmutable. Módulo Documentos.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear nueva versión desde anterior | Todas las versiones con estado | Versión exacta usada y evidencia | Solo borrador; publicada/validada inmutable | Retirar de nuevas operaciones sin alterar usos anteriores | Revocar versión con motivo sin borrar usos | Solo borrador nunca referenciado |

Regla específica: **U(document_id,version), U(storage_disk,storage_path); reemplaza no sobrescribe binario**.

Vista/componente: resources/views/modules/documentos/document_versions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Documentos\\DocumentVersionIndex / DocumentVersionForm o panel integrado en TiposDocumentoIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### DocumentEvent — document_events

Validación, observación, entrega o firma. Módulo Documentos.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Múltiples envíos y validaciones trazables; sin guardar contraseñas ni contenido del correo sensible**.

Vista/componente: resources/views/modules/documentos/document_events/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Documentos\\DocumentEventIndex / DocumentEventForm o panel integrado en TiposDocumentoIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ExpertRuleSet — expert_rule_sets

Familia de reglas de apoyo clínico. Módulo Sistema Experto.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar catálogo/perfil validado | Filtrar/paginar vigentes e inactivos | Detalle y usos | Corregir metadata con auditoría; nunca cambiar significado de catálogo usado | Sí, con motivo; bloquear nuevas referencias | Solo duplicado/registro inválido con motivo | Solo borrador sin referencias; datos usados se archivan |

Regla específica: **U(name); no diagnóstico autónomo**.

Vista/componente: resources/views/modules/experto/expert_rule_sets/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Experto\\ExpertRuleSetIndex / ExpertRuleSetForm o panel integrado en ReglasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ExpertRuleVersion — expert_rule_versions

Reglas reproducibles aprobadas. Módulo Sistema Experto.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Crear nueva versión desde anterior | Todas las versiones con estado | Versión exacta usada y evidencia | Solo borrador; publicada/validada inmutable | Retirar de nuevas operaciones sin alterar usos anteriores | Revocar versión con motivo sin borrar usos | Solo borrador nunca referenciado |

Regla específica: **U(expert_rule_set_id,version); despliegue requiere validación clínica**.

Vista/componente: resources/views/modules/experto/expert_rule_versions/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Experto\\ExpertRuleVersionIndex / ExpertRuleVersionForm o panel integrado en ReglasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ExpertRun — expert_runs

Ejecución con entradas y explicación congeladas. Módulo Sistema Experto.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **U(request_key); snapshot no fuente editable de clínica, referencias con tipo/id/versión/hash**.

Vista/componente: resources/views/modules/experto/expert_runs/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Experto\\ExpertRunIndex / ExpertRunForm o panel integrado en ReglasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ExpertReview — expert_reviews

Revisión humana del resultado. Módulo Sistema Experto.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Registrar hecho con autor, instante y request_key si aplica | Vigentes por defecto; anulados visibles en historial autorizado | Detalle, procedencia y cadena de corrección | Borrador editable; hecho confirmado se corrige con nueva fila supersedes_id | No aplica al hecho; el estado clínico se cambia con nueva observación | Sí con motivo, actor y fecha; nunca ocultar evidencia | Prohibida física para hechos; únicamente borradores vacíos sin efectos |

Regla específica: **Aceptar recomendación no registra prescripción automáticamente**.

Vista/componente: resources/views/modules/experto/expert_reviews/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Experto\\ExpertReviewIndex / ExpertReviewForm o panel integrado en ReglasIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### ReportExport — report_exports

Evidencia de exportación autorizada. Módulo Reportes.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Iniciar flujo válido y autorizado | Bandejas por estado y alcance | Detalle e historial de transiciones | Datos de borrador; transición explícita con auditoría y lock_version | Cerrar/reabrir solo transición autorizada | Cancelar/anular con motivo, conservar relaciones | Solo borrador sin efectos; en otro caso cancelar |

Regla específica: **Reautorizar descarga; no persistir agregados como verdad clínica**.

Vista/componente: resources/views/modules/reportes/report_exports/index.blade.php (lista o panel), show.blade.php (detalle) y form.blade.php donde el contrato permite borrador/alta. Componentes App\\Livewire\\Reportes\\ReportExportIndex / ReportExportForm o panel integrado en ReportesIndex. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

### MigrationLink — migration_links

Procedencia y conciliación de migración. Módulo Reportes.

| Crear | Listar | Ver | Editar/corregir | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| Solo importador idempotente | Conciliación restringida | Fuente/target/hash y problemas | Nueva ejecución de conciliación; no edición de negocio | No aplica | Marcar intento descartado, no borrar evidencia | Retención aprobada, jamás desde CRUD de producto |

Regla específica: **U(batch_key,source_table,source_key,target_table,target_key); acceso exclusivo migración/auditoría; no consultas de negocio**.

Vista/componente: Sin CRUD de producto; comando de conciliación, lectura restringida de incidencias en reporte técnico. Formularios hijo (versión, línea, contacto, tarea, acción) se montan en el detalle del padre, no requieren pantalla independiente ni tabla por pantalla.

## Tablas técnicas: operaciones admitidas

| Tabla | Crear | Listar / ver | Editar | Activar/desactivar | Anular | Eliminar |
|---|---|---|---|---|---|---|
| roles | Catálogo/asignación autorizada | RolesPermisosPanel con alcance | Delegación revisada y auditada | Revocar asignación sin alterar historial | Registrar revocación | Solo sin usos, o retirar pivote con auditoría |
| permissions | Catálogo/asignación autorizada | RolesPermisosPanel con alcance | Delegación revisada y auditada | Revocar asignación sin alterar historial | Registrar revocación | Solo sin usos, o retirar pivote con auditoría |
| model_has_roles | Catálogo/asignación autorizada | RolesPermisosPanel con alcance | Delegación revisada y auditada | Revocar asignación sin alterar historial | Registrar revocación | Solo sin usos, o retirar pivote con auditoría |
| model_has_permissions | Catálogo/asignación autorizada | RolesPermisosPanel con alcance | Delegación revisada y auditada | Revocar asignación sin alterar historial | Registrar revocación | Solo sin usos, o retirar pivote con auditoría |
| role_has_permissions | Catálogo/asignación autorizada | RolesPermisosPanel con alcance | Delegación revisada y auditada | Revocar asignación sin alterar historial | Registrar revocación | Solo sin usos, o retirar pivote con auditoría |
| activity_log | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| sessions | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| password_reset_tokens | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| personal_access_tokens | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| cache | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| cache_locks | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| jobs | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| job_batches | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| failed_jobs | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |
| migrations | Driver del framework | Solo herramienta técnica autorizada; secretos ocultos | Sin formulario de negocio | Invalidar sesión/token o detener job según contrato; no aplica a log/migration | No alterar eventos históricos | Retención técnica aprobada; migraciones y auditoría no desde UI |

## Flujos y transiciones de pantalla

| Agregado | Camino normal | Corrección/rechazo y restricciones |
|---|---|---|
| Caso | BORRADOR → ASIGNADO → EN_VALORACION → PENDIENTE_DECISION → APROBADO → INGRESADO | RECHAZADO / DERIVADO / CANCELADO con decisión; reabrir requiere motivo y revalorar; ingreso idempotente |
| Estancia | ABIERTA → CERRADA | Alta con fecha/motivo; reingreso nueva estancia; anular ingreso erróneo solo tras resolver dependencias |
| Ocupación | Intervalo abierto → cerrado por traslado/alta | Validar cama habilitada, misma institución, no solape, capacidad; traslado atómico |
| Prescripción | BORRADOR → FIRMADA → SUSPENDIDA / FINALIZADA | Cambiar dosis crea versión; cancelar borrador no elimina administraciones; actor acreditado |
| Administración | PROGRAMADA en agenda → ADMINISTRADA / OMITIDA como hecho | Agenda derivada de pauta no es hecho; omisión exige motivo; corrección conserva grupo de dosis |
| Valoración | BORRADOR → FINALIZADA → ANULADA si error | Score calculado por versión; revaluación es otro hecho; corrección enlazada |
| Plan | BORRADOR → VALIDADO/ACTIVO → CERRADO | Nueva versión validada sustituye futura ejecución; nunca cambia tarea ya hecha |
| Tarea | PENDIENTE → REALIZADA / OMITIDA / TRANSFERIDA / CANCELADA | Resultado es ejecución; transferencia conserva origen/destino y motivo |
| Pase | BORRADOR → EMITIDO → RECIBIDO | Emisor no se hace pasar por receptor; snapshot inmutable emitido; addendum si error |
| Alerta | ABIERTA → EN_ATENCION → CERRADA | Reapertura con acción; incidente relacionado no se elimina |
| Documento | REQUERIDO → CARGADO → VALIDADO u OBSERVADO → ARCHIVADO | Reemplazo nueva versión; firma/entrega son eventos distintos de validación |
| Actividad | PROGRAMADA → REALIZADA / CANCELADA | Participación individual conserva asistencia/resultado; no cancelar evento para corregir una asistencia |
| Voluntario | Perfil activo + disponibilidad → asignación → asistencia/ausencia | Ausencia justificada es hecho; disponibilidad excepcional con permiso y motivo |
| Experto | Versión borrador → aprobada; ejecución → resultado → revisión humana | Resultado rechazado permanece; aceptar no prescribe ni diagnostica |

Estados son constantes/enums del código; catálogo de equivalencias de valores heredados se valida en migración, sin tablas ad hoc de workflow. Concurrencia muestra mensaje de conflicto y recarga datos, no pisa cambios.

## Reutilización y experiencia

Un ResidenteSelector, PersonaForm, FechaHoraField, EstadoBadge, MotivoModal, HistorialPanel, PrivateDocumentLink y tablas paginadas Blade; estilos de los tokens CSS actuales, dark mode class. Formularios clínicos compartidos: SignosForm, EvaluacionForm por versión, PrescripcionForm, ResultadoTareaForm. Sin duplicar un formulario por médico/enfermero/psicólogo. Cada submit valida permisos en servidor, deshabilita doble envío, muestra errores por campo y conserva datos si falla. Las listas no descargan todo el expediente; exportar es acción autorizada aparte. Probar teclado/foco, tablas vacías, errores de carga y vista móvil en las pantallas de turno.

