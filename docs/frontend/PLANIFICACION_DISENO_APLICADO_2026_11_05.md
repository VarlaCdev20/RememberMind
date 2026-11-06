# Planificación del equipo: diseño aprobado — 05/11/2026

## Referencia y alcance

La propietaria aprobó aplicar la [propuesta visual](imagenes/planificacion-equipo-aprobada-2026-11-05.png) y pidió separar más las tarjetas y usar Nunito. La imagen de 1536×1024 tiene una cabecera compacta, filtros, barra temporal horizontal, calendario semanal por turno y un detalle lateral cercano a una cuarta parte del contenido. Se conserva la navegación real de RememberMind; no se copia un logotipo generado ni se convierten los datos ficticios de la imagen en registros.

Se usan `collection-header`, `filter-bar`, `field`, botones y modal existentes. `team-planning.css` es un patrón del Design System limitado a `.rm-planning`, con tokens institucionales y Nunito ya instalada. No se modifica la fuente global de otras pantallas. El área clara café grisáceo, salvia, bordes suaves y selección verde se trasladan al calendario, sin nuevos indicadores de asistencia o dotación.

## Implementación e integración

- Semana: siete días y filas de los turnos reales, con sus horas. No se fijan Mañana/Tarde/Noche ni horas del raster como reglas de la institución. Las asignaciones históricas con turno inactivo siguen visibles cuando corresponden a la consulta.
- Tarjetas con borde, separación de 12–14px, nombre, función visual y horario. En las columnas de escritorio, nombres largos ocupan hasta dos líneas y la función puede abreviarse visualmente: nombre/función/horario completos están en la etiqueta accesible, ayuda nativa y panel de detalle. En móvil hay más ancho para mostrar el texto.
- Cada celda o día muestra hasta tres tarjetas y acceso a la lista completa cuando hay más. Los estados vacíos dicen que no hay asignaciones registradas; no demuestran insuficiencia de personal.
- La selección es solo lectura, explícita y por código de asignación. El detalle muestra persona, área, fecha y ambos extremos del horario, incluido el cambio de día. No selecciona a la primera persona por defecto. Al filtrar o cambiar de vista se descarta un detalle que dejó de pertenecer a la consulta.
- El panel permite **Gestionar horarios** mediante el componente existente del trabajador y **Ver cobertura del área** con fecha y área. Se usa este nombre en lugar de prometer una edición directa que no existía en el raster. La nueva asignación reutiliza el formulario y sus controles actuales. El enlace de avisos se conserva cuando la ruta está instalada.
- Se mantienen Mes y Lista. En 1440px hay detalle lateral; en anchos intermedios pasa debajo. En contenido menor a 850px la semana se presenta como agenda por día; los filtros de móvil se distribuyen en dos columnas tras la búsqueda. No se comprimen siete columnas de tarjetas en un teléfono.
- Selección por teclado, foco al detalle y retorno a la tarjeta, etiquetas accesibles, carga, movimiento reducido y acciones de al menos 44px. La selección no escribe asignaciones ni crea avisos.

## Corrección directamente relacionada

La consulta gerencial cargaba todas las asignaciones históricas para renderizar una semana. Ahora limita el período más los días adyacentes necesarios para cruces de medianoche; la variante Superadmin mantiene el alcance de su consulta. No se añaden índices ni cambios de esquema.

El comparador visual anterior exigía igualdad del día de semana y omitía cruces de una guardia nocturna con la mañana siguiente. Se reemplaza por intervalos fechados concretos, con fin al día siguiente cuando corresponde y sin considerar solapamiento un fin contiguo. La validación del guardado sigue en la Action existente; no se inventan límites de descanso ni reglas clínicas. Se elimina el comparador obsoleto que quedó sin consumidores.

## Seguridad y persistencia

La selección exige cuenta activa, lectura de turnos/jornadas/asignaciones y exclusión de Familiar. Solo admite asignaciones activas de la semana y filtros visibles; el código seleccionado está bloqueado contra escritura de propiedades Livewire. No concede `turnos.asignar`: quien solo lee puede ver el detalle, pero no obtiene edición. La revocación de lectura se revalida. Los formularios siguen usando la autorización/backend existentes.

No hay nuevas tablas, columnas, relaciones, permisos, servicios externos ni cambios del motor experto. La dirección de correo protegida no se modifica. Solo se altera la presentación y consulta del módulo Institucion.

## Evidencia

- SQLite: `GerenciaPlanificacionSeguridadTest`, **14 pruebas y 77 aserciones aprobadas**.
- PostgreSQL desechable: misma suite, **14 pruebas y 77 aserciones aprobadas**.
- Positivos/negativos: consulta, detalle sin escrituras, filtros, período ajeno, revocación de permisos, lectura sin edición, cruce nocturno, fin contiguo, restricciones de horarios y rollback existentes.
- E2E de Blade/Livewire con SQL y datos sintéticos: aprobado en 1440, 1280, 1024, 768 y 390px. Semana, mes, filtros, separación entre tarjetas, Nunito cargada, selección por teclado, foco/retorno, nueva asignación y acceso a horarios existentes. Sin errores JavaScript o respuestas HTTP fallidas. Se inspeccionaron capturas de escritorio, agenda y detalle móvil.
- Build Vite aprobado. Se mantienen advertencias de tamaño de assets de la aplicación; no se realiza una optimización global.
- Suite ampliada con `TurnoCompletoEnfermeroTest`: **16 aprobadas y una fallida** en ambos motores. El fallo de `test_flujo_completo_de_turno_de_enfermero_con_alcance_estricto` ocurre en la consulta de Alertas al esperar el texto «Alertas clínicas» (línea210); esa ruta devuelve HTTP200 y no ejecuta este calendario. No se debilita la prueba ni se modifica Alertas dentro del rediseño. Queda pendiente revisar el contrato de ese título. No se declara CI completa aprobada.

Las capturas usan cuentas y personas ficticias con la contraseña ya configurada. El hook de datos existe exclusivamente en el servidor de ensayo desechable. No se cambiaron asignaciones institucionales ni se enviaron correos reales.

Evidencia local: `output/planificacion-diseno-*20261105*` y `output/planificacion-diseno-20261105/`. La entrega excluye los cambios concurrentes de mapas, documentación, planificación de avisos, Alertas y tablas de personal.
