# Cohorte de entrenamiento disponible en la aplicación local

Fecha: 22/10/2026. Alcance autorizado por la propietaria: «NECESITO QUE DE LOS 30 SEEDERS QUE CARGASTE SE MUESTREN EN EL SISTEMA EN LAS VISTAS Y EN LA APLICACION».

## Decisión y alcance

Esta indicación sucede a la entrega inicial, que cargaba únicamente bases desechables. Autoriza agregar los treinta residentes de entrenamiento a la instalación local y hacerlos consultables desde las vistas vigentes. No convierte sus datos en historias reales ni autoriza cambios de estructura, reglas, permisos, firmas o validación clínica.

La instalación utiliza PostgreSQL local `remembermind_dev` y ya tenía explícitamente habilitada la capacidad de simulación local para esa base. No se modificó `.env`, no se reinició la BDD y no se instalaron migraciones. Se conservan 100 tablas de negocio, sin consumir nuevas tablas del máximo aprobado.

Los residentes se identificaron inicialmente como **Residente de entrenamiento 01–30**. Por solicitud posterior de la propietaria, el 22/10 se sustituyeron esos rótulos por treinta nombres y apellidos ficticios naturales, como Carmen Salvatierra Rueda y Alfonso Bustamante Leiva. [Correspondencia, pruebas y preservación de datos](../auditorias/cohorte-nombres-2026-10-22/README.md). Los alias S01–S30 siguen identificando casos del manifiesto, no el nombre visible. No se asignan las mediciones generadas a Elena, Teresa ni a otro residente anterior. No se utilizan credenciales conocidas ni nombres de profesionales reales para los tres actores de procedencia del lote.

## Cómo abrirlos

Desde Psicología:

1. Abrir **Mis residentes**.
2. Buscar **entrenamiento**.
3. Abrir la ficha del residente elegido.
4. Abrir **Evaluación cognitiva** y utilizar **Analizar evidencia** cuando corresponda.

