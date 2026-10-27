# Resumen de Enfermería por período y residente

Entrega local del 27/10/2026, America/La_Paz. Alcance solicitado: resumen por período y residente, vista previa y PDF. Sin nuevas tablas, migraciones, permisos, reglas clínicas ni escrituras en expedientes institucionales.

Actualización de revisión para publicación: el [corte de integración](../sistema/REVISION_PUBLICACION_2026_10_27.md)
añade verificación de `ReporteEnfermeriaPeriodoTest` en PostgreSQL desechable:
9 pruebas y 52 aserciones correctas. Los resultados y el estado Git descritos al
final de esta entrega corresponden a su ejecución anterior; la publicación
posterior no cierra los fallos ajenos de agenda.

## Cómo se utiliza

1. Entrar con la cuenta de Enfermería y abrir **Reportes de enfermería** en el menú.
2. Elegir **Desde**, **Hasta**, **Residente** y, opcionalmente, un tipo de registro. Inicialmente se consultan los últimos siete días, incluido hoy.
3. Pulsar **Aplicar filtros**. Cambiar un campo todavía no cambia los enlaces de exportación: corresponden siempre a los últimos filtros aplicados correctamente.
4. Leer las cantidades por categoría y los registros del período. La tabla muestra 20 registros por página; el gráfico desplegable muestra cantidades por día, no gravedad ni evolución clínica.
5. Abrir **Vista previa** para revisar el documento en otra pestaña. **Descargar PDF** genera el documento A4 horizontal con todos los registros coincidentes, no solo la página visible.

Las fechas inicial y final se incluyen completas. El día posterior queda fuera. No se admite fecha final futura, rango invertido ni tipo de registro desconocido. Si no hay registros se muestra un estado vacío, sin afirmar que el residente está sano.

## Fuentes y significado

| Categoría | Fuente | Fecha que organiza el reporte | Contenido |
|---|---|---|---|
| Cuidados | `ejecuciones_cuidado` e intervención | Ejecución; si falta, programada | Intervención, estado, observación y motivo de omisión |
| Controles | `signos_vitales` | Fecha del control | Valores registrados con unidades; NULL no se convierte en cero |
| Medicación | `administraciones_medicacion`, prescripción y medicamento | Administración; si falta, programada | Medicamento, resultado, dosis administrada cuando consta, observación, omisión, efecto y reacción |
| Incidentes | `incidentes` | Fecha del incidente | Tipo, descripción y estado |
| Alertas | `alertas` | Fecha de la alerta | Título, descripción, estado y responsable |

Cada fila muestra residente, fecha/hora y profesional o responsable registrado. Se identifica expresamente cuándo la fecha es programada. Un cuidado pendiente no se presenta como ejecutado. Se excluyen estados ANULADO/ANULADA. Las alertas no se cuentan como incidentes. La cantidad total es una cantidad de registros de fuentes distintas, no una cantidad de eventos clínicos únicos.

La medicación según necesidad con fecha de administración y sin programación sí se incluye. No se completa una dosis ausente a partir de la dosis prescrita. Los valores originales se representan mediante los casts vigentes de sus modelos, sin añadir interpretación clínica.

## Integración y seguridad

- Página: `admin.enfermeria.reportes`, `/admin/enfermeria/reportes`.
- Vista previa: `admin.enfermeria.reportes.preview`, `/admin/enfermeria/reportes/vista-previa`.
- PDF: `admin.enfermeria.reportes.pdf`, `/admin/enfermeria/reportes/pdf`.
- El acceso individual anterior `admin.enfermeria.pacientes.ficha.pdf` conserva su URL y usa la misma consulta autorizada. Se eliminó su plantilla obsoleta sin consumidores activos.
- Se reutiliza `TurnoEnfermeriaService`: alcance institucional vigente del usuario y sus asignaciones. La lectura de una jornada cerrada sigue disponible dentro de la ventana de consulta existente; este trabajo no amplía esa ventana a todas las asignaciones históricas.
- Todas las entradas revalidan cuenta activa, personal activo salvo lectura global de Superadministración, permiso de dashboard y lectura de las cinco fuentes. Exportar requiere además `enfermeria.ver_ficha_paciente`, ya usado por el PDF individual.
- Un residente enviado fuera del alcance se rechaza; no se sustituye silenciosamente por «todos». Una fuente restringida rechaza el reporte completo, sin presentar una exportación parcial como íntegra.
- Filtros aplicados bloqueados en Livewire; el servidor revalida los parámetros de exportación. Blade escapa textos, incluidas observaciones y nombres. Respuestas privadas con `Cache-Control: no-store`.
- Activitylog registra generación exitosa del PDF, autor, fechas y tipo; no copia mediciones, observaciones ni el PDF al log.

## Arquitectura y archivos de esta entrega

