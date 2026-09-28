# Decisión de Arquitectura (ADR) — Unidad Canónica de Talla en Centímetros

- **Identificador:** ADR-V2.1-002
- **Estado:** APROBADA / CONGELADA
- **Fecha de aprobación:** 2026-09-26
- **Ámbito:** BDD Operativa RememberMind V2.1 (70 tablas operativas)
- **Norma de gobernanza:** `RM-GOV-001` (Modificación de esquema mediante migración incremental aditiva, sin alteración de migraciones históricas)

---

## 1. Contexto y Problema

Durante la auditoría del dominio clínico y de base de datos de RememberMind V2.1, se detectó una inconsistencia histórica en la captura, almacenamiento y validación de la variable clínica `talla`:

1. **Inconsistencia de unidades:** Coexistencia de interfaces y placeholders orientados a metros (`1.65`, `Talla (m)`, `step="0.01"`) con validaciones y constantes orientadas a centímetros (`TALLA_CM_MIN = 50.0`, `TALLA_CM_MAX = 240.0`).
2. **Ambigüedad en base de datos:** La restricción `CHECK` histórica `ck_val_enf_talla` en PostgreSQL permitía `(talla IS NULL OR talla BETWEEN 0.5 AND 240)`, un rango híbrido que toleraba simultáneamente metros (`0.50` a `2.40`) y centímetros (`50` a `240`), imposibilitando garantizar la unidad física real del dato sin interpretación subjetiva.
3. **Cálculo de IMC:** Requería heurísticas de detección numérica para determinar si el valor introducido correspondía a metros o centímetros.

---

## 2. Decisión Formal Aprobada

Por decisión formal de la dirección técnica y funcional de RememberMind:

1. **Unidad Canónica Única:**
   - La unidad canónica de almacenamiento, validación, cálculo y presentación de `talla` en todo RememberMind es **CENTÍMETROS (cm)**.
   - Quedan eliminadas de forma estricta todas las interfaces, etiquetas, columnas de exportación o mensajes que presenten o soliciten `talla` en metros.

2. **Rango Operativo Canónico de Adulto Mayor:**
   - El rango operativo canónico en todas las capas del sistema es de **50.0 cm a 240.0 cm**.
   - Los valores inferiores a 50.0 cm o superiores a 240.0 cm son explícitamente rechazados por validación y por restricciones de base de datos.

3. **Cálculo de Índice de Masa Corporal (IMC):**
   - El cálculo de IMC se realiza convirtiendo internamente los centímetros a metros de manera exclusiva para aplicar la fórmula clínica:
     $$\text{IMC} = \frac{\text{peso (kg)}}{(\text{talla (cm)} / 100)^2}$$
   - No se aplican conversiones de presentación en ninguna otra vista.

4. **Política de Datos Ambiguos (Zero Fabrication):**
   - Conforme a la política de no conversión automática de datos ambiguos, cualquier migración en un entorno con datos preexistentes audita previamente las filas físicas.
   - Queda prohibida la multiplicación automática silenciosa (`$talla * 100`) para valores ambiguos; ante inconsistencias, el proceso se interrumpe para regularización humana auditada.

---

## 3. Implementación Técnica

1. **Migración Incremental Aditiva:**
   - Archivo: `database/migrations/2026_09_26_000100_set_talla_canonical_centimeters.php`.
   - Restricciones CHECK en PostgreSQL:
     * `valoraciones_enfermeria_preadmision.ck_val_enf_talla`: `(talla IS NULL OR (talla >= 50.0 AND talla <= 240.0))`.
     * `mediciones_antropometricas.ck_med_ant_talla`: `(talla IS NULL OR (talla >= 50.0 AND talla <= 240.0))`.
   - Emulación en SQLite mediante triggers de inserción y actualización para la suite de pruebas.
   - Reversibilidad garantizada mediante método `down()`.

2. **Capa de Negocio y Validación:**
   - `ValidacionSignosVitalesService`: Reglas `min:50.0|max:240.0`, mensajes descriptivos en cm, normalización a 1 decimal sin alteración heurística.
   - `StoreSignosVitalesRequest`: Regla `min:50.0|max:240.0`.
   - `ValoracionInicialModal`: Regla `min:50.0|max:240.0`.
   - `ExpedienteClinicoController`: Regla antropométrica `min:50|max:240`.
   - `SaludSignosPanel`: Cálculo visual alineado a `ValidacionSignosVitalesService::calcularImc`.

3. **Vistas e Interfaces:**
   - Modales y paneles unificados a etiqueta `Talla (cm)`, placeholders `165`, atributos `min="50" max="240" step="0.1"`.
   - La precisión de captura de interfaz es de 0.1 cm, coherente con la normalización actual del dominio a una cifra decimal.
   - Tab de signos y exportaciones Excel unificadas a unidad `cm`.

### Portabilidad de integridad

- **PostgreSQL:** Motor actualmente utilizado y auditado; implementa físicamente los CHECK canónicos de talla (`ck_val_enf_talla` y `ck_med_ant_talla`).
- **SQLite:** Utilizado en pruebas automatizadas; la integridad equivalente se emula mediante triggers de inserción y actualización (`BEFORE INSERT` / `BEFORE UPDATE`).
- **MySQL / MariaDB:** No forman parte del baseline soportado. La configuración genérica de Laravel no implica compatibilidad con la BDD Operativa V2.1.

---

## 4. Impacto en el Modelo y Conteo de Tablas

- **Número de tablas operativas:** Se mantiene estrictamente en **70 tablas operativas**.
- **Estructura de columnas:** No se añadieron ni eliminaron columnas; las columnas físicas existentes (`decimal(6,2)` en `valoraciones_enfermeria_preadmision` y `decimal(5,2)` en `mediciones_antropometricas`) soportan plenamente el rango `50.00` a `240.00` cm.
- **Migraciones históricas:** Permanecen inalteradas conforme a `RM-GOV-001`.

---

## 5. Verificación Automatizada

- Prueba específica de dominio: `tests/Feature/TallaCanonicaTest.php` (5 pruebas, 16 aserciones, 0 fallos).
- Pruebas arquitectónicas y de integridad: `BddOperativaV2Test.php`, `IntegracionAdaptadoresV2Test.php`, `ValoracionEnfermeriaAutoriaTest.php`.
