# Historias residenciales de La Paz, 2025–2026

## Contenido y uso

Carga optativa, separada de `DatabaseSeeder`, con 48 residentes, sus familias y un equipo interdisciplinario. Los datos se conservan en las tablas V2 existentes; las historias se crean mediante preadmisión y admisión formal con cama. No hay migraciones, reemplazos ni limpieza de los registros instalados.

Periodo de referencia: 1 de enero de 2025 a **20 de octubre de 2026, 20:00, America/La_Paz**. Las admisiones comienzan el 6 de enero de 2025 y se distribuyen hasta junio de 2026. Cada historia tiene una fecha inicial propia; no se registran cuidados anteriores al ingreso. El conjunto supera ampliamente 10.000 filas relacionadas; no son 10.000 personas distintas.

Las identidades, números de documento, domicilios particulares, matrículas y trayectorias son **sintéticos**. Las zonas de La Paz son reales. No se incorporaron expedientes hospitalarios de personas reales. Los datos visibles no contienen las palabras «demo» ni «prueba». La procedencia sintética queda registrada en esta documentación, el manifiesto privado y los documentos generados.

## Comando independiente

Desde la raíz del proyecto, con el entorno local y PostgreSQL configurados:

```powershell
php database/seeders/LaPaz/ejecutar.php --base=RememberMind
```

Sustituir `RememberMind` por el nombre **exacto** de la conexión local en otra instalación. El comando comprueba esa identidad, exige conexión local y utiliza `pg_dump` de PostgreSQL 18 para crear un respaldo antes de insertar. Deben existir los roles y permisos de la instalación normal. No crea cuentas de reemplazo ni sincroniza permisos existentes.

En esta instalación la base es `remembermind_dev`. La comparación de huellas
de una base poblada puede superar los 128 MB de PHP; se permite ampliar la
memoria únicamente para el proceso de carga, sin cambiar `php.ini`:

```powershell
php -d memory_limit=1024M database/seeders/LaPaz/ejecutar.php --base=remembermind_dev
```

Si ya existen los dos turnos por su nombre exacto, se reutilizan únicamente
cuando horario, orden y estado coinciden. El tipo de estudio «Glucemia de
laboratorio» se reutiliza por nombre exacto, categoría, estado y requisitos de
componentes/informe; su descripción se conserva. Se mantienen sus PK reales
en las nuevas referencias. Una incompatibilidad revierte la carga; no se
renombra ni actualiza el catálogo existente para forzarla.

Los contadores técnicos `secuencias_codigos` sí deben avanzar al admitir
residentes mediante la Action institucional. El control de conservación
comprueba separadamente que no desaparezcan ni retrocedan y que cada avance
de las entidades permitidas corresponda a códigos nuevos realmente insertados.
No se exceptúan las filas clínicas, administrativas, de permisos o de conocimiento
de la comparación estricta de sus huellas.

La [instalación en `remembermind_dev` del 21/10/2026](../../../docs/sistema/CARGA_LA_PAZ_LOCAL_2026_10_21.md)
documenta la integración, adaptación de catálogos existentes, respaldo y
verificación de esta base. La evidencia de `VERIFICACION.md` corresponde a
la instalación original de la rama `SEEDERS`.

Para revisar una cohorte ya cargada:

```powershell
php database/seeders/LaPaz/ejecutar.php --base=RememberMind --verificar
```

Repetir el comando de carga verifica la cohorte sin volver a insertarla, cambiar contraseñas ni sobrescribir archivos. Una carga incompleta produce un error; no se completa silenciosamente ni elimina historia. La comprobación estricta acredita el conjunto original: si después se agregan registros asistenciales a estos residentes, los conteos pueden superar los esperados y requieren revisión, no borrado.

`php artisan db:seed` sigue ejecutando exclusivamente la carga normal del proyecto. Esta cohorte no se invoca desde el arranque, las migraciones, el planificador. El seeder también tiene una guarda propia para impedir invocaciones accidentales fuera de local/testing o contra una base no autorizada.

## Archivos

| Archivo | Responsabilidad |
|---|---|
| `ejecutar.php` | Comando explícito, respaldo y comprobación de conservación |
| `HistoriaResidencialSeeder.php` | Construcción transaccional y verificación de historias |
| `ConservacionDatos.php` | Comparación de huellas de todas las filas preexistentes con PK |
| `datos/personas.php` | Identidades, ingresos, perfiles y antecedentes individuales |
| `datos/ubicaciones.php` | Zonas y macrodistritos paceños |
| `FUENTES.md` | Investigación, decisiones de generación y límites |
| `VERIFICACION.md` | Evidencia técnica de la ejecución de esta versión |