Listado directo: [30 residentes de entrenamiento](http://127.0.0.1:8000/admin/psicologia/evaluaciones-asignadas?buscar=entrenamiento).

También están disponibles en el [listado de resultados del sistema experto](http://127.0.0.1:8000/admin/sistema-experto/residentes?buscar=entrenamiento), sujeto a sus permisos vigentes. La búsqueda inicial por URL se conserva y la paginación muestra doce residentes por página. Los controles de tabla/tarjetas existentes continúan funcionando.

| Residentes | Resultado orientativo esperado |
|---|---|
| Entrenamiento 01–03 | Posible DCL amnésico de dominio único |
| Entrenamiento 04–06 | Posible DCL amnésico multidominio |
| Entrenamiento 07–09 | Posible DCL no amnésico de dominio único |
| Entrenamiento 10–12 | Posible DCL no amnésico multidominio |
| Entrenamiento 13–18 | Cinco dominios P, sin patrón de DCL |
| Entrenamiento 19–30 | Requisitos negativos o abstenciones fundamentadas |

Los resultados se calculan desde las fuentes insertadas y los juicios originales sintéticos identificados; no se insertan las etiquetas finales para forzar el informe. El estado A/P/I es una entrada de ingeniería del contrato, no una firma clínica ni una clasificación automática producida por una puntuación aislada.

## Integración implementada

- `CohorteCognitivaInvestigacionSeeder`: conserva la carga transaccional existente y añade una entrada local optativa. Exige entorno local, base exacta, capacidad local configurada y confirmaciones explícitas de la herramienta CLI. La rama local no ejecuta `RolesAndPermissionsSeeder`, no activa conocimiento y exige que los contratos institucionales correspondientes ya estén presentes y activos.
- `CohorteCognitivaLocal`: reconstruye exclusivamente los cinco bindings nominales COH30 desde instrumentos y preguntas SQL, con versión `TEST-1`, códigos de entrada, orden, tipo, enunciado y estado exactos. Reutiliza los métodos del registro existente. No deduce una prueba por similitud del nombre.
- `CapturasInstrumentalesResidente`: incorpora esos bindings sólo para el residente del lote y en la base local autorizada. Conserva las Policies, permisos, corte, versiones, estados y controles de evidencia originales. No agrega perfiles a los residentes anteriores ni en producción.
- `ResultadosExpertoResidente`: añade un aviso visible de procedencia de entrenamiento; utiliza la misma evaluación institucional y el mismo motor existentes.
- `ResidentesSistemaExperto`: admite la búsqueda inicial `?buscar=entrenamiento` para abrir un listado filtrado y compartible.

Los perfiles dejan de depender de la configuración en memoria del seeder: una petición nueva puede consultar los registros SQL y ejecutar el motor. No se utiliza una página estática ni un HTML con resultados preescritos.

## Carga y preservación

Herramienta concreta:

```powershell
php scripts/cargar-cohorte-local.php remembermind_dev
```

Su alcance es exclusivamente local. Usa un bloqueo asesor PostgreSQL para evitar dos cargas simultáneas, realiza `pg_dump` privado antes de insertar y carga dentro de una transacción. Antes de confirmar compara huellas y cantidades de las filas asociadas a los residentes anteriores, sus respuestas instrumentales, reglas, versiones, permisos y migraciones. Una discrepancia revierte la operación.

La copia se guarda en `storage/app/private/auditorias-locales/cohorte30/`, excluido de Git. `carga.json` contiene cantidades, huellas y hash del respaldo; no publica textos clínicos. No se proporciona un comando de restauración destructiva ni se borra historia para repetir el lote.

Si los treinta residentes ya existen, la herramienta no vuelve a sembrarlos: verifica el lote existente. Un lote parcial o incompatible provoca error y no se elimina automáticamente. Verificación sin carga:

```powershell
php scripts/cargar-cohorte-local.php remembermind_dev --verificar
```

Después de confirmar SQL, la herramienta limpia la configuración temporal instrumental, recupera los treinta residentes desde SQL y ejecuta `PrepararEvaluacionInstitucional` como cuenta sintética explícita autorizada. Compara los cinco dominios, C02, estado y patrón con el manifiesto y guarda `verificacion.json` privado. No informa éxito si hay discrepancias. No atribuye estas consultas o juicios a una persona profesional real.

La autorización de lectura de Medicina, Enfermería y otras cuentas se mantiene intacta. Enfermería no recibe asignaciones ni permisos por cargar el lote. Las métricas de actividad propia de Psicología no se atribuyen a la psicóloga conectada: la procedencia de las aplicaciones sigue siendo el profesional sintético.

## Pruebas y límites

La aceptación comprueba carga optativa local sobre base desechable, preservación de gobierno instalado, recuperación de bindings con configuración vacía, exclusión en producción/base ajena/residente ajeno, treinta entradas en ambas listas, cuatro resultados mediante Livewire y aviso de procedencia. También mantiene duplicación, fallo parcial, rollback, reintento, fuente ajena y abstenciones.

Los resultados técnicos, comandos y cualquier fallo previo se registran en el [acta de esta integración](../auditorias/cohorte-aplicacion-local-2026-10-22/README.md). El build se ejecutó con `npm run build`. La automatización visual del navegador devolvió «Local browser use is disabled in Work settings»; la comprobación funcional se hizo con Livewire y consultas del coordinador, sin afirmar inspección visual.

La carga conserva fuentes SQL, no resuelve la conservación completa del DTO institucional: `persistido=false` y las restricciones de revisión sobre una evaluación transitoria continúan vigentes. No se crea un diagnóstico clínico confirmado ni se presenta la cohorte como validación con pacientes reales. Las [referencias científicas, matriz y límites normativos](COHORTE_INVESTIGACION_SEEDER_2026_10_22.md) siguen vigentes.

Sin `git add`, commit, push ni despliegue.
