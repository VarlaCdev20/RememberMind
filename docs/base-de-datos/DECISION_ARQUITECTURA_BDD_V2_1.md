# Decisión de Arquitectura — BDD Operativa V2.1

- **Fecha de aprobación:** 25/09/2026
- **Estado:** APROBADA / CONGELADA
- **Versión resultante:** BDD Operativa V2.1 (70 tablas operativas)
- **Entidad central:** `residentes`

---

## 1. Contexto

Durante la evolución de la arquitectura de datos de RememberMind desde la versión base V2.0 (69 tablas operativas), se identificó la necesidad clínica y asistencial de registrar la valoración multidimensional de enfermería en la fase de preadmisión de adultos mayores postulantes.

Inicialmente se evaluó una estructura provisional embebida mediante una columna `json` (`valoracion_enfermeria`) en la tabla `preadmisiones`. Tras la auditoría clínica y estructural del modelo, se determinó sustituir dicho enfoque no tipado por una entidad relacional transaccional normalizada sujeta a claves foráneas y restricciones de integridad del motor PostgreSQL.

Posteriormente, en la etapa final de endurecimiento previa al congelamiento formal, se detectó la necesidad de desacoplar rigurosamente la identidad profesional de la cuenta informática, culminando en la separación definitiva de autoría: autor clínico (`cod_personal_valorador`) y actor técnico (`cod_usuario_registro`).

---

## 2. Cambio Estructural Aprobado

1. **Incorporación formal de la tabla número 70:**
   - Nombre: `valoraciones_enfermeria_preadmision`.
   - Clasificación: Transaccional clínica de preadmisión.
   - Clave Primaria: `cod_valoracion_enfermeria` (`string(20)`).
   - Relación con Preadmisiones: Clave foránea única `cod_preadmision` hacia `preadmisiones.cod_preadmision` con cláusula `RESTRICT` en eliminación física, estableciendo una relación formal `1 a 0..1`.

2. **Modelo de Autoría Clínica y Técnica Dual:**
   - `cod_personal_valorador` (`string(20) NOT NULL`): Clave foránea hacia `personal.cod_personal` con `RESTRICT`. Representa al personal institucional autorizado para realizar la valoración de enfermería y responsable del acto asistencial.
   - `cod_usuario_registro` (`string(20) NOT NULL`): Clave foránea hacia `usuarios.cod_usuario` con `RESTRICT`. Representa la cuenta autenticada que ejecutó técnicamente la transacción en el sistema informático.

3. **Restricciones de Integridad del Motor (PostgreSQL):**
   - Incorporación de restricciones `CHECK` fisiológicas en `valoraciones_enfermeria_preadmision`:
     - Escala de dolor (0 a 10), exigiendo intensidad entre 1 y 10 si se declara presencia de dolor (`ck_val_enf_dolor`).
     - Presión arterial sistólica y diastólica (1 a 400 mmHg), exigiendo `pa_sistolica > pa_diastolica` (`ck_val_enf_pa`).
     - Frecuencia cardíaca (1 a 300 lpm, `ck_val_enf_fc`).
     - Frecuencia respiratoria (1 a 100 rpm, `ck_val_enf_fr`).
     - Temperatura corporal (25.00 a 45.00 °C, `ck_val_enf_temperatura`).
     - Saturación de oxígeno (0 a 100%, `ck_val_enf_saturacion`).
     - Peso (20.00 a 300.00 kg, `ck_val_enf_peso`).
     - Talla (50.00 a 240.00 cm, `ck_val_enf_talla`). Unidad canónica formalizada: CENTÍMETROS [cm].

4. **Gobierno y Permisos:**
   - Catálogo canónico exclusivo de permisos: `valoracion_enfermeria.ver`, `valoracion_enfermeria.registrar`, `valoracion_enfermeria.editar`.
   - Eliminación formal de permisos redundantes o no aprobados (`.crear`, `.anular`).
   - Política estricta: `delete()` y `forceDelete()` retornan siempre `false` (prohibición de borrado físico de registros clínicos).
   - SUPERADMINISTRADOR conserva lectura global (`valoracion_enfermeria.ver`) sin recibir privilegios automáticos de escritura clínica.
   - ADMINISTRADOR no posee privilegios de escritura clínica.
   - Prohibición expresa de consultas fallback o fabricación automática de personal/usuarios.

---

## 3. Motivación y Principios de Diseño

- **Separación de dominios:** Desacopla la información clínica y funcional de enfermería respecto a la información administrativa de solicitud/revisión en `preadmisiones`.
- **Estructura y tipado riguroso:** Garantiza tipos atómicos, decimales exactos y restricciones de rango validadas por el motor de base de datos.
- **Trazabilidad profesional inmutable:** Separa quién realizó el acto de salud (personal) de la cuenta de acceso (usuario), posibilitando auditoría médico-legal fidedigna.
- **Normalización relacional:** Cumple la directriz del proyecto de no recurrir a JSON/EAV para sustituir tablas o columnas clínicas estructuradas.
- **Integridad referencial estricta:** Todas las relaciones foráneas hacia preadmisiones, personal y usuarios utilizan `ON DELETE RESTRICT`.

---

## 4. Impacto en el Esquema y Conteo de Tablas

- **Inventario previo (V2.0):** 69 tablas operativas.
- **Inventario vigente (V2.1):** 70 tablas operativas exactas.

### Nueva Clasificación Oficial:
- **Tablas maestras:** 11
- **Tablas transaccionales:** 46 (incluye `valoraciones_enfermeria_preadmision` en posición lógica previa a la admisión formal)
- **Tablas intermedias:** 7
- **Tablas auxiliares:** 6
- **Total operativo:** 11 + 46 + 7 + 6 = **70 tablas operativas**.

---

## 5. Declaración de No Afectación a Reglas Centrales

Se ratifica formalmente que:
1. **No se modificó la entidad central del sistema:** La tabla maestra principal sigue siendo estrictamente `residentes`.
2. **No se modificó el flujo institucional de ingreso:**
   $$\text{Preadmisión PENDIENTE} \longrightarrow \text{revisión} \longrightarrow \text{APROBADA / RECHAZADA} \longrightarrow \text{admisión formal con cama} \longrightarrow \text{residente ADMITIDO}$$
   La valoración clínica de preadmisión se produce exclusivamente mientras la preadmisión está en estado `PENDIENTE`, antes de que se tome la decisión institucional de admisión. La aprobación de la preadmisión o el registro de su valoración jamás crean un residente ni sustituyen el acto de admisión formal.

---

## 6. Dictamen

La arquitectura descrita queda **APROBADA Y CONGELADA** como parte del **Baseline Oficial de Base de Datos Operativa V2.1**.
Cualquier alteración futura a las 70 tablas, sus claves, restricciones o tipos exigirá el cumplimiento del procedimiento de gobernanza y control de cambios establecido.