| Archivo | Responsabilidad |
|---|---|
| `app/Modulos/Reportes/Aplicacion/Consultas/ConsultarReporteEnfermeria.php` | Autorización, filtros, alcance, consulta y representación común |
| `app/Modulos/Reportes/Presentacion/Livewire/Paginas/ReporteEnfermeria.php` | Filtros aplicados y paginación |
| `app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteEnfermeriaController.php` | Vista previa, PDF y auditoría |
| `app/Modulos/Reportes/Presentacion/Http/Controllers/FichaPacienteReporteController.php` | Entrada individual existente, con modelo residente resuelto por el binding vigente |
| `app/Modulos/Reportes/Presentacion/Rutas/web.php` | Nuevas rutas dentro del registro existente |
| `app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php` | Acceso al reporte desde el menú operativo de Enfermería |
| `app/Modulos/Reportes/Presentacion/Vistas/livewire/cuidados/reporte-enfermeria.blade.php` | Pantalla de filtros, resumen, distribución diaria y tabla |
| `app/Modulos/Reportes/Presentacion/Vistas/reportes/enfermeria-resumen.blade.php` | Documento común HTML/PDF mediante namespace modular |
| `app/Modulos/Reportes/Presentacion/Vistas/reportes/enfermeria-registros.blade.php` | Tabla compartida por pantalla y documento |
| `resources/frontend/styles/design-system/patterns/nursing-reports.css` | Ajuste acotado de geometría sobre tokens y componentes existentes |
| `resources/frontend/styles/design-system/index.css` | Importación del patrón |
| `tests/Feature/ReporteEnfermeriaPeriodoTest.php` | Regresión, autorización, filtros, PRN, paginación y PDF |
| `tests/Support/ServidorReporteEnfermeriaSintetico.php` | Router QA protegido, SQLite temporal y fixtures sintéticos |
| `tests/Frontend/reporte-enfermeria-e2e.test.js` | Navegador real, filtros, vacío, vista previa y descarga |

La consulta carga relaciones por eager loading. No incorpora SQL en Blade. Se reutilizan los modelos nominales V2. El nombre histórico del parámetro de la URL individual se conserva por compatibilidad; no introduce una entidad V1 ni una FK nueva.

## Verificación ejecutada

Entorno PHP: SQLite `:memory:`, sin base institucional. Entorno navegador: PHP en puerto efímero y archivo SQLite nuevo, validado en el directorio temporal. El router QA exige entorno testing y cabecera aleatoria; no se registra como ruta de producto. Los recursos externos se bloquean durante el ensayo.

```text
php artisan test --filter='ReporteEnfermeriaPeriodoTest|ReportesPortabilidadTest|ModuloEnfermeriaIntegralTest|test_reporte_individual_usa_signos_reales_y_genera_pdf|superadmin_consulta_residentes_ficha_alertas_registros_y_reportes_globales' --compact
19 pruebas satisfactorias, 105 aserciones.

node --test tests/Frontend/reporte-enfermeria-e2e.test.js
1 recorrido satisfactorio: cinco anchos, claro/oscuro, filtros, estado vacío, vista previa de 23 filas y PDF real.

npm run build
Satisfactorio. Continúa la advertencia existente sobre chunks mayores de 500 kB.
```

Se verifican cuenta/personal inactivos, permisos retirados, fuente restringida, residente ajeno por ambas rutas y por la URL individual, texto escapado, NULL frente a cero registrado, rango completo de días, programación frente a ejecución, incidentes originales, medicación PRN y conservación de todos los registros al paginar.

La ejecución ampliada que incluyó toda `SuperadminEnfermeriaSupervisionTest` produjo **22 pruebas satisfactorias y dos fallidas, 119 aserciones**. Fallan `superadmin_ve_la_agenda_global_de_distintos_turnos` y `enfermero_conserva_el_alcance_de_sus_pacientes_asignados`: la agenda no contiene los residentes esperados. Ambos llaman `AgendaTurnoService::generar`, fuera de las clases cambiadas del reporte. Se informa la incidencia sin debilitar sus pruebas ni modificar reglas de agenda. La prueba de consulta global de residentes/ficha/alertas/registros/reportes sí pasa.

Capturas y documento sintéticos: `output/reporte-enfermeria-20261027/`. Se inspeccionan capturas en móvil/escritorio y páginas renderizadas del PDF, además de comprobar su contenido. Las comprobaciones de reflow, labels, foco y tamaño de campos no constituyen una certificación integral WCAG ni aceptación de todas las pantallas de Enfermería.

## Límites y pendientes

- Vista previa y PDF usan la misma consulta, filtros y plantilla, pero se generan en solicitudes distintas. Si cambian registros entre ambas, cambia el resultado. No son una instantánea histórica firmada ni una nueva evaluación experta conservada.
- La consulta materializa los registros coincidentes para ordenarlos y exportarlos completos. No se midió capacidad con grandes volúmenes ni se implementó exportación masiva asíncrona; rangos extensos pueden aumentar memoria y tiempo.
- No se ejecutó esta entrega contra PostgreSQL. No hay DDL ni pruebas de concurrencia nuevas.
- El PDF es un resumen de registros de cinco categorías, no un expediente clínico completo ni un diagnóstico. Alimentación, hidratación, heridas y dolor conservan sus circuitos propios; no se muestran promedios ficticios de esas fuentes.
- No se cierra todo el módulo Reportes ni la agenda. Quedan visibles los dos fallos ampliados indicados.
- Sin commit ni push: árbol con modificaciones simultáneas ajenas, preservadas.
