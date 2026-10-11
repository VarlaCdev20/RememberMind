# Desarrollo y pruebas seguras

El código canónico usa Laravel en la raíz, módulos en `app/Modulos` y motor
independiente en `motor-experto`. El entorno operativo existente y sus datos
no son fixtures. No sobrescribir `.env`, regenerar su clave ni cargar seeders
contra esa base para comprobar una modificación.

## Pruebas predeterminadas

```bash
composer test
composer test -- --filter=AuthenticationTest
npm test
npm run build
```

`composer test` ejecuta PHPUnit directamente. No arranca Artisan ni borra la
caché de configuración de la instalación operativa antes de las pruebas.
`phpunit.xml` fuerza entorno testing, una clave exclusiva de prueba, SQLite
`:memory:`, correo/cache/sesión en memoria y cola síncrona. El bootstrap aísla
las cachés de configuración y rutas y rechaza otra conexión antes del autoload.
`Tests\TestCase` comprueba la configuración resuelta antes de migraciones y
fixtures. Las cuentas y documentos de las pruebas son sintéticos.

La configuración predeterminada no acredita restricciones ni concurrencia de
PostgreSQL. La integración PostgreSQL requiere un perfil explícito y una base
desechable identificada; no cambiar variables para apuntar las pruebas a la
instalación operativa.

## Acceso de desarrollo

Usar el formulario de login con correo, contraseña y cuenta activa. No hay
una ruta de acceso automático a Enfermería. «Ver como rol» es presentación
de solo lectura y conserva la identidad real; no sirve para probar una
mutación con otra competencia profesional.

## Cierre de un cambio

Revisar diff y referencias; probar el contrato válido y negativos relevantes;
ejecutar el grupo afectado y build si cambia frontend. PostgreSQL para garantías
específicas de ese motor. Hacer commits de alcance propio, excluyendo secretos,
datos reales y artefactos de editor. Un build correcto no reemplaza el recorrido
de usuario ni una prueba clínica del experto.

## PostgreSQL desechable

En Windows con PostgreSQL 18 y PHP disponibles:

```powershell
./scripts/probar-postgres.ps1
```

El script crea un cluster temporal nuevo en loopback y puerto libre, una base
`remembermind_test_<8hex>_testing` y su `<base>_runner`, sin SUPERUSER/CREATEDB.
Ejecuta la cohorte de CI, conserva JUnit/log en la carpeta temporal, restaura las
variables de entorno previas, detiene el servidor y elimina sólo el cluster que
creó después de comprobar la ruta absoluta. No usa `.env` ni la base instalada.
`-PostgresBin`, `-Filtro`, `-Informe` y `-Log` permiten ajustar binarios, cohorte y
ubicación de evidencias; el filtro se pasa como argumento, no como shell generado.

Para un servicio PostgreSQL desechable ya provisionado (como CI), establecer
DB_HOST=127.0.0.1, DB_PORT, DB_DATABASE con ese patrón, DB_USERNAME=<base>_runner
 y DB_PASSWORD. Entonces:

```bash
php vendor/phpunit/phpunit/phpunit -c phpunit.postgres.xml --filter=Fase3ConcurrenciaCamaPostgresTest
```

El perfil exige destino propio antes del autoload y TestCase verifica identidad
real y privilegios antes de migrar. Activa los tests de locks. Nunca sustituir el
nombre por la base operativa para evitar las guardas. `composer test:postgres`
resuelve el mismo perfil; no provisiona un servidor. La cohorte está verificada,
no se atribuye esa ejecución a toda la suite PostgreSQL.

Las vistas de pruebas tienen una caché temporal distinta por proceso y se limpian
al terminar. CI escucha todos los pushes/PR, separa suite SQLite de integración
PostgreSQL y conserva evidencias. Su ejecución remota requiere publicar los commits;
la validación local del workflow no equivale a un run de GitHub Actions.

Dependencias: consultar DEPENDENCIAS_DESARROLLO.md. `npm audit --omit=dev` es estricto;
la auditoría completa conserva el advisory de build y el control temporal vence
2026-11-11, sin aceptar alertas nuevas, runtime afectado o informes fallidos.
