---
title: "Recordarme: implementación, aprobación e instalación"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SQLITE_POSTGRES_BROWSER_AND_APPROVED_LOCAL_INSTALLATION
runtime_verified: true
---

# Recordarme — 26/10/2026

**Implementado, probado en SQLite/PostgreSQL desechables y activado en la
instalación local aprobada.** La propietaria respondió «Sí, autorizo la columna
y los 30 días» a la propuesta concreta. La [decisión estructural registrada](../base-de-datos/DECISION_RECORDARME_2026_10_26.md)
autoriza esta extensión sin ampliar su alcance a otros cambios.

## Causa y contrato

El checkbox enviaba `remember`, pero `User::getRememberToken()` siempre
devolvía NULL y `setRememberToken()` descartaba el dato. `usuarios` no tiene
la columna requerida en su migración vigente ni en el diccionario congelado.
El guard no podía restaurar sesión desde una cookie persistente.

Se reutiliza el mecanismo nativo de Laravel 13/Fortify. No se guarda la
contraseña en una cookie ni se crea un almacén de credenciales paralelo.
Cookie cifrada por el middleware web, HttpOnly, SameSite=Lax y Secure cuando
el entorno HTTPS lo configura. Una cuenta inactiva no se restaura. La
autenticación recuperada conserva las Policies, permisos y scopes existentes.

Duración aprobada: **30 días (43.200 minutos)**. No sustituye el timeout de sesión ordinaria; la cookie
permite reautenticar al desaparecer esa sesión mientras siga siendo válida.
Cerrar sesión invalida el token anterior. Cambiar/restablecer contraseña o
modificar el estado mediante el modelo también lo rota. Rehabilitar la cuenta
no vuelve válida una cookie previa a su desactivación. El comportamiento de
recuperación y las claves de correo no se alteran.

## Extensión exacta aprobada e instalada

| Tabla | Columna | Tipo | NULL | Predeterminado | Índice/FK |
|---|---|---|---|---|---|
| `usuarios` | `remember_token` | VARCHAR(100) | Sí | NULL | Ninguno |

Una columna técnica de autenticación, cardinalidad un token de recuerdo por
cuenta según el mecanismo nativo. Los dispositivos de una cuenta comparten el
token: cerrar sesión lo invalida también para otras cookies persistentes de
esa cuenta. No implica borrar todas las sesiones ya abiertas. No añade tablas,
modifica PK/FK ni toca expedientes clínicos. La cantidad de tablas se conserva;
no se hizo un nuevo inventario global para este cambio.

DDL de referencia: `database/propuestas/recordarme/propuesta.sql`.
Migración reversible registrada:
`database/migrations/2026_10_26_000001_add_remember_token_to_usuarios.php`.
Se trasladó desde propuestas después de la autorización. La instalación
específica dejó las filas existentes con token NULL, sin cambios en sus
contraseñas, identidades ni relaciones. Los tokens se crean al iniciar sesión
marcando la casilla.

## Implementación

| Archivo | Responsabilidad |
|---|---|
| `config/auth.php` | Flag desactivado por defecto para instalaciones sin preparar; activado expresamente en la local aprobada; 30 días; proveedor activo |
| `app/Models/User.php` | Getter/setter condicionado al flag, token oculto y rotación ante contraseña/estado |
| `app/Modulos/Identidad/Infraestructura/Autenticacion/ProveedorUsuariosActivos.php` | Extiende Eloquent; deniega recuperación persistente con flag apagado o cuenta inactiva |
| `app/Providers/FortifyServiceProvider.php` | Registra el proveedor preservando el flujo Fortify existente |
| `resources/views/auth/login.blade.php` | Casilla visible al habilitar la función, con `remember=1` |
| `tests/Feature/RecordarmeTest.php` | Integración del guard, cookie, invalidación y migración reversible |

El token no está en `$fillable`, está en `$hidden` y se excluye de la auditoría
por la lista explícita de atributos ya existente. Su valor nunca se escribe en
documentación ni logs. La cookie no concede permisos nuevos ni firma clínica.

## Verificación ejecutada

```powershell
php artisan test --filter='RecordarmeTest|AuthenticationTest|PasswordResetTest|RecuperacionContrasenaFlujoTest|CorreoNoConfiguradoRespuestaTest|PasswordConfirmationTest|UpdatePasswordTest|BrowserSessionsTest|TwoFactorAuthenticationSettingsTest' --compact
```

**46 pruebas correctas, 282 aserciones; 3 omitidas de doble factor deshabilitado**,
SQLite `:memory:`, sin tocar cuentas
institucionales ni enviar correo. Salida: `output/pruebas-recordarme-20261026.txt`.
La prueba carga la migración candidata únicamente dentro de esa base efímera.

