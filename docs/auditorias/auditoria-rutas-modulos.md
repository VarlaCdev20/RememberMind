# Auditoría de Rutas y Módulos - RememberMind / Casa Amandita

**Fecha:** 01/06/2026
**Objetivo:** Conexión de módulos reales y limpieza segura de duplicidades sin afectar el código base ni la lógica de negocio.

## 1. Rutas Reales Funcionales Detectadas y Conectadas
Se han validado y conectado al sidebar las siguientes rutas Livewire / Controladores funcionales que antes estaban enmascarados o separados:
- `admin.actividades.participacion` (Livewire)
- `admin.actividades.reportes` (Livewire)
- `admin.voluntariado.voluntarios.index` (Vista y posibles componentes)
- `admin.voluntariado.asignaciones.index` (Vista)
- `admin.voluntariado.asistencia.index` (Vista)
- `admin.areas-institucionales.index` (Conectado a través del hub de Gestión Institucional)
- `admin.turnos-asignaciones.index` (Conectado a través del hub de Gestión Institucional)

## 2. Placeholders Pendientes y Renombrados
Se han dejado los siguientes placeholders temporalmente para los módulos que aún no tienen implementación real, pero se renombraron las URLs/nombres de ruta para evitar conflictos con los módulos Livewire:
- `admin.actividades.programacion-placeholder` (Mantenido porque no existe un panel de programación real separado)
- `admin.voluntariado.voluntarios-placeholder` (Comentado/renombrado)
- `admin.voluntariado.asignaciones-placeholder` (Comentado/renombrado)
- `admin.voluntariado.seguimiento-placeholder` (Comentado/renombrado)
- `admin.salud-seguimiento.*` (Toda la suite de salud sigue siendo placeholder, ya que está pendiente su integración global)
- `admin.familia-social.*` (Toda la suite de familia sigue siendo placeholder)
- `admin.roles-permisos.index` (Sigue siendo placeholder a la espera de Spatie Permission)

## 3. Resolución de Rutas Duplicadas
- **Actividades:** Existían tres rutas registradas dos veces (`participacion`, `programacion`, `reportes`). Se le ha dado prioridad a las de Livewire y se les agregó el sufijo `-placeholder` a las rutas falsas que solo usaban `Route::view`.
- **Voluntariado:** Las rutas base estaban duplicadas bajo los esquemas `admin.voluntariado.*` y los placeholders recién creados. El sidebar se ha corregido para apuntar a las rutas `.index` originales y se comentaron/renombraron los placeholders.

## 4. Estructuración Oficial del Voluntariado
- **Convención Recomendada:** `admin.voluntariado.*` y carpeta `resources/views/admin/voluntariado/`.
- **Estado Actual:** Actualmente la lógica y vistas residen en `resources/views/admin/voluntarios/` a pesar de que el prefijo web es `voluntariado`.
- **Acción a futuro:** En una futura limpieza destructiva, se debe trasladar el contenido de `voluntarios/` a `voluntariado/` y eliminar la carpeta `voluntarios/`.

## 5. Gestión Institucional (Panel Integrador)
La vista `resources/views/admin/gestion-institucional/index.blade.php` ha dejado de ser un simple placeholder y se ha convertido en un **HUB / Panel Central**.
Ahora integra botones de redirección directos a los módulos reales de:
- **Áreas Institucionales**
- **Turnos y Asignaciones**
- Además, deja un espacio reservado ("Próximamente") para Estados y Catálogos.

## 6. Carpetas Pendientes de Limpieza (Advertencia: NO BORRAR TODAVÍA)
- `resources/views/admin/voluntarios/` (Debe ser unificada con `voluntariado/`).
- `resources/views/admin/areas-institucionales/` y `resources/views/admin/turnos-asignaciones/` (Ambas carpetas son correctas y funcionales, pero se debe evaluar si se mantienen sueltas o se mueven a subcarpetas de `gestion-institucional`).

---
**Nota:** El sistema se encuentra estable, compilado y en funcionamiento sin duplicidades en el menú.
