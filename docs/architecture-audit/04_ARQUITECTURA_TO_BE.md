# Arquitectura objetivo propuesta

## Decisión central

Un monolito modular Laravel, MVC para la web y capas lógicas. Eloquent permanece como modelo y persistencia pragmáticos: no se duplicará cada entidad en una clase de dominio pura y otra ORM. Domain depende parcialmente de Laravel por decisión explícita; no se vende como Clean Architecture estricta.

```text
app/
  Modules/
    Identity/
    Institution/
    Residents/
    Admissions/
    Clinical/
    Medication/
    Assessment/
    Care/
    Social/
    Safety/
    ExpertSystem/
    Reports/
  Providers/                 registro explícito de Policies y adaptadores
  Http/Middleware/           sesión, estado de cuenta, contexto transversal
  Support/Documents/         archivos privados y renderizadores compartidos
routes/
  web.php                    raíz, auth y carga de grupos
  administration.php         Identity + Institution
  residents.php              Residents + Admissions + Social
  clinical.php               Clinical + Medication + Assessment
  care.php                   Care + Safety
  expert.php                 ExpertSystem; Reports junto a recurso autorizado
tests/
  Unit/
  Feature/
  Integration/
```

Ejemplo de módulo realmente usado, no plantilla obligatoria para todos:

```text
Admissions/
  Models/Admission.php
  Models/AdmissionCase.php
  Models/BedAssignment.php
  Actions/AdmitResident.php
  Actions/AssignBed.php
  DTOs/AdmissionData.php
  Queries/AdmissionQueue.php
  Http/Controllers/AdmissionController.php
  Http/Requests/StoreAdmissionRequest.php
  Policies/AdmissionPolicy.php
  Enums/AdmissionStatus.php
```

Agregar Resources, Services, Jobs, Contracts o ValueObjects solo cuando exista una responsabilidad concreta. No crear un ServiceProvider, repositorio, interfaz y factory para cada clase. Mantener las migraciones en database/migrations con orden global y tests agrupados por módulo.

## Reglas de dependencia

Presentation invoca Actions/Queries. Actions verifican autoridad cuando pueden llamarse desde múltiples entradas, coordinan transacciones e invocan modelos/reglas. Models no conocen Controllers, Livewire, Inertia, Mail o el cliente Python. Una Action puede leer relaciones de otro módulo, pero sus escrituras pasan por el caso de uso propietario; evitar cadenas circulares de Actions que abran transacciones independientes.

Residents no debe importar ExpertSystem. ExpertSystem lee snapshots autorizados desde Queries; Reports consume datos y no actualiza clínica. Admission conoce camas de Institution y residente de Residents; la raíz transaccional es AdmitResident. Care consume prescripciones para mostrar tareas, nunca modifica sus términos. El puerto experto se registra en un Provider y su fake funciona en pruebas sin red.

## Abstracciones evaluadas

| Patrón | Decisión | Justificación |
|---|---|---|
| Repository | No generalizado | Eloquent/Queries ya cubren persistencia; solo si aparece un origen alternativo real |
| CQRS | No infraestructura CQRS | Separar consultas y escrituras en clases basta; una BD, sin bus ni modelos duplicados obligatorios |
| Event Sourcing | No | Historial clínico versionado y bitácora resuelven la necesidad sin reconstruir toda la app desde eventos |
| Domain Events | Opcionales | Para varios efectos independientes posteriores al commit; no sustituir la transacción crítica |
| Value Objects | Selectivos | Dosis/unidad o periodo cuando evitan estados inválidos; no envolver cada string |
| Interfaces | Selectivas | ExpertEngineInterface, un proveedor externo intercambiable; no interfaz por Action |
| Factories | Sí para tests | Datos sintéticos expresivos; no una capa de factories de negocio universal |
| Command Bus | No | Inyección directa y execute legible |
| Microservicios | No | Costos de despliegue, contratos y consistencia injustificados para una desarrolladora |

## Evolución, operación y escalabilidad

Aplicación web + worker + PostgreSQL + almacenamiento privado. Redis solo cuando las mediciones justifiquen cola/caché compartida; no es condición inicial. Transacciones cortas, consultas paginadas, índices según acceso, reportes pesados en Jobs. El motor Python futuro es integración especializada, no excusa para descomponer el resto del sistema.
