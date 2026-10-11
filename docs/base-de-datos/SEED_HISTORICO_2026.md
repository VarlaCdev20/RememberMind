---
title: "Carga sintética histórica 2026"
status: CURRENT
source_of_truth: false
verification_scope: SYNTHETIC_DATASET_INTEGRATION
runtime_verified: true
last_reviewed: 2026-10-08
---

# Carga sintética histórica 2026

Datos exclusivamente de desarrollo. No representan personas, diagnósticos ni órdenes reales. No cambian la estructura V2.1 + V2.2, las reglas clínicas ni los permisos.

## Autorizaciones y alcance

La propietaria autorizó retirar el dataset anterior identificado como muestra, crear **30 residentes activos y 30 camas sintéticas**, permitir nacimientos anteriores a 2026 y cargar los catálogos de glucemia de laboratorio y Barthel de diez actividades. Los hechos operativos se limitan a 2026. Referencia fija: **2026-10-08**, corte clínico **10:00, America/La_Paz**. Los turnos y sus asignaciones representan la planificación del día; no acreditan cuidados futuros como realizados.

Los 30 ingresos se distribuyen entre enero y octubre y usan `FormalizarAdmision`: preadmisión pendiente, valoración, revisión, aprobación y admisión con cama, contacto, consentimiento, historial de estado y seguro. Hay otras cuatro solicitudes sin residente: dos pendientes, una aprobada sin formalizar y una rechazada. Un sueño nocturno comienza a documentarse el día posterior al ingreso.

Los expedientes incluyen controles diarios y seguimiento interdisciplinario. Los campos que no corresponden al caso permanecen nulos: no se inventan alergias, heridas, diagnósticos ni dispositivos para completar celdas. Las identidades y series clínicas son deterministas; las contraseñas se generan mediante aleatoriedad criptográfica y no se regeneran al repetir la carga.

## Casos de QA

### Dolor V2 — ampliación 09/10/2026

El mismo `Historical2026Seeder` incorpora una inicial sin reevaluar, episodio
8 → 5 → 3 y episodio 4 → 4; incluye frecuencia, factores de alivio y un caso
sin campos opcionales. Cada reevaluación referencia la inicial, con autor y
fecha propios. La respuesta solo aparece en reevaluaciones y tipo_dolor queda
NULL. No se añade un seeder paralelo ni reglas de severidad.

Estos casos se generan al cargar un dataset nuevo en una BDD autorizada. La
salida temprana idempotente conserva datasets ya cargados; no reescribe el
historial de desarrollo. Verificados en PostgreSQL aislado y con
`Historical2026SeedIntegrityTest` (3 pruebas, 35 aserciones).

La asignación de perfiles y fechas está en [historical2026.php](../../database/seeders/data/historical2026.php); los nombres visibles son nombres humanos normales. `storage/app/private/historical2026/manifest.json` relaciona los perfiles internos con los códigos del residente.

| Caso | Evidencia longitudinal |
|---|---|
| Estable | Signos variados dentro de su trayectoria, descanso y actividad cotidiana |
| Movilidad | Apoyo inicial, sesiones de fisioterapia, disminución de dolor y mejor autonomía |
| Deterioro | Mayor necesidad de apoyo a partir del día 84 |
| Nutrición | Menor ingesta, descenso gradual de peso y recuperación parcial tras seguimiento |
| Dolor | Tratamiento temporal de cinco días, suspensión documentada y controles posteriores |
| Herida | Tres residentes, lesión superficial, dolor posterior al incidente, ocho curaciones por caso y cierre |
| Sueño | Periodo de despertares/ansiedad, seguido de recuperación del descanso; sin afirmar causalidad |
| Cognición | Observación descriptiva, sin diagnóstico o ranking automático |
| Crítica histórica | Pulso 38 lpm evaluado con la regla vigente, atención médica, recontrol 70 lpm y cierre con eventos |
| Alerta abierta | Disminución reciente de ingesta, solicitud de revisión y cuidado pendiente al corte |
| Incidente | Caída, revisión médica y apoyo temporal de movilidad |
| Participación | Participación parcial, respetando preferencias |
| Visitas | Periodicidad semanal, mensual o visita ocasional según el caso |

## Catálogos investigados

