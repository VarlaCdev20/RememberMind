# Capas, casos de uso y convenciones

| Capa lógica | Clases | Responsabilidad | Prohibición práctica |
|---|---|---|---|
| Presentation | Controllers, Requests, Resources, Livewire temporal, Vue | HTTP, validación de formato, autorización de entrada, respuesta y estado visual | Decidir tratamientos, asignar cama con SQL desde Vue, generar documentos dentro del formulario |
| Application | Actions, DTOs, Queries | Casos de uso, límites transaccionales, carga autorizada y coordinación | HTML, dependencia de listeners del navegador |
| Domain pragmático | Models, Enums, reglas/servicios puntuales | Relaciones, estados válidos, cálculos y transiciones | HTTP, sesiones implícitas en cálculos puros, Python/AHP en Controller |
| Infrastructure | Eloquent, Storage, adaptadores, Jobs de integración | Guardar, enviar, renderizar y conectar | Definir criterios clínicos como efecto oculto de una integración |

Eloquent participa en dominio y persistencia. La separación es de responsabilidades, no cuatro árboles físicos idénticos. DTO solo si un proceso tiene datos complejos, varias entradas o contrato estable; un CRUD de área simple puede usar validated() explícito.

## Casos de uso y atomicidad

| Action | Dentro de transacción PostgreSQL | Fuera/después del commit | Control de carrera |
|---|---|---|---|
| AdmitResident | Caso aprobado, residente, estancia, evento de estado y asignación opcional | PDF, notificaciones | Bloquear caso, clave de idempotencia, unicidad de estancia/cama |
| AssignBed | Cerrar asignación anterior y abrir nueva | Refrescar dashboard | Bloquear estancia y cama; índice único parcial |
| RegisterDiagnosis | Diagnóstico/versionado, autor y procedencia | Aviso si corresponde | Versión esperada |
| CreatePrescription | Cabecera y todos sus ítems, validación final | Aviso a turno | Idempotencia, estado de prescriptor |
| AdministerMedication | Releer orden vigente y dosis, registrar ejecución/omisión | Actualizar resumen/alerta no crítica | Bloquear orden/dosis y UNIQUE clave de ejecución |
| PerformAssessment | Respuestas, puntaje, versión, finalización | Tendencia y alerta derivada | Versión esperada y cálculo servidor |
| CreateCarePlan | Plan y tareas coherentes | Avisos de asignación | Una versión activa por plan lógico |
| RegisterFall | Incidente y acción inmediata/alerta requerida | Notificar equipo | Clave de solicitud |
| RunExpertInference | Crear ejecución y snapshot pequeño coherente | HTTP en Job | Clave única snapshot+motor+solicitud |
| ValidateExpertInference | Revisión, autor, decisión y motivo | Crear propuesta de seguimiento | Verificar versión/estado y política |

Una transacción no revierte correo ni archivos. Guardar estado documental PENDING y ejecutar generación idempotente después del commit; permitir reintento visible. Si una alerta es una invariante del registro se crea en la misma transacción. Si es una proyección derivada puede ir a Job recuperable. Evitar mantener locks mientras se llama Python/PDF.

## Convenciones propuestas

Tablas nuevas: inglés plural snake_case; columnas id, person_id, resident_id, recorded_at, occurred_at. No renombrar todas las legacy por estética: mapear y retirar por corte. ID interno bigint identity; código público legacy_code UNIQUE mientras existan referencias externas. Una clave pública opaca no sustituye una Policy. Fechas de hechos con zona normalizada a UTC y presentación America/La_Paz; fechas civiles como nacimiento permanecen date.

Controllers singulares AdmissionController; Actions verbo+objeto AdmitResident; Queries ResidentTimeline; DTO AdmissionData; enum AdmissionStatus con valores estables snake_case. Permisos recurso.acción en inglés, por ejemplo residents.view, prescriptions.create, prescriptions.validate, medication_administrations.create; se migran mediante una tabla documental de equivalencias, nunca concediendo por similitud textual.

Rutas plural kebab-case /residents/{resident}/assessments, nombres residents.assessments.store. Vue Pages PascalCase con Index/Show/Create/Edit; componentes de dominio ResidentHeader/ClinicalTimeline. UI traducida al español, sin exponer nombres de tablas al usuario. Estados borrador, finalizado y anulado separados de riesgo bajo/alto y de estado de cuenta. Un comentario de corrección requiere motivo; no usar delete como sinónimo universal de anular.

Mover User exige revisar auth.providers, factories, relaciones polimórficas Spatie/activity_log y morph map. Conservar App\Models\User temporalmente si evita romper autenticación; un namespace nuevo no justifica una migración peligrosa.
