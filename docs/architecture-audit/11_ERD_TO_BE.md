# ERD lógico TO-BE

Los diagramas separan grupos para legibilidad. El catálogo completo de 51 tablas funcionales y restricciones está en 10; los diagramas no omiten la necesidad de FK explícitas ni implican aprobación del DDL. id es PK salvo pivotes. Un enlace opcional se representa con o|/o{.

```mermaid
erDiagram
  persons ||--o{ person_identifiers : identifica
  persons ||--o| users : acceso
  persons ||--o| professionals : ejerce
  professions ||--o{ professionals : clasifica
  professions ||--o{ specialties : especializa
  professionals ||--o{ professional_specialties : posee
  specialties ||--o{ professional_specialties : habilita
  persons ||--o{ staff_assignments : trabaja
  positions ||--o{ staff_assignments : cargo
  institutions ||--o{ areas : organiza
  areas ||--o{ staff_assignments : ubica
  institutions ||--o{ rooms : contiene
  rooms ||--o{ beds : contiene
  institutions ||--o{ shifts : define
  persons ||--o{ staff_schedules : agenda
  shifts o|--o{ staff_schedules : concreta
  persons ||--o| residents : residente
  residents ||--o{ resident_contacts : vincula
  persons ||--o{ resident_contacts : contacto
  residents ||--o{ consents : autoriza
  resident_contacts o|--o{ consents : otorgante
  persons ||--o{ admission_cases : solicita
  admission_cases o|--o{ admissions : origina
  residents ||--o{ admissions : reingresa
  admissions ||--o{ bed_assignments : ocupa
  beds ||--o{ bed_assignments : asigna
  admissions ||--o{ resident_status_changes : transiciona
```

```mermaid
erDiagram
  residents ||--o{ clinical_notes : historia
  residents ||--o{ diagnoses : diagnosticos
  residents ||--o{ allergies : alergias
  residents ||--o{ vital_signs : medidas
  residents ||--o{ prescriptions : ordenes
  prescriptions ||--|{ prescription_items : contiene
  prescription_items ||--o{ medication_administrations : ejecuta
  assessment_instruments ||--|{ instrument_versions : versiona
  instrument_versions ||--o{ assessments : define
  residents ||--o{ assessments : evaluado
  residents ||--o{ care_plans : cuidado
  care_plans o|--o{ care_tasks : propone
  care_tasks ||--o{ care_task_executions : cumple
  residents ||--o{ daily_observations : observado
  residents ||--o{ handovers : comunica
  residents ||--o{ therapy_sessions : rehabilita
  care_plans o|--o{ therapy_sessions : orienta
  residents ||--o{ nutrition_plans : alimenta
  care_plans o|--o{ nutrition_plans : orienta
```

```mermaid
erDiagram
  institutions ||--o{ activities : ofrece
  activities ||--o{ activity_participations : participa
  persons ||--o{ activity_participations : asiste
  persons ||--o{ volunteer_assignments : voluntario
  residents o|--o{ volunteer_assignments : recibe
  areas o|--o{ volunteer_assignments : destino
  residents ||--o{ visits : recibe
  persons ||--o{ visits : visita
  residents ||--o{ social_followups : seguimiento
  professionals ||--o{ social_followups : realiza
  residents ||--o{ incidents : hechos
  residents ||--o{ alerts : alertas
  alerts ||--o{ alert_actions : resuelve
  residents ||--o{ expert_runs : infiere
  expert_runs ||--o{ expert_reviews : revisa
  persons o|--o{ documents : propietario_alternativo
  residents o|--o{ documents : propietario_alternativo
  admission_cases o|--o{ documents : propietario_alternativo
  documents ||--|{ document_versions : versiones
```

Las FK de autoría/revisión apuntan a users (salvo professional_id explícito) y se omiten gráficamente para evitar cruces. documents tiene exactamente uno de sus tres propietarios, no tres simultáneos. Los profesionales de sesiones también se enlazan a professionals. Una prescripción borrador puede temporalmente tener cero ítems; la cardinalidad mínima de uno aplica al firmar y se valida transaccionalmente. Los índices temporales no se deducen del dibujo: véase 10.

No crear tablas expediente, dashboard ni timeline para copiar todas las relaciones. Estas son composiciones de lectura. No duplicar resident_id en medication_administrations: se obtiene de prescription_items → prescriptions.