Veinte presentaciones farmacéuticas verificadas en [LINAME 2026–2027, actualización AGEMED CR/29/2026](https://www.agemed.gob.bo/circulares/2026/Actualizacion%20LINAME%20PRECIOS%20al%207-09-2026.xlsx). Las fuentes y concentraciones figuran en [medicamentos_investigados.php](../../database/seeders/data/medicamentos_investigados.php). La vía se deduce de la presentación; concentración y dosis prescrita se guardan separadamente. No se inventan marcas comerciales. La identidad previa de losartán se conserva porque tiene órdenes de un expediente ajeno al dataset.

Las órdenes clínicas son ejemplos sintéticos asociados a su caso. La levotiroxina se separa del desayuno conforme a la [ficha oficial de DailyMed](https://dailymed.nlm.nih.gov/dailymed/drugInfo.cfm?setid=b717aff6-f754-458d-be87-d12d87c5da00). Los objetivos individuales de glucemia de los dos casos sintéticos tienen contexto en [ADA 2026, adultos mayores](https://diabetesjournals.org/care/article/49/Supplement_1/S277/163921/13-Older-Adults-Standards-of-Care-in-Diabetes-2026); no constituyen una regla universal del sistema.

Barthel almacena los nombres de las diez actividades y los valores del formulario vigente, sin reproducir texto de cuestionarios ajenos ni añadir bandas de clasificación. Cada aplicación conserva diez respuestas, suma y máximo de 100 puntos. Glucemia guarda resultado, informe y archivo privado, sin rango universal inventado.

## Ejecución optativa

Nunca se invoca desde `DatabaseSeeder` ni se permite en producción. Instalar antes los roles y permisos vigentes. Establecer `HISTORICAL2026_ALLOW_DATABASE` al **nombre exacto de la base de desarrollo autorizada**, y `SEED_REFERENCE_DATE=2026-10-08`.

```powershell
php artisan db:seed --class=Historical2026Seeder
php artisan test --filter=Historical2026SeedIntegrityTest
```

Repetir verifica la carga existente; no completa silenciosamente una carga parcial ni reemplaza expedientes. `migrate:fresh --seed` se ejecutó exclusivamente contra una nueva base PostgreSQL desechable. No debe ejecutarse para actualizar una base con expedientes ajenos.

Las credenciales nuevas se guardan en `storage/app/private/historical2026/credenciales.json`. Los documentos tienen archivo privado real y hash comprobado. No incluir credenciales, respaldos ni expedientes en Git.

## Limpieza y preservación

La sustitución local del 08/10/2026 retiró **1.526 filas operativas de la muestra anterior**, incluidos los 20 residentes repetidos como Marta Rojas y el residente temporal. Se respaldó PostgreSQL y se verificó la restauración antes de actuar. Limpieza y carga se confirmaron en una misma transacción, conservando cada fila ajena con comparación exacta antes/después.

Se conservaron **dos residentes no identificados como muestra** y los recursos compartidos que sostienen sus historias. Por eso la base de desarrollo tiene 32 residentes en total, 30 de ellos nuevos. Uno de los expedientes preservados ya carecía de admisión/ocupación; esa inconsistencia previa no se atribuye al nuevo dataset ni se corrige inventando una admisión.

## Evidencia y límites

- PostgreSQL: las 71 tablas operativas tienen filas; 30 admisiones nuevas, 30 camas nuevas, 30 nombres completos y nacimientos distintos, 5.161 signos y cero Marta Rojas.
- SQLite: `Historical2026SeedIntegrityTest` pasa tres casos: cobertura/roles/idempotencia, rechazo de producción y rollback ante fallo de archivo.
- La revisión independiente detectó inconsistencias de dolor y valores incompatibles con edición; se corrigieron únicamente los datos sintéticos afectados y se registró auditoría técnica.
- La suite completa ejecutada durante el trabajo reportó 779 aprobados, 15 omitidos y seis fallos. Uno pertenecía a una expectativa de signos cargada antes de añadir el recontrol histórico; su prueba actual pasa. Los otros cinco pertenecen a filtros, Mi Turno, hidratación, pase de turno y rutas/permisos de administración; quedan pendientes de su revisión propia.
- Frontend: 86/87 pruebas pasan; falla la comprobación previa de etiquetas del sidebar. Build ejecutado correctamente.

Esta evidencia acredita el dataset y los casos ejecutados, no una certificación médica, de concurrencia, seguridad integral ni de todas las pantallas. Sin commit, staging o push.
