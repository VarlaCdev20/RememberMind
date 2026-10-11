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
