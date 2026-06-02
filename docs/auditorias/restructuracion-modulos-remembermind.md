# Reestructuración de Módulos - RememberMind

**Fecha:** 01/06/2026
**Objetivo:** Conectar lo funcional, eliminar duplicidad y establecer convenciones arquitectónicas claras sin duplicar CRUD ni romper el Expediente del Adulto Mayor.

## 1. Módulos Reales Detectados y Respetados
- **Usuarios & Autenticación:** `admin.usuarios.*`
- **Bitácora:** `admin.bitacora.index`
- **Expediente del Adulto Mayor:** `admin.adultos-mayores.*` con todas sus relaciones (`familiares`, `medicacion`, `signos-vitales`, etc.).

## 2. Decisiones Arquitectónicas Aplicadas

### A. Gestión Institucional
Se convirtió el placeholder de Gestión Institucional en un panel puente ("Hub").
- Conecta a los módulos reales de **Áreas Institucionales** (`admin.areas-institucionales.index`) y **Turnos/Asignaciones** (`admin.turnos-asignaciones.index`).
- El Personal de Salud y Personal Administrativo se enlaza de vuelta al módulo global de **Usuarios**, ya que son roles del sistema, evitando duplicar tablas o CRUDs.
- Cargos y Especialidades permanecen en estado "Próximamente", pero referenciando la existencia del modelo en la base de datos.

### B. Familia y Social
Se implementaron paneles informativos globales (`ficha-social`, `red-apoyo`). Se ha documentado estrictamente en la interfaz que la creación y gestión real de familiares ocurre DENTRO del Expediente Individual del Adulto Mayor. No se duplicará el CRUD de familiares aquí.

### C. Salud y Evaluación Geriátrica
Se mantuvieron los paneles globales de consulta, pero se establecieron advertencias en la UI indicando que la **Ficha Médica, Medicación y Signos Vitales** se registran desde la ficha clínica de cada residente.
- **Resultados Preventivos:** Permanece como placeholder seguro. Se confirma que es un motor futuro.

### D. Voluntariado
**Convención Oficial:** El módulo debe llamarse y enlazarse bajo `Voluntariado`.
- Se han conectado las rutas reales `.index` (`admin.voluntariado.voluntarios.index`, `admin.voluntariado.asignaciones.index`, `admin.voluntariado.asistencia.index`).
- **Advertencia de limpieza:** Existe una dualidad entre la carpeta física `admin/voluntarios/` y el prefijo de ruta `voluntariado`. La carpeta oficial que se consolidará en el futuro será `admin/voluntariado/`, pendiente a limpieza destructiva posterior.

### E. Actividades
El menú lateral ahora utiliza las rutas reales del módulo Livewire:
- `admin.actividades.index` (reemplazando el placeholder de programación)
- `admin.actividades.participacion`
- `admin.actividades.reportes`

## 3. Placeholders y Rutas Duplicadas Corregidas
- Las rutas `Route::view` que duplicaban módulos Livewire funcionales en `web.php` han sido renombradas con sufijos como `-placeholder` (ej. `programacion-placeholder`) o comentadas para evitar choques en el Name Router de Laravel.

## 4. Pendientes Backend
- Conversión de `AdultoMayorController@show` a componentes Livewire de carga asíncrona para mejorar la velocidad (Lazy Load).
- Implementación del motor de cálculo de riesgo (Resultados Preventivos).
- Refactorización de la carpeta física de Voluntarios.
