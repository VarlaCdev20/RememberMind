# Diez escenarios cognitivos en el censo actual — 19/11/2026

La propietaria corrigió el alcance a **diez escenarios en los residentes
actuales**. Se mantiene el censo de treinta residentes, las camas existentes,
los permisos, el esquema y las reglas aprobadas. Esta carga no restituye
diagnósticos desaparecidos ni acredita que hubiera diez diagnósticos en el
corte anterior: la consulta inicial encontró cero registros de diagnóstico.

## Recorrido

`EscenariosDclResidentesSeeder` añade datos ficticios reconocibles por el lote
`DCL10-20261119-V1`, sobre diez documentos exactos del censo sintético. Conserva
las fuentes anteriores. No crea cuentas, residentes, camas, jornadas, recetas,
diagnósticos ni revisiones profesionales.

Cada escenario incorpora cinco capturas con respuestas originales tipadas,
dos controles de cambio cognitivo, un registro de funcionamiento instrumental,
dos soportes psicológicos y nueve juicios originales. C02 se deriva; no se
introduce un juicio original C02. Se emplean diez atenciones ficticias por
escenario para conservar los contextos originales y su procedencia.

Los juicios pasan por `RegistrarValoracionPetersenOriginal`; la evaluación
pasa por `GuardarResultadoInstitucional`. Este último vuelve a adquirir SQL,
ejecuta el conocimiento vigente y conserva resultados, hechos, reglas,
condiciones, criterios y explicación. El perfil esperado se usa solamente para
comparar la salida; nunca se inserta como resultado ni como hecho del residente.

Se utiliza el contrato numérico ya existente de HVLT-R, BTA, TMT-B, BNT y ROCF
con valores totalmente ficticios. Los cinco instrumentos docentes nuevos solo
contienen campos de puntuación; no reproducen reactivos, estímulos o materiales
protegidos. No se reactivan los antiguos instrumentos `COH30-*`/`TEST-*` ni se
retiran las exclusiones del flujo operativo. Los perfiles docentes se configuran
solo durante la carga y se restauran al terminar, incluso ante un fallo.
La lectura histórica del resultado guardado conserva sus fuentes originales y
funciona sin ese perfil temporal. La entrada normal abre automáticamente el
último resultado guardado del residente, sin ejecutar otra evaluación. La
consulta actual no debe confundirse con el resultado histórico seleccionado.

## Casos y oráculo técnico

| Documento del censo | Residente | Perfil esperado |
|---|---|---|
| 99120002 | Jorge Salinas Quispe | PET-01: amnésico de dominio único |
| 99120003 | Teresa Aguilar Rojas | PET-02: amnésico multidominio |
| 99120001 | Elena Vargas Mamani | PET-03: no amnésico de dominio único |
| 99120004 | Luis Fernández Paredes | PET-04: no amnésico multidominio |
| 99120005 | Mercedes Choque Rivera | PET-01 |
| 99120006 | Raúl Cabrera Flores | PET-02 |
| 99120007 | Carmen López Arce | PET-03 |
| 99120008 | Julio Gutiérrez Vega | PET-04 |
| 99120009 | Gloria Sánchez Condori | PET-01 |
| 99120010 | Hugo Romero Torrico | PET-02 |

Los nombres de esta tabla identifican expedientes ficticios del censo local,
no personas evaluadas. La salida es **posible DCL orientativo con soporte del
escenario**; no confirma un diagnóstico clínico ni demuestra validez en personas.
No se calcula una tasa de prevalencia a partir de esta distribución de ensayo.

## Instalación y reintento

El seeder es optativo y no se añade a `DatabaseSeeder`. El instalador requiere
`local`, PostgreSQL de bucle local y el nombre exacto `remembermind_dev`:

```powershell
php scripts/cargar-escenarios-dcl.php --aplicar --base=remembermind_dev --usuario=COD_USUARIO --area=COD_AREA
```

El usuario y el área deben ser explícitos: cuenta activa de Psicología, personal
activo y área de Psicología. También deben existir las versiones adoptadas y
la conservación institucional. El seeder no concede permisos ni activa reglas.
Las pruebas solo admiten SQLite en memoria o el clúster PostgreSQL temporal
controlado por el proyecto.

Las fuentes de cada caso se confirman juntas. La inferencia utiliza su propia
transacción, como exige el contrato. Si falla, las fuentes completas permanecen
disponibles para reintentar; no se anuncia un resultado inexistente. Un UUID
determinista por caso permite recuperar el resultado conservado sin duplicar
fuentes ni ejecuciones. Un lote parcial o alterado se rechaza.