Cobertura propia de Recordarme: restauración sin sesión previa, cookie HttpOnly,
SameSite, expiración de 30 días, Secure configurable, ausencia de cookie al no
marcar, credenciales incorrectas, logout, contraseña actualizada/restablecida,
cuenta inactiva, reactivación, token/cookie manipulados, serialización, auditoría,
convivencia con esquema anterior, extensión sobre filas pobladas y reversión.
PostgreSQL desechable: `php tests/Support/EjecutarRecordarmePostgres.php`;
**10 pruebas, 48 aserciones** correctas, incluyendo migración poblada y
reversión. Se crea un runner sin privilegios globales y una base nuevos con
nombres aleatorios; ambos y su configuración temporal se eliminan al terminar.
Evidencia: `output/pruebas-recordarme-postgres-20261026.txt`.

Navegador: `node --test tests/Frontend/recordarme-e2e.test.js` correcto. Marca
la casilla en el formulario real, autentica contra Fortify, cierra el navegador
y abre otro conservando **solo la cookie persistente**, sin cookie de sesión
ni contraseña. Confirma restauración, cierra sesión y prueba que reutilizar la
cookie antigua ya no autentica. Todo con cuenta sintética/SQLite temporal y
red externa bloqueada; no cambia cuentas reales.

Comprobación institucional de solo lectura: conexión PostgreSQL
`remembermind_dev`, columna `remember_token` ausente y flag apagado antes de
la autorización. Después se ejecutó solo la migración expresamente aprobada,
mediante el procedimiento descrito en la sección siguiente.
Regresión adicional: `BrowserSessionsTest` pasa (1 prueba, 1 aserción).
`TwoFactorAuthenticationSettingsTest` omite sus 3 pruebas porque la función
está deshabilitada en la configuración vigente; no se declara validada.
Build correcto (4,22 s), con la advertencia existente de tamaño de chunk.
Regresión frontend final: **7 pruebas correctas, 0 fallos/omisiones (57,30 s)**,
con recorrido real en la base sintética y matriz de cinco anchos/claro/oscuro:

```powershell
node --test tests/Frontend/auth-access.test.js tests/Frontend/recuperacion-contrasena-e2e.test.js tests/Frontend/recordarme-e2e.test.js
```

Salida: `output/pruebas-recordarme-frontend-20261026.txt`. La suite PHP queda
aislada del flag local mediante `phpunit.xml`; los ensayos de Recordarme lo
habilitan expresamente sobre sus propias bases.

Revisión técnica local en fase de lectura, realizada por el mismo agente:
proveedor de cookies condicionado a ACTIVO y al flag, token fuera de
mass assignment/serialización/auditoría, cookie protegida por el middleware
web y guard nativo, sin cambios de permisos. No es una auditoría independiente.

## Instalación autorizada y rollback

1. Confirmar aprobación de columna y duración; comprobar destino y copia de
   seguridad institucional sin mostrar credenciales.
2. Ejecutar exclusivamente la migración propuesta mediante `--path`, sin
   migraciones ajenas ni reseteos. Verificar que las filas y las claves existentes
   no cambiaron y que la columna es nullable.
3. Habilitar `AUTH_REMEMBER_ENABLED=true` y `AUTH_REMEMBER_MINUTES=43200` en la
   configuración local secreta sin sobrescribir `.env`; refrescar caché de config.
4. Comprobar login marcado, recuperación con cookie, logout y rechazo de cookie
   revocada. No usar contraseñas reales en scripts o logs.
5. Rollback: apagar primero el flag y refrescar configuración. Revertir solo la
   migración de esta extensión tras autorización; elimina únicamente los tokens
   persistentes, conservando cuentas, hashes y relaciones. Las cookies antiguas
   dejarán de restaurar sesiones.

Instalación ejecutada:

```powershell
php work/instalar-recordarme-20261026.php --aprobado-20261026
```

El instalador exige entorno local, PostgreSQL en loopback y destino exacto
`remembermind_dev`. Usa transacción y bloqueo de `usuarios` con timeout de
5 segundos; ejecuta únicamente la migración aprobada. Compara el contenido
completo de las cuentas excluyendo la nueva columna y las definiciones de
PK/FK/UNIQUE antes y después; una diferencia aborta la transacción.

Resultado: **133 cuentas antes/después; contenido preservado; PK/FK/UNIQUE
preservadas; 116 tablas antes/después**. Se verifica VARCHAR(100) nullable.
Evidencia sin filas ni credenciales: `output/instalacion-recordarme-20261026.json`.
Después se modifican exclusivamente `AUTH_REMEMBER_ENABLED=true` y
`AUTH_REMEMBER_MINUTES=43200`, verificando que el resto de `.env` no cambie.
Se refresca la configuración sin modificar correo ni credenciales existentes.

La verificación HTTP de `/login` confirma casilla visible y valor explícito;
la comprobación de configuración confirma proveedor `ProveedorUsuariosActivos`,
flag activado, duración 43.200 minutos y columna instalada.

**Estado final: instalado y activado localmente con autorización específica.**
No hubo commit, push ni despliegue. El navegador personal de la propietaria
no se utilizó para introducir credenciales ni crear sesiones de prueba.
