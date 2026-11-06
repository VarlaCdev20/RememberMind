# Áreas y equipo: vista compartida con Superadministración — 05/11/2026

La propietaria pidió traer a Gerencia la vista de Áreas de Superadmin. Ambas entradas ya utilizaban la misma ruta y componente Livewire, pero devolvían composiciones diferentes según el rol.

## Implementado

Se extrajeron cabecera, indicadores, filtros y tarjetas originales de Superadmin a `institucion::livewire.identidad.partials.areas-coleccion`. Las dos vistas incluyen esa misma composición; se evita copiar y mantener dos diseños. Se conserva el lenguaje cálido, los componentes del sistema y las acciones autorizadas. La cuadrícula responde en una, dos o tres columnas, los nombres largos pueden envolver y las acciones de tarjeta tienen objetivos de 44px. La búsqueda reutiliza `rm-filter-search` para separar el icono del texto.

Gerencia muestra los tres indicadores reales de áreas (total/activas/inactivas) y puede buscar por nombre o código. Conserva su ficha de equipo por fecha y jornada, su formulario y la transición de estado independiente de la edición. No presenta responsables permanentes ni infiere ausencia de personal desde una asignación histórica. Los indicadores y ficha históricos de Superadmin permanecen en su variante. La variante Gerencia conserva las acciones que ya ofrecía; no se incorporan por copia los modales analíticos de Superadmin.

Se eliminó del bloque compartido un aviso técnico que mostraba nombres de permisos y sugería ejecutar un seeder dentro del producto. Las acciones siguen condicionadas por permisos y protegidas por los controles backend existentes. No se cambian rutas, permisos, reglas institucionales ni estructura de BDD.

## Verificación

- SQLite: 24 pruebas, 106 aserciones aprobadas (`GerenciaAreasEquipoTest|AreasInstitucionalesV2Test`).
- PostgreSQL desechable: las mismas 24 pruebas y 106 aserciones aprobadas.
- Cobertura: equipo por fecha, revocación de lectura, desactivación con compromisos, edición sin cambio oculto de estado, filtros de tarjetas, reutilización en Superadmin, acceso sin permiso y exportaciones existentes.
- E2E Blade/Livewire con SQL y datos sintéticos: recorrido de filtros, tarjetas y ficha de equipo en 1440, 1280, 1024, 768 y 390px. Verifica reflujo, icono de búsqueda separado, ausencia de datos indefinidos y errores JavaScript/HTTP.
- Build Vite aprobado; inspección visual de escritorio y móvil. No se ejecuta CI completa ni se afirma una auditoría global de accesibilidad.
- Sin legacy ejecutable nuevo. La vista de Gerencia anterior sigue como contenedor de sus modales operativos; la colección se comparte. Los cambios concurrentes de mapas, documentación y planificación quedan fuera del commit.

Evidencias locales: `output/gerencia-areas-compartidas-*20261105*` y `output/gerencia-areas-compartidas-20261105/`.