El instalador compara huellas de las filas físicas anteriores, verifica que
no desaparezcan ni cambien, permite avanzar los contadores técnicos y conserva
el inventario de tablas. El informe detallado se escribe en almacenamiento
privado local, fuera de Git. Un respaldo previo se conserva también fuera de Git.

## Verificación

La prueba `EscenariosDclResidentesSeederTest` cubre los cuatro patrones, la
persistencia de las diez evaluaciones, reintento sin duplicados, preservación
de historia y esquema, exclusión de expedientes sin marca sintética, destino
incorrecto, denegación a Enfermería, fallo y reversión de la captura de un caso,
restauración de configuración y lectura histórica desde la ficha de Enfermería.
La evidencia final es:

- SQLite: **3 pruebas, 177 aserciones**, sin errores ni omisiones.
- PostgreSQL temporal: **3 pruebas, 177 aserciones**, sin errores ni omisiones.
- Regresión de abstención y valores originales: **2 pruebas, 17 aserciones**
  en SQLite. No se forzó un perfil cuando falta información.
- Sintaxis PHP y formato de los tres archivos: correctos.
- Instalación local: diez evaluaciones nuevas; se conservaron **13 376 filas
  anteriores** mediante sus huellas. El reintento conservó las **19 429 filas**
  del nuevo corte y no añadió ninguna fila.
- Inventario posterior: **30 residentes, 30 camas, 51 cuentas y 100 tablas**.
  No se modificaron reglas ni asignaciones de personal o residentes.

| Tabla física | Filas nuevas |
|---|---:|
| aplicaciones_instrumento | 50 |
| respuestas_instrumento | 480 |
| instrumentos / preguntas_instrumento | 5 / 48 |
| atenciones | 100 |
| controles_cognitivos / controles_funcionales_iadl | 20 / 10 |
| valoraciones_psicopedagogicas | 20 |
| valoraciones_petersen_originales / soportes_valoracion_petersen | 90 / 150 |
| evaluaciones_expertas / evaluacion_criterios | 10 / 60 |
| evaluaciones_reglas, incluidas condiciones tipadas | 1000 |
| hechos_instantanea_experta | 3900 |
| activity_log | 110 |

Natalia conserva exactamente sus cinco residentes actuales. Jorge Salinas
Quispe tiene el escenario PET-01; Teresa Aguilar Rojas y Hugo Romero Torrico,
PET-02. Se verificó la recuperación SQL de los tres resultados bajo los permisos
y asignaciones reales de Natalia. La entrada al apartado Sistema experto
abre el último resultado guardado. El historial permite elegir otra evaluación;
«Volver a la consulta actual» mantiene el acceso a las fuentes actuales, sin
reescribir ni recalcular el resultado conservado.

La carga inicial no modificó el frontend; la corrección de entrada posterior
se describe abajo. Las limitaciones generales de CI del corte
[publicado el 19/11](../sistema/PUBLICACION_2026_11_19.md) siguen separadas de
estas pruebas dirigidas. Los resultados técnicos no acreditan evaluación en
personas ni aprobación clínica.

## Corrección de la entrada normal

La prueba inicial de carga abría un enlace que incluía `resultadoGuardado`.
Eso acreditaba el guardado y la recuperación, pero no la entrada desde el botón
normal. La incidencia comunicada por la propietaria reprodujo esa diferencia:
la página entraba al lector anterior y Enfermería no mostraba el historial
institucional en ese estado, aunque el resultado existiera.

Se corrigió la selección inicial y la visibilidad del historial existente.
No se inventan resultados cuando no hay evaluaciones, no se selecciona un
perfil favorable por encima de un resultado posterior, no se sustituye un
resultado corrupto por otro silenciosamente y se conserva la autorización
del residente y de las fuentes al leer.

La regresión cambió el acceso de la prueba al enlace normal sin parámetros y
falló antes de la corrección. Tras el parche: **4 pruebas, 182 aserciones** en
SQLite y **4 pruebas, 182 aserciones** en PostgreSQL temporal, sin errores,
fallos u omisiones. Cubren entrada al último resultado, selección explícita,
consulta actual, ausencia de resultados, acceso ajeno, corrupción y perfil
positivo desde Enfermería. Build aprobado, con el aviso previo de tamaño de
bundle. Se comprobó además la ventana real de Chrome con la sesión de Natalia:
la dirección normal de Jorge mostró «Posible DCL amnésico de dominio único»,
su fecha y los estados guardados de los cinco dominios.
