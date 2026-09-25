# Auditoría de Roles y Permisos - RememberMind

### 1. Roles Existentes en Base de Datos
| Rol | Usuarios | Permisos | Estado / Diagnóstico |
|---|---|---|---|
| **SUPERADMINISTRADOR** | 0 | 3 | Creado por migración pero sin usuarios. Tiene 3 permisos residuales de enfermería. |
| **ADMINISTRADOR** | 0 | 0 | Nuevo rol institucional, pero sin permisos ni usuarios asignados. |
| **ENFERMEROS** | 1 | 0 | Nuevo rol institucional. ¡Peligro! Tiene 1 usuario pero 0 permisos. Recibirá errores 403 en todo lado. |
| **MEDICO GENERAL/GERIATRA** | 0 | 0 | Institucional. Sin permisos asignados. |
| **PSICOLOGO/A** | 0 | 0 | Institucional. Sin permisos asignados. |
| **PEDAGOGO** | 0 | 0 | Institucional. Sin permisos asignados. |
| **NUTRICIONISTA** | 0 | 0 | Institucional. Sin permisos asignados. |
| **FISIOTERAPEUTA** | 0 | 0 | Institucional. Sin permisos asignados. |
| **VOLUNTARIO** | 0 | 0 | Institucional (Renombrado por migración). Sin permisos asignados. |
| **FAMILIAR** | 0 | 0 | Institucional (Renombrado por migración). Sin permisos asignados. |
| **admin** | 1 | 108 | **Rol Obsoleto / Duplicado**. Sin embargo, concentra todos los permisos y al único usuario administrador. |
| **personal_salud** | 0 | 40 | **Rol Obsoleto**. Eliminado por migración pero recreado por el `RolesAndPermissionsSeeder`. |
| **personal_admin** | 0 | 61 | **Rol Obsoleto**. Eliminado por migración pero recreado por el seeder. |
| **voluntario** | 0 | 8 | **Rol Obsoleto / Duplicado**. Recreado por el seeder en minúsculas. |
| **familiar** | 0 | 4 | **Rol Obsoleto / Duplicado**. Recreado por el seeder en minúsculas. |

### 2. Tabla Resumen de Permisos por Módulo (136 en total)
- **Admisiones (2)**: `admisiones.ver_dashboard`, `admisiones.crear`
- **Adultos Mayores (7)**: `adultos.ver`, `adultos.crear`, `adultos.archivar`, etc.
- **Salud/Enfermería (30+)**: `enfermeria.ver_dashboard`, `salud.ficha.crear`, `valoracion_enfermeria.crear`, `signos_vitales.editar`, etc.
- **Turnos/Asignaciones (15+)**: `turnos.ver`, `asignacion_turno.asignar`, etc.
- **Documentos (15+)**: `documentos.ver`, `documentos_usuarios.validar`, etc.
- **Usuarios/Roles (25+)**: `usuarios.crear`, `roles.ver`, etc.

### 3. Permisos Faltantes o Inconsistentes
- **Enfermería:** Las vistas utilizan `@can('salud.valoracion.crear')` y `@can('turnos.asignar')`, pero el rol `ENFERMEROS` (que ya tiene un usuario) tiene **0 permisos asignados** en la Base de Datos.
- **Preadmisiones:** Se requieren y existen `admisiones.ver_dashboard` y `admisiones.crear`, y están correctamente aplicados en el frontend (`PreadmisionesPanel` y `web.php`).
- **Inconsistencia Crítica:** La migración `2026_06_03_020435_clean_legacy_roles` borró los roles en minúsculas y creó los institucionales (mayúsculas), pero el archivo `database/seeders/RolesAndPermissionsSeeder.php` nunca fue actualizado. Por lo tanto, si alguien corre `php artisan db:seed`, se vuelve a corromper la BD creando de nuevo los roles `admin`, `personal_salud` y dejándole 0 permisos a los roles nuevos.

### 4. Roles que deben Conservarse
Se debe avanzar estrictamente hacia el catálogo institucional (mayúsculas):
`SUPERADMINISTRADOR`, `ADMINISTRADOR`, `ENFERMEROS`, `MEDICO GENERAL/GERIATRA`, `PSICOLOGO/A`, `PEDAGOGO`, `NUTRICIONISTA`, `FISIOTERAPEUTA`, `VOLUNTARIO`, `FAMILIAR`.

### 5. Riesgos Técnicos si se eliminan roles ahora mismo
Si haces un `Role::where('name', 'admin')->delete();` en este instante:
1. Tu usuario administrador principal perderá acceso a la plataforma (bloqueado por 403 Forbidden).
2. Nadie podrá asignar permisos a los nuevos roles porque el panel de administración de roles estará inaccesible.
3. Se generarán datos huérfanos en `model_has_roles`.

### 6. Recomendación de Limpieza Segura (Roadmap sugerido)
**No apliques cambios hasta confirmar los siguientes pasos:**
1. **Refactorizar el Seeder (`RolesAndPermissionsSeeder.php`)**: Eliminar todas las referencias a `admin`, `personal_salud`, `voluntario`, etc., y reemplazarlas por los roles en mayúsculas, mapeando correctamente qué permisos va a tener cada uno de los 10 roles institucionales.
2. **Crear una Migración de Traspaso**: Mover los usuarios (como el enfermero) de sus roles antiguos/vacíos al nuevo rol correspondiente *después* de haber corrido el nuevo Seeder. Por ejemplo, pasar el usuario del rol `admin` al `SUPERADMINISTRADOR`.
3. **Limpieza Final**: Borrar de la BD permanentemente los roles en minúsculas y ejecutar `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()`.