Los archivos generados quedan en `storage/app/private/la-paz-2025-2026/`: expedientes, informes vinculados, manifiesto, resultado de la carga y respaldo anterior. No se publican en Git. Las contraseñas se generan con aleatoriedad criptográfica, se guardan mediante hash en `usuarios` y se entregan únicamente en el archivo privado `credenciales.json`; nunca se imprimen en consola. Los correos usan un dominio reservado no entregable para evitar mensajes a personas reales.

## Relaciones y variedad

Se conserva la cadena preadmisión → revisión → aprobación → admisión → residente → ocupación → contacto/consentimiento. La carga incluye jornadas y asignaciones, antecedentes, diagnósticos conocidos, indicaciones, atenciones, notas, signos, sueño, ingesta, hidratación, eliminación, movilidad, dolor, controles cognitivos descriptivos, planes y ejecuciones de cuidado, prescripciones/horarios/administraciones, valoraciones profesionales, respuestas de Barthel, actividades, visitas, estudios/resultados/informes, derivaciones e incidentes con heridas y sus curaciones cuando corresponden.

Las doce trayectorias comprenden hipertensión, diabetes, hipotiroidismo, artrosis con apoyo para la marcha, dificultades observadas de orientación, cambios de ingesta/peso, evolución de autonomía, sueño interrumpido, dolor musculoesquelético, participación variable, lesión superficial y seguimiento posterior a una caída. Se combinan con antecedentes adicionales explícitos. Cada persona tiene identidad, ingreso y serie longitudinal propios. Una categoría o una medición numérica puede repetirse legítimamente; se evita duplicar la misma fila o copiar una identidad completa.

Las columnas no aplicables conservan `NULL`: por ejemplo, glucemia no medida, una reacción adversa ausente, suspensión de una orden activa, respuesta todavía no evaluada, o un dato documental sin fuente. No se inventan anomalías, documentos ni efectos para rellenar toda celda. Las FK y campos obligatorios sí se completan conforme al esquema.

## Sistema experto

La carga genera **fuentes operativas**, no salidas del experto: no incorpora versiones, reglas, pesos, criterios, evaluaciones, trazas, resultados ni etiquetas de deterioro cognitivo/demencia. Conserva intacto cualquier conocimiento ya instalado. Las clasificaciones e interpretaciones automáticas de las aplicaciones y resultados de estudio quedan vacías; la suma aritmética de Barthel conserva sus respuestas originales.

Sirve para recorrer el sistema, revisar consultas, relaciones, volumen y adquisición de evidencia. No constituye un estudio epidemiológico representativo ni datos de entrenamiento clínicamente validados. Una evaluación predictiva posterior debe separar residentes completos entre conjuntos, conservar el orden temporal y obtener etiquetas independientes de profesionales; dividir filas del mismo residente entre entrenamiento y evaluación produciría fuga de información.

## Seguridad y recuperación

La carga utiliza una transacción y bloqueo exclusivo por cohorte, mantiene activas las FK y no cambia el esquema. Compara por hash las filas anteriores y rechaza inserciones en tablas fuera de una lista operativa explícita. Un error revierte las escrituras de la operación y retira solamente los archivos nuevos creados por ella. Los roles conservan sus permisos existentes: Enfermería administra cuidados y medicación; Medicina prescribe; Familiar accede mediante el vínculo correspondiente.

El respaldo `respaldo-*/anterior.dump` contiene la base anterior y permanece en almacenamiento privado. Restaurarlo es una operación distinta y destructiva que no realiza este comando. No ejecutar `migrate:fresh`, `db:wipe` ni borrados para repetir la carga.

## Verificación reproducible

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter=LaPazSeedIntegrityTest
./scripts/probar-postgres.ps1 -Filtro LaPazSeedIntegrityTest
php vendor/laravel/pint/builds/pint database/seeders/LaPaz tests/Feature/LaPazSeedIntegrityTest.php --test
```

Las extensiones `pdo_sqlite`/`sqlite3` se habilitan solo para ese proceso si el PHP local las tiene desactivadas. El runner PostgreSQL crea y elimina su propio cluster desechable; nunca apunta los tests a la base instalada. No hay cambios frontend: build y QA visual no aplican a esta entrega.
