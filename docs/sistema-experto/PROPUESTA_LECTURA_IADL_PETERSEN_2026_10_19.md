# Lectura IADL para preparar Petersen

19/10/2026. **APROBADA por instrucción expresa de la propietaria: «PRIMERO HAS ESTO».**

La captura Petersen y sus tres permisos ya están aprobados. Su autorización no
concede automáticamente lectura de cada fuente vinculada. El permiso
`controles_funcionales_iadl.ver` se comprueba en el lector descriptivo existente
y se provisiona a Medicina/Psicología mediante un seeder acotado e idempotente.
El código mantiene esa comprobación; no la sustituye por el permiso de Petersen.

Se provisiona exclusivamente `controles_funcionales_iadl.ver` a los
roles MEDICO GENERAL/GERIATRA y PSICOLOGO/A, además de la consulta técnica de
Superadministración, con cuenta activa y Policy contextual del residente.
No autoriza crear, rectificar, borrar IADL, otros expedientes ni escribir por
Superadministración. No añade tablas, columnas ni otros permisos.

Esto permite seleccionar y consultar los originales IADL para C03 cuando
existan; el permiso por sí solo no acredita independencia instrumental.
El seeder general incorpora este único permiso para instalaciones nuevas; la
instalación existente usa `PermisosLecturaIadlPetersenSeeder` sin resincronizar
roles. La Policy exige el alcance contextual del residente y competencia
Medicina/Psicología, o consulta técnica de Superadministración. Si se revoca el
permiso, la consulta omite la fuente antes de leer sus filas. Aun con permiso,
C03 queda pendiente hasta contar con una valoración original y soportes aptos.

## Instalación y comprobación del 19/10/2026

Se confirmó `APP_ENV=local`, PostgreSQL en loopback, base `remembermind_dev` y
los tres roles de destino antes de ejecutar exclusivamente:

```powershell
php artisan db:seed --class=PermisosLecturaIadlPetersenSeeder --no-interaction
```

La consulta posterior confirmó lectura para Medicina, Psicología y
Superadministración, y ausencia del permiso en Enfermería, Gerencia y
Administración. El seeder no crea cuentas, residentes, controles IADL ni tablas.
La prueba de provisión idempotente pasó en SQLite; la matriz contextual y los
cuatro patrones Petersen pasaron en PostgreSQL desechable (8 pruebas, 47
aserciones). La clase completa del baseline de roles pasó en SQLite (15 pruebas,
111 aserciones). La autorización de IADL no representa una valoración clínica
original ni una clasificación C03 automática.
