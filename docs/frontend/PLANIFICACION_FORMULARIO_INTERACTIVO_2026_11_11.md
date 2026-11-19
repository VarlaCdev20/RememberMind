# Planificación y asignación interactiva — 11/11/2026

## Alcance entregado

- Tabla diaria y tabla de horarios del trabajador con componentes y estilos compartidos, fechas reales, área, horas, cambio de día y situación. En contenedores estrechos las filas se reorganizan sin superponerse.
- «Ver horarios» abre las asignaciones existentes. «Asignar» y «Nueva asignación» abren el formulario.
- Modal ampliado con selección del trabajador, datos del horario y vista previa de siete fechas por página. Los cambios de campos invalidan la vista previa anterior.
- Enfermería selecciona mañana, tarde o noche mediante tarjetas. El calendario representa el ciclo aprobado y las 24 horas de descanso posteriores a la noche.
- Los horarios ordinarios ofrecen turnos activos de ocho horas. Enfermería conserva los especiales del catálogo. La selección no modifica el catálogo.
- Atajos de días laborables, todos o ninguno para asignaciones ordinarias. Validación antes de la confirmación y del guardado. La vista previa no escribe asignaciones.

Se reutilizan `GestionarHorariosPersonal`, la autorización y los componentes existentes. Este ajuste no requiere migraciones, dependencias ni permisos nuevos. Las comprobaciones de cruces y descansos se ejecutan al guardar en la acción existente.

## Verificación ejecutada

- `php artisan test --filter='PersonalInstitucionalHorariosTest|RotacionEnfermeriaTest' --no-ansi`: 12 pruebas, 74 aserciones correctas con SQLite.
- `scripts/probar-postgres.ps1 -Filtro 'PersonalInstitucionalHorariosTest|RotacionEnfermeriaTest'`: 12 pruebas, 74 aserciones correctas en PostgreSQL 18.4 desechable. Informe en `output/planificacion-diseno-20261105/horarios-postgres.junit.xml`.
- Dos pruebas dirigidas de `GerenciaPlanificacionSeguridadTest`: 2 pruebas, 16 aserciones correctas sobre apertura de calendario y selección de solo lectura.
- `node --test tests/Frontend/planificacion-diseno-e2e.test.js`: correcto en 1440, 1280, 1024, 768 y 390 px. Comprueba filtros, teclado, tabla, consulta de horarios, selección de trabajador, vista previa y navegación. Sin errores JavaScript ni respuestas HTTP fallidas.
- `npm run build`: correcto. Conserva la advertencia de tamaño de un bloque JavaScript existente.
- `git diff --check` sobre los archivos cambiados del flujo: correcto.

Las capturas `lista-*`, `horarios-tabla-*` y `formulario-interactivo-*` están en `output/planificacion-diseno-20261105`. Proceden de un servidor aislado con datos sintéticos. El navegador de la sesión de la propietaria está deshabilitado para esta ejecución, por lo que no se acredita inspección de su pestaña real.

## Uso

1. Abrir Planificación del equipo y pulsar nueva asignación.
2. Elegir trabajador, área, tipo de asignación y turno. En Enfermería rotativa, elegir el turno inicial y el final del período.
3. Pulsar «Ver calendario» y recorrer las fechas previstas. Si se cambian los datos, generar de nuevo la vista previa.
4. Pulsar «Crear asignación» y confirmar. Los conflictos de las fechas seleccionadas se comunican antes de completar el guardado.

## Integración pendiente

Los archivos de planificación ya contenían cambios de trabajos anteriores y el árbol incluye numerosos cambios ajenos a este ajuste. No se crea un commit parcial que omita dependencias ni se publica el conjunto sin revisar su separación.

## Ajuste del buscador de trabajador

La selección comienza con un buscador compartido y muestra coincidencias después de escribir dos caracteres. Acepta nombre completo, apellidos, correo y rol, con un máximo de ocho resultados visibles y aviso para precisar la consulta. Cada fila permite reconocer el nombre, rol y correo antes de seleccionar. El backend conserva la restricción a roles de personal ya admitidos por el selector y precarga el perfil para evitar consultas individuales.

Verificado con dos pruebas dirigidas de búsqueda y calendario (20 aserciones), compilación correcta y la prueba de navegador aislado en los cinco anchos. Incluye búsqueda sin coincidencias, nombre completo y apertura del formulario desde el resultado.
