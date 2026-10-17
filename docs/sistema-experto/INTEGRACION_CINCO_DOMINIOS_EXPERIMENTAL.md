# O.R.I.O.N. — Perfil experimental de cinco dominios y compuerta Petersen

Fecha: 2026-10-17. **Estado:** verificación técnica sintética; no habilitado para uso asistencial ni validado clínicamente.

## Autoridad documental

Se mantienen las decisiones del [Documento Maestro](https://docs.google.com/document/d/1d68S-EZTyYVKsYm-cOtaktYSNUBfDAi59FHexkEd88I/edit), el [contrato experimental G09-EXP](https://docs.google.com/document/d/1b4Vf4MrkodLJIUQF_PtQ_7frOe8F4-nnasedktlaMfw/edit), el [Documento 03 Petersen](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit) y la [Matriz 03A](https://docs.google.com/spreadsheets/d/1UwW3TsUZsnw6fEEXLVbyAB4_UddEWgjAeoZ_3By5x00/edit).

## Lo que hace este incremento

`OrquestadorCincoDominiosExperimental` construye un perfil sintético con la **misma identidad ficticia, identificador de evaluación y fecha de corte**:

- `COG-MEM`: ejecuta el motor real `MotorExperto::evaluarCOGMEM` con `EjemploTecnicoCOGMEM::paquete()`, `MemoriaTrabajo` y `AdaptadorHechosCOGMEM`, en versión **`VER_TEST`**.
- `COG-ATE`, `COG-EJE`, `COG-LEN`, `COG-VIS`: ejecuta el perfil G08/G09 y su cotejo con el núcleo D-130 usando **`G09-EXP-20261017`**, con SQLite **solo en memoria**.
- `Petersen`: ejecuta `ClasificadorPetersenExperimental` con versión **`DCL-PET-20261017-v0.1`**, asignando únicamente `U` a criterios C01–C05 y a los cinco dominios cuando las fuentes son estos prototipos sin interpretación clínica. Debe devolver `ABSTENCION_INFORMACION_INSUFICIENTE`, sin patrón `PET`.

El conjunto conserva un manifiesto `versiones` por motor y `version_orquestacion=INTEGRA5-EXP-01`; **no** simula una versión clínica única ni copia reglas D-131 a otros dominios.

## Qué NO hace

- No deduce `A` de `DIFICULTAD_EVIDENCIADA` de la demostración técnica de Memoria.
- No deduce `P` de `SIN_DIFICULTAD_EVIDENCIADA` o de `EVALUABLE_SIN_SOPORTE` de G09.
- No acredita C01 (cambio), C02 (alteración objetiva), C03 (autonomía/IADL), C04 (diferencial) o C05 (evaluabilidad) por el mero hecho de generar resultados de laboratorio.
- No consulta ni guarda datos de residentes reales, no añade migraciones ni expone salidas a la UI asistencial.
- No implica que los cinco dominios hayan sido interpretados con normas G08 ni que el sistema diagnostique DCL.

## Pruebas

```bash
php motor-experto/tests/orion_cinco_dominios_experimental.php
```

Prueba escenarios `literal_A`, `literal_B` y `SIN_DATO` de Memoria, más las quince tareas instrumentales **ficticias** de G09. Verifica cinco claves de dominio, identidad y corte común, versiones independientes, conservación de los estados `U` en Petersen, ausencia de patrón, ausencia de residentes reales y rechazo de invocación asistencial.

El flujo `.github/workflows/orion-g09-experimental.yml` ejecuta este test junto con las suites previas de G09 y los 30 casos más 32 configuraciones de DCL.

## Siguiente contrato técnico a preparar

1. Definir, como **especificación por fuente y subcomponente**, la conversión de una evaluación instrumental **realmente interpretable** a proposiciones `A`, `P`, `U`, `I` y `X`, sin inferir `P` del simple fallo de una regla positiva.
2. Documentar procedencia y verificación independiente de C01–C05 (en particular IADL, cambio longitudinal y evaluación diferencial).
3. Crear casos mixtos y de abstención que cubran todos los límites instrumentales G08 y reglas de admisibilidad G07/G10.
4. Solo cuando estos contratos y sus datos de prueba sean verificables, alimentar a Petersen con evidencias sintéticas tipadas que simulen un perfil clínico formalizado. La validación profesional y la autorización asistencial seguirán siendo fases independientes.

**No interpretar las pruebas sintéticas como hallazgos clínicos ni reportar que la clasificación Petersen está operativa con residentes.**
